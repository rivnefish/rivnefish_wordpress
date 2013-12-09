<?php
/*
Template Name: Full Width Page
*/
?>
<?php get_header(); ?>

<div id="main-area">
	<div class="container fullwidth">
		<?php get_template_part( 'includes/breadcrumbs', 'page' ); ?>

		<div id="content" class="clearfix">
			<div id="left-area">
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', get_post_format() ); ?>

					<?php
						if ( comments_open() && 'on' == et_get_option( 'explorable_show_pagescomments', 'false' ) )
							comments_template( '', true );
					?>

				<?php endwhile; ?>
			</div> <!-- end #left-area -->
		</div> <!-- end #content -->
	</div> <!-- end .container -->
</div> <!-- end #main-area -->

<?php get_footer(); ?>