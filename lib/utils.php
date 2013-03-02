<?php
/**
 * Theme wrapper
 *
 * @link http://scribu.net/wordpress/theme-wrappers.html
 */
function p2_template_path() {
	return p2_Wrapping::$main_template;
}

function p2_sidebar_path() {
	return p2_Wrapping::sidebar();
}

class p2_Wrapping {
	// Stores the full path to the main template file
	static $main_template;

	// Stores the base name of the template file; e.g. 'page' for 'page.php' etc.
	static $base;

	static function wrap($template) {
		self::$main_template = $template;

		self::$base = substr(basename(self::$main_template), 0, -4);

		if (self::$base === 'index') {
			self::$base = false;
		}

		$templates = array('base.php');

		if (self::$base) {
			array_unshift($templates, sprintf('base-%s.php', self::$base));
		}

		return locate_template($templates);
	}

	static function sidebar() {
		$templates = array('templates/sidebar.php');

		if (self::$base) {
			array_unshift($templates, sprintf('templates/sidebar-%s.php', self::$base));
		}

		return locate_template($templates);
	}
}
add_filter('template_include', array('p2_Wrapping', 'wrap'), 99);

/**
 * Page titles
 */
function p2_title() {
	if (is_home()) {
		if (get_option('page_for_posts', true)) {
			echo get_the_title(get_option('page_for_posts', true));
		} else {
			print('Latest Posts');
		}
	} elseif (is_archive()) {
		$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
		if ($term) {
			echo $term->name;
		} elseif (is_post_type_archive()) {
			echo get_queried_object()->labels->name;
		} elseif (is_day()) {
			printf('Daily Archives: %s', get_the_date());
		} elseif (is_month()) {
			printf('Monthly Archives: %s', get_the_date('F Y'));
		} elseif (is_year()) {
			printf('Yearly Archives: %s', get_the_date('Y'));
		} elseif (is_author()) {
			printf('Author Archives: %s', get_the_author());
		} else {
			single_cat_title();
		}
	} elseif (is_search()) {
		printf('Search Results for %s', get_search_query());
	} elseif (is_404()) {
		print('Not Found');
	} else {
		the_title();
	}
}

/**
 * Opposite of built in WP functions for trailing slashes
 */
function leadingslashit($string) {
	return '/' . unleadingslashit($string);
}

function unleadingslashit($string) {
	return ltrim($string, '/');
}

function add_filters($tags, $function) {
	foreach($tags as $tag) {
		add_filter($tag, $function);
	}
}

function is_element_empty($element) {
	$element = trim($element);
	return empty($element) ? false : true;
}
