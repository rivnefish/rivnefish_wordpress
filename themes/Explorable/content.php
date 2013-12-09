<?php
/**
 * The template for displaying posts on single pages
 *
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix entry-post' ); ?>>
<?php
	$single_postinfo = et_get_option( 'explorable_postinfo2' );

	if ( is_page() ) {
		$et_ptemplate_settings = maybe_unserialize( get_post_meta( get_the_ID(), 'et_ptemplate_settings', true ) );
		$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
	}

	$et_fullwidth = ( isset( $fullwidth ) && $fullwidth ) || is_page_template( 'page-full.php' );

	$thumb = '';

	$width = (int) apply_filters( 'et_blog_image_width', 640 );
	if ( $et_fullwidth )
		$width = (int) apply_filters( 'et_blog_image_width_fullwidth', 960 );
	$height = (int) apply_filters( 'et_blog_image_height', 280 );

	$classtext = '';
	$titletext = get_the_title();
	$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Indeximage' );
	$thumb = $thumbnail["thumb"];

	$show_thumb = is_page() ? et_get_option( 'explorable_page_thumbnails', 'false' ) : et_get_option( 'explorable_thumbnails', 'on' );
?>

<?php if ( '' != $thumb && 'false' != $show_thumb ) { ?>
	<div class="post-thumbnail">
		<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
		<div class="post-description">
			<h1><?php the_title(); ?></h1>
		<?php
			if ( $single_postinfo && ! is_page() ) {
				echo '<p class="meta-info">';
				et_postinfo_meta( $single_postinfo, et_get_option( 'explorable_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Explorable' ), esc_html__( '1 comment', 'Explorable' ), '% ' . esc_html__( 'comments', 'Explorable' ) );
				echo '</p>';
			}
		?>
		</div> <!-- .post-description -->
	<?php if ( ( $author_avatar = get_avatar( get_the_author_meta( 'ID' ), 61 ) ) && 'on' == et_get_option( 'explorable_show_avatar_on_posts', 'on' ) && '' != $author_avatar && ! is_page() ) : ?>
		<?php echo '<span class="et-avatar">' . $author_avatar . '</span>'; ?>
	<?php endif; ?>
	</div> <!-- .post-thumbnail -->
<?php } ?>

	<div class="entry-content">
	<?php if ( is_page() && ( '' == $thumb || 'false' == $show_thumb ) ) : ?>
		<h1 class="title"><?php the_title(); ?></h1>
	<?php endif; ?>
	<?php
		the_content();
		wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Explorable' ), 'after' => '</div>' ) );
	?>
	</div> <!-- .entry-content -->
</article> <!-- end .entry-post-->