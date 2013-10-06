<?php
/**
 * Navigation and sidebar setup
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2_navigation' )) :

class p2_navigation
{
	public static function register()
	{
		/* register menus */
		add_action( 'after_setup_theme', array(__CLASS__, 'register_nav_menus') );
		/* register sidebars */
		add_action( 'widgets_init', array(__CLASS__, 'register_sidebars') );
		/* tidy up menu generation */
		add_filter( 'nav_menu_css_class', array(__CLASS__, 'nav_menu_css_class'), 10, 2 );
		add_filter( 'nav_menu_item_id', '__return_null' );
		add_filter( 'wp_nav_menu_args', array(__CLASS__, 'nav_menu_args') );
	}

	/**
	 * Registers navigation menus
	 */
	public static function register_nav_menus()
	{
		register_nav_menus(array(
			'footer_navigation' => 'Footer Navigation'
		));
	}

	/**
	 * Registers sidebars
	 */
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

	/**
	 * Remove the id="" on nav menu items
	 * Return 'menu-slug' for nav menu classes
	 */
	function nav_menu_css_class($classes, $item)
	{
		$slug = sanitize_title($item->title);
		$classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
		$classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

		$classes[] = 'menu-' . $slug;

		$classes = array_unique($classes);

		return array_filter($classes, 'is_element_empty');
	}

	/**
	 * Clean up wp_nav_menu_args
	 *
	 * Remove the container
	 * Use p2_Nav_Walker() by default
	 */
	function nav_menu_args($args = '')
	{
		$nav_menu_args['container'] = false;
		if (!$args['items_wrap']) {
			$nav_menu_args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
		}
		$nav_menu_args['depth'] = 3;
		if (!$args['walker']) {
			$nav_menu_args['walker'] = new p2_Nav_Walker();
		}
		return array_merge($args, $nav_menu_args);
	}
}
p2_navigation::register();

endif;
	

/**
 * Cleaner walker for wp_nav_menu()
 *
 * Walker_Nav_Menu (WordPress default) example output:
 *	 <li id="menu-item-8" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8"><a href="/">Home</a></li>
 *	 <li id="menu-item-9" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9"><a href="/sample-page/">Sample Page</a></l
 *
 * Roots_Nav_Walker example output:
 *	 <li class="menu-home"><a href="/">Home</a></li>
 *	 <li class="menu-sample-page"><a href="/sample-page/">Sample Page</a></li>
 */
class p2_Nav_Walker extends Walker_Nav_Menu 
{
	function check_current($classes) 
	{
		return preg_match('/(current[-_])|active|dropdown/', $classes);
	}

	function start_lvl(&$output, $depth = 0, $args = array())
	{
		$output .= "\n<ul class=\"dropdown-menu\">\n";
	}

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{
		$item_html = '';
		parent::start_el($item_html, $item, $depth, $args);

		if ($item->is_dropdown && ($depth === 0)) {
			$item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
			$item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
		}
		elseif (stristr($item_html, 'li class="divider')) {
			$item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
		}
		elseif (stristr($item_html, 'li class="nav-header')) {
			$item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
		}

		$output .= $item_html;
	}

	function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output)
	{
		$element->is_dropdown = !empty($children_elements[$element->ID]);

		if ($element->is_dropdown) {
			if ($depth === 0) {
				$element->classes[] = 'dropdown';
			} elseif ($depth === 1) {
				$element->classes[] = 'dropdown-submenu';
			}
		}

		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}
}
