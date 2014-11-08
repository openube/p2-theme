<?php
/**
 * initial setup
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2' ) ) {
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
		public static function register() {

			/* get the theme options */
			$theme_options = p2_theme_options::get_theme_options();
			//print('<pre>' . print_r($theme_options, true) . '</pre>');exit;
			$supported_features = apply_filters( 'p2-theme-features', array( __CLASS__, 'get_supported_features' ) );

			// Make theme available for translation
			load_theme_textdomain('p2', get_template_directory() . '/lang');

			/*
			 * Switches default core markup for search form, comment form,
			 * and comments to output valid HTML5.
			 */
			add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

			/**
			 * add_theme_support for different navigation elements
			 */
			add_theme_support( 'p2_top_navigation' );
			add_theme_support( 'p2_header_navigation' );
			add_theme_support( 'p2_footer_navigation' );

			// Tell the TinyMCE editor to use a custom stylesheet
			add_editor_style('/editor-style.css');

			// Enable /?s= to /search/ redirect
			add_theme_support('nice-search');

			/* filter to mess with page/post titles */
			add_filter('the title', array(__CLASS__, 'title'), 100, 2);

			/* theme wrapper */
			add_filter('template_include', array(__CLASS__, 'wrap'), 99);

			if ($theme_options['use_post_thumbnails']) {
				// Add post thumbnails (http://codex.wordpruse_post_thumbnailsess.org/Post_Thumbnails)
				add_theme_support('post-thumbnails');
				set_post_thumbnail_size($theme_options['post_thumbnail_width'], $theme_options['post_thumbnail_height'], true);
			}

			/* add a custom header */
			if ($theme_options["use_custom_header"]) {
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
			if ($theme_options["use_boilerplate_htaccess"]) {
				add_theme_support('h5bp-htaccess');
			}

			/* use post formats */
			if (count($theme_options["use_post_formats"])) {
				add_theme_support('post-formats', $theme_options["use_post_formats"]);
			}

			/* make pagination in unordered lists */
			add_filter( 'wp_link_pages_link', array(__CLASS__, 'wrap_pagination_links') );
		}

		/**
		 * Theme Wrapper
		 * @link http://scribu.net/wordpress/theme-wrappers.html
		 * called with the template_include filter
		 */
		public static function wrap($template)
		{
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
		public static function template_path()
		{
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
		 * test to see if any sidebars have been configured
		 */
		public static function has_sidebars($which = "any")
		{
			switch($which) {
				case "pages":
					return (is_page() && is_active_sidebar('pages-sidebar'));
					break;
				case "posts":
					return ((is_single() || is_archive()) && is_active_sidebar('posts-sidebar'));
					break;
				case "global":
					return (is_active_sidebar('global-sidebar'));
					break;
				case "nonglobal":
					return (self::has_sidebars("pages") || self::has_sidebars("posts"));
					break;
				case "both":
					return (self::has_sidebars("global") && self::has_sidebars("nonglobal"));
					break;
				case "single":
					$has_global = self::has_sidebars("global");
					$has_nonglobal = self::has_sidebars("nonglobal");
					return (($has_global && !$has_nonglobal) || (!$has_global && $has_nonglobal));
				case "any":
				default:
					return (self::has_sidebars("global") || self::has_sidebars("nonglobal"));
					break;
			}
		}

		/**
		 * prints column classes dependent on how many sidebars there are
		 */
		public static function column_classes($sidebar = false)
		{
			$classes = "";
			if (self::has_sidebars('both')) {
				$classes .= $sidebar? 'col-lg-6 col-sm-4': 'col-lg-6 col-sm-8';
			} elseif (self::has_sidebars('single')) {
				$classes .= $sidebar? 'col-sm-4': 'col-sm-8';
			} else {
				$classes .= $sidebar? '': 'col-lg-12 col-md-12 col-sm-12 col-xs-12';
			}
			return $classes;
		}

		/**
		 * displays global, posts or page sidebar
		 */
		public static function display_sidebars()
		{
			if (self::has_sidebars()) {
				printf('<div class="sidebar %s" role="complimentary">', self::column_classes(true));
				if (is_active_sidebar('global-sidebar')) {
					print('<div class="global-sidebar pull-right col-lg-6" role="complimentary">');
					dynamic_sidebar('global-sidebar');
					print('</div>');
				}
				if (is_page() && is_active_sidebar('pages-sidebar')) {
					print('<div class="sidebar pages-sidebar pull-right col-lg-6" role="complementary">');
					dynamic_sidebar('pages-sidebar');
					print('</div>');
				} else if ((is_single() || is_archive()) && is_active_sidebar('posts-sidebar')) {
					print('<div class="sidebar posts-sidebar pull-right col-lg-6" role="complementary">');
					dynamic_sidebar('posts-sidebar');
					print('</div>');
				}
				print('</div>');
			}
		}

		/**
		 * Styles the header text displayed on the blog.
		 */
		public static function header_style()
		{
			$header_image = get_header_image();
			$text_colour   = get_header_textcolor();
			print('<style type="text/css">');
			if ( ! empty( $header_image ) ) {
				printf('#site-header{background: url(%s) no-repeat scroll top center;height:%spx;}', esc_url($header_image), get_custom_header()->height);
			}
			if (has_nav_menu('top_navigation')) {
				printf('#site-header{margin-top:50px}');
			}
			$disp = display_header_text()? '': 'display:none;';
			printf('#site-header a{color:#%s;%s}', $text_colour, $disp);
			print('</style>');
		}

		/**
		 * Styles the header image displayed on the Appearance > Header admin panel.
		 */
		public static function admin_header_style()
		{
		    printf('<style type="text/css">.appearance_page_custom-header #headimg{border:one;-webkit-box-sizing: border-box;-moz-box-sizing:border-box;box-sizing:border-box;');
		    if ( ! empty( $header_image ) ) {
				printf('background: url(%s) no-repeat scroll top;', esc_url( get_header_image() ) );
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
			printf('<div id="headimg" style="background: url(%s) no-repeat scroll top;">', esc_url( get_header_image() ));
			$style = ' style="color:#' . get_header_textcolor() . ';"';
			printf('<h1><a id="name" onclick="return false;" href="#"%s>%s</a></h1>', $style, get_bloginfo( 'name' ));
			print('</div>');
		}

		/**
		 * interface for an option to allow selection of an image from the media library
		 * This prints a hidden field to store the option value, and an area which will
		 * serve as the preview for the images currently selected for the control
		 */
		public static function media_selection_form_control($name, $id, $value, $multiple = true)
		{
			$class = $multiple? ' multiple': ' single';
			printf('<div class="media-selection-control%s"><input type="hidden" name="%s" id="%s" value="%s">', $class, $name, $id, $value);

			/* make an array from the image IDs */
			if ( $value && ! empty( $value ) ) {
				$image_ids = explode(',', $value);
			} else {
				$image_ids = array();
			}
			/* preview area. Has same ID as the input with a '-selection' suffix */
			printf('<div id="%s-preview" class="media-selection-preview" data-inputid="%s">', $id, $id);
			/* get previews for the images added so far */
			if (count($image_ids)) {
				foreach($image_ids as $image_id) {
					$image_attributes = wp_get_attachment_image_src( $image_id, 'thumbnail' );
					if ($image_attributes) {
						printf('<div class="image-container" data-imageid="%s"><div data-inputid="%s" data-imageid="%s" class="image-inner"><img src="%s" /><a class="remove-image" href="#" title="%s">&#61826;</a></div></div>', $image_id, $id, $image_id, $image_attributes[0], __('Remove image', 'p2mis') );
					}
				}

			} else {
				print('<p>No images selected.</p>');
			}
			print('</div><span style="clear:both;">&nbsp;</span>');
			/* button which will activate the media browser/uploader */
			$buttonClass = $multiple? 'mediaBrowserButtonImages': 'mediaBrowserButtonImage';
			$buttonText = $multiple? 'Select Images': 'Select Image';
			printf('<p class="media-selection-button"><a class="button-secondary %s" data-inputid="%s" href="#">%s</a></p></div>', $buttonClass, $id, $buttonText);
		
		}

		/**
		 * wraps pagination links in <li> tags so they can be represented by unordered lists
		 */
		public static function wrap_pagination_links($link)
		{
			return '<li>' . $link . '</li>';
		}
		
	}
	/**
	 * this class is run with priority 1
	 * all other classes are run on the same hook with priorities over 100
	 * to enable child themes to remove theme support for various features
	 */
	add_action( 'after_setup_theme', array('p2', 'register'), 1 );
}