<?php
	$index_postinfo = et_get_option( 'explorable_postinfo1' );

	$thumb = '';
	$width = (int) apply_filters( 'et_blog_image_width', 640 );
	$height = (int) apply_filters( 'et_blog_image_height', 280 );
	$classtext = '';
	$titletext = get_the_title();
	$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Indeximage' );
	$thumb = $thumbnail["thumb"];
?>
<article class="entry-post clearfix">
<?php if ( 'on' == et_get_option( 'explorable_thumbnails_index', 'on' ) && '' != $thumb ) { ?>
	<div class="post-thumbnail">
		<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
		<div class="post-description">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php
			if ( $index_postinfo ) {
				echo '<p class="meta-info">';
				et_postinfo_meta( $index_postinfo, et_get_option( 'explorable_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Explorable' ), esc_html__( '1 comment', 'Explorable' ), '% ' . esc_html__( 'comments', 'Explorable' ) );
				echo '</p>';
			}
		?>
		</div> <!-- .post-description -->
	<?php if ( ( $author_avatar = get_avatar( get_the_author_meta( 'ID' ), 61 ) ) && 'on' == et_get_option( 'explorable_show_avatar_on_posts', 'on' ) && '' != $author_avatar ) : ?>
		<?php echo '<span class="et-avatar">' . $author_avatar . '</span>'; ?>
	<?php endif; ?>
	</div> <!-- .post-thumbnail -->
<?php } else { ?>
	<div class="post-description no_thumb">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php
			if ( $index_postinfo ) {
				echo '<p class="meta-info">';
				et_postinfo_meta( $index_postinfo, et_get_option( 'explorable_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Explorable' ), esc_html__( '1 comment', 'Explorable' ), '% ' . esc_html__( 'comments', 'Explorable' ) );
				echo '</p>';
			}
		?>
	</div> <!-- .post-description -->
<?php } ?>
	<?php if ( 'false' == et_get_option( 'explorable_blog_style', 'false' ) ) { ?>
		<p><?php truncate_post( 370 ); ?></p>
	<?php } else { ?>
		<?php the_content( '' ); ?>
	<?php } ?>
	<a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e( 'Read More', 'Explorable' ); ?></a>
</article> <!-- .entry-post -->