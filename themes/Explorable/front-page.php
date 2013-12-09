<?php if ( is_front_page() && is_page() ) { include( get_page_template() ); return; } ?>

<?php get_header(); ?>

<?php if ( have_posts() ) : ?>

	<?php get_template_part( 'includes/fullwidth_map', 'front_page' ); ?>

<?php endif; ?>

<?php get_footer(); ?>