<?php get_header(); ?>

<div id="main-area">
	<div class="container">
		<?php get_template_part( 'includes/breadcrumbs', '404' ); ?>

		<div id="content" class="clearfix">
			<div id="left-area">
				<?php get_template_part( 'includes/no-results', '404' ); ?>
			</div> <!-- end #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- end #content -->
	</div> <!-- end .container -->
</div> <!-- end #main-area -->

<?php get_footer(); ?>