
<nav class="nav__filter">
  <ul>
    <?php
      foreach (menu_data() as $menu_item) {
        $data = get_page_data($menu_item['slug']);
        $url = (string) $data->url;
        if (
          $data->template == 'category.php' &&
          $data->title != get_page_title(false)
        ) {

          $query_str = explode('&', preg_replace('~^id=~', '', $_SERVER['QUERY_STRING']));

          // add to query string
          $add_filters = $query_str;
          $add_filters[] = $url;

          // remove from query string
          $remove_filters = $query_str;
          unset($remove_filters[array_search($url, $remove_filters)]); ?>

        <?php if (!in_array($url, $query_str)): ?>
          <li><a class="nav__filter--add" href="<?= link_to_query($add_filters); ?>"><?= $data->title; ?></a></li>
        <?php endif; ?>

        <?php if (in_array($url, $query_str)): ?>
          <li><a class="nav__filter--remove" href="<?= link_to_query($remove_filters); ?>"><?= $data->title; ?></a></li>
        <?php endif; ?>

    <?php } } ?>
   </ul>
</nav>
