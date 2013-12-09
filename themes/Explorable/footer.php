<?php if ( ! et_is_listing_page() || ( is_single() && 'listing' == get_post_type() ) ) : ?>
	<footer id="main-footer">
		<div class="container">
			<?php get_sidebar( 'footer' ); ?>

			<p id="copyright"><?php printf( __( 'Designed by %1$s | Powered by %2$s', 'Explorable' ), '<a href="http://www.elegantthemes.com" title="Premium WordPress Themes">Elegant Themes</a>', '<a href="http://www.wordpress.org">WordPress</a>' ); ?></p>
		</div> <!-- end .container -->
	</footer> <!-- end #main-footer -->
<?php endif; ?>

	<?php wp_footer(); ?>
</body>
</html>