<?php
/**
 * Add HTML5 Boilerplate's .htaccess via WordPress
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists('p2_htaccess')) :

	class p2_htaccess
	{
		public static function register()
		{
			if (current_theme_supports('h5bp-htaccess')) {
	  			add_action( 'generate_rewrite_rules', array(__CLASS__, 'add_h5bp_htaccess') );
			}
		}

		public static function add_h5bp_htaccess($content)
		{
	  		global $wp_rewrite;
			$home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
			$htaccess_file = $home_path . '.htaccess';
			$mod_rewrite_enabled = function_exists('got_mod_rewrite') ? got_mod_rewrite() : false;
			if ((!file_exists($htaccess_file) && is_writable($home_path) && $wp_rewrite->using_mod_rewrite_permalinks()) || is_writable($htaccess_file)) {
				if ($mod_rewrite_enabled) {
					$h5bp_rules = extract_from_markers($htaccess_file, 'HTML5 Boilerplate');
					if ($h5bp_rules === array()) {
						$insertion = file_get_contents('https://github.com/h5bp/server-configs/blob/master/apache/.htaccess');
						if ($insertion) {
							$rules = explode("\n", $insertion)
							return insert_with_markers($htaccess_file, 'HTML5 Boilerplate', $rules);
						}
					}
				}
			}
			return $content;
		}
	}
	add_action( 'after_setup_theme', array('p2_htaccess', 'register'), 101 );
}