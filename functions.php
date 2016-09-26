<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

if (!defined('MS_THEME_REV'))
	define('MS_THEME_REV', json_decode(file_get_contents(dirname(__FILE__) . '/config.json'))[0]->rev);
if (!defined('MS_THEME_VERSION'))
	define('MS_THEME_VERSION', '0.1.0');
if (!defined('MS_FILE_MANAGER_URI'))
	define('MS_FILE_MANAGER_URI', get_site_url(false) . 'plugins/file_manager/');

function all_pages() {
	global $pagesArray;
	return $pagesArray;
}

function filter_by_tags($arr, $tags, $all = false) {
	$res = array();
	if (gettype($tags) == 'string') {
		$tags = array($tags);
	}
	if ($all) { // all tags match
		foreach ($arr as $item) {
			if (
				property_exists($item, 'tags') &&
				isset($item->tags) &&
				!array_diff(
					$tags,
					$item->tags
			)) {
				$res[] = $item;
			}
		}
	} else { // any tags match
		foreach ($arr as $item) {
			if (
				property_exists($item, 'tags') &&
				isset($item->tags) &&
				!!array_intersect(
					$tags,
					$item->tags
			)) {
				$res[] = $item;
			}
		}
	}

	return $res;
}

function get_page($slug){
	$file = 'data/pages/'. $slug .'.xml';
	$content = '';
	if (file_exists($file)){
		$data_index = getXML($file);
		$content = stripslashes(
			html_entity_decode(
				$data_index->content,
				ENT_QUOTES,
				'UTF-8'
		));
	}
	return $content;
}

// retrieve and re-format pages arrays as objects
function get_pages($args = array()) {
	if (!isset($args['limit'])) $args['limit'] = 10;
	if (!isset($args['pages'])) $args['pages'] = all_pages();

	$pages = array();
	$count = 0;

	foreach ($args['pages'] as $key => $value) {
		$pages[$count] = (object) $value;

		// add page data_type for parsing merged content later
		$pages[$count]->data_type = 'page';

		// format other relevant data
		$pages[$count]->publish_date = strtotime($value['pubDate']);
		$pages[$count]->content = get_page($key);
		$pages[$count]->tags = explode(',', $value['meta']);
		$count += 1;
		if ($count == $args['limit']) break;
	}

	return $pages;
}

function render_page($key) {
	if ( // render page
		property_exists($key, 'data_type') &&
		$key->data_type == 'page'
	) {
		echo $key->content;
	} else { // is a file, figure out what kind and render it
		echo '<img src="' . MS_FILE_MANAGER_URI . $key->file_path .'">';
	}
}

function render_pages($arr) {
	foreach ($arr as $page) {
		echo '<div class="container">';
		render_page($page);
		echo '</div>';
	}
}

function order($arr, $cmp = 'desc') {
	function desc($a, $b) {
		if ($a->publish_date == $b->publish_date) return 0;
		return ($a->publish_date < $b->publish_date) ? -1 : 1;
	}
	function asc($a, $b) {
		if ($a->publish_date == $b->publish_date) return 0;
		return ($a->publish_date > $b->publish_date) ? 1 : -1;
	}
	usort($arr, $cmp);
	return $arr;
}

// convenience for merging tags and pages
function merge_content($arr1, $arr2) {
	return array_merge($arr1, $arr2);
}

function get_tagged_content() {
	$json = file_get_contents(GSPLUGINPATH .'/file_manager/uploads/metadata.json');
	$data = json_decode($json);

	// remove items without publish_date
	foreach ($data as $key => $item) {
		if (!property_exists($item, 'publish_date')) {
			unset($data[$key]);
		}
	}

	return $data;
}
