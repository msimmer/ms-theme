<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

include('header.inc.php'); ?>

	<main>

    <?php

      // all content
      $files = get_tagged_content();
      $pages = get_pages();
      $content_all = merge_content($files, $pages);
      $sorted_content = order($content_all);

      // display
      render_pages($sorted_content);

    ?>

	</main>

<?php include('footer.inc.php'); ?>
