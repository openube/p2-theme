<?php
/**
 * initial setup and theme options
 */
class p2_theme_setup
{
	function register() {

		/* Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus) */
		register_nav_menus(array(
			'primary_navigation' => __('Primary Navigation', 'roots'),
		));

		// Add post thumbnails (http://codex.wordpress.org/Post_Thumbnails)
		add_theme_support('post-thumbnails');
		// set_post_thumbnail_size(150, 150, false);
		// add_image_size('category-thumb', 300, 9999); // 300px wide (and unlimited height)

		// Tell the TinyMCE editor to use a custom stylesheet
		add_editor_style('/css/editor-style.css');

		add_theme_support('bootstrap-gallery');     // Enable Bootstrap's thumbnails component on [gallery]
		add_theme_support('nice-search');           // Enable /?s= to /search/ redirect

		add_theme_support( 'custom-background', array(
		'default-color' => 'e6e6e6',
	) );


	}
	

	/* Backwards compatibility for older than PHP 5.3.0
	if (!defined('__DIR__')) { define('__DIR__', dirname(__FILE__)); }

	// Define helper constants
	$get_theme_name = explode('/themes/', get_template_directory());

	define('WP_BASE',                   wp_base_dir());
	define('THEME_NAME',                next($get_theme_name));
	define('RELATIVE_PLUGIN_PATH',      str_replace(site_url() . '/', '', plugins_url()));
	define('FULL_RELATIVE_PLUGIN_PATH', WP_BASE . '/' . RELATIVE_PLUGIN_PATH);
	define('RELATIVE_CONTENT_PATH',     str_replace(site_url() . '/', '', content_url()));
	define('THEME_PATH',                RELATIVE_CONTENT_PATH . '/themes/' . THEME_NAME);*/
}
add_action( 'after_setup_theme', array('p2_theme_setup', 'register') );