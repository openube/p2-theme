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
				'description' => 'Allows you to customize site options.',
				'fields' => array(
					'google_analytics_id' => array(
						'label' => 'Google Analytics ID',
						'type' => 'text',
						'default' => ''
					),
					'excerpt_length' => array(
						'label' => 'Length of Post excerpts',
						'type' => 'number',
						'default' => '50'
					),
					'excerpt_length' => array(
						'label' => '"Read more" link text',
						'type' => 'text',
						'default' => 'Read More&helli[p;'
					)
				)
			),
			array(
				'section' => 'theme_colours',
				'title' => 'Theme Colours',
				'priority' => 36, 
				'capability' => 'edit_theme_options',
				'description' => 'Allows you to customize site colours.',
				'fields' => array(
					'background_colour' => array(
						'label' => 'Background colour',
						'type' => 'colour',
						'default' => '#ffffff',
						'selector' => 'html,body',
						'rules' => '{background-color:%s}'
					),
					'text_colour' => array(
						'label' => 'Text colour',
						'type' => 'colour',
						'default' => '#000000',
						'selector' => 'html,body',
						'rules' => '{color:%s}'
					),
					'heading_colour' => array(
						'label' => 'Heading colour',
						'type' => 'colour',
						'default' => '#0000ff',
						'selector' => 'h1,h2,h3,h4,h5,h6',
						'rules' => '{color:%s}'
					),
					'link_colour' => array(
						'label' => 'Link colour',
						'type' => 'colour',
						'default' => '#0000cc',
						'selector' => 'a',
						'rules' => '{color:%s}'
					),
					'link_hover_colour' => array(
						'label' => 'Link colour (hover)',
						'type' => 'colour',
						'default' => '#0000ff',
						'selector' => 'a:hover',
						'rules' => '{color:%s}'
					),
					'link_active_colour' => array(
						'label' => 'Link colour (active)',
						'type' => 'colour',
						'default' => '#ff0000',
						'selector' => 'a:active',
						'rules' => '{color:%s}'
					),
					'link_visited_colour' => array(
						'label' => 'Link colour',
						'type' => 'colour',
						'default' => '#0000cc',
						'selector' => 'a:visited',
						'rules' => '{color:%s}'
					)
				)
			)
		);
		return $customisation_options;
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
				if ($details["type"] == 'colour' || $details['type'] == 'colouralpha') {
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
	public static function option_text($fielddata, $settings = array())
	{
		$options = self::get_theme_options();
		$settings = wp_parse_args($settings, array(
			"length" => 60,
			"class" => ''
			));
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
	 * colour input field callback with alpha
	 */
	public static function option_colouralpha($fielddata)
	{
		$options = self::get_wkw_options();
		$field = $fielddata["field"];
		$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
		$ho = self::rgba2hexop($option_value);
		//print_r($ho);
		printf('<input id="wkw_options_%s_hex" class="color-picker-hex" placeholder="Hex value" name="wkw_options[%s][hex]" type="text" data-default-color="%s" value="%s" size="7" /><br />Opacity (1 = opaque, 0 = transparent): <input "wkw-options_%s_op" type="text" size="5" name="wkw_options[%s][op]" value="%s" />', $field, $field, $ho['hex'], $ho['hex'], $field, $field, $ho['op'], $ho['op']);
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
		$customiser_fields = self::get_customisation_options();
		$options = self::get_theme_options();
		foreach ($customiser_fields as $cf) {
			foreach ($cf['fields'] as $fieldname => $details) {
				if ( ! isset($options[$fieldname]) ) {
					$options[$fieldname] = $details['default'];
				}
				if ( !isset($wkw_options[$fieldname])) {
					$wkw_options[$fieldname] = $options[$fieldname];
				}
				switch ($details['type']) {
				    case 'colour':
				        if ( ! preg_match('/^[#0-9A-Fa-f]{4,7}$/', $wkw_options[$fieldname]) ) {
							$wkw_options[$fieldname] = $options[$fieldname];
						}
						break;
					case 'colouralpha':
						/* make sure we have a hex value and opacity value */
					    if ( ! preg_match('/^[#0-9A-Fa-f]{4,7}$/', $wkw_options[$fieldname]['hex']) || ((floatval($wkw_options[$fieldname]['op']) > 1) || (floatval($wkw_options[$fieldname]['op']) < 0)) ) {
					    	$wkw_options[$fieldname] = $options[$fieldname];
					    } else {
					    	$rgb = self::hex2rgb($wkw_options[$fieldname]['hex']);
					    	$op = floatval($wkw_options[$fieldname]['op']);
					    	$wkw_options[$fieldname] = 'rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',' . $op . ')';
					    }
					    break;
					case 'opacity':
						if ((floatval($wkw_options[$fieldname]) > 1) || (floatval($wkw_options[$fieldname]) < 0)) {
							$wkw_options[$fieldname] = $options[$fieldname];
						} else {
							$wkw_options[$fieldname] = floatval($wkw_options[$fieldname]);
						}
					default:

						break;
				}
			}
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

	/**
	 * Extract a HEX colour and opacity from rgba colour
	 */
	function rgba2hexop($rgbaStr)
	{
		if (preg_match('/rgba\(([0-9]+),([0-9]+),([0-9]+),([0-9\.]+)\)/', $rgbaStr, $matches)) {
			$hexStr = '';
			for ($i = 1; $i < 4; $i++) {
				$hex = dechex($matches[$i]);
				if (strlen($hex) == 1) {
					$hex = "0" . $hex;
				}
				$hexStr .= $hex;
			}
			return array('hex' => '#' . $hexStr, 'op' => floatval($matches[4]));		
		} else {
			return array('hex' => '#000000', 'op' => 0.5);
		}
	}
}
p2_theme_options::register();