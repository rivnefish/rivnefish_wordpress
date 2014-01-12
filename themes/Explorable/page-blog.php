<?php
/*
Template Name: Blog Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta( get_the_ID(), 'et_ptemplate_settings', true ) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;

$et_ptemplate_blogstyle = isset( $et_ptemplate_settings['et_ptemplate_blogstyle'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_blogstyle'] : false;

$et_ptemplate_showthumb = isset( $et_ptemplate_settings['et_ptemplate_showthumb'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_showthumb'] : false;

$blog_cats = isset( $et_ptemplate_settings['et_ptemplate_blogcats'] ) ? (array) array_map( 'intval', $et_ptemplate_settings['et_ptemplate_blogcats'] ) : array();
$et_ptemplate_blog_perpage = isset( $et_ptemplate_settings['et_ptemplate_blog_perpage'] ) ? (int) $et_ptemplate_settings['et_ptemplate_blog_perpage'] : 10;
?>
<?php get_header(); ?>

<div id="main-area">
	<div class="container<?php if ( $fullwidth ) echo ' fullwidth'; ?>">
		<?php get_template_part( 'includes/breadcrumbs', 'page' ); ?>

		<div id="content" class="clearfix">
			<div id="left-area">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; ?>

				<?php $cat_query = '';
				if ( !empty($blog_cats) ) $cat_query = '&cat=' . implode(",", $blog_cats);
				else echo '<!-- blog category is not selected -->'; ?>
				<?php
					$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
				?>
				<?php query_posts("posts_per_page=$et_ptemplate_blog_perpage&paged=" . $et_paged . $cat_query); ?>
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php require 'includes/entry.php' ?>

				<?php endwhile; ?>
					<div class="page-nav clearfix">
						<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
						else { ?>
							 <?php get_template_part('includes/navigation'); ?>
						<?php } ?>
					</div> <!-- end .entry -->
				<?php else : ?>
					<?php get_template_part('includes/no-results'); ?>
				<?php endif; wp_reset_query(); ?>

			</div> <!-- end #left-area -->

			<?php if ( ! $fullwidth ) get_sidebar(); ?>
		</div> <!-- end #content -->
	</div> <!-- end .container -->
</div> <!-- end #main-area -->

<?php get_footer(); ?>