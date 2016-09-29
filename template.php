<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<?php include('header.inc.php'); ?>
<main><?php apply_filter(returnPageContent(get_slug()), 'content_shortcodes'); ?></main>
<?php include('footer.inc.php'); ?>
