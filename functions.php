<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

/*

	Todo:
	 - support pagination (infinite scroll)
	 - merge fragments with pages for pagination
	 - add secondary filter functionality
	 - make things less case-sensitive

	Feature ideas:
	 - add grouping to display contents as a block without having to add them to a post
	 - ajaxify?
	 - port for flatfile re: query strings?

*/

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

function filter_by_tags($arr, $tags, $match_all = false) {
	$res = array();
	if (gettype($tags) == 'string') {
		$tags = array($tags);
	}
	if ($match_all) { // all tags must match
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

		// omit collections pages
		if ($value['template'] == 'category.php' || $value['template'] == 'homepage.php') continue;

		$pages[$count] = (object) $value;

		// add page data_type for parsing merged content later
		$pages[$count]->data_type = 'page';

		// format other relevant data
		$pages[$count]->publish_date = strtotime($value['pubDate']);
		$pages[$count]->content = get_page($key);
		$pages[$count]->tags = explode(',', $value['meta']);
		$pages[$count]->url = $value['url'] == 'index' ? '/' : get_site_url(false) . 'index.php?id=' . $value['url'];
		$count += 1;

		if ($count == $args['limit']) break;
	}

	return $pages;
}

function render_page($key, $print = false) {
	if ( // render page
		property_exists($key, 'data_type') &&
		$key->data_type == 'page'
	) {
		$html = '';
		$html .= '<article>';
		$html .= '<div class="container">';
		$html .= '<div class="row">';
		$html .= '<div class="twelve columns">';
		$html .= $key->content;
		$html .= '</div></div></div></article>';
		echo $html;
	} else { // is a file, figure out what kind and render it
		$html = '';
		switch ($key->mime_type) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$html .= '<img alt="'. $key->name .'" src="' . MS_FILE_MANAGER_URI . $key->file_path .'">';
				break;

			case 'video/webm':
	      $html = '';
	      $html .= '<video controls preload="auto">';
	      $html .= '<source src="'. MS_FILE_MANAGER_URI . $key->file_path .'" type="video/webm">';
	      $html .= '<source src="'. MS_FILE_MANAGER_URI . preg_replace('~\.webm$~', '.mp4', $key->file_path) .'" type="video/mp4">';
	      $html .= 'Your browser doesn\'t support HTML5 video tag.';
	      $html .= '</video>';
	     	break;

			case 'audio/ogg':;
				$html .= '<audio controls preload="auto">';
				$html .= '<source src="' . MS_FILE_MANAGER_URI . $key->file_path . '">';
				$html .= '<source src="' . MS_FILE_MANAGER_URI . preg_replace('~\.ogg$~', '.mp3', $key->file_path) . '">';
				$html .= '</audio>';
				break;

			case 'video/mp4': // served by webm case
			case 'audio/mpeg3': // served by ogg case
			case 'audio/x-mpeg-3':
				break;

			// TODO
			case 'application/pdf':
				break;

			case 'text/plain':
				break;

			default:
				$html .= 'Unsupported content type: ' . $key->mime_type;
				break;
		}
		if ($print) {
			echo $html;
		} else {
			return $html;
		}
	}
}

function render_pages($arr) {
	foreach ($arr as $page) {
		$html = '';
		$html .= '<article>';
		$html .= '<div class="container">';
		$html .= '<div class="row">';
		$html .= '<div class="twelve columns">';
		$html .= render_page($page, false);
		$html .= '</div></div></div></article>';
		$html .= '<hr>';
		echo $html;
	}
}

function desc($a, $b) {
	if ($a->publish_date == $b->publish_date) return 0;
	return ($a->publish_date < $b->publish_date) ? -1 : 1;
}
function asc($a, $b) {
	if ($a->publish_date == $b->publish_date) return 0;
	return ($a->publish_date > $b->publish_date) ? -1 : 1;
}

function order($arr, $cmp = 'desc') {
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
