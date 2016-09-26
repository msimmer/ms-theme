<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

# Get this theme's settings based on what was entered within its plugin.
# This function is in functions.php
// $innov_settings = tags_Settings();

include('header.inc.php'); ?>

	<main>

    <?php

      $files = get_tagged_content();
      $files_foo = filter_by_tags($files, 'foo');
      $pages = get_pages();
      $pages_foo = filter_by_tags($pages, 'foo');
      $content_all = merge_content($files_foo, $pages_foo);
      $sorted_content = order($content_all);


      render_pages($sorted_content);

    ?>

  	<?php include('sidebar.inc.php'); ?>

	</main>

<?php include('footer.inc.php'); ?>
