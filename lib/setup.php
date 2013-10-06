<?php
/**
 * initial setup
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2' ) ) :
/**
 * this class contains stuff you would normally put in a theme functions file
 * as well as the template wrapper and theme upgrade functions. 
 */
class p2
{
	/**
	 * Stores the theme version
	 * @var string
	 */
	public static $version = "1.0";

	/**
	 * Stores the full path to the main template file
	 * @var string
	 */
	static $main_template;

	/**
	 * Stores the base name of the template file; e.g. 'page' for 'page.php' etc.
	 * @var string
	 */
	static $base;

	/**
	 * registers all the methiods of the class with the Wordpress API
	 * and adds support for different features in the theme
	 */
	function register() {

		/* get the theme options */
		$options = p2_theme_options::get_theme_options();

		// Add post thumbnails (http://codex.wordpress.org/Post_Thumbnails)
		add_theme_support('post-thumbnails');
		// set_post_thumbnail_size(150, 150, false);
		// add_image_size('category-thumb', 300, 9999); // 300px wide (and unlimited height)

		// Tell the TinyMCE editor to use a custom stylesheet
		add_editor_style('/css/editor-style.css');

		// Enable /?s= to /search/ redirect
		add_theme_support('nice-search');

		/* filter to mess with page/post titles */
		add_filter('the title', array(__CLASS__, 'title'), 100, 2);

		/* theme wrapper */
		add_filter('template_include', array(__CLASS__, 'wrap'), 99);

		/* add a custom background */
		if ($options["use_custom_background"]) {
			add_theme_support( 'custom-background', array(
				'default-color' => 'e6e6e6',
			) );
		}

		/* add a custom header */
		if ($options["use_custom_header"]) {
			$args = array(
				'default-text-color'     => '220e10',
				'default-image'          => '',
				'height'                 => 230,
				'width'                  => 1600,
				'wp-head-callback'       => array(__CLASS__, 'header_style'),
				'admin-head-callback'    => array(__CLASS__, 'admin_header_style'),
				'admin-preview-callback' => array(__CLASS__, 'admin_header_image'),
			);
			add_theme_support( 'custom-header', $args );
		}

		/* add HTML5 boilerplate .htaccess rules */
		if ($options["use_boilerplate_htaccess"]) {
			add_theme_support('h5bp-htaccess');
		}
	}

	/**
	 * Theme Wrapper
	 * @link http://scribu.net/wordpress/theme-wrappers.html
	 * called with the template_include filter
	 */
	public static function wrap($template) {
		self::$main_template = $template;

		self::$base = substr(basename(self::$main_template), 0, -4);

		if (self::$base === 'index') {
			self::$base = false;
		}

		$templates = array('base.php');

		if (self::$base) {
			array_unshift($templates, sprintf('base-%s.php', self::$base ));
		}

		return locate_template($templates);
	}

	/**
	 * getter for template path
	 */
	public static function template_path() {
		return self::$main_template;
	}

	/**
	 * used as a filter on the title
	 */
	public static function title($title, $post_id)
	{
		if (is_home()) {
			if (get_option('page_for_posts', true)) {
				return get_the_title(get_option('page_for_posts', true));
			} else {
				return 'Latest Posts';
			}
		} elseif (is_archive()) {
			$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
			if ($term) {
				return $term->name;
			} elseif (is_post_type_archive()) {
				return get_queried_object()->labels->name;
			} elseif (is_day()) {
				return sprintf('Daily Archives: %s', get_the_date());
			} elseif (is_month()) {
				return sprintf('Monthly Archives: %s', get_the_date('F Y'));
			} elseif (is_year()) {
				return sprintf('Yearly Archives: %s', get_the_date('Y'));
			} elseif (is_author()) {
				global $post;
				$author_id = $post->post_author;
				return sprintf('Author Archives: %s', get_the_author_meta('display_name', $author_id));
			} else {
				return single_cat_title();
			}
		} elseif (is_search()) {
			return sprintf('Search Results for %s', get_search_query());
		} elseif (is_404()) {
			return 'File Not Found';
		} else {
			return $title;
		}
	}

	/**
	 * displays posts or page sidebar
	 */
	public static function display_sidebar()
	{
		if (is_page() && is_active_sidebar('sidebar-pages')) {
			print('<aside class="sidebar sidebar-pages" role="complementary">');
			dynamic_sidebar('sidebar-pages');
			print('</aside>');
		}
		if ((is_single() || is_archive()) && is_active_sidebar('sidebar-posts')) {
			print('<aside class="sidebar sidebar-posts" role="complementary">');
			dynamic_sidebar('sidebar-posts');
			print('</aside>');
		}
	}

	/**
	 * Styles the header text displayed on the blog.
	 */
	public static function header_style()
	{
		$header_image = get_header_image();
		$text_color   = get_header_textcolor();
		/* If no custom options for text are set, let's bail. */
		if ( empty( $header_image ) && $text_color == get_theme_support( 'custom-header', 'default-text-color' ) ) {
			return;
		}
		/* If we get this far, we have custom styles */
		print('<style type="text/css">');
		if ( ! empty( $header_image ) ) {
			printf('.site-header {background: url("%s") no-repeat scroll top;}', esc_url($header_image));
		}
		if ( ! display_header_text() ) {
			print('.site-title{display:none}');
		}
		print('</style>');
	}

	/**
	 * Styles the header image displayed on the Appearance > Header admin panel.
	 */
	public static function admin_header_style()
	{
		$header_image = get_header_image();
	    printf('<style type="text/css">.appearance_page_custom-header #headimg{border:one;-webkit-box-sizing: border-box;-moz-box-sizing:border-box;box-sizing:border-box;');
	    if ( ! empty( $header_image ) ) {
			printf('background: url("%s") no-repeat scroll top;', esc_url($header_image) );
		}
		print('}');
		if ( ! display_header_text() ) {
		    print('#headimg h1,#headimg h2{display:none;}');
		}
		print('#headimg h1{font: bold 60px/1 serif;margin: 0;padding: 58px 0 10px;}');
		print('#headimg h1 a {text-decoration: none}');
		print('#headimg h1 a:hover {text-decoration: underline;}');
		print('.default-header img {max-width: 230px;width: auto;}</style>');
	}

	/**
	 * Outputs markup to be displayed on the Appearance > Header admin panel.
	 * This callback overrides the default markup displayed there.
	 */
	public static function admin_header_image()
	{
		printf('<div id="headimg" style="background: url(%s) no-repeat scroll top;">', esc_url( header_image() ));
		$style = ' style="color:#' . get_header_textcolor() . ';"';
		printf('<h1><a id="name" onclick="return false;" href="#"%s>%s</a></h1>', $style, get_bloginfo( 'name' ));
		print('</div>');
	}
	
}
add_action( 'after_setup_theme', array('p2', 'register') );
endif;