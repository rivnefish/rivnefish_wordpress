<?php get_header(); ?>

<?php
	$et_full_post = 'on' == get_post_meta( get_the_ID(), '_et_full_post', true ) ? true : false;

	$et_location_lat = get_post_meta( get_the_ID(), '_et_listing_lat', true );
	$et_location_lng = get_post_meta( get_the_ID(), '_et_listing_lng', true );

	$et_location_rating = '<div class="location-rating"></div>';

	$et_rating = et_get_rating();

	if ( 0 != $et_rating )
		$et_location_rating = '<div class="location-rating"><span class="et-rating"><span style="' . sprintf( 'width: %dpx;', esc_attr( $et_rating * 17 ) ) . '"></span></span></div>';
?>

<?php if ( '' != $et_location_lat && '' != $et_location_lng ) : ?>
<div id="et-single-map"></div>

<script type="text/javascript">
	(function($){
		var $et_single_map = $( '#et-single-map' );

		$et_single_map.gmap3({
			map:{
				options:{
					center : [<?php echo esc_html( $et_location_lat ); ?>, <?php echo esc_html( $et_location_lng ); ?>],
					zoom: <?php echo esc_js( et_get_option( 'explorable_default_zoom_level', 3 ) ); ?>,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					mapTypeControl: true,
					mapTypeControlOptions: {
						position : google.maps.ControlPosition.LEFT_CENTER,
						style : google.maps.MapTypeControlStyle.DROPDOWN_MENU
					},
					streetViewControlOptions: {
						position: google.maps.ControlPosition.LEFT_CENTER
					},
					navigationControl: false,
					scrollwheel: true,
					streetViewControl: true,
					zoomControl: false
				}
			},
			marker : {
				id : 'et_single_marker',
				latLng : [<?php echo esc_html( $et_location_lat ); ?>, <?php echo esc_html( $et_location_lng ); ?>],
				options: {
					icon : "<?php echo get_template_directory_uri(); ?>/images/red-marker.png"
				},
				events : {
					mouseover: function( marker ){
						$( '#et_marker_0' ).css( { 'display' : 'block', 'opacity' : 0 } ).animate( { bottom : '15px', opacity : 1 }, 500 );
					},
					mouseout: function( marker ){
						$( '#et_marker_0' ).animate( { bottom : '50px', opacity : 0 }, 500, function() {
							$(this).css( { 'display' : 'none' } );
						} );
					}
				}
			},
			overlay : {
				latLng : [<?php echo esc_html( $et_location_lat ); ?>, <?php echo esc_html( $et_location_lng ); ?>],
				options : {
					content : '<div id="et_marker_0" class="et_marker_info"><div class="location-description"> <div class="location-title"> <h2><?php the_title(); ?></h2> <div class="listing-info"><p><?php echo wp_strip_all_tags( addslashes( get_the_term_list( get_the_ID(), 'listing_type', '', ', ' ) ) ); ?></p></div> </div> <?php echo $et_location_rating; ?> </div> <!-- .location-description --> </div> <!-- .et_marker_info -->',
					offset : {
						y:-42,
						x:-122
					}
				}
			}
		});
	})(jQuery)
</script>
<?php endif; ?>

<div id="main-area">
	<div class="container<?php if ( $et_full_post ) echo ' fullwidth'; ?>">
		<?php get_template_part( 'includes/breadcrumbs', 'single' ); ?>

		<?php while ( have_posts() ) : the_post(); ?>
			<div class="et-map-post">
			<?php
				$thumb = '';
				$width = (int) apply_filters( 'et_single_listing_image_width', 960 );
				$height = (int) apply_filters( 'et_single_listing_image_height', 320 );
				$classtext = '';
				$titletext = get_the_title();
				$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'ListingSingle' );
				$thumb = $thumbnail["thumb"];
			?>
			<?php if ( '' != $thumb ) : ?>
				<div class="thumbnail">
					<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>

					<div class="et-description">
						<h1><?php the_title(); ?></h1>

					<?php if ( ( $et_description = get_post_meta( get_the_ID(), '_et_listing_description', true ) ) && '' != $et_description ) : ?>
						<p><?php echo esc_html( $et_description ); ?></p>
					<?php endif; ?>

					<?php if ( 0 != $et_rating ) : ?>
						<span class="et-rating"><span style="<?php printf( 'width: %dpx;', esc_attr( $et_rating * 21 ) ); ?>"></span></span>
					<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			</div> <!-- .et-map-post -->

			<div id="content" class="clearfix">
				<div id="left-area">
					<ul id="meta-info">
						<li><strong><?php esc_html_e( 'Author', 'Explorable' ); ?>:</strong> <?php the_author(); ?></li>
						<li><strong><?php esc_html_e( 'Date Posted', 'Explorable' ); ?>:</strong> <?php the_time( et_get_option( 'explorable_date_format', 'M j, Y' ) ); ?></li>
						<li><strong><?php esc_html_e( 'Category', 'Explorable' ); ?>:</strong> <?php echo get_the_term_list( get_the_ID(), 'listing_type', '', ', ' ); ?></li>
					<?php if ( ( $et_location_address = get_post_meta( get_the_ID(), '_et_listing_custom_address', true ) ) && '' != $et_location_address ) : ?>
						<li class="last"><strong><?php esc_html_e( 'Address:', 'Explorable' ); ?></strong> <?php echo esc_html( $et_location_address ); ?></li>
					<?php endif; ?>
					</ul> <!-- #meta-info -->
					<div class="entry-content">
					<?php
						the_content();
						wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Explorable' ), 'after' => '</div>' ) );
					?>
					</div> <!-- .entry-content -->

					<?php
						if ( comments_open() && 'on' == et_get_option( 'explorable_show_postcomments', 'on' ) )
							comments_template( '', true );
					?>
				</div> <!-- end #left-area -->

				<?php if ( ! $et_full_post ) get_sidebar(); ?>
			</div> <!-- end #content -->
		<?php endwhile; ?>
	</div> <!-- end .container -->
</div> <!-- end #main-area -->

<?php get_footer(); ?>