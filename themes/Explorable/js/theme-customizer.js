/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	wp.customize( 'et_explorable[link_color]', function( value ) {
		value.bind( function( to ) {
			$( 'a' ).css( 'color', to );
		} );
	} );

	wp.customize( 'et_explorable[font_color]', function( value ) {
		value.bind( function( to ) {
			$( 'body' ).css( 'color', to );
		} );
	} );

	wp.customize( 'et_explorable[headings_color]', function( value ) {
		value.bind( function( to ) {
			$( 'h1, h2, h3, h4, h5, h6, .widget h4.widgettitle, .entry h2.title a, h1.title, #comments, #reply-title' ).css( 'color', to );
		} );
	} );

	wp.customize( 'et_explorable[top_menu_bar]', function( value ) {
		value.bind( function( to ) {
			$( '.nav li ul, .et_mobile_menu, #main-header' ).css( 'background', to );
		} );
	} );

	wp.customize( 'et_explorable[menu_link_color]', function( value ) {
		value.bind( function( to ) {
			$( '#top-navigation a' ).css( 'color', to );
		} );
	} );

	wp.customize( 'et_explorable[active_color]', function( value ) {
		value.bind( function( to ) {
			$( '#top-navigation li.current-menu-item > a, .et_mobile_menu li.current-menu-item > a' ).css( 'color', to );
			$( '#top-navigation > nav > ul > li.current-menu-item > a:before, .mobile_nav:before' ).css( 'border', to );
		} );
	} );

	wp.customize( 'et_explorable[color_schemes]', function( value ) {
		value.bind( function( to ) {
			var $body = $( 'body' ),
				body_classes = $body.attr( 'class' ),
				et_customizer_color_scheme_prefix = 'et_color_scheme_',
				body_class;

			body_class = body_classes.replace( /et_color_scheme_[^\s]+/, '' );
			$body.attr( 'class', $.trim( body_class ) );

			if ( 'none' !== to  )
				$body.addClass( et_customizer_color_scheme_prefix + to );
		} );
	} );
} )( jQuery );