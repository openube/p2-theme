<?php
/**
 * Theme options
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2_theme_options' ) ) :

/**
 * class to add options for the theme
 * includes google analytics ID, global metadata and some aspects of the colour scheme
 */
class p2_theme_options
{
	public static function register()
	{
        /* add the wordpress theme options page */
        add_action( 'admin_menu', array(__CLASS__, 'add_options_pages') );
        /* register settings */
        add_action( 'admin_init', array(__CLASS__, 'register_theme_options') );

        /* Setup the Theme Customizer settings and controls */
		add_action( 'customize_register', array(__CLASS__, 'register_customiser_settings' ) );
		/* Setup the theme customiser live preview */
		add_action( 'customize_preview_init', array(__CLASS__, 'register_customiser_script' ) );

		/* Output custom CSS for options */
		add_action( 'wp_head', array(__CLASS__, 'wp_head') );
		/* Add google analytics to the footer */
		add_action( 'wp_footer', array(__CLASS__, 'wp_footer'), 20);
	}

	/**
	* add a submenu to the theme admin menu to access the theme options page
	*/
	public static function add_options_pages() 
	{
		$theme_settings_page = add_theme_page( 'Theme options', 'Theme options', 'manage_options', 'theme_options', array(__CLASS__, 'theme_options_page') );
		$theme_colours_page = add_theme_page( 'Theme colours', 'Theme colours', 'manage_options', 'theme_colours', array(__CLASS__, 'theme_colours_page') );
	}

	/**
	 * callbacks for options pges
	 * both use a generic method to output options in different sections
	 */
	public static function theme_options_page()
	{
		self::theme_settings_page('theme_options');
	}
	public static function theme_colours_page()
	{
		self::theme_settings_page('theme_colours');
	}

	/**
	 * creates the options page
	 */
	public static function theme_settings_page($section) 
	{
		print ("<div class=\"wrap\"><div class=\"icon32\" id=\"icon-themes\"><br /></div><h2>Theme options</h2>");
		if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == "true") {
			echo '<div id="message" class="updated"><p><strong>Settings saved.</strong></p></div>';
		}
		settings_errors('p2_options');
		printf('<form method="post" action="options.php"><input type="hidden" name="p2-section" value="%s" />', $section);
		settings_fields('p2_options');
		do_settings_sections($section);
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
				'customiser' => false,
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
						'type' => 'integer',
						'default' => '50'
					),
					'excerpt_more' => array(
						'label' => '"Read more" link text',
						'type' => 'text',
						'default' => 'Read More&hellip;'
					),
					'use_custom_header' => array(
						'label' => 'Use custom header?',
						'type' => 'checkbox',
						'default' => true
					),
					'use_custom_background' => array(
						'label' => 'Use custom background?',
						'type' => 'checkbox',
						'default' => true
					),
					'use_post_formats' => array(
						'label' => 'Use post formats?',
						'type' => 'checkbox',
						'default' => false
					),
					"use_boilerplate_htaccess" => array(
						'label' => 'Use HTML5 boilerplate .htaccess rules?',
						'type' => 'checkbox',
						'default' => true
					),
					'global_keywords' => array(
						'label' => 'Global keywords',
						'type' => 'text',
						'default' => '',
						'description' => 'Enter keywords which will be used on all pages (sepaated by semicolons).'
					),
					'global_description' => array(
						'label' => 'Global description',
						'type' => 'textarea',
						'default' => '',
						'description' => 'Enter a description which will be used on all pages (when excerpts are blank)'
					)
				)
			),
			array(
				'section' => 'theme_colours',
				'title' => 'Theme Colours',
				'customiser' => true,
				'customiser_section' => 'colors',
				'priority' => 36, 
				'capability' => 'edit_theme_options',
				'description' => 'Allows you to customize site colours.',
				'fields' => array(
					'text_colour' => array(
						'label' => 'Text colour',
						'type' => 'colour',
						'default' => '#000000',
						'selector' => 'html,body',
						'customiser' => true,
						'rules' => '{color:%s}'
					),
					'heading_colour' => array(
						'label' => 'Heading colour',
						'type' => 'colour',
						'default' => '#0000ff',
						'selector' => 'h1,h2,h3,h4,h5,h6',
						'customiser' => true,
						'rules' => '{color:%s}'
					),
					'link_colour' => array(
						'label' => 'Link colour',
						'type' => 'colour',
						'default' => '#0000cc',
						'customiser' => true,
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
		$stored_options = get_option('p2_options');
		$options = array();
		foreach ($customiser_fields as $cf) {
			foreach ($cf['fields'] as $fieldname => $details) {
				$options[$fieldname] = (is_array($stored_options) && isset($stored_options[$fieldname]))? $stored_options[$fieldname]: $details['default'];
			}
		}
		return $options;
	}

	/**
	 * prints output for the wp_head hook
	 */
	public static function wp_head()
	{
		global $post;
		$options = self::get_theme_options();

		/* custom CSS */
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

		/* metadata */
		print($out);
	}

	/**
	 * things to be placed in the page footer
	 */
	public static function wp_footer()
	{

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
				__CLASS__,
				'validate_theme_options'
			)
		);
		$customiser_fields = self::get_customisation_options();
		foreach ($customiser_fields as $cf) {
			add_settings_section(
				$cf['section'], 
				$cf['title'], 
				array(
					__CLASS__,
					'section_text'
				), 
				$cf['section']
			);
			foreach ($cf['fields'] as $fieldname => $details) {
				$method = 'option_' . $details['type'];
				add_settings_field(
					$fieldname,
					$details['label'],
					array(
						__CLASS__,
						$method
					) ,
					$cf['section'],
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
		$cls = ($settings["class"] == '')? '': ' class="' . $settings["class"] . '"';
		$field = $fielddata["field"];
		$len = (isset($settings["length"]) && intVal($settings["length"]) > 0) ? $settings["length"] : 60;
		$option_value = (isset($options[$field]) && trim($options[$field]) != "") ? trim($options[$field]) : "";
		printf('<input id="p2_options_%s" name="p2_options[%s]" type="text" value="%s" size="%s"%s />', $field, $field, $option_value, $len, $cls);
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
	 * simple textarea field callback
	 */
	public static function option_checkbox($fielddata)
	{
		$options = self::get_theme_options();
		$field = $fielddata["field"];
		$desc = isset($fielddata["description"]) ? $fielddata["description"] : "";
		$option_value = (isset($options[$field]) && $options[$field]) ? true : false;
		$chckd = $option_value? ' checked="checked"': '';
		printf('<input type="checkbox" id="p2_options_%s" name="p2_options[%s]"%s />%s', $field, $field, $chckd, $desc);
	}

	/**
	 * simple input field callback
	 */
	public static function option_integer($fielddata, $settings = array())
	{
		$settings["length"] = 7;
		$settings["class"] = 'integer';
		self::option_text($fielddata, $settings);
	}

	/**
	 * validate_wkw_options callback for checking option values
	 */
	public static function validate_theme_options($theme_options)
	{
		$customiser_fields = self::get_customisation_options();
		$options = self::get_theme_options();
		/* see whether we are validating settings or colours */
		if (isset($theme_options["google_analytics_id"])) {
			$fields = $customiser_fields[0]["fields"];
		} else {
			$fields = $customiser_fields[1]["fields"];
		}
		foreach ($fields as $fieldname => $details) {
			switch ($details['type']) {
				case 'checkbox':
					$options[$fieldname] = isset($theme_options[$fieldname]);
					break;
				case 'integer':
					$options[$fieldname] = intval($theme_options[$fieldname]);
					break;
			    case 'colour':
			        if ( ! preg_match('/^[#0-9A-Fa-f]{4,7}$/', $theme_options[$fieldname]) ) {
						$options[$fieldname] = $options[$fieldname];
					}
					break;
				case 'colouralpha':
					/* make sure we have a hex value and opacity value */
				    if (preg_match('/^[#0-9A-Fa-f]{4,7}$/', $theme_options[$fieldname]['hex']) || ((floatval($theme_options[$fieldname]['op']) > 1) || (floatval($theme_options[$fieldname]['op']) < 0)) ) {
				    	$rgb = self::hex2rgb($theme_options[$fieldname]['hex']);
				    	$op = floatval($theme_options[$fieldname]['op']);
				    	$options[$fieldname] = 'rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',' . $op . ')';
				    }
				    break;
				case 'opacity':
					if ((floatval($theme_options[$fieldname]) > 0) && (floatval($theme_options[$fieldname]) < 1)) {
						$options[$fieldname] = floatval($theme_options[$fieldname]);
					}
				default:

					break;
			}
		}
		return $options;
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
			if ($cf['customiser']) {
				if (isset($cf['customiser_section'])) {
					$section = $cf['customiser_section'];
				} else {
					$section = $cf['section'];
					$wp_customize->add_section( $cf['section'],
						array(
							'title' => $cf['title'],
							'priority' => $cf['priority'], 
							'capability' => $cf['capability'],
							'description' => $cf['description']
						)
					);
				}
				$priority = 1;
				foreach ($cf['fields'] as $fieldname => $details) {
					if (isset($details['customiser']) && $details['customiser']) {
						/* Register new settings to the WP database */
						$wp_customize->add_setting( $section . '[' . $fieldname . ']',
							array(
								'default' => $details['default'],
								'type' => 'option',
								'capability' => 'edit_theme_options',
								'transport' => 'postMessage'
							) 
						);
						/* define the control */
						if ($details["type"] == "colour") {
							$wp_customize->add_control( 
								new WP_Customize_Color_Control(
									$wp_customize,
									$section . '_' . $fieldname,
									array(
										'label' => $details['label'],
										'section' => $section,
										'settings' => $section . '[' . $fieldname . ']',
										'priority' => $priority		
									) 
								)
							);
							$priority++;
						} elseif ($details["type"] == "text") {
							$wp_customize->add_control( 
								new WP_Customize_Control(
									$wp_customize,
									$section . '_' . $fieldname,
									array(
										'label' => $details['label'],
										'section' => $section,
										'settings' => $section . '[' . $fieldname . ']',
										'priority' => $priority		
									) 
								)
							);
							$priority++;
						}
					}
				}
			}
		}
		/* make some stuff use live preview JS */
		$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
	}

	/**
	 * enqueues the script to enable real-time customisation of theme options
	 */
	public static function register_customiser_script()
	{
		wp_enqueue_script('p2_customiser_script', get_template_directory_uri() . '/js/customiser.js', array( 'jquery','customize-preview' ), p2::$version, true );
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
endif;