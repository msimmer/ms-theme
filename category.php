<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<?php include('header.inc.php'); ?>

  <main>

    <?php

      $request = array_map(function(&$val) {
        return ucfirst($val);
      }, explode(
        '&',
        str_replace(
          'id=',
          '',
          $_SERVER['QUERY_STRING'])
        )
      );

      $category = get_page_title(false);
      $files = filter_by_tags(get_tagged_content(), $request, true);
      $pages = filter_by_tags(get_pages(), $category);
      $content = order(merge_content($files, $pages));

      render_pages($content);

    ?>

  </main>

<?php include('footer.inc.php'); ?>
