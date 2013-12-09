(function($){
	$.fn.et_simple_slider = function( options ) {
		var settings = $.extend( {
			slide         			: '.et-slide',				 	// slide class
			arrows					: '.et-slider-arrows',			// arrows container class
			prev_arrow				: '.et-arrow-prev',				// left arrow class
			next_arrow				: '.et-arrow-next',				// right arrow class
			controls 				: '.et-controllers a',			// control selector
			control_active_class	: 'et-active-control',			// active control class name
			previous_text			: 'Previous',					// previous arrow text
			next_text				: 'Next',						// next arrow text
			fade_speed				: 500,							// fade effect speed
			use_arrows				: true,							// use arrows?
			use_controls			: true,							// use controls?
			manual_arrows			: '',							// html code for custom arrows
			append_controls_to		: '',							// controls are appended to the slider element by default, here you can specify the element it should append to
			controls_class			: 'et-controllers',				// controls container class name
			slideshow				: false,						// automattic animation?
			slideshow_speed			: 7000,							// automattic animation speed
			on_slide_changing		: function(){},					// callback function that runs when a slide changes
			on_slide_change_end		: function(){}					// callback function that runs when a slide changes
		}, options );

		return this.each( function() {
			var $et_slider 			= $(this),
				$et_slide			= $et_slider.find( settings.slide ),
				et_slides_number	= $et_slide.length,
				et_fade_speed		= settings.fade_speed,
				et_active_slide		= 0,
				$et_slider_arrows,
				$et_slider_prev,
				$et_slider_next,
				$et_slider_controls,
				et_slider_timer,
				controls_html = '';

			if ( settings.use_arrows && et_slides_number > 1 ) {
				if ( settings.manual_arrows == '' )
					$et_slider.append( '<div class="et-slider-arrows"><a class="et-arrow-prev" href="#">' + settings.previous_text + '</a><a class="et-arrow-next" href="#">' + settings.next_text + '</a></div>' );
				else
					$et_slider.append( settings.manual_arrows );

				$et_slider_arrows 	= $( settings.arrows );
				$et_slider_prev 	= $et_slider_arrows.find( settings.prev_arrow );
				$et_slider_next 	= $et_slider_arrows.find( settings.next_arrow );

				$et_slider_next.click( function(){
					et_slider_move_to( 'next' );

					return false;
				} );

				$et_slider_prev.click( function(){
					et_slider_move_to( 'previous' );

					return false;
				} );
			}

			if ( settings.use_controls && et_slides_number > 1 ) {
				for ( var i = 1; i <= et_slides_number; i++ ) {
					controls_html += '<a href="#"' + ( i == 1 ? ' class="' + settings.control_active_class + '"' : '' ) + '>' + i + '</a>';
				}

				controls_html =
					'<div class="' + settings.controls_class + '">' +
						controls_html +
					'</div>';

				if ( settings.append_controls_to == '' )
					$et_slider.append( controls_html );
				else
					$( settings.append_controls_to ).append( controls_html );

				$et_slider_controls	= $et_slider.find( settings.controls ),

				$et_slider_controls.click( function(){
					et_slider_move_to( $(this).index() );

					return false;
				} );
			}

			et_slider_auto_rotate();

			function et_slider_auto_rotate(){
				if ( settings.slideshow && et_slides_number > 1 ) {
					et_slider_timer = setTimeout( function() {
						et_slider_move_to( 'next' );
					}, settings.slideshow_speed );
				}
			}

			function et_slider_move_to( direction ) {
				var $active_slide = $et_slide.eq( et_active_slide ),
					$next_slide;

				if ( direction == 'next' || direction == 'previous' ){

					if ( direction == 'next' )
						et_active_slide = ( et_active_slide + 1 ) < et_slides_number ? et_active_slide + 1 : 0;
					else
						et_active_slide = ( et_active_slide - 1 ) >= 0 ? et_active_slide - 1 : et_slides_number - 1;

				} else {

					if ( et_active_slide == direction ) return;

					et_active_slide = direction;

				}

				$next_slide	= $et_slide.eq( et_active_slide );

				if ( settings.use_controls && et_slides_number > 1 )
					$et_slider_controls.removeClass( settings.control_active_class ).eq( et_active_slide ).addClass( settings.control_active_class );

				if ( settings.on_slide_changing )
    				settings.on_slide_changing( $next_slide );

				$active_slide.animate( { opacity : 0 }, et_fade_speed, function(){
					$(this).css('display', 'none');

					$next_slide.css( { 'display' : 'block', opacity : 0 } ).animate( { opacity : 1 }, et_fade_speed, function(){
						if ( settings.on_slide_change_end )
    						settings.on_slide_change_end( $next_slide );
					} );
				} );

				if ( typeof et_slider_timer != 'undefined' ) {
					clearInterval( et_slider_timer );
					et_slider_auto_rotate();
				}
			}

			$.fn.et_simple_slider.external_move_to = function( slide ) {
				et_slider_move_to( slide );
			}
		} );
	}

	var et_window_width;

	$(document).ready( function(){
		var $et_top_menu = $( 'ul.nav' ),
			$featured_slider = $('#et-slider-wrapper'),
			$et_listings_item = $('#et-listings li'),
			$comment_form = $('form#commentform'),
			$et_filter_form = $('#et-filter-map'),
			$et_list_view = $('#et-list-view'),
			$et_filter_listing_type,
			$et_filter_listing_location,
			$et_filter_listing_rating,
			$et_mobile_listings_item,
			et_filter_options_html = '';

		et_window_width = $(window).width();

		$et_top_menu.superfish({
			delay		: 500, 										// one second delay on mouseout
			animation	: { opacity : 'show', height : 'show' },	// fade-in and slide-down animation
			speed		: 'fast', 									// faster animation speed
			autoArrows	: true, 									// disable generation of arrow mark-up
			dropShadows	: false										// disable drop shadows
		});

		if ( $('ul.et_disable_top_tier').length ) $("ul.et_disable_top_tier > li > ul").prev('a').attr('href','#');

		$('#left-area').fitVids();

		$('.et-place-main-text').tinyscrollbar();

		$et_listings_item.find('.et-mobile-link').click( function( event ) {
			event.stopPropagation();
		} );

		$et_listings_item.click( function(){
			var $this_li = $(this);

			if ( $this_li.hasClass( 'et-active-listing' ) ) return false;

			$this_li.siblings( '.et-active-listing' ).removeClass( 'et-active-listing' );

			$this_li.addClass( 'et-active-listing' );

			google.maps.event.trigger( $("#et_main_map").gmap3({ get: { id: "et_marker_" + $this_li.index() } }), 'click' );
		} );

		if ( $('#et-list-view.et-normal-listings').length ){
			$('#et-list-view.et-normal-listings').append( '<a href="#" class="et-date">' + et_custom.toggle_text + '</a>' );

			$et_list_view = $('#et-list-view.et-normal-listings');

			$et_list_view.find( '.et-date' ).click( function() {

				if ( $et_list_view.hasClass( 'et-listview-open' ) )
					$et_list_view.removeClass( 'et-listview-open' );
				else
					$et_list_view.addClass( 'et-listview-open' );

				return false;
			} );
		}

		if ( $featured_slider.length ){
			$featured_slider.et_simple_slider( {
				slide : '.et-map-slide',
				use_controls : false,
				on_slide_changing : function( $next_slide ){
					google.maps.event.trigger($("#et_main_map").gmap3({ get: { id: "et_marker_" + $next_slide.index() } }), 'click');

					$et_listings_item.filter( '.et-active-listing' ).removeClass( 'et-active-listing' );
					$et_listings_item.eq( $next_slide.index() ).addClass( 'et-active-listing' );

					$et_mobile_listings_item.filter( '.et-active-listing' ).removeClass( 'et-active-listing' );
					$et_mobile_listings_item.eq( $next_slide.index() ).addClass( 'et-active-listing' );
				},
				on_slide_change_end : function( $next_slide ){
					if ( et_window_width >= 960 ) $('.et-place-main-text:visible').tinyscrollbar_update();

					$next_slide.siblings().removeClass( 'et-active-map-slide' );
					$next_slide.addClass( 'et-active-map-slide' );
				}
			} );

			$featured_slider.draggable();
			$featured_slider.draggable('option', 'cancel', '.track');
			$featured_slider.draggable('option', 'cancel', '.thumb');
		}

		(function et_search_bar(){
			var $searchinput = $(".et-search-form .search_input"),
				searchvalue = $searchinput.val();

			$searchinput.focus(function(){
				if (jQuery(this).val() === searchvalue) jQuery(this).val("");
			}).blur(function(){
				if (jQuery(this).val() === "") jQuery(this).val(searchvalue);
			});
		})();

		$comment_form.find('input:text, textarea').each(function(index,domEle){
			var $et_current_input = jQuery(domEle),
				$et_comment_label = $et_current_input.siblings('label'),
				et_comment_label_value = $et_current_input.siblings('label').text();
			if ( $et_comment_label.length ) {
				$et_comment_label.hide();
				if ( $et_current_input.siblings('span.required') ) {
					et_comment_label_value += $et_current_input.siblings('span.required').text();
					$et_current_input.siblings('span.required').hide();
				}
				$et_current_input.val(et_comment_label_value);
			}
		}).bind('focus',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === et_label_text) jQuery(this).val("");
		}).bind('blur',function(){
			var et_label_text = jQuery(this).siblings('label').text();
			if ( jQuery(this).siblings('span.required').length ) et_label_text += jQuery(this).siblings('span.required').text();
			if (jQuery(this).val() === "") jQuery(this).val( et_label_text );
		});

		// remove placeholder text before form submission
		$comment_form.submit(function(){
			$comment_form.find('input:text, textarea').each(function(index,domEle){
				var $et_current_input = jQuery(domEle),
					$et_comment_label = $et_current_input.siblings('label'),
					et_comment_label_value = $et_current_input.siblings('label').text();

				if ( $et_comment_label.length && $et_comment_label.is(':hidden') ) {
					if ( $et_comment_label.text() == $et_current_input.val() )
						$et_current_input.val( '' );
				}
			});
		});

		et_duplicate_menu( $('ul.nav'), $('.mobile_nav'), 'mobile_menu', 'et_mobile_menu' );

		$('body').append( $('#et-list-view').clone().removeClass('et-normal-listings').addClass('et-mobile-listings') );
		$et_mobile_listings_item = $('.et-mobile-listings li');

		function et_duplicate_menu( menu, append_to, menu_id, menu_class ){
			var $cloned_nav;

			menu.clone().attr('id',menu_id).removeClass().attr('class',menu_class).appendTo( append_to );
			$cloned_nav = append_to.find('> ul');
			$cloned_nav.find('.menu_slide').remove();
			$cloned_nav.find('li:first').addClass('et_first_mobile_item');

			append_to.click( function(){
				if ( $(this).hasClass('closed') ){
					$(this).removeClass( 'closed' ).addClass( 'opened' );
					$cloned_nav.slideDown( 500 );
				} else {
					$(this).removeClass( 'opened' ).addClass( 'closed' );
					$cloned_nav.slideUp( 500 );
				}
				return false;
			} );

			append_to.find('a').click( function(event){
				event.stopPropagation();
			} );
		}

		if ( $et_filter_form.length ) {
			$et_filter_listing_type = $et_filter_form.find( '#et-listing-type' );
			$et_filter_listing_location = $et_filter_form.find( '#et-listing-location' );
			$et_filter_listing_rating = $et_filter_form.find( '#et-listing-rating' );

			$et_filter_listing_type.find( 'option' ).each( function() {
				var $this_option = $( this );

				et_filter_options_html += '<li data-value="' + $this_option.attr( 'value' ) + '">' + $this_option.text() + '</li>';
			} );

			$( 'a.listing-type' ).append( '<ul>' + et_filter_options_html + '</ul>' );

			et_filter_options_html = '';

			$et_filter_listing_location.find( 'option' ).each( function() {
				var $this_option = $( this );

				et_filter_options_html += '<li data-value="' + $this_option.attr( 'value' ) + '">' + $this_option.text() + '</li>';
			} );

			$( 'a.listing-location' ).append( '<ul>' + et_filter_options_html + '</ul>' );

			et_filter_options_html = '';

			for ( var i = 5; i >= 1; i-- ) {
				et_filter_options_html += '<li data-value="' + i + '">' + '<span class="et-rating"><span style="width: ' + ( 17 * i ) + 'px;"></span></span>' + '</li>';
			}

			$( 'a.listing-rating' ).append( '<ul>' + '<li data-value="none">' + $( 'a.listing-rating' ).text() + '</li>' + et_filter_options_html + '</ul>' );

			if ( $et_filter_listing_type.find( ':selected' ).length ) {
				$( 'a.listing-type .et_explorable_filter_text' ).text( $et_filter_listing_type.find( ':selected' ).text() );
			}

			if ( $et_filter_listing_location.find( ':selected' ).length ) {
				$( 'a.listing-location .et_explorable_filter_text' ).text( $et_filter_listing_location.find( ':selected' ).text() );
			}

			if ( $et_filter_listing_rating.find( ':selected' ).length ) {
				$( 'a.listing-rating .et_explorable_filter_text' ).html( $( 'a.listing-rating li[data-value=' + $et_filter_listing_rating.find( ':selected' ).val() + ']' ).html() );
			}

			$( 'a.filter-type' ).click( function() {
				var $this_element = $(this);

				if ( $this_element.hasClass( 'filter-type-open' ) ) return false;

				$this_element.addClass( 'filter-type-open' );

				$this_element.siblings( '.filter-type-open' ).each( function() {
					var $this_link = $(this);

					$this_link.removeClass( 'filter-type-open' ).find( 'ul' ).animate( { 'opacity' : 0 }, 500, function() {
						$(this).css( 'display', 'none' );
					} );
				} );

				$this_element.find( 'ul' ).css( { 'display' : 'block', 'opacity' : '0' } ).animate( { 'opacity' : 1 }, 500 );

				return false;
			} );

			$( 'a.filter-type li' ).click( function() {
				var $this_element = $(this),
					$parent_link = $this_element.closest('a'),
					$active_filter_option,
					filter_order;

				$parent_link.find( '.et_explorable_filter_text' ).html( $this_element.html() );
				filter_order = $parent_link.index('.filter-type');
				$active_filter_option = $et_filter_form.find( 'select' ).eq( filter_order );

				$active_filter_option.find( ':selected' ).removeAttr( 'selected' );
				$active_filter_option.find( 'option[value=' + $this_element.attr( 'data-value' ) + ']' ).attr("selected", "selected");

				$parent_link.removeClass( 'filter-type-open' ).find( 'ul' ).animate( { 'opacity' : 0 }, 500, function() {
					$(this).css( 'display', 'none' );
				} );

				return false;
			} );

			$( '.et_filter_arrow, .et_explorable_filter_text' ).click( function( event ) {
				var $this_element = $(this),
					$parent_link = $this_element.closest('a');

				if ( $parent_link.hasClass( 'filter-type-open' ) ) {
					$parent_link.find( 'ul' ).animate( { 'opacity' : 0 }, 500, function() {
						$(this).css( 'display', 'none' );
					} );

					$parent_link.removeClass( 'filter-type-open' );

					return false;
				}
			} );
		}
	});

	function et_listing_make_fluid() {
		var $et_main_map = $( '#et_main_map' ),
			new_listing_height;

		if ( $et_main_map.length ) {
			if ( et_window_width < 960 )
				new_listing_height = $('.et-normal-listings #et-listings .overview ul').height();
			else
				new_listing_height = $('.et-normal-listings').height() - 81;

			$('.et-normal-listings #et-listings, .et-normal-listings .viewport').height( new_listing_height );

			$('.et-normal-listings #et-listings').tinyscrollbar_update();
		}
	}

	$(window).load( function(){
		$('.et-normal-listings #et-listings').tinyscrollbar();

		et_listing_make_fluid();

		if ( $("#et_main_map").length )
			google.maps.event.trigger( $("#et_main_map").gmap3({ get: { id: "et_marker_0" } }), 'click' );
	} );

	$(window).resize( function(){
		var $et_main_map = $( '#et_main_map' ),
			$et_single_map = $( '#et-single-map' );

		et_window_width = $(window).width();

		if ( $et_main_map.length ) {
			$et_main_map.gmap3("get").panTo( et_active_marker.position );

			if ( $('.et-place-main-text:visible') ) $('.et-place-main-text:visible').tinyscrollbar_update();

			et_listing_make_fluid();
		}

		if ( $et_single_map.length ) {
			$et_single_map.gmap3("get").panTo(
				$et_single_map.gmap3({
					get: {
						id : 'et_single_marker'
					}
				}).position
			);
		}
	} );
})(jQuery)