<?php
/**
 * Theme options
 */
class p2_theme_options
{
	public static function register()
	{
        /* add the wordpress theme options page */
        add_action( 'admin_menu', array('p2_theme_options', 'add_options_page') );
        /* register settings */
        add_action( 'admin_init', array('p2_theme_options', 'register_theme_options') );

        /* Setup the Theme Customizer settings and controls */
		add_action( 'customize_register' , array('p2_theme_options', 'register_customiser_settings' ) );
		/* Output custom CSS for options */
		add_action( 'wp_head' , array('p2_theme_options', 'print_css') );
		/* Add google analytics to the footer */
		add_action('wp_footer', array('p2_theme_options', 'google_analytics'), 20);
	}

	/**
	* add a submenu to the theme admin menu to access the theme options page
	*/
	public static function add_options_page() 
	{
		$theme_options_page = add_theme_page( 'Theme options', 'Theme options', 'manage_options', 'theme_options', array('p2_theme_options', 'theme_options_page') );
	}

	/**
	 * creates the options page
	 */
	public static function theme_options_page() 
	{
		print ("<div class=\"wrap\"><div class=\"icon32\" id=\"icon-themes\"><br /></div><h2>Theme options</h2>");
		if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == "true") {
			echo '<div id="message" class="updated"><p><strong>Settings saved.</strong></p></div>';
		}
		settings_errors('p2_options');
		printf('</p><p>To see the images currently stored for names in the wiki, <a href="%s">visit the Image management page</a></p>', admin_url('themes.php?page=manage_names'));
		print ('<form method="post" action="options.php">');
		settings_fields('p2_options');
		do_settings_sections('p2');
        print(self::get_palette());
		print ('<p><input type="submit" class="button-primary" name="submit" value="Save settings" /></p></form></div>');
	}

	/**
	 * gets the customisation options for both the theme customizer
	 * and the theme options page.
	 */
	private static function get_customisation_options()
	{
		$customisation_options = array(
			array(
				'section' => 'theme_options',
				'title' => 'Theme Options',
				'priority' => 35, 
				'capability' => 'edit_theme_options',
				'description' => 'Allows you to customize colours on the pages and blog.',
				'fields' => array(
					'background_colour' => array(
						'label' => 'Background colour',
						'type' => 'colour',
						'default' => '#5bb173',
						'selector' => 'html,body,.search form p.hellip span,.goButton a',
						'rules' => '{background-color:%s}'
					)
				)
			)
		);
		return $customiser_options;
	}

	/**
	 * gets the theme options from the database or uses the default values
	 */
	public static function get_theme_options()
	{
		$customiser_fields = self::get_customisation_options();
		$stored_options = get_option('p2_theme_options');
		$options = array();
		foreach ($customiser_fields as $cf) {
			foreach ($cf['fields'] as $fieldname => $details) {
				$options[$fieldname] = (is_array($stored_options) && isset($stored_options[$fieldname]))? $stored_options[$fieldname]: $details['default'];
			}
		}
		return $options;
	}

	/**
	 * prints the CSS for the wp_head hook
	 */
	public static function print_css()
	{
		$options = self::get_theme_options();
		$out = '<!--Customizer CSS--><style type="text/css">';
		$customiser_fields = self::get_customisation_options();
		foreach ($customiser_fields as $cf) {
			foreach ($cf['fields'] as $fieldname => $details) {
				$value = isset($options[$fieldname])? $options[$fieldname]: $details['default'];
				if (is_array($details['selector'])) {
					for ($i = 0; $i < count($details['selector']); $i++) {
						$out .= $details['selector'][$i] . sprintf($details['rules'][$i], $value);
					}
				} else {
					$out .= $details['selector'] . sprintf($details['rules'], $value);
				}
			}
		}
		$out .= '</style><!--/Customizer CSS-->';
		print($out);
	}

	/**
	 * returns an input containing the default colour palette
	 */
	private static function get_palette()
	{
		$options = self::get_theme_options();
		$customiser_fields = self::get_customisation_options();
		foreach ($customiser_fields as $cf) {
			foreach ($cf['fields'] as $fieldname => $details) {
				if ($details['type'] == 'colour') {
					$palette[] = (isset($options[$fieldname]))? $options[$fieldname]: $details['default'];
				}
			}
		}
		return sprintf('<input type="hidden" id="p2_default_palette" value="%s" />', implode(',', array_unique($palette)));
	}

	/**
	 * registers settings and sections for theme options page
	 */
	public static function register_theme_options() {
		register_setting( 
			'p2_options',
			'p2_options',
			array(
				'p2_theme_options',
				'validate_theme_options'
			)
		);
		$customiser_fields = self::get_customisation_options();
		foreach ($customiser_fields as $cf) {
			add_settings_section(
				$cf['section'], 
				$cf['title'], 
				array(
					'p2_theme_options',
					'section_text'
				), 
				'wkw'
			);
			foreach ($cf['fields'] as $fieldname => $details) {
				$method = 'option_' . $details['type'];
				add_settings_field(
					$fieldname,
					$details['label'],
					array(
						'p2_theme_options',
						$method
					) ,
					'p2',
					$cf['section'],
					array(
						"field" => $fieldname,
						"desc" => ""
					)
				);
			}
		}
	}

	/**
	 * settings section text callback
	 */
	public static function section_text()
	{
		echo '';
	}

	/**
	 * simple input field callback
	 */
	public static function option_text($fielddata)
	{
		$options = self::get_theme_options();
		$field = $fielddata["field"];
		$len = (isset($fielddata["length"]) && intVal($fielddata["length"]) > 0) ? $fielddata["length"] : 60;
		$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
		printf('<input id="p2_options_%s" name="p2_options[%s]" type="text" value="%s" size="%s" />', $field, $field, $option_value, $len);
	}

	/**
	 * colour input field callback
	 */
	public static function option_colour($fielddata)
	{
		$options = self::get_theme_options();
		$field = $fielddata["field"];
		$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
		printf('<input id="p2_options_%s" class="color-picker-hex" placeholder="Hex value" name="p2_options[%s]" type="text" data-default-color="%s" value="%s" size="7" />', $field, $field, $option_value, $option_value);
	}

	/**
	 * simple textarea field callback
	 */
	public static function option_textarea($fielddata)
	{
		$options = self::get_theme_options();
		$field = $fielddata["field"];
		$desc = isset($fielddata["description"]) ? $fielddata["description"] : "";
		$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
		printf('<textarea class="widefat" id="p2_options_%s" name="p2_options[%s]" cols="40" rows="5">%s</textarea>%s', $field, $field, $option_value, $desc);
	}

	/**
	 * validate_wkw_options callback for checking option values
	 */
	public static function validate_theme_options($theme_options)
	{
		if (!isset($theme_options[""]) || trim($theme_options[""]) == "") {
			add_settings_error("p2_options", "slug", "title");
		}
		return $theme_options;
	}


	/**
	 * This hooks into 'customize_register' (available as of WP 3.4) and allows
	 * you to add new sections and controls to the Theme Customize screen.
	 */
	public static function register_customiser_settings( $wp_customize )
	{
		$customiser_fields = self::get_customisation_options();
		foreach ($customiser_fields as $cf) {
			/* Define a new section to the Theme Customizer */
			$wp_customize->add_section( $cf['section'],
				array(
					'title' => $cf['title'],
					'priority' => $cf['priority'], 
					'capability' => $cf['capability'],
					'description' => $cf['description']
				)
			);
			$priority = 1;
			foreach ($cf['fields'] as $fieldname => $details) {
				/* Register new settings to the WP database */
				$wp_customize->add_setting( $cf['section'] . '[' . $fieldname . ']',
					array(
						'default' => $details['default'],
						'type' => 'option',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage'
					) 
				);
				/* define the control */
				$wp_customize->add_control( 
					new WP_Customize_Color_Control(
						$wp_customize,
						$cf['section'] . '_' . $fieldname,
						array(
							'label' => $details['label'],
							'section' => $cf['section'],
							'settings' => $cf['section'] . '[' . $fieldname . ']',
							'priority' => $priority		
						) 
					)
				);
				$priority++;
			}
		}
		/* make some stuff use live preview JS */
		$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
	}


	/**
	 * add the google analytics to the page footer if the option has been configured
	 * injected using wp_footer action
	 */
	public static function google_analytics()
	{
		$options = self::get_theme_options();
		if (isset($options['google_analytics_id']) && trim($options['google_analytics_id']) != "") {
			printf("<script>var _gaq=[['_setAccount','%S'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'));</script>", $options['google_analytics_id']);
		}
	}
}
p2_theme_options::register();