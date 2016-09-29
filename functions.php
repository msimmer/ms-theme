<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }

/*

	Todo:
	 - support pagination (infinite scroll)
	 - merge fragments with pages for pagination
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

function get_page_data($slug = false) {
	$data = NULL;
	$slug = $slug == false ? get_slug() : $slug;
	$file = 'data/pages/'. $slug .'.xml';
	if (file_exists($file)) $data = getXML($file);
	return $data;
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
		if (
			$value['template'] == 'category.php' ||
			$value['template'] == 'homepage.php'
		) continue;

		$pages[$count] = (object) $value;

		// add page data_type for parsing merged content later
		$pages[$count]->data_type = 'page';

		// format other relevant data
		$pages[$count]->publish_date = strtotime($value['pubDate']);
		$pages[$count]->content = get_page($key);
		$pages[$count]->tags = explode(',', $value['meta']);
		$pages[$count]->url = $value['url'] == 'index' ? '/' : link_to($value['url']);
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
		$html .= wrap(apply_filter($key->content, 'excerpt_shortcodes', false));


	} else { // is a file, figure out what kind and render it

		$html = '';
		switch ($key->mime_type) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$inner = wrap('<img alt="'. $key->name .'" src="' . MS_FILE_MANAGER_URI . $key->file_path .'">');
				$html .= $inner;
				break;

			case 'video/webm':
				$inner = '';
	      $inner .= '<video controls preload="auto">';
	      $inner .= '<source src="'. MS_FILE_MANAGER_URI . $key->file_path .'" type="video/webm">';
	      $inner .= '<source src="'. MS_FILE_MANAGER_URI . preg_replace('~\.webm$~', '.mp4', $key->file_path) .'" type="video/mp4">';
	      $inner .= 'Your browser doesn\'t support inner5 video tag.';
	      $inner .= '</video>';
	      $html .= wrap($inner);
	     	break;

			case 'audio/ogg':
				$inner = '';
				$inner .= '<audio controls preload="auto">';
				$inner .= '<source src="' . MS_FILE_MANAGER_URI . $key->file_path . '">';
				$inner .= '<source src="' . MS_FILE_MANAGER_URI . preg_replace('~\.ogg$~', '.mp3', $key->file_path) . '">';
				$inner .= '</audio>';
				$html .= wrap($inner);
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
				$html .= wrap('Unsupported content type: ' . $key->mime_type);
				break;
		}
	}
	if ($print === true) {
		echo $html;
	} elseif ($print === false) {
		return $html;
	}
}

function render_pages($arr) {
	foreach ($arr as $page) {
		$html = '';
		$html .= '<article>';
		$html .= render_page($page, false);
		$html .= '</article>';
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

function wrap($content) {
	return <<< EOT
	<div class="container">
	<div class="row">
	<div class="twelve columns">{$content}</div>
	</div>
	</div>
EOT;
}

function content_shortcodes($content, $print = true) {

	$html = '';

	// get shortcodes for inner content

	// [text]
	preg_match_all(
		'~<[^>]+>\s*\[text\]\s*</[^>]+>(.*)\[/text\]\s*</[^>]+>~s',
		$content,
		$match_text
	);

	// [gallery]
	preg_match_all(
		'~<[^>]+>\s*\[gallery\]\s*</[^>]+>(.*)\[/gallery\]\s*</[^>]+>~s',
		$content,
		$match_gallery
	);

	// parse gallery content
	$gallery = array();
	if (!empty($match_gallery[1])) {
		$gallery = array_map(function(&$item) {
			return preg_replace('~<([^>\s]+)\s*(?:[^>]+)?>(<img[^>]+>)</\1>~', '$2', $item);
		}, $match_gallery[1]);
	}


	// concatenate parsed content
	if (!empty($match_text[1])) {
		$html .= wrap($match_text[1][0]);
	};
	if (!empty($gallery)) {
		$gallery_str = '';
		foreach ($gallery as $image) {
			$gallery_str .= $image;
		}
		$html .= wrap($gallery_str);
	}

	if ($print === true) {
		echo $html;
	} elseif ($print === false) {
		return $html;
	}
}

function excerpt_shortcodes($content, $print = true) {

	// [excerpt]
	preg_match_all(
		'~<[^>]+>\s*\[excerpt\]\s*</[^>]+>(.*)\[/excerpt\]\s*</[^>]+>~s',
		$content,
		$match_excerpt
	);

	if (!empty($match_excerpt[1])) {
		if ($print === true) {
			echo $match_excerpt[1][0];
		} elseif ($print === false) {
			return $match_excerpt[1][0];
		}
	}
}

function apply_filter($content, $filter, $print) {
	return call_user_func($filter, $content, $print);
}

// convenience method to merge tags and pages
function merge_content($arr1, $arr2) {
	return array_merge($arr1, $arr2);
}

// convenience method to return page slug
function get_slug() {
	return get_page_slug(false);
}

function link_to($id) {
	return get_site_url(false) . 'index.php?id=' . $id;
}

function link_to_query($query_str) {
	$query_str = is_array($query_str) ? $query_str : array($query_str);
	return link_to(implode('&', $query_str));
}
