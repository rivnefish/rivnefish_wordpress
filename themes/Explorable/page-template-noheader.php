<?php
/*
Template Name: No Header/Footer Page
*/
?>
<?php $hide_head_bg = true; ?>
<?php $hide_footer = true; ?>

<?php include 'header.php'; ?>

<?php while ( have_posts() ) : the_post(); ?>
<?php the_content(); ?>
<?php endwhile; ?>

<?php include 'footer.php'; ?>