<?php
/**
 * Enqueue scripts and stylesheets
 */
function p2_enqueue_scripts() {
	wp_enqueue_style('theme-stylesheet', get_template_directory_uri() . '/css/style.css', false, null);

	// Load style.css from child theme
	if (is_child_theme()) {
		wp_enqueue_style('child-theme-stylesheet', get_stylesheet_uri(), false, null);
	}

	// jQuery is loaded using the same method from HTML5 Boilerplate:
	// Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
	// It's kept in the header instead of footer to avoid conflicts with plugins.
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', false, null, false);
	} else {
		// register scripts for admin area
		wp_register_script('theme-admin-scripts', get_template_directory_uri() . '/js/admin.js', false, null, true);
		wp_enqueue_script('theme-admin-scripts');

	}
	/* Enqueue live preview javascript in Theme Customizer admin screen */
	add_action( 'customize_preview_init' , 'p2_customiser_live_preview' );

	wp_register_script('modernizr', get_template_directory_uri() . '/js/vendor/modernizr-2.6.2.min.js', false, null, false);
	wp_register_script('theme-scripts', get_template_directory_uri() . '/js/scripts.js', false, null, true);
	wp_enqueue_script('jquery');
	wp_enqueue_script('modernizr');
	wp_enqueue_script('theme-scripts');
}
add_action('wp_enqueue_scripts', 'p2_enqueue_scripts', 100);

// http://wordpress.stackexchange.com/a/12450
function p2_jquery_local_fallback($src, $handle) {
	static $add_jquery_fallback = false;

	if ($add_jquery_fallback) {
		echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/js/vendor/jquery-1.9.1.min.js"><\/script>\')</script>' . "\n";
		$add_jquery_fallback = false;
	}

	if ($handle === 'jquery') {
		$add_jquery_fallback = true;
	}

	return $src;
}
if (!is_admin()) {
	add_filter('script_loader_src', 'p2_jquery_local_fallback', 10, 2);
}

/**
 * This outputs the javascript needed to automate the live settings preview.
 */
function p2_customiser_live_preview()
{
	wp_enqueue_script('wkw-theme-customizer', get_template_directory_uri() . '/js/customiser.js', array( 'jquery','customize-preview' ), '', true);
}
