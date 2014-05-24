<?php
/**
 * background.php
 * implements a custom background
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

if ( ! class_exists( 'p2_custom_background' ) ) {
	class p2_custom_background
	{
		/**
		 * gradient_options array
		 */
		public static $gradient_options = array();

		/**
		 * repeat options array
		 */
		public static $repeat_options = array();

		/**
		 * registers all the methiods of the class with the Wordpress API
		 * and adds support for different features in the theme
		 */
		public static function register() {

			/* get the theme options */
			$theme_options = p2_theme_options::get_theme_options();

			/* add a custom background */
			if ($theme_options["use_custom_background"]) {
				add_action( 'admin_menu', array(__CLASS__, 'add_background_admin_menu' ) );
				/*add_theme_support( 'custom-background', array(
					'default-color' => 'e6e6e6',
				) );*/
			}
	        /* register settings */
	        add_action( 'admin_init', array(__CLASS__, 'register_background_options') );

			/* Output custom CSS for options */
			add_action( 'wp_head', array(__CLASS__, 'wp_head') );
			/* Add google analytics to the footer */
			add_action( 'wp_footer', array(__CLASS__, 'wp_footer'), 20);

	        /* set gradient types */
	        self::$gradient_options['horizontal2'] = __('Horizontal, two colours', 'p2_theme');
			self::$gradient_options['horizontal3'] = __('Horizontal, three colours', 'p2_theme');
			self::$gradient_options['vertical2'] = __('Vertical, two colours', 'p2_theme');
			self::$gradient_options['vertical3'] = __('Vertical, three colours', 'p2_theme');
			self::$gradient_options['directional'] = __('Directional', 'p2_theme');
			self::$gradient_options['radial'] = __('Radial', 'p2_theme');

			/* set repeat types */
			self::$repeat_options['no-repeat'] = __('Do not repeat image', 'p2_theme');
			self::$repeat_options['repeat-x'] = __('Repeat horizontally', 'p2_theme');
			self::$repeat_options['repeat-y'] = __('Repeat vertically', 'p2_theme');
			self::$repeat_options['repeat'] = __('Repeat (tile)', 'p2_theme');
			self::$repeat_options['stretch'] = __('Stretch', 'p2_theme');

		}

		public static function add_background_admin_menu()
		{
			add_theme_page(
				__('Background', 'p2_theme'),
				__('Background', 'p2_theme'),
				"manage_options",
				"background_settings",
				array(__CLASS__, "background_options_page")
			);

		}

		public static function background_options_page()
		{
			printf('<div class="wrap"><h1>%s</h1>', __('Custom Background', 'p2_theme'));
			if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == "true") {
				printf('<div id="message" class="updated"><p><strong>%s</strong></p></div>', __('Settings saved', 'p2_theme'));
			}
			settings_errors('p2_background_options');
			print('<form method="post" action="options.php">');
			settings_fields('p2_background_options');
			do_settings_sections('p2_background_options');
			printf('<p><input type="submit" class="button-primary" name="submit" value="%s" /></p></form></div>', __('Save settings', 'p2_theme'));
		}


		/**
		 * gets the background options from the database or uses the default values
		 */
		public static function get_background_options()
		{
			$defaults = self::get_default_options();
			$background_options = get_option('p2_background_options');
			$options = array();
			foreach ($defaults as $name => $value) {
				$options[$name] = (isset($background_options[$name]))? $background_options[$name]: $value;
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
		 * registers settings and sections for theme options page
		 * Used by 'admin_init' hook
		 */
		public static function register_background_options()
		{
			/* register the setting with Wordpress - all options are stored here */
			register_setting( 
				'p2_background_options',
				'p2_background_options',
				array(
					__CLASS__,
					'validate_background_options'
				)
			);

			/* get the options data */
			$option_data = self::get_option_data();
			foreach ($option_data as $section) {
				/* just do the Theme options here - customiser is handlked elsewhere */
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
							"name" => $details["name"]
						)
					);
				}
			}
		}

		/**
		 * validation callback for register_setting function
		 * @see register_background_options()
		 * @param array theme options to validate
		 */
		public static function validate_background_options($background_options)
		{
			$default_options = self::get_default_options();
			$option_data = self::get_option_data();
			foreach ($option_data as $section) {
				foreach ($section["settings"] as $details) {
					switch ($details["type"]) {
						case "integer":
							if (isset($background_options[$details["name"]])) {
								$background_options[$details["name"]] = intval($background_options[$details["name"]]);
							} else {
								$background_options[$details["name"]] = $default_options[$details["name"]];
							}
							break;
						case 'text':
							if (isset($background_options[$details["name"]])) {
								$background_options[$details["name"]] = trim($background_options[$details["name"]]);
							} else {
								$background_options[$details["name"]] = $default_options[$details["name"]];
							}
							break;
						case "checkbox":
							$background_options[$details["name"]] = (isset($background_options[$details["name"]]));
							break;
						case "colour":
							if (isset($background_options[$details["name"]]) && preg_match('/^#[0-9a-f]{3,6}$/', trim(strtolower($background_options[$details["name"]])))) {
								$background_options[$details["name"]] = trim(strtolower($background_options[$details["name"]]));
							} else {
								add_settings_error('p2_background_options[' . $details["name"] . ']', 'invalid_colour', __('Invalid color specified for background - please use hexadecimal colours only here'));
								$background_options[$details["name"]] = $default_options[$details["name"]];
							}
							break;
						case "gradient_type":
							if ( ! in_array($background_options[$details["name"]], array_keys(self::$gradient_options))) {
								$background_options[$details["name"]] = $default_options[$details["name"]];
							}
							break;
						case "gradient_direction":
							$val = intval($background_options[$details["name"]]);
							if ($val >= 0 && $val <= 360) {
								$background_options[$details["name"]] = $val;
							}
							break;
						case "colour_stop":
							$val = intval($background_options[$details["name"]]);
							if ($val >= 0 && $val <= 100) {
								$background_options[$details["name"]] = $val;
							}
							break;
						case "background_repeat":
							if ( ! in_array($background_options[$details["name"]], array_keys(self::$repeat_options))) {
								$background_options[$details["name"]] = $default_options[$details["name"]];
							}
							break;
						case "background_position":
							$val = trim($background_options[$details["name"]]);
							$keywords = '(top|bottom|left|right|center)';
							$units = '(%|em|px)';
							if (preg_match('/^' . $keywords . '$/', $val) ||
							    preg_match('/^' . $keywords . ' ' . $keywords . '$/', $val) ||
							    preg_match('/^[0-9]+' . $units . '$/', $val) ||
								preg_match('/^[0-9]+' . $units . ' [0-9]+' . $units . '$/', $val) ||
								preg_match('/^' . $keywords . ' [0-9]+' . $units . '$/', $val) ||
							    preg_match('/^[0-9]+' . $units . ' ' . $keywords . '$/', $val)) {
							    $background_options[$details["name"]] = $val;
							} else {
								$background_options[$details["name"]] = $default_options[$details["name"]];	
							}
							break;
						case "additional":
							/* check images for image backgrounds */
							if ( isset($background_options["additional"]) ) {
							}
							if ( ! isset($background_options["additional"]) || ! in_array($background_options["additional"], array("gradient", "single", "multiple")) ) {
								$background_options["additional"] = false;
							} else {
								if ( ($background_options["additional"] == "single" && trim($background_options["single_image"]) == "")
									|| ($background_options["additional"] == "multiple" && trim($background_options["multiple_image"]) == "") ) {
									$msg = ($background_options["additional"] == "single")? 'No image selected for background': 'No images selected for background';
									add_settings_error('p2_background_options', 500, $msg);
									$background_options["additional"] = false;
								}
							}
							break;
					}
				}
			}
			return $background_options;
		}


		/**
		 * gets the customisation options for both the theme customiser
		 * and the theme options page.
		 */
		private static function get_option_data()
		{
			return array(
				array(
					'name' => 'p2_background_options',
					'page' => 'p2_background_options',
					'title' => __('Background Options', 'p2_theme'),
					'capability' => 'edit_theme_options',
					'description' => __('Allows you to customize the site background colour.', 'p2_theme'),
					'settings' => array(
						array(
							'name' => 'colour',
							'label' => __('Background colour', 'p2_theme'),
							'type' => 'colour',
							'default' => '#ffffff'
						),
						array(
							'name' => 'additional',
							'label' => __('Additional options', 'p2_theme'),
							'type' => 'additional',
							'default' => false
						),
						array(
							'name' => 'gradient_type',
							'label' => __('Type of gradient', 'p2_theme'),
							'type' => 'gradient_type',
							'default' => 'vertical2'
						),
						array(
							'name' => 'gradient_direction',
							'label' => __('Direction of gradient?', 'p2_theme'),
							'type' => 'gradient_direction',
							'default' => '45'
						),
						array(
							'name' => 'gradient1',
							'label' => __('First colour in gradient', 'p2_theme'),
							'type' => 'colour',
							'default' => '#ffffff'
						),
						array(
							'name' => 'colour_stop1',
							'label' => __('First colour stop', 'p2_theme'),
							'type' => 'colour_stop',
							'default' => '0'
						),
						array(
							'name' => 'gradient2',
							'label' => __('Second colour in gradient', 'p2_theme'),
							'type' => 'colour',
							'default' => '#ffffff'
						),
						array(
							'name' => 'colour_stop2',
							'label' => __('Second colour stop', 'p2_theme'),
							'type' => 'colour_stop',
							'default' => '100'
						),
						array(
							'name' => 'gradient3',
							'label' => __('Third colour in gradient', 'p2_theme'),
							'type' => 'colour',
							'default' => '#ffffff'
						),
						array(
							'name' => 'single_image',
							'label' => __('Background image', 'p2_theme'),
							'type' => 'single_image',
							'default' => ''
						),
						array(
							'name' => 'background_repeat',
							'label' => __('Repeat the image?', 'p2_theme'),
							'type' => 'background_repeat',
							'default' => 'no-repeat'
						),
						array(
							'name' => 'background_position',
							'label' => __('Image position', 'p2_theme'),
							'type' => 'background_position',
							'default' => 'top left'
						),
						array(
							'name' => 'multiple_image',
							'label' => __('Background images (slideshow)', 'p2_theme'),
							'type' => 'multiple_image',
							'default' => ''
						),
						array(
							'name' => 'slide_transition',
							'label' => __('Speed of transition (ms)', 'p2_theme'),
							'type' => 'integer',
							'default' => '500'
						),
						array(
							'name' => 'slide_pause',
							'label' => __('Pause between slides (ms)', 'p2_theme'),
							'type' => 'integer',
							'default' => '5000'
						)
					)
				)
			);
		}

		/**
		 * settings section text callback
		 * @see add_settings_section()
		 */
		public static function section_text()
		{
			echo '';
		}

		/**
		 * gradient / single image / multiple image radio field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_additional($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$sel_single = $sel_multiple = $sel_gradient = '';
			if ($options[$name] == "single") {
				$sel_single = ' checked';
			} elseif ($options[$name] == "multiple") {
				$sel_multiple = ' checked';
			} elseif ($options[$name] == "gradient") {
				$sel_gradient = ' checked';
			}
			printf('<p><label for="p2_background_options_additional_gradient"><input type="checkbox" class="checkOnClick chooseOne" name="p2_background_options[additional]" id="p2_background_options_additional_gradient" value="gradient"%s>%s</label>', $sel_gradient, __('Use gradient', 'p2_theme'));
			printf('<br /><label for="p2_background_options_additional_single"><input type="checkbox" class="checkOnClick chooseOne" name="p2_background_options[additional]" id="p2_background_options_additional_single" value="single"%s>%s</label>', $sel_single, __('Use a single image', 'p2_theme'));
			printf('<br /><label for="p2_background_options_additional_multiple"><input type="checkbox" class="checkOnClick chooseOne" name="p2_background_options[additional]" id="p2_background_options_additional_multiple" value="multiple"%s>%s</label></p>', $sel_multiple, __('Use multiple images (slideshow)', 'p2_theme'));
		}

		/**
		 * text field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_text($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$cls = (isset($fielddata["class"]))? ' class="' . $fielddata["class"] . '"': '';
			$len = (isset($fielddata["length"]) && intVal($fielddata["length"]) > 0) ? $fielddata["length"] : 60;
			$desc = (isset($fielddata["desc"]) && trim($fielddata["desc"]) != "")? '<p><em>' . trim($fielddata["desc"]) . '</em></p>': '';
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			printf('<p><input id="p2_background_options_%s" name="p2_background_options[%s]" type="text" value="%s" size="%s"%s /></p>%s', $name, $name, $value, $len, $cls, $desc);
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
		 * checkbox field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */
		public static function option_checkbox($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$chckd = (isset($options[$name]) && $options[$name]) ? ' checked' : '';
			printf('<input class="checkOnClick" type="checkbox" id="p2_background_options_%s" name="p2_background_options[%s]"%s />', $name, $name, $chckd);
		}

		/**
		 * colour field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_colour($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			printf('<input id="p2_background_options_%s" class="color-picker-hex" placeholder="Hex value" name="p2_background_options[%s]" type="text" data-default-color="%s" value="%s" size="7" />', $name, $name, $value, $value);

		}

		/**
		 * colour field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_colour_stop($fielddata)
		{
			$options = self::get_background_options();
			$fielddata["length"] = 3;
			$fielddata["desc"] = __('Input a percentage between 0 and 100', 'p2_theme');
			self::option_text($fielddata);

		}

		/**
		 * single image field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_single_image($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			self::media_selection_form_control("p2_background_options[$name]", "p2_background_options_$name", $value, false);
		}

		/**
		 * multiple image field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_multiple_image($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			self::media_selection_form_control("p2_background_options[$name]", "p2_background_options_$name", $value, true);
		}

		/**
		 * interface for the image selection options
		 * This prints a hidden field to store the option value, and an area which will
		 * serve as the preview for the images currently selected for the control
		 */
		public static function media_selection_form_control($name, $id, $value, $multiple = true)
		{
			$class = $multiple? ' multiple': ' single';
			printf('<div class="media-selection-control%s"><input class="checkOnChange" type="hidden" name="%s" id="%s" value="%s">', $class, $name, $id, $value);

			/* make an array from the image IDs */
			if ( $value && ! empty( $value ) ) {
				$image_ids = explode(',', $value);
			} else {
				$image_ids = array();
			}
			/* preview area. Has same ID as the input with a '-selection' suffix */
			printf('<div id="%s-preview" class="media-selection-preview" data-inputid="%s">', $id, $id);
			/* get previews for the images added so far */
			if (count($image_ids)) {
				foreach($image_ids as $image_id) {
					$image_attributes = wp_get_attachment_image_src( $image_id, 'thumbnail' );
					if ($image_attributes) {
						printf('<div class="image-container" data-imageid="%s"><div data-inputid="%s" data-imageid="%s" class="image-inner"><img src="%s" /><a class="remove-image" href="#" title="%s">&#61826;</a></div></div>', $image_id, $id, $image_id, $image_attributes[0], __('Remove image', 'p2_theme') );
					}
				}

			} else {
				$msg = $multiple? __('No images selected', 'p2_theme'): __('No image selected', 'p2_theme');
				printf('<p>%s.</p>', $msg);
			}
			print('</div><span style="clear:both;">&nbsp;</span>');
			/* button which will activate the media browser/uploader */
			$buttonClass = $multiple? 'mediaBrowserButtonImages': 'mediaBrowserButtonImage';
			$buttonText = $multiple? __('Select Images', 'p2_theme'): __('Select Image', 'p2_theme');
			printf('<p class="media-selection-button"><a class="button-secondary %s" data-inputid="%s" href="#">%s</a></p></div>', $buttonClass, $id, $buttonText);
		}

		/**
		 * background gradient type field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_gradient_type($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			printf('<p><select class="checkOnChange" name="p2_background_options[%s]" id="p2_background_options_%s">', $name, $name);
			foreach (self::$gradient_options as $type => $label) {
				$sel = $value == $type? ' selected': '';
				printf('<option value="%s"%s>%s</option>', $type, $sel, $label);	
			}
			print('</select></p>');
		}

		/**
		 * background gradient direction field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_gradient_direction($fielddata)
		{
			$options = self::get_background_options();
			$fielddata["length"] = 3;
			$fielddata["desc"] = __('Input a number between 0 and 360', 'p2_theme');
			self::option_text($fielddata);
		}

		/**
		 * background image tile/repeat field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_background_repeat($fielddata)
		{
			$options = self::get_background_options();
			$name = $fielddata["name"];
			$value = (isset($options[$name]) && trim($options[$name]) != "") ? trim($options[$name]) : "";
			printf('<p><select class="checkOnChange" name="p2_background_options[%s]" id="p2_background_options_%s">', $name, $name);
			foreach (self::$repeat_options as $type => $label) {
				$sel = $value == $type? ' selected': '';
				printf('<option value="%s"%s>%s</option>', $type, $sel, $label);	
			}
			print('</select></p>');
		}

		/**
		 * background image position field callback
		 * @see add_settings_field()
		 * @param array data passed to callback by add_settings_field
		 */ 
		public static function option_background_position($fielddata)
		{
			$fielddata["class"] = 'integer';
			$fielddata["desc"] = __('Position can use keywords <em>top</em>, <em>bottom</em>, <em>left</em> or <em>right</em>. Alternatively, values can be entered in pixels, percentages or ems.');
			self::option_text($fielddata);
		}

		/**
		 * prints styles from custom backgrounds to the head element
		 */
		public static function wp_head()
		{
			$options = self::get_background_options();
			//print('<pre>' . print_r($options, true) . '</pre>');
			$out = '';
			if ($options['additional'] == 'gradient') {
				switch ($options['gradient_type']) {
					case "horizontal2":
						$out .= sprintf('background-image: -webkit-linear-gradient(left, color-stop(%s %s%%), color-stop(%s %s%%));', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= sprintf('background-image: -o-linear-gradient(left, %s %s%%, %s %s%%);', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= sprintf('background-image: linear-gradient(to right, %s %s%%, %s %s%%);', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= 'background-repeat: repeat-x;';
						$out .= sprintf('filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=\'%s\', endColorstr=\'%s\', GradientType=1)";', $options['gradient1'], $options['gradient2']);
						break;
					case "horizontal3":
						$out .= sprintf('background-image: -webkit-linear-gradient(left, %s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= sprintf('background-image: -o-linear-gradient(left, %s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= sprintf('background-image: linear-gradient(to right, %s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= 'background-repeat: no-repeat;';
						$out .= sprintf('filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=\'%s\', endColorstr=\'%s\', GradientType=1)";', $options['gradient1'], $options['gradient3']);
						break;
					case "vertical2":
						$out .= sprintf('background-image: -webkit-linear-gradient(top, %s %s%%, %s %s%%);', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= sprintf('background-image: -o-linear-gradient(top, %s %s%%, %s %s%%);', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= sprintf('background-image: linear-gradient(to bottom, %s %s%%, %s %s%%);', $options['gradient1'], $options['colour_stop1'], $options['gradient2'], $options['colour_stop2']);
						$out .= 'background-repeat: repeat-x;';
						$out .= sprintf('filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=\'%s\', endColorstr=\'%s\', GradientType=0)";', $options['gradient1'], $options['gradient2']);
						break;
					case "vertical3":
						$out .= sprintf('background-image: -webkit-linear-gradient(%s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= sprintf('background-image: -o-linear-gradient(%s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= sprintf('background-image: linear-gradient(%s, %s %s%%, %s);', $options['gradient1'], $options['gradient2'], $options['colour_stop2'], $options['gradient3']);
						$out .= 'background-repeat: no-repeat;';
						$out .= sprintf('filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=\'%s\', endColorstr=\'%s\', GradientType=0)";', $options['gradient1'], $options['gradient3']);
						break;
					case "directional":
						$out .= 'background-repeat: repeat-x;';
						$out .= sprintf('background-image: -webkit-linear-gradient(%sdeg, %s, %s);', $options['gradient_direction'], $options['gradient1'], $options['gradient2']);
						$out .= sprintf('background-image: -o-linear-gradient(%sdeg, %s, %s);', $options['gradient_direction'], $options['gradient1'], $options['gradient2']);
						$out .= sprintf('background-image: linear-gradient(%sdeg, %s, %s);', $options['gradient_direction'], $options['gradient1'], $options['gradient2']);
						break;
					case "radial":
						$out .= sprintf('background-image: -webkit-radial-gradient(circle, %s, %s);', $options['gradient1'], $options['gradient2']);
						$out .= sprintf('background-image: radial-gradient(circle, %s, %s);', $options['gradient1'], $options['gradient2']);
						$out .= 'background-repeat: no-repeat;';
						break;
				}
			} elseif ( $options["additional"] == "single" ) {
				/* check for single image without stretching */
				if ( ! empty($options["single_image"]) && $options['background_repeat'] != 'stretch' ) {
					if ( $imagesrc = wp_get_attachment_image_src($options["single_image"]) ) {
						$out .= sprintf('background-image:url(%s);', $imagesrc[0]);
						$out .= sprintf('background-repeat:%s;', $options['background_repeat']);
						$out .= sprintf('background-position:%s;', $options["background_position"]);
					}
				}
			}
			if ( ! empty($out) ) {
				printf('<style type="text/css">html {%s}</style>', $out);
			}
		}

		/**
		 * add backstretch initilisation to the page footer
		 * Used by hook: 'wp_footer'
		 */
		public static function wp_footer()
		{
			$options = self::get_background_options();
			$images = array();
			if ( $options["additional"] == "multiple" ) {
				if ( ! empty($options["multiple_image"]) ) {
					$slide_ids = explode(',', $options["multiple_image"]);
					$images = array();
					foreach ($slide_ids as $slide_id) {
						if ($imagesrc = wp_get_attachment_image_src($slide_id)) {
							$images[] = $imagesrc[0];
						}
					}
				}
			} elseif ( $options["additional"] == "single") {
				if ( ! empty($options["single_image"]) && $options['background_repeat'] == 'stretch' ) {
					if ($imagesrc = wp_get_attachment_image_src($options["single_image"])) {
						$images[] = $imagesrc[0];
					}
				}
			}
			if ( count($images) ) {
				print('<script type="text/javascript">(function($){');
				if ( count($images) == 1) {
					printf('$.backstretch(["%s"]);', $images[0]);
				} else {
					printf('$.backstretch(["%s"],{fade:%d,transition:%d});', implode('","', $images), $options["slide_transition"], $options["slide_pause"]);
				}
				print('})(jQuery);</script>');
			}
		}
	}
	p2_custom_background::register();
}