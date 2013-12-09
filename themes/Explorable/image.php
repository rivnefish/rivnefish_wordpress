<?php get_header(); ?>

<div id="main-area">
	<div class="container">
		<?php get_template_part( 'includes/breadcrumbs', 'image' ); ?>

		<div id="content" class="clearfix">
			<div id="left-area">
				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'image-attachment' ); ?>>
						<div class="entry-attachment">
							<h1 class="title"><?php the_title(); ?></h1>
							<div class="attachment">
<?php
/**
* Grab the IDs of all the image attachments in a gallery so we can get the URL of the next adjacent image in a gallery,
* or the first image (if we're looking at the last image in a gallery), or, in a gallery of one, just the link to that image file
*/
$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
foreach ( $attachments as $k => $attachment ) :
if ( $attachment->ID == get_the_ID() )
break;
endforeach;

$k++;
// If there is more than 1 attachment in a gallery
if ( count( $attachments ) > 1 ) :
if ( isset( $attachments[ $k ] ) ) :
// get the URL of the next image attachment
$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
else :
// or get the URL of the first image attachment
$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
endif;
else :
// or, if there's only 1 image, get the URL of the image
$next_attachment_url = wp_get_attachment_url();
endif;
?>
						<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment"><?php
						$attachment_size = apply_filters( 'explorable_attachment_size', array( 960, 960 ) );
						echo wp_get_attachment_image( get_the_ID(), $attachment_size );
						?></a>

						<?php if ( ! empty( $post->post_excerpt ) ) : ?>
						<div class="entry-caption">
							<?php the_excerpt(); ?>
						</div>
						<?php endif; ?>
					</div><!-- .attachment -->

				</div><!-- .entry-attachment -->

				<div id="image-navigation" class="navigation clearfix">
					<span class="previous-image"><?php previous_image_link( false, __( '&larr; Previous', 'Explorable' ) ); ?></span>
					<span class="next-image"><?php next_image_link( false, __( 'Next &rarr;', 'Explorable' ) ); ?></span>
				</div><!-- #image-navigation -->

				<div class="entry-description">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Explorable' ), 'after' => '</div>' ) ); ?>
				</div><!-- .entry-description -->
			</article> <!-- .image-attachment -->

			<?php comments_template(); ?>

		<?php endwhile; ?>

			</div> <!-- end #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- end #content -->
	</div> <!-- end .container -->
</div> <!-- end #main-area -->

<?php get_footer(); ?>