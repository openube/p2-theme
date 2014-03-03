<?php
/**
 * Theme options
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2_theme_options' ) ) :

	/**
	 * class to add options for the theme
	 * includes settings for the theme (in a theme options page) and
	 * theme customisations via the Wordpress Theme Customisation API
	 */
	class p2_theme_options
	{
		public static function register()
		{
	        /* add the wordpress theme options page */
	        add_action( 'admin_menu', array(__CLASS__, 'add_options_page') );
	        /* register settings */
	        add_action( 'admin_init', array(__CLASS__, 'register_theme_options') );

			/* Setup the Theme Customizer settings and controls */
			add_action( 'customize_register' , array( __CLASS__ , 'register_customiser_options' ) );
			/* Setup the theme customiser live preview */
			add_action( 'customize_preview_init', array(__CLASS__, 'register_customiser_script' ) );
			/* handle AJAX request for cusomizer options */
			add_action( 'wp_ajax_customiser_options', array( __CLASS__ , 'get_ajax_options' ) );

			/* Output custom CSS for options */
			add_action( 'wp_head', array(__CLASS__, 'wp_head') );
			/* Add google analytics to the footer */
			add_action( 'wp_footer', array(__CLASS__, 'wp_footer'), 20);
		}

		/**
		 * gets the theme options from the database or uses the default values
		 */
		public static function get_theme_options()
		{
			$defaults = self::get_default_options();
			$theme_options = get_option('p2_options');
			$options = array();
			foreach ($defaults as $name => $value) {
				$options[$name] = (isset($theme_options[$name]))? $theme_options[$name]: $value;
			}
			return $options;
		}

		/**
		 * gets default theme options
		 */
		public static function get_default_options()
		{
			$defaults = array();
			$option_data = self::get_option_data();
			foreach ($option_data as $section) {
				foreach($section["settings"] as $fieldname => $details) {
					$defaults[$fieldname] = $details["default"];
				}
			}
			return $defaults;
		}

		/**
		 * gets the customisation options for both the theme customiser
		 * and the theme options page.
		 */
		private static function get_option_data()
		{
			return array(
				array(
					'name' => 'p2_options',
					'page' => 'p2_options',
					'title' => __('Theme Options', 'p2_theme'),
					'customiser' => false,
					'capability' => 'edit_theme_options',
					'description' => __('Allows you to customize site options.', 'p2_theme'),
					'settings' => array(
						'google_analytics_id' => array(
							'label' => __('Google Analytics ID', 'p2_theme'),
							'type' => 'text',
							'default' => ''
						),
						'google_verification' => array(
							'label' => __('Google Verification ID', 'p2_theme'),
							'type' => 'text',
							'default' => ''
						),
						'excerpt_length' => array(
							'label' => __('Length of Post excerpts', 'p2_theme'),
							'type' => 'integer',
							'default' => 50
						),
						'excerpt_more' => array(
							'label' => __('"Read more" link text', 'p2_theme'),
							'type' => 'text',
							'default' => __('Read More&hellip;', 'p2_theme'),
						),
						'use_custom_header' => array(
							'label' => __('Use custom header?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						),
						'use_custom_background' => array(
							'label' => __('Use custom background?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						),
						'use_post_formats' => array(
							'label' => __('Use post formats?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => false
						),
						"use_boilerplate_htaccess" => array(
							'label' => __('Use HTML5 boilerplate .htaccess rules?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						)
					)
				),
				array(
					"name" => 'link_styles',
					"title" => __( 'Link Colours', 'p2_theme' ),
					"customiser" => true,
					"priority" => 38,
					"description" => __('Change the colours for links.', 'p2_theme'),
					"settings" => array(
						array(
							"name" => "link_colour",
							"label" => __( 'Normal link colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 1,
							"selector" => ".content a",
							'default' => '#1b399b'
						),
						array(
							"name" => "link_colour_visited",
							"label" => __( 'Visited link colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 2,
							"selector" => ".content a:visited",
							'default' => '#3658c9'
						),
						array(
							"name" => "link_colour_hover",
							"label" => __( 'Active link colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 3,
							"selector" => ".content a:hover, .content a:active",
							'default' => '#3658c9'
						),
						array(
							"name" => "footer_link_colour",
							"label" => __( 'Footer link colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 5,
							"selector" => ".content-info a",
							'default' => '#1b399b'
						),
						array(
							"name" => "footer_link_colour_hover",
							"label" => __( 'Footer link colour (active)', 'p2_theme' ),
							"type" => "colour",
							"priority" => 6,
							"selector" => ".content-info a:hover,.content-info a:active",
							'default' => '#3658c9'
						),
						array(
							"name" => "breadcrumb_link_colour",
							"label" => __( 'Breadcrumb link colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 7,
							"selector" => ".breadcrumbs li a",
							'default' => '#1b399b'
						),
						array(
							"name" => "breadcrumb_link_colour_hover",
							"label" => __( 'Breadcrumb link colour (active)', 'p2_theme' ),
							"type" => "colour",
							"priority" => 8,
							"selector" => ".breadcrumbs li a:hover,.breadcrumbs li a:active",
							'default' => '#3658c9'
						)
					)
				),
				array(
					"name" => 'heading_styles',
					"title" => __( 'Headings', 'p2_theme' ),
					"customiser" => true,
					"priority" => 36,
					"description" => __('Change the colours for headings.', 'p2_theme'),
					"settings" => array(
						array(
							"name" => "h1_colour",
							"label" => __( 'H1 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 1,
							"selector" => ".content h1",
							'default' => '#114f5a'
						),
						array(
							"name" => "h2_colour",
							"label" => __( 'H2 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 2,
							"selector" => ".content h2",
							'default' => '#114f5a'
						),
						array(
							"name" => "h3_colour",
							"label" => __( 'H3 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 3,
							"selector" => ".content h3",
							'default' => '#114f5a'
						),
						array(
							"name" => "h4_colour",
							"label" => __( 'H4 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 4,
							"selector" => ".content h4",
							'default' => '#115a48'
						),
						array(
							"name" => "h5_colour",
							"label" => __( 'H5 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 5,
							"selector" => ".content h5",
							'default' => '#115a48'
						),
						array(
							"name" => "h6_colour",
							"label" => __( 'H6 heading colour', 'p2_theme' ),
							"type" => "colour",
							"priority" => 6,
							"selector" => ".content h6",
							'default' => '#115a48'
						)
					)
				)
			);
		}

		/**
		 * registers settings and sections for theme options page
		 * Used by 'admin_init' hook
		 */
		public static function register_theme_options()
		{
			/* register the setting with Wordpress - all options are stored here */
			register_setting( 
				'p2_options',
				'p2_options',
				array(
					__CLASS__,
					'validate_theme_options'
				)
			);

			/* get the options data */
			$option_data = self::get_option_data();
			foreach ($option_data as $section) {
				/* just do the Theme options here - customiser is handlked elsewhere */
				if (!isset($section["customiser"])) {
					add_settings_section(
						$section['name'], 
						$section['title'], 
						array(
							__CLASS__,
							'section_text'
						), 
						$section['page']
					);
					/* go through each field */
					foreach ($section["settings"] as $fieldname => $details) {
						$method = 'option_' . $details['type'];
						add_settings_field(
							$fieldname,
							$details['label'],
							array(
								__CLASS__,
								$method
							) ,
							$section['page'],
							$section['name'],
							array(
								"fieldname" => $fieldname
							)
						);
					}
				}
			}
		}

		/**
		 * This hooks into 'customize_register' (available as of WP 3.4) and allows
		 * you to add new sections and controls to the Theme Customize screen.
		 * @see add_action('customize_register',$func)
		 * @param \WP_Customize_Manager $wp_customize
		 */
		public static function register_customiser_settings ( $wp_customize )
		{
			/* get option data */
			$options = self::get_option_data();

			/* get the default values */
			$default_options = self::get_default_options();
			
			/* go through options adding fields */
			foreach ($options as $section) {

				if (isset($section["customiser"])) {

					$section_name = 'p2_options_' . $section["name"];

					/* Define a new section to the Theme Customizer */
					$wp_customize->add_section( $section_name, 
						array(
							'title' => $section["title"], // Visible title of section
							'priority' => $section["priority"], // Determines what order this appears in
							'capability' => 'edit_theme_options', // Capability needed to tweak
							'description' => $section["description"], // Descriptive tooltip
						) 
					);

					foreach ($section["settings"] as $setting) {
				
						$setting_name = 'p2_options[' . $setting["name"] . ']';
						
						/* Register new settings */
						$wp_customize->add_setting( $setting_name,
							array(
								'default' => $default_options[$setting["name"]], //Default setting/value to save
								'type' => 'option', //Is this an 'option' or a 'theme_mod'?
								'capability' => 'edit_theme_options', //Optional. Special permissions for accessing this setting.
								'transport' => 'postMessage', //What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
							) 
						);

						/* add Customiser controls */
						switch ($setting["type"]) {
							case "colour":
								$wp_customize->add_control( new WP_Customize_Color_Control( //Instantiate the color control class
									$wp_customize, //Pass the $wp_customize object (required)
									str_replace(array('[', ']'), array('_', ''), $setting_name), //Set a unique ID for the control
									array(
										'label' => $setting["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $setting["priority"], //Determines the order this control appears in for the specified section
									) 
								) );
								break;
							case "text":
							case "checkbox":
							case "dropdown-pages":
								$wp_customize->add_control(	new WP_Customize_Control( //Instantiate the general control class
									$wp_customize,
									str_replace(array('[', ']'), array('_', ''), $setting_name), //Set a unique ID for the control
									array(
										'type' => $setting["type"], // type of control (text, checkbox or dropdown pages)
										'label' => $setting["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $setting["priority"], //Determines the order this control appears in for the specified section
									)
								) );
								break;
							case "radio":
							case "select":
								$wp_customize->add_control(	new WP_Customize_Control( //Instantiate the general control class
									$wp_customize,
									str_replace(array('[', ']'), array('_', ''), $setting_name), //Set a unique ID for the control
									array(
										'type' => $setting["type"], // type of control (radio or select)
										'label' => $setting["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $setting["priority"], //Determines the order this control appears in for the specified section
										'choices' => $setting["choices"] // Array of possible values for radio/select
									)
								) );
								break;

						}
					}
				}
			}	
			/* make some stuff use live preview JS */
			$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
			$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
			$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
		 }


		/***********************************************
		 * THEME OPTIONS PAGE                          *
		 ***********************************************/

		/**
		* add a submenu to the theme admin menu to access the theme options page
		* Used by 'admin_menu' hook
		*/
		public static function add_options_page() 
		{
			$theme_settings_page = add_theme_page( 'Theme options', 'Theme options', 'manage_options', 'p2_options', array(__CLASS__, 'theme_options_page') );
		}

		/**
		 * callback for options page
		 * @see add_options_page()
		 * @see add_theme_page()
		 */
		public static function theme_options_page()
		{
			printf('<div class="wrap"><div class="icon32" id="icon-themes"><br /></div><h2>%s</h2>', __('Theme options', 'p2_theme'));
			if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == "true") {
				printf('<div id="message" class="updated"><p><strong>%s</strong></p></div>', __('Settings saved', 'p2_theme'));
			}
			settings_errors('p2_options');
			print('<form method="post" action="options.php">');
			print('<pre>');
			print_r(self::get_theme_options());
			print('</pre>');
			settings_fields('p2_options');
			do_settings_sections('p2_options');
			printf('<p><input type="submit" class="button-primary" name="submit" value="%s" /></p></form></div>', __('Save settings', 'p2_theme'));
		}

		/***********************************************
		 * callbacks for theme option fields           *
		 ***********************************************/

		/**
		 * settings section text callback
		 * @see add_settings_section()
		 */
		public static function section_text()
		{
			echo '';
		}

		/**
		 * text field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_text($fielddata)
		{
			$options = self::get_theme_options();
			$field = $fielddata["fieldname"];
			$cls = (isset($fielddata["class"]))? ' class="' . $fielddata["class"] . '"': '';
			$len = (isset($fielddata["length"]) && intVal($fielddata["length"]) > 0) ? $fielddata["length"] : 60;
			$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
			printf('<input id="p2_options_%s" name="p2_options[%s]" type="text" value="%s" size="%s"%s />', $field, $field, $option_value, $len, $cls);
		}

		/**
		 * integer field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_integer($fielddata)
		{
			if (!isset($fielddata["length"])) {
				$fielddata["length"] = 7;
			}
			$fielddata["class"] = 'integer';
			self::option_text($fielddata);
		}

		/**
		 * textarea field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_textarea($fielddata)
		{
			$options = self::get_theme_options();
			$field = $fielddata["fieldname"];
			$desc = isset($fielddata["description"]) ? $fielddata["description"] : "";
			$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
			printf('<textarea class="widefat" id="p2_options_%s" name="p2_options[%s]" cols="40" rows="5">%s</textarea>%s', $field, $field, $option_value, $desc);
		}

		/**
		 * checkbox field callbackget_ajax_data
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_checkbox($fielddata)
		{
			$options = self::get_theme_options();
			$field = $fielddata["fieldname"];
			$desc = isset($fielddata["description"]) ? $fielddata["description"] : "";
			$chckd = (isset($options[$field]) && $options[$field]) ? ' checked="checked"': '';
			printf('<input type="checkbox" id="p2_options_%s" name="p2_options[%s]"%s />%s', $field, $field, $chckd, $desc);
		}

		/**
		 * validation callback for register_setting function
		 * @see register_theme_options()
		 * @param array theme options to validate
		 */
		public static function validate_theme_options($theme_options)
		{
			$option_data = self::get_option_data();
			$options = self::get_theme_options();
			/* get all fields not handled by customiser */
			$settings = array();
			foreach ($option_data as $section) {
				if (!isset($section["customiser"])) {
					$settings = array_merge($fields, $section["settings"]);
				}
			}
			foreach ($settings as $fieldname => $details) {
				switch ($details['type']) {
					case 'checkbox':
						$options[$fieldname] = isset($theme_options[$fieldname]);
						break;
					case 'integer':
						$options[$fieldname] = intval($theme_options[$fieldname]);
						break;
					default:
						$options[$fieldname] = trim($theme_options[$fieldname]);
						break;
				}
			}
			return $options;
		}

		/***********************************************
		 * THEME CUSTOMISER STUFF                      *
		 ***********************************************/

		/**
		 * enqueues the script to enable real-time customisation of theme options
		 * Used by hook: 'customize_preview_init'
		 */
		public static function register_customiser_script()
		{
			wp_enqueue_script('p2_customiser_script', get_template_directory_uri() . '/js/customiser.js', array( 'jquery','customize-preview' ), p2::$version, true );
		}


		/**
		 * This outputs the json needed by the live settings javascript to tell it 
		 * which settings affect which parts of the page.
		 * Used by ajax hook: 'wp_ajax_customiser_options'
		 */
		public static function get_ajax_data()
		{
			echo json_encode(
				array(
					"data" => self::get_option_data(),
					"values" => self::get_theme_options(),
					"palette" => self::get_palette()
				)
			);
			die();
		}

		 /**
		  * This will output the custom WordPress settings to the live theme's WP head.
		  * Used by hook: 'wp_head'
		  */
		public static function wp_head()
		{
			/* get theme options */
			$theme_options = self::get_theme_options();

			/* get option data */
			$option_data = self::get_option_data();
			
			/* output styles to head */
			$css = '<style type="text/css">' . "\n<!--\n";

			/* go through $option_data and use selectors defined there */
			foreach ($option_data as $section) {
				foreach ($section["settings"] as $setting) {
					if (isset($setting["selector"])) {
						if (is_array($setting["selector"])) {
							for ($i = 0; $i < count($setting["selector"]); $i++) {
								$property = (isset($setting["property"]) && isset($setting["property"][$i]))? $setting["property"][$i]: "color";
								$value_fmt = (isset($setting["value_fmt"]) && isset($setting["value_fmt"][$i]))? $setting["value_fmt"][$i]: "%s";
								$value = str_replace('%s', $theme_options[$section["name"]][$setting["name"]], $value_fmt);
								$css .= sprintf('%s{%s:%s}', $setting["selector"][$i], $property, $value);
							}
						} else {
							$property = isset($setting["property"])? $setting["property"]: "color";
							$value_fmt = isset($setting["value_fmt"])? $setting["value_fmt"]: "%s";
							$value = str_replace('%s', $theme_options[$section["name"]][$setting["name"]], $value_fmt);
							$css .= sprintf('%s{%s:%s}', $setting["selector"], $property, $value);
						}
					}
				}
			}

			$normal_css .= "\n      -->\n    </style>\n";
			echo $ie_css . $ie_55_to_8_css . $ie6_css . $ie7_css . $normal_css;
			print('<!--/Customizer CSS-->');
		}
		 
		/**
		 * add the google analytics to the page footer if the option has been configured
		 * Used by hook: 'wp_footer'
		 */
		public static function wp_footer()
		{
			$options = self::get_theme_options();
			if (isset($options['google_analytics_id']) && trim($options['google_analytics_id']) != "") {
				printf("<script>var _gaq=[['_setAccount','%S'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'));</script>", $options['google_analytics_id']);
			}
		}

        /**
         * returns the default colour palette used by customiser colour controls
         * @see get_ajax_data()
         */
        private static function get_palette()
        {
			/* get theme options */
			$theme_options = self::get_theme_options();

			/* get option data */
			$option_data = self::get_option_data();
			
			/* put black and white in the pallette */
            $palette = array('#ffffff', '#000000');

			/* go through $option_data looking for colours */
			foreach ($option_data as $section) {
				foreach ($section["settings"] as $setting) {
					if ($setting["type"] == "colour" && isset($theme_options[$section["name"]]) && !empty($theme_options[$section["name"]])) {
            			$palette[] = $theme_options[$section["name"]];
            		}
            	}
            }
            return array_unique($palette);
        }

		/**
		 * Convert a hexadecimal color code to its RGB equivalent
		 *
		 * @param string $hexStr (hexadecimal color value)
		 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
		 * @param string $separator (to separate RGB values. Applicable only if second parameter is true.)
		 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
		 */
		function hex2RGB($hexStr, $returnAsString = false, $separator = ',')
		{
		    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
		    $rgbArray = array();
		    if (strlen($hexStr) == 6) {
		        $colorVal = hexdec($hexStr);
		        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
		        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		        $rgbArray['blue'] = 0xFF & $colorVal;
		    } elseif (strlen($hexStr) == 3) {
		        $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
		        $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
		        $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		    } else {
		        return false;
		    }
		    return $returnAsString ? implode($separator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
		}

	}
	p2_theme_options::register();

endif;