<?php
	global $post;
	theme_post_wrapper(
		array(
				'id' => theme_get_post_id(), 
				'class' => theme_get_post_class(),
				'content' => theme_get_excerpt(), 
				'after' => theme_get_metadata_icons( 'date,author,comments,edit', 'footer' )
		)
	);
?>
