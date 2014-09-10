<?php
/**
 * Github updater for University of Leeds Wordpress theme
 *
 * @author Peter Edwards <p.l.edwards@leeds.ac.uk>
 * @version 1.2.1
 * @package Wordpress
 * @subpackage UoL_theme
 */

if ( ! class_exists('uol_theme_updater') ) :

	class uol_theme_updater
	{

		/**
		 * registers methods with hooks/filters
		 */
		public static function register()
		{
			/* define an alternative API for theme update checking */
			add_filter( 'pre_set_site_transient_update_themes', array(__CLASS__, 'check_theme_update') );

			/* define an alternative response for theme information checking */
			add_filter( 'themes_api', array(__CLASS__, 'check_theme_info'), 10, 3 );

		}

		/**
		 * Add our github repo to the filter transient
		 *
		 * @param $transient
	 	 * @return object $ transient
		 */
		public static function check_theme_update( $transient )
		{
			if ( empty( $transient->checked ) ) {
				return $transient;
			}
	 
			if ( $data = self::get_theme_info() ) {
				/* Get the remote version */
				$remote_version = $data->version;

				/* Get current theme version */
				$current_version = "1.2";//self::get_current_version();
		 
				/* If a newer version is available, add the update */
				if ( version_compare( $current_version, $remote_version, '<')) {
					$update = array();
					$update["slug"] = get_template();
					$update["new_version"] = $remote_version;
					$update["url"] = $data->theme_url;
					$update["package"] = $data->download_link;
					$transient->response[get_template()] = $update;
				}
			}
			return $transient;
		}

		/**
		 * Add our self-hosted description to the filter
		 *
		 * @param boolean $false
		 * @param array $action
		 * @param object $arg
		 * @return bool|object
		 */
		public static function check_theme_info($false, $action, $arg)
		{
			if ($arg->slug === get_template()) {
				return self::get_remote_information();
			}
			return false;
		}

		/**
		 * Return the current installed version
		 */
		public static function get_current_version()
		{
			return get_option('uol_theme_version');
		}
 
		/**
		 * Return the remote version
		 * @return string $remote_version
		 */
		public static function get_remote_version()
		{
			if ( $data = self::get_theme_info() ) {
				return $data->version;
			}
			return false;
		}
	 
		/**
		 * Get information about the remote version
		 * @return bool|object
		 */
		public static function get_remote_information()
		{
			if ( $data = self::get_theme_info() ) {
				$obj = new stdClass();
      			$obj->slug = get_template();
      			$obj->theme_name = $data->theme_name;
      			$obj->theme_url = $data->homepage;
      			$obj->new_version = $data->version;
      			$obj->requires = $data->requires;
      			$obj->tested = $data->tested;
      			$obj->downloaded = 0;
      			$obj->last_updated = $data->last_updated;
      			$obj->sections = array(
        			'description' => $data->description
      			);
      			$obj->download_link = $data->download_link;
      			return $obj;
      		}
		}
	 
		/**
		 * wrapper for github API calls
		 * stores results in a transient
		 */
		public static function get_theme_info()
		{
			/* set return value to false */
			$data = false;
			delete_site_transient( 'github_theme_info' );
			/* try to get result of API call via transient */
			if ( false === ( $data = get_site_transient( 'github_theme_info' ) ) ) {

				/* no transient stored - go to get the data from guthub */
				$curl_options = array(
	    			CURLOPT_HEADER => false,
	    			CURLOPT_RETURNTRANSFER => true,
	    			CURLOPT_TIMEOUT => 2,
	    			CURLOPT_USERAGENT => 'essl-pvac',
	    			CURLOPT_URL => "https://api.github.com/repos/essl-pvac/uol-wordpress-theme/contents/package.json"
				);
				$ch = curl_init();
				curl_setopt_array($ch, $curl_options);

				/* check the response */
				if ( false !== ($response = curl_exec( $ch ) ) ) {
					/* make sure it will decode */
					$package_data = @json_decode($response);
					if ($package_data !== null) {
						$data = @json_decode(base64_decode($package_data->content));
						if ( $data !== null ) {
							/* set the transient */
							set_site_transient( 'github_theme_info', $data, 12 * HOUR_IN_SECONDS );
						}
					} else {
						$data = false;
					}
				}
			}
			//print('<pre>' . print_r($data, true) . '</pre>');exit;
			return $data;
		}
	}

	uol_theme_updater::register();

endif;