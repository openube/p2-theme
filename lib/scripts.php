<?php
/**
 * Enqueue scripts and stylesheets
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists('p2_theme_scripts') ) {

	class p2_theme_scripts
	{
		/* flag for jquery fallback insertion */
		static $add_jquery_fallback = false;

		/**
		 * register with Wordpress API
		 */
		public static function register()
		{
			/* use wp_enqueue_scripts hook to embed front-end scripts */
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_theme_scripts'), 100);

			/* use wp_enqueue_scripts hook to embed front-end styles */
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_theme_styles'), 100);

			/* use admin_enqueue_scripts hook to embed admin scripts */
			add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_scripts'), 100);

			/* Enqueue live preview javascript in Theme Customizer admin screen */
			add_action( 'customize_preview_init' , array(__CLASS__, 'customiser_live_preview') );
		}
		
		/**
		 * jQuery is loaded using the same method from HTML5 Boilerplate:
		 * Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
		 * It's kept in the header instead of footer to avoid conflicts with plugins.
		 */
		public static function enqueue_theme_scripts() 
		{
			/* make sure we're not on admin pages */
			if ( ! is_admin() ) {
				
				/* deregister jquery */
				wp_deregister_script('jquery');
				
				/* re-register jquery from google CDN */
				wp_register_script(
					'jquery',
					'//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js'
				);
				
				/* modernizr */
				wp_register_script(
					'modernizr',
					get_template_directory_uri() . '/js/vendor/modernizr-2.6.2.min.js'
				);
				
				/* theme script */
				wp_register_script(
					'theme-scripts',
					get_template_directory_uri() . '/js/p2.min.js', 
					array( 'jquery' ),
					p2::$version,
					true
				);
				
				/* queue them all up */
				wp_enqueue_script('jquery');
				wp_enqueue_script('modernizr');
				wp_enqueue_script('theme-scripts');

				/* add a filter to script loader for jQuery local fallback */
				add_filter('script_loader_src', array(__CLASS__, 'jquery_local_fallback'), 10, 2);
			}
		}

		/**
		 * queues up theme styles
		 * hooked into wp_enqueue_scripts
		 */
		public static function enqueue_theme_styles()
		{
			/* queue up main stylesheet */
			wp_enqueue_style(
				'theme-stylesheet',
				get_template_directory_uri() . '/style.css',
				false, 
				p2::$version
			);

			/* Load style.css from child theme */
			if (is_child_theme()) {
				wp_enqueue_style(
					'child-theme-stylesheet',
					get_stylesheet_uri()
				);
			}
		}
		
		/**
		 * register scripts for admin area
		 */
		public static function enqueue_admin_scripts()
		{
			/* enqueue media library scripts */
	        wp_enqueue_media();

			/* theme admin scripts */
			wp_enqueue_script(
				'p2-theme-admin-script', 
				get_template_directory_uri() . '/js/admin.js', 
				array('jquery', 'iris', 'jquery-ui-sortable'),
				p2::$version,
				true
			);
			wp_localize_script( 
				'p2-theme-admin-script', 
				'p2theme_msg',
				array(
		 			'empty_single' => __('No image selected', 'p2_theme'),
		 			'empty_multiple' => __('No images selected', 'p2_theme'),
		 			'select_single' => __('Select Image', 'p2_theme'),
		 			'select_multiple' => __('Select Images', 'p2_theme'),
		 			'deleteimage' => __('remove image', 'p2_theme')
				)
			);
			wp_enqueue_style(
				'p2-theme-admin-style',
				get_template_directory_uri() . '/css/admin.css',
				array( 'dashicons' ),
				p2::$version
			);
		}

		/**
		 * local jQuery fallback
		 * @see http://wordpress.stackexchange.com/a/12450
		 */
		public static function jquery_local_fallback($src, $handle)
		{
			if (self::$add_jquery_fallback) {
				echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/js/vendor/jquery-1.9.1.min.js"><\/script>\')</script>' . "\n";
				self::$add_jquery_fallback = false;
			}
			if ($handle === 'jquery') {
				self::$add_jquery_fallback = true;
			}
			return $src;
		}

		/**
		 * This outputs the javascript needed to automate the live settings preview.
		 */
		public static function customiser_live_preview()
		{
			wp_enqueue_script(
				'p2-theme-customizer',
				get_template_directory_uri() . '/js/customiser.js',
				array( 'jquery','customize-preview' ),
				p2::$version,
				true
			);
		}
	}
	p2_theme_scripts::register();
}