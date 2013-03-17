<?php
/**
 * Register sidebars and widgets
 */
if ( ! class_exists('p2_sidebar') ) :

class p2_sidebar
{
	public static function register()
	{
		add_action( 'widgets_init', array('p2_sidebar', 'register_sidebars') );
	}

	public static function register_sidebars()
	{
		/* Sidebars */
		register_sidebar(array(
		'name'					=> __('Primary Sidebar', 'roots'),
		'id'						=> 'sidebar-primary',
		'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
		'after_widget'	=> '</div></section>',
		'before_title'	=> '<h3>',
		'after_title'   => '</h3>',
		));
		register_sidebar(array(
		'name'					=> __('Footer', 'roots'),
		'id'						=> 'sidebar-footer',
		'before_widget' => '<section class="widget %1$s %2$s"><div class="widget-inner">',
		'after_widget'	=> '</div></section>',
		'before_title'	=> '<h3>',
		'after_title'	  => '</h3>',
		));

	}
}
p2_sidebar::register();
endif;