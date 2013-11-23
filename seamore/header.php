<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta name="author" content="Yaroslav Hrabar, Viktor Malafey">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type') ?>; charset=<?php bloginfo('charset') ?>" />
<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url') ?>" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style.ie6.css" type="text/css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style.ie7.css" type="text/css" media="screen" /><![endif]-->
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/jquery.tooltip.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/buttons.css" type="text/css" media="screen" />
<?php if(WP_VERSION < 3.0): ?>
<link rel="alternate" type="application/rss+xml" title="<?php printf(__('%s RSS Feed', THEME_NS), get_bloginfo('name')); ?>" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php printf(__('%s Atom Feed', THEME_NS), get_bloginfo('name')); ?>" href="<?php bloginfo('atom_url'); ?>" />
<?php endif; ?>
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php
remove_action('wp_head', 'wp_generator');
wp_enqueue_script('jquery');
if ( is_singular() && get_option( 'thread_comments' ) ) {
	wp_enqueue_script( 'comment-reply' );
}
wp_head(); ?>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/script.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.tooltip.min.js"></script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26724670-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
</head>
<body <?php if(function_exists('body_class')) body_class(); ?>>
<div id="art-page-background-middle-texture">
<div id="art-page-background-glare">
    <div id="art-page-background-glare-image"> </div>
</div>
<div id="art-main">
    <div class="cleared reset-box"></div>
    <div class="art-header">
    <div class="art-header-wrapper">
    <div class="art-header-inner">
        <div class="art-logo">
        <?php if(theme_get_option('theme_header_show_headline')): ?>
        <h1 class="art-logo-name"><a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1>
        <?php endif; ?>
        <?php if(theme_get_option('theme_header_show_slogan')): ?>
            <h2 class="art-logo-text"><?php bloginfo('description'); ?></h2>
        <?php endif; ?>
        </div>
    </div>
    </div>
    </div>
    <div class="cleared reset-box"></div>
    <div class="art-nav">
    	<div class="art-nav-l"></div>
    	<div class="art-nav-r"></div>
        <div class="art-nav-outer">
        <div class="art-nav-wrapper">
        <div class="art-nav-inner">
    	<?php 
    		echo theme_get_menu(array(
    				'source' => theme_get_option('theme_menu_source'),
    				'depth' => theme_get_option('theme_menu_depth'),
    				'menu' => 'primary-menu',
    				'class' => 'art-hmenu'	
    			)
    		);
    	?>
        <!-- # start category menu -->
        <?php category_menu(); ?>
        <!-- #end category menu -->

        </div>
        </div>
        </div>
    </div>
    <div class="cleared reset-box"></div>
    <div class="art-sheet">
        <div class="art-sheet-tl"></div>
        <div class="art-sheet-tr"></div>
        <div class="art-sheet-bl"></div>
        <div class="art-sheet-br"></div>
        <div class="art-sheet-tc"></div>
        <div class="art-sheet-bc"></div>
        <div class="art-sheet-cl"></div>
        <div class="art-sheet-cr"></div>
        <div class="art-sheet-cc"></div>
        <div class="art-sheet-body">
