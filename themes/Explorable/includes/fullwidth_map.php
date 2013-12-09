<div id="et_main_map"></div>

<script type="text/javascript">
	(function($){
		var $et_main_map = $( '#et_main_map' );

		et_active_marker = null;

		$et_main_map.gmap3({
			map:{
				options:{
<?php
while ( have_posts() ) : the_post();
	$et_location_lat = get_post_meta( get_the_ID(), '_et_listing_lat', true );
	$et_location_lng = get_post_meta( get_the_ID(), '_et_listing_lng', true );

				if ( '' != $et_location_lat && '' != $et_location_lng )
					printf( 'center: [%s, %s],', $et_location_lat, $et_location_lng );

	break;
endwhile;
rewind_posts();
?>
					zoom:<?php
							if ( is_home() || is_front_page() )
								echo esc_js( et_get_option( 'explorable_homepage_zoom_level', 3 ) );
							else
								echo esc_js( et_get_option( 'explorable_default_zoom_level', 5 ) ); ?>,
					mapTypeId: google.maps.MapTypeId.<?php echo esc_js( strtoupper( et_get_option( 'explorable_map_type', 'Roadmap' ) ) ); ?>,
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
			}
		});

		function et_add_marker( marker_order, marker_lat, marker_lng, marker_description ){
			var marker_id = 'et_marker_' + marker_order;

			$et_main_map.gmap3({
				marker : {
					id : marker_id,
					latLng : [marker_lat, marker_lng],
					options: {
						icon : "<?php echo get_template_directory_uri(); ?>/images/red-marker.png"
					},
					events : {
						click: function( marker ){
							if ( et_active_marker ){
								et_active_marker.setAnimation( null );
								et_active_marker.setIcon( '<?php echo get_template_directory_uri(); ?>/images/red-marker.png' );
							}
							et_active_marker = marker;

							marker.setAnimation( google.maps.Animation.BOUNCE);
							marker.setIcon( '<?php echo get_template_directory_uri(); ?>/images/blue-marker.png' );
							$(this).gmap3("get").panTo( marker.position );

							$.fn.et_simple_slider.external_move_to( marker_order );
						},
						mouseover: function( marker ){
							$( '#' + marker_id ).css( { 'display' : 'block', 'opacity' : 0 } ).stop(true,true).animate( { bottom : '15px', opacity : 1 }, 500 );
						},
						mouseout: function( marker ){
							$( '#' + marker_id ).stop(true,true).animate( { bottom : '50px', opacity : 0 }, 500, function() {
								$(this).css( { 'display' : 'none' } );
							} );
						}
					}
				},
				overlay : {
					latLng : [marker_lat, marker_lng],
					options : {
						content : marker_description,
						offset : {
							y:-42,
							x:-122
						}
					}
				}
			});
		}

<?php
$i = 0;
while ( have_posts() ) : the_post();
	$et_location_lat = get_post_meta( get_the_ID(), '_et_listing_lat', true );
	$et_location_lng = get_post_meta( get_the_ID(), '_et_listing_lng', true );

	$et_location_rating = '<div class="location-rating"></div>';
	if ( ( $et_rating = et_get_rating() ) && 0 != $et_rating )
		$et_location_rating = '<div class="location-rating"><span class="et-rating"><span style="' . sprintf( 'width: %dpx;', esc_attr( $et_rating * 17 ) ) . '"></span></span></div>';

	if ( '' != $et_location_lat && '' != $et_location_lng ) {
?>
			et_add_marker( <?php printf( '%1$d, %2$s, %3$s, \'<div id="et_marker_%1$d" class="et_marker_info"><div class="location-description"> <div class="location-title"> <h2>%4$s</h2> <div class="listing-info"><p>%5$s</p></div> </div> ' . $et_location_rating . ' </div> <!-- .location-description --> </div> <!-- .et_marker_info -->\'',
				$i,
				esc_html( $et_location_lat ),
				esc_html( $et_location_lng ),
				get_the_title(),
				wp_strip_all_tags( addslashes( get_the_term_list( get_the_ID(), 'listing_type', '', ', ' ) ) )
			); ?> );
<?php
	}

	$i++;
endwhile;

rewind_posts();
?>
		})(jQuery)
	</script>

<div id="et-slider-wrapper" class="et-map-post">
	<div id="et-map-slides">

<?php
	$i = 1;
	while ( have_posts() ) : the_post();
		$thumb = '';
		$width = (int) apply_filters( 'et_map_image_width', 480 );
		$height = (int) apply_filters( 'et_map_image_height', 240 );
		$classtext = '';
		$titletext = get_the_title();
		$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'MapIndex' );
		$thumb = $thumbnail["thumb"];
?>
		<div class="et-map-slide<?php if ( 1 == $i ) echo esc_attr( ' et-active-map-slide' ); ?>">
		<?php if ( ''!= $thumb ) { ?>
			<div class="thumbnail">
				<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
				<div class="et-description">
					<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php if ( ( $et_description = get_post_meta( get_the_ID(), '_et_listing_description', true ) ) && '' != $et_description ) : ?>
					<p><?php echo esc_html( $et_description ); ?></p>
				<?php endif; ?>
				<?php if ( ( $et_rating = et_get_rating() ) && 0 != $et_rating ) : ?>
					<span class="et-rating"><span style="<?php printf( 'width: %dpx;', esc_attr( $et_rating * 21 ) ); ?>"></span></span>
				<?php endif; ?>
				</div>
			<?php
				printf( '<div class="et-date-wrapper"><span class="et-date">%s<span>%s</span></span></div>',
					get_the_time( _x( 'F j', 'Location date format first part', 'Explorable' ) ),
					get_the_time( _x( 'Y', 'Location date format second part', 'Explorable' ) )
				);
			?>
			</div>
		<?php } ?>

		<?php if ( ( $et_location_address = get_post_meta( get_the_ID(), '_et_listing_custom_address', true ) ) && '' != $et_location_address ) : ?>
			<div class="et-map-postmeta"><?php echo esc_html( $et_location_address ); ?></div>
		<?php endif; ?>

			<div class="et-place-content">
				<div class="et-place-text-wrapper">
					<div class="et-place-main-text">
						<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
						<div class="viewport">
							<div class="overview">
							<?php
								if ( has_excerpt() )
									the_excerpt();
								else
									the_content( '' );
							?>
							</div>
						</div>
					</div> <!-- .et-place-main-text -->
				</div> <!-- .et-place-text-wrapper -->
				<a class="more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'More Information', 'Explorable' ); ?><span>&raquo;</span></a>
			</div> <!-- .et-place-content -->
		</div> <!-- .et-map-slide -->
<?php
	$i++;
	endwhile;

	rewind_posts();
?>
	</div> <!-- #et-map-slides -->
</div> <!-- .et-map-post -->


<?php
	$listing_types_args = array( 'hide_empty' => 1 );
	$listing_locations_args = array( 'hide_empty' => 1 );
	$listing_types = get_terms( 'listing_type', apply_filters( 'listing_types_args', $listing_types_args ) );
	$listing_locations = get_terms( 'listing_location', apply_filters( 'listing_locations_args', $listing_locations_args ) );
?>
<div id="filter-bar">
	<div class="container">
		<form method="get" id="et-filter-map" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<a href="#" class="filter-type listing-type"><span class="et_explorable_filter_text"><?php esc_html_e( 'All Listing Types', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
			<a href="#" class="filter-type listing-location"><span class="et_explorable_filter_text"><?php esc_html_e( 'All Locations', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
			<a href="#" class="filter-type listing-rating"><span class="et_explorable_filter_text"><?php esc_html_e( 'Any Rating', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
			<button id="et-filter"><?php esc_html_e( 'Filter', 'Explorable' ); ?></button>
			<input type="hidden" value="" name="s" />
			<input type="hidden" value="listing" name="post_type" />

			<select name="et-listing-type" id="et-listing-type">
  				<option value="none"><?php esc_html_e( 'All Listing Types', 'Explorable' ); ?></option>
<?php
		if ( $listing_types ) {
			foreach( $listing_types as $listing_type ) {
				printf( '<option value="%d"%s>%s</option>',
					esc_attr( $listing_type->term_id ),
					( isset( $_GET['et-listing-type'] ) && 'none' != $_GET['et-listing-type'] ? selected( intval( $_GET['et-listing-type'] ), $listing_type->term_id, false ) : '' ),
					esc_html( $listing_type->name )
				);
			}
		}
?>
  			</select>

  			<select name="et-listing-location" id="et-listing-location">
  				<option value="none"><?php esc_html_e( 'All Locations', 'Explorable' ); ?></option>
<?php
		if ( $listing_locations ) {
			foreach( $listing_locations as $listing_location ) {
				printf( '<option value="%d"%s>%s</option>',
					esc_attr( $listing_location->term_id ),
					( isset( $_GET['et-listing-location'] ) && 'none' != $_GET['et-listing-location'] ? selected( intval( $_GET['et-listing-location'] ), $listing_location->term_id, false ) : '' ),
					esc_html( $listing_location->name )
				);
			}
		}
?>
  			</select>

			<select name="et-listing-rating" id="et-listing-rating">
				<option value="none"><?php esc_html_e( 'Any Rating', 'Explorable' ); ?></option>
<?php
			for ( $i = 5; $i >= 1; $i-- )
				printf( '<option value="%1$d"%2$s>%1$d</option>',
					intval( $i ),
					( isset( $_GET['et-listing-rating'] ) && 'none' != $_GET['et-listing-rating'] ? selected( intval( $_GET['et-listing-rating'] ), $i, false ) : '' )
				);
?>
			</select>
		</form>
	</div> <!-- .container -->
</div> <!-- #filter-bar -->

<div id="et-list-view" class="et-normal-listings">
	<h2 id="listing-results"><?php esc_html_e( 'Listing Results', 'Explorable' ); ?></h2>

	<div id="et-listings">
		<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
		<div class="viewport">
			<div class="overview">
				<ul>
<?php
				$i = 1;
				while ( have_posts() ) : the_post();
					$thumb = '';
					$width = (int) apply_filters( 'et_listing_results_image_width', 60 );
					$height = (int) apply_filters( 'et_listing_results_image_height', 60 );
					$classtext = '';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'ListingIndex' );
					$thumb = $thumbnail["thumb"];
?>
					<li class="<?php if ( 1 == $i ) echo esc_attr( 'et-active-listing ' ); ?>clearfix">
						<div class="listing-image">
							<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
						</div> <!-- .listing-image -->
						<div class="listing-text">
							<h3><?php the_title(); ?></h3>
							<p><?php echo wp_strip_all_tags( get_the_term_list( get_the_ID(), 'listing_type', '', ', ' ) ); ?></p>
						<?php if ( ( $et_rating = et_get_rating() ) && 0 != $et_rating ) : ?>
							<span class="et-rating"><span style="<?php printf( 'width: %dpx;', esc_attr( $et_rating * 17 ) ); ?>"></span></span>
						<?php endif; ?>
						</div> <!-- .listing-text -->
						<a href="<?php the_permalink(); ?>" class="et-mobile-link"><?php esc_html_e( 'Read more', 'Explorable' ); ?></a>
					</li>
<?php
					$i++;
				endwhile;
?>
				</ul>
			</div> <!-- .overview -->
		</div> <!-- .viewport -->
	</div> <!-- #et-listings -->
</div> <!-- #et-list-view -->