<?php get_header(); ?>

<?php if ( have_posts() ) : ?>

	<?php get_template_part( 'includes/fullwidth_map', 'archive-listing' ); ?>

<?php else : ?>

	<div id="et-listings-no-results">

	<?php
		$listing_types_args = array( 'hide_empty' => 1 );
		$listing_locations_args = array( 'hide_empty' => 1 );
		$listing_types = get_terms( 'listing_type', apply_filters( 'listing_types_args', $listing_types_args ) );
		$listing_locations = get_terms( 'listing_location', apply_filters( 'listing_locations_args', $listing_locations_args ) );
	?>

		<div id="filter-bar">
			<div class="container">
				<h1><?php esc_html_e( 'No Results Found.', 'Explorable' ); ?></h1>
				<p><?php esc_html_e( 'Try searching different criteria.', 'Explorable' ); ?></p>

				<form method="get" id="et-filter-map" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<a href="#" class="filter-type listing-type"><span class="et_filter_text"><?php esc_html_e( 'All Listing Types', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
					<a href="#" class="filter-type listing-location"><span class="et_filter_text"><?php esc_html_e( 'All Locations', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
					<a href="#" class="filter-type listing-rating"><span class="et_filter_text"><?php esc_html_e( 'Any Rating', 'Explorable' ); ?></span><span class="et_filter_arrow"></span></a>
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
	</div> <!-- #et-listings-no-results -->

<?php endif; ?>

<?php get_footer(); ?>