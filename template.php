<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

include('header.inc.php'); ?>

	<main>

    <?php

      // filtered content
      //
      // $files = get_tagged_content();
      // $files_foo = filter_by_tags($files, 'konig 1995');
      // $pages = get_pages();
      // $pages_foo = filter_by_tags($pages, 'konig 1995');
      // $content_all = merge_content($files_foo, $pages_foo);
      // $sorted_content = order($content_all);

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
