<?php

if ( ! isset( $content_width ) ) $content_width = 520;

add_action( 'after_setup_theme', 'et_setup_theme' );
if ( ! function_exists( 'et_setup_theme' ) ){
	function et_setup_theme(){
		global $themename, $shortname, $et_store_options_in_one_row, $default_colorscheme;
		$themename = 'Explorable';
		$shortname = 'explorable';
		$et_store_options_in_one_row = true;

		$default_colorscheme = "Default";

		$template_directory = get_template_directory();

		require_once( $template_directory . '/epanel/custom_functions.php' );

		require_once( $template_directory . '/includes/functions/comments.php' );

		require_once( $template_directory . '/includes/functions/sidebars.php' );

		load_theme_textdomain( 'Explorable', $template_directory . '/lang' );

		require_once( $template_directory . '/epanel/core_functions.php' );

		require_once( $template_directory . '/epanel/post_thumbnails_explorable.php' );

		include( $template_directory . '/includes/widgets.php' );

		register_nav_menus( array(
			'primary-menu' => __( 'Primary Menu', 'Explorable' ),
		) );

		add_action( 'init', 'et_explorable_register_listing_posttype', 0 );

		add_action( 'wp_enqueue_scripts', 'et_explorable_load_scripts_styles' );

		add_action( 'wp_head', 'et_add_viewport_meta' );

		add_action( 'pre_get_posts', 'et_home_posts_query' );

		add_action( 'pre_get_posts', 'et_explorable_taxonomy_query' );

		add_filter( 'wp_page_menu_args', 'et_add_home_link' );

		add_filter( 'et_get_additional_color_scheme', 'et_remove_additional_stylesheet' );

		add_action( 'wp_enqueue_scripts', 'et_add_responsive_shortcodes_css', 11 );

		add_action( 'admin_enqueue_scripts', 'et_explorable_admin_scripts_styles' );

		add_filter( 'body_class', 'et_add_map_class' );

		add_filter( 'body_class', 'et_add_tablet_class' );

		add_action( 'wp_head', 'et_attach_bg_images' );

		// don't display the empty title bar if the widget title is not set
		remove_filter( 'widget_title', 'et_widget_force_title' );

		add_action( 'et_header_top', 'et_add_mobile_navigation' );
	}
}

function et_add_home_link( $args ) {
	// add Home link to the custom menu WP-Admin page
	$args['show_home'] = true;
	return $args;
}

function et_explorable_load_scripts_styles(){
	$template_dir = get_template_directory_uri();
	$protocol = is_ssl() ? 'https' : 'http';

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'Explorable' ) ) {
		$subsets = 'latin,latin-ext';

		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'Explorable' );

		if ( 'cyrillic' == $subset )
			$subsets .= ',cyrillic,cyrillic-ext';
		elseif ( 'greek' == $subset )
			$subsets .= ',greek,greek-ext';
		elseif ( 'vietnamese' == $subset )
			$subsets .= ',vietnamese';

		$query_args = array(
			'family' => 'Open+Sans:300italic,700italic,800italic,400,300,700,800',
			'subset' => $subsets,
		);

		wp_enqueue_style( 'explorable-fonts-open-sans', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	}

	if ( 'off' !== _x( 'on', 'Lobster font: on or off', 'Explorable' ) ) {
        $subsets = 'latin,latin-ext,cyrillic,cyrillic-ext';
		$query_args = array(
			'family' => 'Lobster',
			'subset' => $subsets,
		);

		wp_enqueue_style( 'explorable-fonts-lobster', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null );
	}

	// wp_enqueue_script( 'google-maps-api', 'http://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ), '1.0', false );
	// wp_enqueue_script( 'gmap3', $template_dir . '/js/gmap3.min.js', array( 'jquery' ), '1.0', false );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'tinyscrollbar', $template_dir . '/js/jquery.tinyscrollbar.min.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'superfish', $template_dir . '/js/superfish.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'fitvids', $template_dir . '/js/jquery.fitvids.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'custom_script', $template_dir . '/js/custom.js', array( 'jquery' ), '1.0', true );
	wp_localize_script( 'custom_script', 'et_custom', array(
		'toggle_text'		=> __( '<span>Toggle</span> List View', 'Explorable' ),
		'mobile_nav_text' 	=> esc_html__( 'Navigation Menu', 'Explorable' ),
	) );

	$et_gf_enqueue_fonts = array();
	$et_gf_heading_font = sanitize_text_field( et_get_option( 'heading_font', 'none' ) );
	$et_gf_body_font = sanitize_text_field( et_get_option( 'body_font', 'none' ) );

	if ( 'none' != $et_gf_heading_font ) $et_gf_enqueue_fonts[] = $et_gf_heading_font;
	if ( 'none' != $et_gf_body_font ) $et_gf_enqueue_fonts[] = $et_gf_body_font;

	if ( ! empty( $et_gf_enqueue_fonts ) ) et_gf_enqueue_fonts( $et_gf_enqueue_fonts );

	/*
	 * Loads the main stylesheet.
	 */
	wp_enqueue_style( 'explorable-style', get_stylesheet_uri() );

	if ( is_single() && 'listing' == get_post_type() ) {
		wp_enqueue_script( 'metadata', $template_dir . '/js/jquery.MetaData.js', array('jquery'), '4.11', true );
		wp_enqueue_script( 'et-rating', $template_dir . '/js/jquery.rating.pack.js', array('jquery'), '4.11', true );
		wp_enqueue_style( 'et-rating', $template_dir . '/css/jquery.rating.css' );
	}
}

function et_add_mobile_navigation(){
	echo '<div id="et_mobile_nav_menu">' . '<a href="#" class="mobile_nav closed">' . esc_html__( 'Navigation Menu', 'Explorable' ) . '</a>' . '</div>';
}

/**
 * Filters the main query on homepage
 */
function et_home_posts_query( $query = false ) {
	/* Don't proceed if it's not homepage or the main query */
	if ( ! ( is_home() && is_front_page() ) || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	$query->set( 'post_type', 'listing' );
	$query->set( 'posts_per_page', '-1' );
}

function et_explorable_taxonomy_query( $query = false ) {
	if ( ! ( is_post_type_archive( 'listing' ) || is_tax( 'listing_type' ) || is_tax( 'listing_location' ) ) ) return;

	if ( is_admin() || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	$query->set( 'posts_per_page', '-1' );

	if ( is_post_type_archive( 'listing' ) ) {
		$taxomony_query = array( 'relation' => 'AND' );

		if ( isset( $_GET['et-listing-type'] ) && 'none' != $_GET['et-listing-type'] )
			$taxomony_query[] = array(
				'taxonomy' => 'listing_type',
				'field' => 'id',
				'terms' => array( intval( $_GET['et-listing-type'] ) ),
			);

		if ( isset( $_GET['et-listing-location'] ) && 'none' != $_GET['et-listing-location'] )
			$taxomony_query[] = array(
				'taxonomy' => 'listing_location',
				'field' => 'id',
				'terms' => array( intval( $_GET['et-listing-location'] ) ),
			);

		if ( ( isset( $_GET['et-listing-type'] ) && 'none' != $_GET['et-listing-type'] ) || ( isset( $_GET['et-listing-location'] ) && 'none' != $_GET['et-listing-location'] ) )
			$query->set( 'tax_query', $taxomony_query );

		if ( isset( $_GET['et-listing-rating'] ) && 'none' != $_GET['et-listing-rating'] ) {
			$query->set( 'meta_key', '_et_explorable_comments_rating' );
			$query->set( 'meta_value', intval( $_GET['et-listing-rating'] ) );
		}
	}
}

function et_add_viewport_meta(){
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />';
}

function et_remove_additional_stylesheet( $stylesheet ){
	global $default_colorscheme;
	return $default_colorscheme;
}

// flush permalinks on theme activation
add_action( 'after_switch_theme', 'et_rewrite_flush' );
function et_rewrite_flush() {
    flush_rewrite_rules();
}

if ( ! function_exists( 'et_list_pings' ) ){
	function et_list_pings($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
	<?php }
}

if ( ! function_exists( 'et_get_the_author_posts_link' ) ){
	function et_get_the_author_posts_link(){
		global $authordata, $themename;

		$link = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ),
			esc_attr( sprintf( __( 'Posts by %s', $themename ), get_the_author() ) ),
			get_the_author()
		);
		return apply_filters( 'the_author_posts_link', $link );
	}
}

if ( ! function_exists( 'et_get_comments_popup_link' ) ){
	function et_get_comments_popup_link( $zero = false, $one = false, $more = false ){
		global $themename;

		$id = get_the_ID();
		$number = get_comments_number( $id );

		if ( 0 == $number && !comments_open() && !pings_open() ) return;

		if ( $number > 1 )
			$output = str_replace('%', number_format_i18n($number), ( false === $more ) ? __('% Comments', $themename) : $more);
		elseif ( $number == 0 )
			$output = ( false === $zero ) ? __('No Comments',$themename) : $zero;
		else // must be one
			$output = ( false === $one ) ? __('1 Comment', $themename) : $one;

		return '<span class="comments-number">' . '<a href="' . esc_url( get_permalink() . '#respond' ) . '">' . apply_filters('comments_number', $output, $number) . '</a>' . '</span>';
	}
}

if ( ! function_exists( 'et_postinfo_meta' ) ){
	function et_postinfo_meta( $postinfo, $date_format, $comment_zero, $comment_one, $comment_more ){
		global $themename;

		$postinfo_meta = '';

		if ( in_array( 'author', $postinfo ) ){
			$postinfo_meta .= ' ' . esc_html__('By',$themename) . ' ' . et_get_the_author_posts_link();
		}

		if ( in_array( 'date', $postinfo ) )
			$postinfo_meta .= ' ' . esc_html__('on',$themename) . ' ' . get_the_time( $date_format );

		if ( in_array( 'categories', $postinfo ) )
			$postinfo_meta .= ' ' . esc_html__('in',$themename) . ' ' . get_the_category_list(', ');

		if ( in_array( 'comments', $postinfo ) )
			$postinfo_meta .= ' | ' . et_get_comments_popup_link( $comment_zero, $comment_one, $comment_more );

		echo $postinfo_meta;
	}
}

function et_explorable_register_listing_posttype() {
	$labels = array(
		'name' 					=> _x( 'Listings', 'post type general name', 'Explorable' ),
		'singular_name' 		=> _x( 'Listing', 'post type singular name', 'Explorable' ),
		'add_new' 				=> _x( 'Add New', 'Listing item', 'Explorable' ),
		'add_new_item'			=> __( 'Add New Listing', 'Explorable' ),
		'edit_item' 			=> __( 'Edit Listing', 'Explorable' ),
		'new_item' 				=> __( 'New Listing', 'Explorable' ),
		'all_items' 			=> __( 'All Listings', 'Explorable' ),
		'view_item' 			=> __( 'View Listing', 'Explorable' ),
		'search_items' 			=> __( 'Search Offers', 'Explorable' ),
		'not_found' 			=> __( 'Nothing found', 'Explorable' ),
		'not_found_in_trash' 	=> __( 'Nothing found in Trash', 'Explorable' ),
		'parent_item_colon' 	=> '',
	);

	$args = array(
		'labels' 				=> $labels,
		'public' 				=> true,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true,
		'can_export'			=> true,
		'show_in_nav_menus'		=> false,
		'query_var' 			=> true,
		'has_archive' 			=> true,
		'rewrite' 				=> apply_filters( 'et_listing_posttype_rewrite_args', array( 'slug' => 'listing', 'with_front' => false, 'feeds' => true ) ),
		'capability_type' 		=> 'post',
		'hierarchical' 			=> false,
		'menu_position' 		=> null,
		'supports' 				=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' ),
	);

	register_post_type( 'listing' , apply_filters( 'et_listing_posttype_args', $args ) );

	$labels = array(
		'name' 				=> _x( 'Types', 'taxonomy general name', 'Explorable' ),
		'singular_name' 	=> _x( 'Type', 'taxonomy singular name', 'Explorable' ),
		'search_items' 		=>  __( 'Search Types', 'Explorable' ),
		'all_items' 		=> __( 'All Types', 'Explorable' ),
		'parent_item' 		=> __( 'Parent Type', 'Explorable' ),
		'parent_item_colon' => __( 'Parent Type:', 'Explorable' ),
		'edit_item' 		=> __( 'Edit Type', 'Explorable' ),
		'update_item' 		=> __( 'Update Type', 'Explorable' ),
		'add_new_item' 		=> __( 'Add New Type', 'Explorable' ),
		'new_item_name' 	=> __( 'New Type Name', 'Explorable' ),
		'menu_name' 		=> __( 'Types', 'Explorable' ),
	);

	register_taxonomy( 'listing_type', array( 'listing' ), array(
		'hierarchical' 	=> true,
		'labels' 		=> $labels,
		'show_ui' 		=> true,
		'query_var' 	=> true,
		'rewrite' 		=> apply_filters( 'et_listing_category_rewrite_args', array( 'slug' => 'listing-type', 'with_front' => false, 'feeds' => true ) ),
	) );

	$labels = array(
		'name' 				=> _x( 'Locations', 'taxonomy general name', 'Explorable' ),
		'singular_name' 	=> _x( 'Location', 'taxonomy singular name', 'Explorable' ),
		'search_items' 		=>  __( 'Search Locations', 'Explorable' ),
		'all_items' 		=> __( 'All Locations', 'Explorable' ),
		'parent_item' 		=> __( 'Parent Location', 'Explorable' ),
		'parent_item_colon' => __( 'Parent Location:', 'Explorable' ),
		'edit_item' 		=> __( 'Edit Location', 'Explorable' ),
		'update_item' 		=> __( 'Update Location', 'Explorable' ),
		'add_new_item' 		=> __( 'Add New Location', 'Explorable' ),
		'new_item_name' 	=> __( 'New Location Name', 'Explorable' ),
		'menu_name' 		=> __( 'Locations', 'Explorable' ),
	);

	register_taxonomy( 'listing_location', array( 'listing' ), array(
		'hierarchical' 	=> false,
		'labels' 		=> $labels,
		'show_ui' 		=> true,
		'query_var' 	=> true,
		'rewrite' 		=> apply_filters( 'et_listing_location_rewrite_args', array( 'slug' => 'location' ) ),
	) );
}

//add filter to ensure the text Listing is displayed when user updates a listing
add_filter( 'post_updated_messages', 'et_custom_post_type_updated_message' );
function et_custom_post_type_updated_message( $messages ) {
	global $post, $post_id;

	$messages['listing'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Listing updated. <a href="%s">View Listing</a>', 'Explorable' ), esc_url( get_permalink( $post_id ) ) ),
		2 => __( 'Custom field updated.', 'Explorable' ),
		3 => __( 'Custom field deleted.', 'Explorable' ),
		4 => __( 'Listing updated.', 'Explorable' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Listing restored to revision from %s', 'Explorable' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Listing published. <a href="%s">View Listing</a>', 'Explorable' ), esc_url( get_permalink( $post_id ) ) ),
		7 => __( 'Listing saved.', 'Explorable' ),
		8 => sprintf( __( 'Listing submitted. <a target="_blank" href="%s">Preview Listing</a>', 'Explorable' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) ),
		9 => sprintf( __( 'Listing scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Listing</a>', 'Explorable' ),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i', 'Explorable' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ) ),
		10 => sprintf( __( 'Listing draft updated. <a target="_blank" href="%s">Preview testimonial</a>', 'Explorable' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ) )
	);

	return $messages;
}

add_action( 'add_meta_boxes', 'et_listing_posttype_meta_box' );
function et_listing_posttype_meta_box() {
	add_meta_box( 'et_settings_meta_box', __( 'ET Settings', 'Explorable' ), 'et_listing_settings_meta_box', 'listing', 'normal', 'high' );
	add_meta_box( 'et_settings_meta_box', __( 'ET Settings', 'Explorable' ), 'et_single_settings_meta_box', 'post', 'normal', 'high' );
	add_meta_box( 'et_settings_meta_box', __( 'ET Settings', 'Explorable' ), 'et_single_settings_meta_box', 'page', 'normal', 'high' );
}

function et_listing_settings_meta_box() {
	$post_id = get_the_ID();
	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<p><?php esc_html_e( 'Drag and drop a marker to detect Location latitude and longtitude automatically.', 'Explorable' ); ?></p>

	<div id="et_admin_map"></div>

	<p>
		<label for="et_listing_lat" style="min-width: 150px; display: inline-block;"><?php esc_html_e( 'Location Latitude', 'Explorable' ); ?>: </label>
		<input type="text" name="et_listing_lat" id="et_listing_lat" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_listing_lat', true ) ); ?>" />
		<br />
		<small><?php esc_html_e( 'e.g.', 'Explorable' ); ?> <code>37.715342685425995</code></small>
	</p>

	<p>
		<label for="et_listing_lng" style="min-width: 150px; display: inline-block;"><?php esc_html_e( 'Location Longtitude', 'Explorable' ); ?>: </label>
		<input type="text" name="et_listing_lng" id="et_listing_lng" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_listing_lng', true ) ); ?>" />
		<br />
		<small><?php esc_html_e( 'e.g.', 'Explorable' ); ?> <code>-122.43436531250012</code></small>
	</p>

	<p>
		<label for="et_listing_custom_address" style="min-width: 150px; display: inline-block;"><?php esc_html_e( 'Location Address', 'Explorable' ); ?>: </label>
		<input type="text" name="et_listing_custom_address" id="et_listing_custom_address" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_listing_custom_address', true ) ); ?>" />
		<br />
		<small>
			<?php esc_html_e( 'e.g.', 'Explorable' ); ?> <code>2425 Broadway Street, San Francisco, CA, 94567</code>
			<br />
		</small>
	</p>

	<p>
		<label for="et_listing_description" style="min-width: 150px; display: inline-block;"><?php esc_html_e( 'Description', 'Explorable' ); ?>: </label>
		<input type="text" name="et_listing_description" id="et_listing_description" class="regular-text" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_listing_description', true ) ); ?>" />
		<br />
	</p>

	<p id="et-rating">
		<label style="min-width: 150px; display: inline-block; margin-bottom: 8px;"><?php esc_html_e( 'Rating', 'Explorable' ); ?>: </label>
		<br />
	<?php for ( $increment = 1; $increment <= 5; $increment = $increment+1  ) { ?>
		<input name="et_star" type="radio" class="star" value="<?php echo esc_attr( $increment ); ?>" <?php checked( get_post_meta( $post_id, '_et_author_rating', true ) >= $increment ); ?> />
	<?php } ?>
	</p>
<?php
}

function et_single_settings_meta_box() {
	$post_id = get_the_ID();
	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<p>
		<label for="et_single_header_bg"><?php esc_html_e( 'Header Background Image', 'Explorable' ); ?>: </label>
		<input type="text" name="et_single_header_bg" id="et_single_header_bg" size="90" value="<?php echo esc_attr( get_post_meta( $post_id, '_et_single_header_bg', true ) ); ?>" />
		<input class="upload_image_button" type="button" value="<?php esc_html_e( 'Upload Image', 'Explorable' ); ?>" /><br/>
		<small><?php esc_html_e( 'enter URL or upload an image if you want to specify the Header Background Image', 'Explorable' ); ?></small>
	</p>
<?php
}

add_action( 'save_post', 'et_metabox_settings_save_details', 10, 2 );
function et_metabox_settings_save_details( $post_id, $post ){
	global $pagenow;

	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	if ( !isset( $_POST['et_settings_nonce'] ) || ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    if ( 'listing' == $post->post_type ){
    	if ( isset( $_POST['et_listing_lat'] ) )
			update_post_meta( $post_id, '_et_listing_lat', sanitize_text_field( $_POST['et_listing_lat'] ) );
		else
			delete_post_meta( $post_id, '_et_listing_lat' );

		if ( isset( $_POST['et_listing_lng'] ) )
			update_post_meta( $post_id, '_et_listing_lng', sanitize_text_field( $_POST['et_listing_lng'] ) );
		else
			delete_post_meta( $post_id, '_et_listing_lng' );

		if ( isset( $_POST['et_listing_custom_address'] ) )
			update_post_meta( $post_id, '_et_listing_custom_address', sanitize_text_field( $_POST['et_listing_custom_address'] ) );
		else
			delete_post_meta( $post_id, '_et_listing_custom_address' );

		if ( isset( $_POST['et_listing_description'] ) )
			update_post_meta( $post_id, '_et_listing_description', sanitize_text_field( $_POST['et_listing_description'] ) );
		else
			delete_post_meta( $post_id, '_et_listing_description' );

		if ( isset( $_POST['et_star'] ) ) {
			update_post_meta( $post_id, '_et_author_rating', intval( $_POST['et_star'] ) );
			et_update_post_user_rating( $post_id );
		} else {
			delete_post_meta( $post_id, '_et_author_rating' );
			et_update_post_user_rating( $post_id );
		}
    } else if ( in_array( $post->post_type, array( 'post', 'page' ) ) ) {
    	if ( isset( $_POST['et_single_header_bg'] ) )
			update_post_meta( $post_id, '_et_single_header_bg', esc_url_raw( $_POST['et_single_header_bg'] ) );
		else
			delete_post_meta( $post_id, '_et_single_header_bg' );
    }
}

function et_explorable_admin_scripts_styles( $hook ) {
	global $typenow;

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) return;

	$template_dir = get_template_directory_uri();

	if ( ! isset( $typenow ) ) return;

	if ( 'listing' == $typenow ){
		wp_enqueue_style( 'et_admin_styles', $template_dir . '/css/admin_settings.css' );
		wp_enqueue_script( 'google-maps-api-admin', 'http://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'gmap3-admin', $template_dir . '/js/gmap3.min.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'et_admin_map', $template_dir . '/js/admin_settings_map.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'et_admin_map', 'et_map_admin_settings', array(
			'theme_dir' 			=> $template_dir,
			'detect_address'		=> __( 'Detect the marker address', 'Explorable' ),
			'note_address'			=> __( 'It will replace the value, set in Location Address', 'Explorable' ),
			'detect_lat_lng'		=> __( 'Detect Latitude and Longtitude values, using the address', 'Explorable' ),
			'fill_address_notice'	=> __( 'Please, fill in the Location Address field', 'Explorable' ),
		) );

		wp_enqueue_script( 'metadata', $template_dir . '/js/jquery.MetaData.js', array('jquery'), '4.11', true );
		wp_enqueue_script( 'et-rating', $template_dir . '/js/jquery.rating.pack.js', array('jquery'), '4.11', true );
		wp_enqueue_style( 'et-rating', $template_dir . '/css/jquery.rating.css' );
	}

	if ( in_array( $typenow, array( 'post', 'page' ) ) ) {
		wp_enqueue_script( 'et_image_upload_custom', $template_dir . '/js/admin_custom_uploader.js', array( 'jquery' ) );
	}
}

if ( ! function_exists( 'et_is_listing_page' ) ) :
	function et_is_listing_page() {
		if ( is_single() && 'listing' == get_post_type() ) return true;

		return ( ( is_home() && is_front_page() ) || is_post_type_archive( 'listing' ) || is_tax( 'listing_type' ) || is_tax( 'listing_location' ) );
	}
endif;

function et_add_map_class( $classes ) {
	if ( et_is_listing_page() ) $classes[] = 'et_map_full_screen';

	return $classes;
}

function et_attach_bg_images() {
	$template_directory = get_template_directory_uri();
	$header_bg = et_get_option( 'explorable_bg_image', $template_directory .  '/images/bg.jpg' );

	if ( is_singular() && ( $et_single_header_bg = get_post_meta( get_the_ID(), '_et_single_header_bg', true ) ) && '' != $et_single_header_bg )
		$header_bg = $et_single_header_bg;
?>
	<style>
		#et-header-bg { background-image: url(<?php echo esc_html( $header_bg ); ?>); }
	</style>
<?php
}

if ( ! function_exists( 'et_get_rating' ) ) :
	function et_get_rating() {
		$rating = ( $overall_rating = get_post_meta( get_the_ID(), '_et_explorable_comments_rating', true ) ) && '' != $overall_rating ? $overall_rating : 0;

		// if the post has only an author rating
		if ( 0 == $rating ) $rating = (int) get_post_meta( get_the_ID(), '_et_author_rating', true );

		return $rating;
	}
endif;

add_action( 'comment_post', 'et_add_rating_commentmeta', 10, 2 );
function et_add_rating_commentmeta( $comment_id, $comment_approved ){
	#when user adds a comment, check if it's approved

	$comment_rating = ( isset( $_POST['et_star'] ) ) ? $_POST['et_star'] : 0;
	add_comment_meta( $comment_id, 'et_comment_rating', $comment_rating );
	if ( $comment_approved == 1 ) {
		$comment_info = get_comment( $comment_id );
		et_update_post_user_rating( $comment_info->comment_post_ID );
	}
}

function et_get_approved_comments( $post_id ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' ORDER BY comment_date", $post_id ) );
}

function et_get_post_user_rating( $post_id ){
	#calculates user (comments) + author rating for the post
	$approved_comments = et_get_approved_comments( $post_id );

	$user_rating = 0;
	$approved_comments_number = ! empty( $approved_comments ) ? count( $approved_comments ) : 0;

	if ( ! empty( $approved_comments ) ) {
		foreach ( $approved_comments as $comment ) {
			$comment_rating = get_comment_meta( $comment->comment_ID, 'et_comment_rating', true ) ? get_comment_meta( $comment->comment_ID, 'et_comment_rating', true ) : 0;
			if ( 0 == $comment_rating ) $approved_comments_number--;

			$user_rating += $comment_rating;
		}
	}

	$author_rating = (int) get_post_meta( $post_id, '_et_author_rating', true );
	if ( 0 < $author_rating ) {
		$user_rating += $author_rating;
		$approved_comments_number++;
	}

	$result = ( $user_rating <> 0 ) ? round( $user_rating / $approved_comments_number ) : 0;

	# save user and author rating to the post meta
	if ( ! get_post_meta( $post_id, '_et_explorable_comments_rating', true ) )
		update_post_meta( $post_id, '_et_explorable_comments_rating', intval( $result ) );

	return $result;
}

function et_update_post_user_rating( $post_id ){
	#update (recalculate) user (comments) rating for the post
	$new_comments_rating = et_get_post_user_rating( $post_id );

	update_post_meta( $post_id, '_et_explorable_comments_rating', $new_comments_rating );
}

add_action( 'wp_set_comment_status', 'et_comment_status_changed', 10, 2 );
function et_comment_status_changed( $comment_id, $comment_status ){
	$comment_info = get_comment( $comment_id );
	if ( $comment_info ) et_update_post_user_rating( $comment_info->comment_post_ID );
}

add_filter( 'comment_form_field_comment', 'et_comment_form_add_rating' );
function et_comment_form_add_rating( $comment_field ){
	if ( 'listing' != get_post_type() ) return $comment_field;

	$rating_field = '<div id="et-rating"><div class="clearfix">';

	for ( $increment = 1; $increment <= 5; $increment = $increment+1 )
		$rating_field .= '<input name="et_star" type="radio" class="star" value="' . esc_attr( $increment ) . '" />';

	$rating_field .= '</div></div>';

	return $rating_field . $comment_field;
}

function et_add_tablet_class( $classes ){
	if ( strstr( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) || strstr( $_SERVER['HTTP_USER_AGENT'], 'iPhone' ) || strstr( $_SERVER['HTTP_USER_AGENT'], 'Android' ) )
		$classes[] = 'et_tablet';

	return $classes;
}

add_filter( 'manage_edit-listing_columns', 'et_listing_edit_columns' );
function et_listing_edit_columns( $columns ) {
	$columns = array(
		'cb' 					=> '<input type="checkbox" />',
		'title' 				=> __( 'Title', 'Explorable' ),
		'et_listing_type' 		=> __( 'Type', 'Explorable' ),
		'et_listing_location'	=> __( 'Location', 'Explorable' ),
	);

	return $columns;
}

add_action( 'manage_posts_custom_column', 'et_listing_custom_columns' );
function et_listing_custom_columns( $column ) {
	$custom_fields = get_post_custom();

	switch ( $column ) {
		case 'et_listing_type' :
			$et_listing_types = get_the_terms( get_the_ID(), 'listing_type' );

			if ( !empty( $et_listing_types ) ) {
				$out = array();
				foreach ( $et_listing_types as $et_listing_type ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => 'listing', 'listing_type' => $et_listing_type->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $et_listing_type->name, $et_listing_type->term_id, 'listing_type', 'display' ) )
					);
				}

				echo join( ', ', $out );
			} else {
				_e( 'None', 'Explorable' );
			}

			break;
		case 'et_listing_location' :

			$et_listing_locations = get_the_terms( get_the_ID(), 'listing_location' );

			if ( !empty( $et_listing_locations ) ) {
				$out = array();
				foreach ( $et_listing_locations as $et_listing_location ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => 'listing', 'listing_location' => $et_listing_location->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $et_listing_location->name, $et_listing_location->term_id, 'listing_location', 'display' ) )
					);
				}

				echo join( ', ', $out );
			} else {
				_e( 'None', 'Explorable' );
			}

			break;
	}
}

if ( function_exists( 'get_custom_header' ) ) {
	// compatibility with versions of WordPress prior to 3.4

	add_action( 'customize_register', 'et_explorable_customize_register' );
	function et_explorable_customize_register( $wp_customize ) {
		$google_fonts = et_get_google_fonts();

		$font_choices = array();
		$font_choices['none'] = 'Default Theme Font';
		foreach ( $google_fonts as $google_font_name => $google_font_properties ) {
			$font_choices[ $google_font_name ] = $google_font_name;
		}

		$wp_customize->remove_section( 'title_tagline' );
		$wp_customize->remove_section( 'background_image' );

		$wp_customize->add_section( 'et_google_fonts' , array(
			'title'		=> __( 'Fonts', 'Explorable' ),
			'priority'	=> 50,
		) );

		$wp_customize->add_section( 'et_color_schemes' , array(
			'title'       => __( 'Schemes', 'Explorable' ),
			'priority'    => 60,
			'description' => __( 'Note: Color settings set above should be applied to the Default color scheme.', 'Explorable' ),
		) );

		$wp_customize->add_setting( 'et_explorable[link_color]', array(
			'default'		=> '#4bb6f5',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[link_color]', array(
			'label'		=> __( 'Link Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[link_color]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[font_color]', array(
			'default'		=> '#2a2a2a',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[font_color]', array(
			'label'		=> __( 'Main Font Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[font_color]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[headings_color]', array(
			'default'		=> '#3d5054',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[headings_color]', array(
			'label'		=> __( 'Headings Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[headings_color]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[top_menu_bar]', array(
			'default'		=> '#232323',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[top_menu_bar]', array(
			'label'		=> __( 'Menu Bar Background Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[top_menu_bar]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[menu_link_color]', array(
			'default'		=> '#ffffff',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[menu_link_color]', array(
			'label'		=> __( 'Menu Links Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[menu_link_color]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[active_color]', array(
			'default'		=> '#4bb6f5',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'et_explorable[active_color]', array(
			'label'		=> __( 'Menu Active Color', 'Explorable' ),
			'section'	=> 'colors',
			'settings'	=> 'et_explorable[active_color]',
		) ) );

		$wp_customize->add_setting( 'et_explorable[heading_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'et_explorable[heading_font]', array(
			'label'		=> __( 'Header Font', 'Explorable' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'et_explorable[heading_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );

		$wp_customize->add_setting( 'et_explorable[body_font]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options'
		) );

		$wp_customize->add_control( 'et_explorable[body_font]', array(
			'label'		=> __( 'Body Font', 'Explorable' ),
			'section'	=> 'et_google_fonts',
			'settings'	=> 'et_explorable[body_font]',
			'type'		=> 'select',
			'choices'	=> $font_choices
		) );

		$wp_customize->add_setting( 'et_explorable[color_schemes]', array(
			'default'		=> 'none',
			'type'			=> 'option',
			'capability'	=> 'edit_theme_options',
			'transport'		=> 'postMessage'
		) );

		$wp_customize->add_control( 'et_explorable[color_schemes]', array(
			'label'		=> __( 'Color Schemes', 'Explorable' ),
			'section'	=> 'et_color_schemes',
			'settings'	=> 'et_explorable[color_schemes]',
			'type'		=> 'select',
			'choices'	=> array(
				'none'   => __( 'Default', 'Explorable' ),
				'blue'   => __( 'Blue', 'Explorable' ),
				'green'  => __( 'Green', 'Explorable' ),
				'purple' => __( 'Purple', 'Explorable' ),
				'red'    => __( 'Red', 'Explorable' ),
			),
		) );
	}

	add_action( 'customize_preview_init', 'et_explorable_customize_preview_js' );
	function et_explorable_customize_preview_js() {
		wp_enqueue_script( 'explorable-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), false, true );
	}

	add_action( 'wp_head', 'et_explorable_add_customizer_css' );
	add_action( 'customize_controls_print_styles', 'et_explorable_add_customizer_css' );
	function et_explorable_add_customizer_css(){ ?>
		<style>
			a { color: <?php echo esc_html( et_get_option( 'link_color', '#4bb6f5' ) ); ?>; }
			body { color: <?php echo esc_html( et_get_option( 'font_color', '#2a2a2a' ) ); ?>; }
			h1, h2, h3, h4, h5, h6, .widget h4.widgettitle, .entry h2.title a, h1.title, #comments, #reply-title { color: <?php echo esc_html( et_get_option( 'headings_color', '#3d5054' ) ); ?>; }

			#top-navigation a { color: <?php echo esc_html( et_get_option( 'menu_link_color', '#ffffff' ) ); ?> }
			#top-navigation li.current-menu-item > a, .et_mobile_menu li.current-menu-item > a { color: <?php echo esc_html( et_get_option( 'active_color', '#4bb6f5' ) ); ?> }
			.nav li ul, .et_mobile_menu, #main-header { background-color: <?php echo esc_html( et_get_option( 'top_menu_bar', '#232323' ) ); ?> }
			#top-navigation > nav > ul > li.current-menu-item > a:before, .mobile_nav:before { border-top-color: <?php echo esc_html( et_get_option( 'top_menu_bar', '#232323' ) ); ?>; }

		<?php
			$et_gf_heading_font = sanitize_text_field( et_get_option( 'heading_font', 'none' ) );
			$et_gf_body_font = sanitize_text_field( et_get_option( 'body_font', 'none' ) );

			if ( 'none' != $et_gf_heading_font || 'none' != $et_gf_body_font ) :

				if ( 'none' != $et_gf_heading_font )
					et_gf_attach_font( $et_gf_heading_font, 'h1, h2, h3, h4, h5, h6, .location-title h2, .et-description h1, .post-description h2, .post-description h1, #listing-results, #et-listings-no-results h1, #et-listings-no-results h2, #comments, #reply-title, #left-area .wp-pagenavi, .entry-content h1, .entry-content h2, .entry-content h3, .entry-content h4' );

				if ( 'none' != $et_gf_body_font )
					et_gf_attach_font( $et_gf_body_font, 'body' );

			endif;
		?>
		</style>
	<?php }

	/*
	 * Adds color scheme class to the body tag
	 */
	add_filter( 'body_class', 'et_customizer_color_scheme_class' );
	function et_customizer_color_scheme_class( $body_class ) {
		$color_scheme        = et_get_option( 'color_schemes', 'none' );
		$color_scheme_prefix = 'et_color_scheme_';

		if ( 'none' !== $color_scheme ) $body_class[] = $color_scheme_prefix . $color_scheme;

		return $body_class;
	}

	add_action( 'customize_controls_print_footer_scripts', 'et_load_google_fonts_scripts' );
	function et_load_google_fonts_scripts() {
		wp_enqueue_script( 'et_google_fonts', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.js', array( 'jquery' ), '1.0', true );
	}

	add_action( 'customize_controls_print_styles', 'et_load_google_fonts_styles' );
	function et_load_google_fonts_styles() {
		wp_enqueue_style( 'et_google_fonts_style', get_template_directory_uri() . '/epanel/google-fonts/et_google_fonts.css', array(), null );
	}
}