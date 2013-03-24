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
			'name'			=> 'Posts Sidebar',
			'id'			=> 'sidebar-posts',
			'before_widget' => '<section class="posts-sidebar-widget widget %1$s %2$s"><div class="widget-inner">',
			'after_widget'	=> '</div></section>',
			'before_title'	=> '<h3>',
			'after_title'   => '</h3>',
		));
		register_sidebar(array(
			'name'			=> 'Pages sidebar',
			'id'			=> 'sidebar-pages',
			'before_widget' => '<section class="pages-sidebar-widget widget %1$s %2$s"><div class="widget-inner">',
			'after_widget'	=> '</div></section>',
			'before_title'	=> '<h3>',
			'after_title'	=> '</h3>',
		));

	}

	public static function display()
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
}
p2_sidebar::register();
endif;