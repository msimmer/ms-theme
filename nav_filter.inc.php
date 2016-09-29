
<nav class="nav__filter">
  <ul>
    <?php

      $page_slug = (string) get_slug();
      foreach (menu_data() as $menu_item) {
        $data = get_page_data($menu_item['slug']);
        $url = (string) $data->url;
        if (
          $data->template == 'category.php' &&
          $url != $page_slug
        ) {

          $query_str = explode('&', preg_replace('~^id=~', '', $_SERVER['QUERY_STRING']));

          // add to query string
          $add_filters = $query_str;
          $add_filters[] = get_slug();
          unset($add_filters[array_search($page_slug, $add_filters)]);

          // remove from query string
          $remove_filters = $query_str;
          unset($remove_filters[array_search($url, $remove_filters)]);
          unset($remove_filters[array_search($page_slug, $remove_filters)]); ?>

        <?php if (!in_array($url, $query_str)): ?>
          <li><a class="nav__filter--add" href="<?= link_to_query($add_filters); ?>"><?= $data->title; ?></a></li>
        <?php endif; ?>

        <?php if (in_array($url, $query_str)): ?>
          <li><a class="nav__filter--remove" href="<?= !empty($remove_filters) ? link_to_query($remove_filters) : get_site_url(false) . $page_slug . '/'; ?>"><?= $data->title; ?></a></li>
        <?php endif; ?>

    <?php } } ?>
   </ul>
</nav>
