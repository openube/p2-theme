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

if ( ! class_exisits('p2_cleanup') ):
/**
 * cleans up various bits and pieces in Wordpress
 */
class p2_cleanup
{
	/* register all hooks and filters */
	public static function register()
	{
		/* Clean up wp_head() */
		add_action( 'init', array('p2_cleanup', 'head_cleanup') );
		/* Remove the WordPress version from RSS feeds */
		add_filter( 'the_generator', '__return_false' );
		/* Clean up language_attributes() used in <html> tag */
		add_filter( 'language_attributes', array('p2_cleanup', 'language_attributes') );
		/* clean up CSS link tags */
		add_filter( 'style_loader_tag', array('p2_cleanup', 'style_tag') );
		/* filter body classes */
		add_filter( 'body_class', array('p2_cleanup', 'body_class') );
		/* wrap object embeds */
		add_filter( 'embed_oembed_html', array('p2_cleanup', 'embed_wrap'), 10, 4 );
		add_filter( 'embed_googlevideo', array('p2_cleanup', 'embed_wrap'), 10, 2 );
		/* add t=humbnail class */
		add_filter( 'wp_get_attachment_link', array('p2_cleanup', 'attachment_link_class'), 10, 1);
		/* remove dashboard widgets */
		add_action( 'admin_init', array('p2_cleanup', 'remove_dashboard_widgets') );
		/* clean up excerpts */
		add_filter( 'excerpt_length', array('p2_cleanup', 'excerpt_length') );
		add_filter( 'excerpt_more', array('p2_cleanup', 'excerpt_more') );
		/* remove self-closing tags */
		add_filter( 'get_avatar', array('p2_cleanup', 'remove_self_closing_tags') );
		add_filter( 'comment_id_fields', array('p2_cleanup', 'remove_self_closing_tags') );
		add_filter( 'post_thumbnail_html', array('p2_cleanup', 'remove_self_closing_tags') );
		/* remove blog description */
		add_filter( 'get_bloginfo_rss', array('p2_cleanup', 'remove_default_description') );
		/* allow iframes in tinyMCE	*/
		//add_filter( 'tiny_mce_before_init', array('p2_cleanup', 'change_mce_options') );
		/* add first and last classes on widgets */
		add_filter( 'dynamic_sidebar_params', array('p2_cleanup', 'widget_first_last_classes') );
		/* nice search URLs */
		add_action( 'template_redirect', array('p2_cleanup','nice_search_redirect') );
		/* search request filter */
		add_filter( 'request', array('p2_cleanup', 'request_filter') );
		/* search form template */
		add_filter( 'get_search_form', array('p2_cleanup', 'get_search_form') );
	}

	/**
	 * Clean up wp_head()
	 *
	 * Remove unnecessary <link>'s
	 * Remove inline CSS used by Recent Comments widget
	 * Remove inline CSS used by posts with galleries
	 */
	public static function head_cleanup() {
		// Originally from http://wpengineer.com/1438/wordpress-header/
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
		/* remove comments script */
		global $wp_widget_factory;
		remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
		/* remove gallery style injection */
		add_filter('use_default_gallery_style', '__return_null');
	}

	/**
	 * Clean up language_attributes() used in <html> tag
	 * Change lang="en-US" to lang="en"
	 * Remove dir="ltr"
	 */
	public static function language_attributes()
	{
		$attributes = array();
		$output = '';
		if (function_exists('is_rtl')) {
			if (is_rtl() == 'rtl') {
				$attributes[] = 'dir="rtl"';
			}
		}
		$lang = get_bloginfo('language');
		if ($lang && $lang !== 'en-US') {
			$attributes[] = "lang=\"$lang\"";
		} else {
			$attributes[] = 'lang="en"';
		}
		return trim(implode(' ', $attributes));
	}

	/**
	 * Clean up output of stylesheet <link> tags
	 */
	public static function style_tag($input)
	{
		preg_match_all("!<link rel='stylesheet'\s?(id='[^']+')?\s+href='(.*)' type='text/css' media='(.*)' />!", $input, $matches);
		// Only display media if it's print
		$media = $matches[3][0] === 'print' ? ' media="print"' : '';
		return '<link rel="stylesheet" href="' . $matches[2][0] . '"' . $media . '>' . "\n";
	}

	/**
	 * Add and remove body_class() classes
	 */
	public static function body_class($classes)
	{
		/* add post type slug */
		if (is_single() || is_page() && !is_front_page()) {
			$classes[] = basename(get_permalink());
		}
		/* Remove unnecessary classes */
		$home_id_class = 'page-id-' . get_option('page_on_front');
		$remove_classes = array(
			'page-template-default',
			$home_id_class
		);
		return array_diff($classes, $remove_classes);
	}


	/**
	 * Wrap embedded media as suggested by Readability
	 *
	 * @link https://gist.github.com/965956
	 * @link http://www.readability.com/publishers/guidelines#publisher
	 */
	public static function embed_wrap($cache, $url, $attr = '', $post_ID = '')
	{
		return '<div class="entry-content-asset">' . $cache . '</div>';
	}

	/**
	 * Add class="thumbnail" to attachment items
	 */
	public static function attachment_link_class($html)
	{
		$postid = get_the_ID();
		$html = str_replace('<a', '<a class="thumbnail"', $html);
		return $html;
	}

	/**
	 * Remove unnecessary dashboard widgets
	 * @link http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
	 */
	public static function remove_dashboard_widgets()
	{
		remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
		remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
		remove_meta_box('dashboard_primary', 'dashboard', 'normal');
		remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
	}

	/**
	 * Clean up the_excerpt()
	 */
	public static function excerpt_length($length)
	{
		$options = p2_theme_options::get_theme_options();
		return $options["excerpt_length"];
	}
	public static function excerpt_more($more)
	{
		$options = p2_theme_options::get_theme_options();
	  	return ' &hellip; <a href="' . get_permalink() . '">' . $options["excerpt_more"] . '</a>';
	}

	/**
	 * Remove unnecessary self-closing tags
	 */
	public static function p2_remove_self_closing_tags($input)
	{
		return str_replace(' />', '>', $input);
	}

	/**
	 * Don't return the default description in the RSS feed if it hasn't been changed
	 */
	public static function remove_default_description($bloginfo)
	{
		return '';
	}


	/**
	* Allow more tags in TinyMCE including <iframe> and <script>
	*/
	public static function change_mce_options($options)
	{
		$ext = 'pre[id|name|class|style],iframe[align|longdesc|name|width|height|frameborder|scrolling|marginheight|marginwidth|src],script[charset|defer|language|src|type]';
		if (isset($options['extended_valid_elements'])) {
			$options['extended_valid_elements'] .= ',' . $ext;
		} else {
			$options['extended_valid_elements'] = $ext;
		}
		return $options;
	}

	/**
	 * Add additional classes onto widgets
	 *
	 * @link http://wordpress.org/support/topic/how-to-first-and-last-css-classes-for-sidebar-widgets
	 */
	public static function widget_first_last_classes($params)
	{
		global $my_widget_num;
		$this_id = $params[0]['id'];
		$arr_registered_widgets = wp_get_sidebars_widgets();
		if (!$my_widget_num) {
			$my_widget_num = array();
		}
		if (!isset($arr_registered_widgets[$this_id]) || !is_array($arr_registered_widgets[$this_id])) {
			return $params;
		}
		if (isset($my_widget_num[$this_id])) {
			$my_widget_num[$this_id] ++;
		} else {
			$my_widget_num[$this_id] = 1;
		}
		$class = 'class="widget-' . $my_widget_num[$this_id] . ' ';
		if ($my_widget_num[$this_id] == 1) {
			$class .= 'widget-first ';
		} elseif ($my_widget_num[$this_id] == count($arr_registered_widgets[$this_id])) {
			$class .= 'widget-last ';
		}
		$params[0]['before_widget'] = preg_replace('/class=\"/', "$class", $params[0]['before_widget'], 1);
		return $params;
	}
	

	/**
	 * Redirects search results from /?s=query to /search/query/, converts %20 to +
	 *
	 * @link http://txfx.net/wordpress-plugins/nice-search/
	 */
	public static function p2_nice_search_redirect()
	{
		global $wp_rewrite;
		if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
			return;
		}
		$search_base = $wp_rewrite->search_base;
		if (is_search() && !is_admin() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
			wp_redirect(home_url("/{$search_base}/" . urlencode(get_query_var('s'))));
			exit();
		}
	}

	/**
	 * Fix for empty search queries redirecting to home page
	 *
	 * @link http://wordpress.org/support/topic/blank-search-sends-you-to-the-homepage#post-1772565
	 * @link http://core.trac.wordpress.org/ticket/11330
	 */
	public static function request_filter($query_vars)
	{
		if (isset($_GET['s']) && empty($_GET['s'])) {
			$query_vars['s'] = ' ';
		}
		return $query_vars;
	}

	/**
	 * Tell WordPress to use searchform.php from the templates/ directory
	 */
	public static function get_search_form($argument)
	{
		if ($argument === '') {
	    	locate_template('/templates/searchform.php', true, false);
		}
	}
}
p2_cleanup::register();
endif;