<?php
/**
 * Theme options
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2_theme_options' ) ) {

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
				foreach($section["settings"] as $details) {
					$defaults[$details["name"]] = $details["default"];
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
						array(
							'name' => 'google_analytics_id',
							'label' => __('Google Analytics ID', 'p2_theme'),
							'type' => 'text',
							'allowempty' => true,
							'default' => ''
						),
						array(
							'name' => 'google_verification',
							'label' => __('Google Verification ID', 'p2_theme'),
							'type' => 'text',
							'allowempty' => true,
							'default' => ''
						),
						array(
							'name' => 'use_post_thumbnails',
							'label' => __('Use post thumbnails? (featured images)', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						),
						array(
							'name' => 'post_thumbnail_width',
							'label' => __('Post thumbnail width', 'p2_theme'),
							'type' => 'integer',
							'allowzero' => false,
							'default' => 320
						),
						array(
							'name' => 'post_thumbnail_height',
							'label' => __('Post thumbnail height', 'p2_theme'),
							'type' => 'integer',
							'allowzero' => false,
							'default' => 240
						),
						array(
							'name' => 'excerpt_length',
							'label' => __('Length of Post excerpts', 'p2_theme'),
							'type' => 'integer',
							'allowzero' => false,
							'default' => 50
						),
						array(
							'name' => 'excerpt_more',
							'label' => __('"Read more" link text', 'p2_theme'),
							'type' => 'text',
							'allowempty' => false,
							'default' => __('Read More&hellip;', 'p2_theme'),
						),
						array(
							'name' => 'use_custom_header',
							'label' => __('Use custom header?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						),
						array(
							'name' => 'use_custom_background',
							'label' => __('Use custom background?', 'p2_theme'),
							'type' => 'checkbox',
							'default' => true
						),
						array(
							'name' => 'use_post_formats',
							'label' => __('Use post formats?', 'p2_theme'),
							'type' => 'checkboxes',
							'default' => array(),
							'choices' => array(
								'aside',
								'image',
								'video',
								'audio',
								'quote',
								'link',
								'gallery'
							)
						),
						array(
							'name' => 'use_boilerplate_htaccess',
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
					"priority" => 35,
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
				),
				array(
					"name" => 'navbar_styles',
					"title" => __( 'Navigation', 'p2_theme' ),
					"customiser" => true,
					"priority" => 37,
					"description" => __('Change the navigation bars.', 'p2_theme'),
					"settings" => array(
						array(
							"name" => "top_navbar_vertical",
						 	"label" => __( 'Vertical alignment (top fixed navbar)', 'p2_theme' ),
							"type" => "radio",
							"priority" => 2,
							"default" => 'navbar-fixed-top',
							"choices" => array(
								'navbar-fixed-top' => "Top",
								'navbar-fixed-bottom' => 'Bottom'
							)
						),
						array(
							"name" => "top_navbar_colour",
						 	"label" => __( 'Colour (top fixed navbar)', 'p2_theme' ),
							"type" => "radio",
							"priority" => 3,
							"default" => 'navbar-default',
							"choices" => array(
								'navbar-default' => "Default",
								'navbar-inverse' => 'Inverse'
							)
						),
						array(
							"name" => "header_navbar_colour",
						 	"label" => __( 'Colour (navbar under header)', 'p2_theme' ),
							"type" => "radio",
							"priority" => 5,
							"default" => 'navbar-default',
							"choices" => array(
								'navbar-default' => "Default",
								'navbar-inverse' => 'Inverse'
							)
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
				if (!$section["customiser"]) {
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
					foreach ($section["settings"] as $details) {
						$method = 'option_' . $details['type'];
						add_settings_field(
							$details["name"],
							$details['label'],
							array(
								__CLASS__,
								$method
							) ,
							$section['page'],
							$section['name'],
							array(
								"fieldname" => $details["name"]
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
		public static function register_customiser_options( $wp_customize )
		{
			/* get option data */
			$options = self::get_option_data();

			/* get the default values */
			$default_options = self::get_default_options();
			
			/* go through options adding fields */
			foreach ($options as $section) {

				if ($section["customiser"]) {

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

					foreach ($section["settings"] as $details) {
				
						$setting_name = 'p2_options[' . $details["name"] . ']';
						
						/* Register new settings */
						$wp_customize->add_setting( $setting_name,
							array(
								'default' => $default_options[$details["name"]], //Default setting/value to save
								'type' => 'option', //Is this an 'option' or a 'theme_mod'?
								'capability' => 'edit_theme_options', //Optional. Special permissions for accessing this setting.
								'transport' => 'postMessage', //What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
							) 
						);

						/* add Customiser controls */
						switch ($details["type"]) {
							case "colour":
								$wp_customize->add_control( new WP_Customize_Color_Control( //Instantiate the color control class
									$wp_customize, //Pass the $wp_customize object (required)
									str_replace(array('[', ']'), array('_', ''), $setting_name), //Set a unique ID for the control
									array(
										'label' => $details["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $details["priority"], //Determines the order this control appears in for the specified section
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
										'type' => $details["type"], // type of control (text, checkbox or dropdown pages)
										'label' => $details["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $details["priority"], //Determines the order this control appears in for the specified section
									)
								) );
								break;
							case "radio":
							case "select":
								$wp_customize->add_control(	new WP_Customize_Control( //Instantiate the general control class
									$wp_customize,
									str_replace(array('[', ']'), array('_', ''), $setting_name), //Set a unique ID for the control
									array(
										'type' => $details["type"], // type of control (radio or select)
										'label' => $details["label"], //Admin-visible name of the control
										'section' => $section_name, //ID of the section this control should render in (can be one of yours, or a WordPress default section)
										'settings' => $setting_name, //Which setting to load and manipulate
										'priority' => $details["priority"], //Determines the order this control appears in for the specified section
										'choices' => $details["choices"] // Array of possible values for radio/select
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
		 * multiple checkbox field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_checkboxes($fielddata)
		{
			$options = self::get_theme_options();
			$option_data = self::get_option_data();
			$field = $fielddata["fieldname"];
			$desc = isset($fielddata["description"]) ? $fielddata["description"] : "";
			$choices = array();
			foreach ($option_data as $section) {
				foreach ($section["settings"] as $details) {
					if ($details["name"] == $field && isset($details["choices"])) {
						$choices = $details["choices"];
					}
				}
			}
			if (count($choices)) {
				$count = 1;
				foreach ($choices as $choice) {
					$chckd = (in_array($choice, $options[$field]))? ' checked="checked"': '';
					printf('<label for="id="p2_options_%s_%d"><input type="checkbox" id="p2_options_%s_%d" name="p2_options[%s][]" value="%s"%s /> %s</label><br />', $field, $count, $field, $count, $field, $choice, $chckd, $choice);
					$count++;
				}
			}
		}

		/**
		 * validation callback for register_setting function
		 * @see register_theme_options()
		 * @param array theme options to validate
		 */
		public static function validate_theme_options($theme_options)
		{
			$default_options = self::get_default_options();
			$option_data = self::get_option_data();
			foreach ($option_data as $section) {
				if (!isset($section["customiser"])) {
					foreach ($section["settings"] as $details) {
						switch ($details["type"]) {
							case "integer":
								if (isset($theme_options[$details["name"]])) {
									$theme_options[$details["name"]] = intval($theme_options[$details["name"]]);
								} else {
									$theme_options[$details["name"]] = $default_options[$details["name"]];
								}
								if ($details["allowzero"] === false && $theme_options[$details["name"]] == 0) {
									$theme_options[$details["name"]] = $default_options[$details["name"]];	
								}
								break;
							case 'text':
								if (isset($theme_options[$details["name"]])) {
									$theme_options[$details["name"]] = trim($theme_options[$details["name"]]);
								} else {
									$theme_options[$details["name"]] = $default_options[$details["name"]];
								}
								if ($details["allowempty"] === false && $theme_options[$details["name"]] == '') {
									$theme_options[$details["name"]] = $default_options[$details["name"]];
								}
								break;
							case "checkbox":
								$theme_options[$details["name"]] = (isset($theme_options[$details["name"]]));
								break;
							case "checkboxes":
								$selection = array();
								foreach ($details["choices"] as $choice) {
									if (in_array($choice, $theme_options[$details["name"]])) {
										$selection[] = $choice;
									}
								}
								$theme_options[$details["name"]] = $selection;
								break;
							default:
								if (!isset($theme_options[$details["name"]])) {
									$theme_options[$details["name"]] = $default_options[$details["name"]];
								}
								break;
						}
					}
				}
			}
			return $theme_options;
		}

		/***********************************************
		 * THEME CUSTOMISER STUFF                      *
		 ***********************************************/

		/**
		 * This outputs the json needed by the live settings javascript to tell it 
		 * which settings affect which parts of the page.
		 * Used by ajax hook: 'wp_ajax_customiser_options'
		 */
		public static function get_ajax_data()
		{
			echo json_encode(
				array(
					"settings" => self::get_option_data(),
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
				foreach ($section["settings"] as $details) {
					if (isset($details["selector"])) {
						if (is_array($details["selector"])) {
							for ($i = 0; $i < count($details["selector"]); $i++) {
								$property = (isset($details["property"]) && isset($details["property"][$i]))? $details["property"][$i]: "color";
								$value_fmt = (isset($details["value_fmt"]) && isset($details["value_fmt"][$i]))? $details["value_fmt"][$i]: "%s";
								$value = str_replace('%s', $theme_options[$details["name"]], $value_fmt);
								$css .= sprintf('%s{%s:%s}', $details["selector"][$i], $property, $value);
							}
						} else {
							$property = isset($details["property"])? $details["property"]: "color";
							$value_fmt = isset($details["value_fmt"])? $details["value_fmt"]: "%s";
							$value = str_replace('%s', $theme_options[$details["name"]], $value_fmt);
							$css .= sprintf('%s{%s:%s}', $details["selector"], $property, $value);
						}
					}
				}
			}

			$css .= "\n      -->\n    </style>\n";
			print($css . '<!--/Customizer CSS-->');
			if (isset($theme_options['google_verification']) && trim($theme_options['google_verification']) != "") {
				printf('<meta name="google-verification" value="%s">', $theme_options['google_verification']);
			}
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
				if ($section["customiser"]) {
					foreach ($section["settings"] as $setting) {
						if ($setting["type"] == "colour" && isset($theme_options[$setting["name"]]) && !empty($theme_options[$setting["name"]])) {
	            			$palette[] = $theme_options[$setting["name"]];
	            		}
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

}