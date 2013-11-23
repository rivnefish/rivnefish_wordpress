	<?php
		global $post;
		ob_start();

		$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
		if ( $images ) {
			$total_images = count( $images );
			$image = array_shift( $images );
			$image_img_tag = wp_get_attachment_image( $image->ID, 'thumbnail' );
	?>
			<div class="gallery-thumb">
				<a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
			</div><!-- .gallery-thumb -->
			<p><em><?php printf( _n( 'This gallery contains <a %1$s>%2$s photo</a>.', 'This gallery contains <a %1$s>%2$s photos</a>.', $total_images, THEME_NS ),
					'href="' . get_permalink() . '" title="' . sprintf( esc_attr__( 'Permalink to %s', THEME_NS ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark"',
					number_format_i18n( $total_images )
				); ?></em>
			</p>
	<?php 
		}
		
		echo theme_get_excerpt();
		
		theme_post_wrapper(
			array(
					'id' => theme_get_post_id(), 
					'class' => theme_get_post_class(),
					'thumbnail' => theme_get_post_thumbnail(),
					'title' => '<a href="' . get_permalink( $post->ID ) . '" rel="bookmark" title="' . get_the_title() . '">' . get_the_title() . '</a>', 
					'before' => theme_get_metadata_icons( 'date,author,edit', 'header' ),
					'content' => ob_get_clean(), 
					'after' => theme_get_metadata_icons( 'category,tag,comments', 'footer' )
			)
		);
