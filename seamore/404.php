<?php get_header(); ?>
<div class="art-content-layout">
    <div class="art-content-layout-row">
        <div class="art-layout-cell art-content">
			<?php get_sidebar('top'); ?>
			<?php theme_404_content(); ?>
			<?php get_sidebar('bottom'); ?>
          <div class="cleared"></div>
        </div>
        <div class="art-layout-cell art-sidebar1">
          <?php get_sidebar('default'); ?>
          <div class="cleared"></div>
        </div>
    </div>
</div>
<div class="cleared"></div>
<?php get_footer(); ?>