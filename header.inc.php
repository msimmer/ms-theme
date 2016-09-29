<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<!DOCTYPE html>
<head>
	<title><?php get_site_name(); ?></title>
	<meta name="robots" content="index, follow">
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
  <link href="<?php get_theme_url(); ?>/images/icons/favicon.ico?" rel="shortcut icon">
  <link href="<?php get_theme_url(); ?>/images/icons/touch.png?" rel="apple-touch-icon-precomposed">
	<link href="<?php get_theme_url(); ?>/assets/stylesheets/<?php echo MS_THEME_REV; ?>.css" rel="stylesheet">
  <?php get_header(); ?>
  <style type="text/css">
    /*body { opacity: 1; }*/
  </style>
</head>
  <body class="<?php (get_slug() == 'index' ? 'index' : 'default'); ?>">

  	<header class="container">
     <nav class="nav__main twelve columns">
       <ul><?php get_navigation(true); ?></ul>
     </nav>
     <?php
     if (get_page_data()->template == 'category.php') {
      include 'nav_filter.inc.php';
     } ?>
    </header>
