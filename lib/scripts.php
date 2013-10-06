<?php
/**
 * Enqueue scripts and stylesheets
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists('p2_theme_scripts') ) :

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
			wp_deregister_script('jquery');
			wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js', false, null, false);
			wp_register_script('modernizr', get_template_directory_uri() . '/js/vendor/modernizr-2.6.2.min.js', false, null, false);
			wp_register_script('theme-scripts', get_template_directory_uri() . '/js/scripts.js', false, null, true);
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
		wp_enqueue_style('theme-stylesheet', get_template_directory_uri() . '/css/style.css', false, null);

		/* Load style.css from child theme */
		if (is_child_theme()) {
			wp_enqueue_style('child-theme-stylesheet', get_stylesheet_uri(), false, null);
		}
	}
	
	/**
	 * register scripts for admin area
	 */
	public static function enqueue_admin_scripts()
	{
		wp_register_script('theme-admin-scripts', get_template_directory_uri() . '/js/admin.js', array('jquery','colorpicker'), null, true);
		wp_enqueue_script('theme-admin-scripts');
	}

	/**
	 * local jQuery fallback
	 * @see http://wordpress.stackexchange.com/a/12450
	 */
	function jquery_local_fallback($src, $handle)
	{
		if ($add_jquery_fallback) {
			echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/js/vendor/jquery-1.9.1.min.js"><\/script>\')</script>' . "\n";
			$add_jquery_fallback = false;
		}
		if ($handle === 'jquery') {
			$add_jquery_fallback = true;
		}
		return $src;
	}

	/**
	 * This outputs the javascript needed to automate the live settings preview.
	 */
	public static function customiser_live_preview()
	{
		wp_enqueue_script('wkw-theme-customizer', get_template_directory_uri() . '/js/customiser.js', array( 'jquery','customize-preview' ), '', true);
	}
}
p2_theme_scripts::register();
endif;