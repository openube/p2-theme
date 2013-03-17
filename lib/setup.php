<?php
/**
 * initial setup and theme options
 */
class p2_theme_setup
{
	function register() {

		/* Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus) */
		register_nav_menus(array(
			'primary_navigation' => __('Primary Navigation', 'roots'),
		));

		// Add post thumbnails (http://codex.wordpress.org/Post_Thumbnails)
		add_theme_support('post-thumbnails');
		// set_post_thumbnail_size(150, 150, false);
		// add_image_size('category-thumb', 300, 9999); // 300px wide (and unlimited height)

		// Tell the TinyMCE editor to use a custom stylesheet
		add_editor_style('/css/editor-style.css');

		add_theme_support('bootstrap-gallery');     // Enable Bootstrap's thumbnails component on [gallery]
		add_theme_support('nice-search');           // Enable /?s= to /search/ redirect

		add_theme_support( 'custom-background', array(
			'default-color' => 'e6e6e6',
		) );
		/* Implements a custom header */
		$args = array(
			// Text color and image (empty to use none).
			'default-text-color'     => '220e10',
			'default-image'          => '',

			// Set height and width, with a maximum value for the width.
			'height'                 => 230,
			'width'                  => 1600,

			// Callbacks for styling the header and the admin preview.
			'wp-head-callback'       => array('p2_theme_setup', 'header_style'),
			'admin-head-callback'    => array('p2_theme_setup', 'admin_header_style'),
			'admin-preview-callback' => array('p2_theme_setup', 'admin_header_image'),
		);
		add_theme_support( 'custom-header', $args );


	}
	
}
add_action( 'after_setup_theme', array('p2_theme_setup', 'register') );

/**
 * Styles the header text displayed on the blog.
 */
public static function header_style()
{
	$header_image = get_header_image();
	$text_color   = get_header_textcolor();
	/* If no custom options for text are set, let's bail. */
	if ( empty( $header_image ) && $text_color == get_theme_support( 'custom-header', 'default-text-color' ) ) {
		return;
	}
	/* If we get this far, we have custom styles */
	print('<style type="text/css">');
	if ( ! empty( $header_image ) ) {
		print('.site-header {background: url("%s") no-repeat scroll top;}', esc_url($header_image));
	}
	if ( ! display_header_text() ) {
		print('.site-title{display:none}');
	}
	print('</style>');
}

/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 */
public static function admin_header_style()
{
	$header_image = get_header_image();
    printf('<style type="text/css">.appearance_page_custom-header #headimg{border:one;-webkit-box-sizing: border-box;-moz-box-sizing:border-box;box-sizing:border-box;');
    if ( ! empty( $header_image ) ) {
		printf('background: url("%s") no-repeat scroll top;', esc_url($header_image) );
	}
	print('}');
	if ( ! display_header_text() ) {
	    print('#headimg h1,#headimg h2{display:none;}')
	}
	print('#headimg h1{font: bold 60px/1 serif;margin: 0;padding: 58px 0 10px;}');
	print('#headimg h1 a {text-decoration: none}';
	print('#headimg h1 a:hover {text-decoration: underline;}');
	print('.default-header img {max-width: 230px;width: auto;}</style>');
}

/**
 * Outputs markup to be displayed on the Appearance > Header admin panel.
 * This callback overrides the default markup displayed there.
 */
public static function _admin_header_image()
{
	printf('<div id="headimg" style="background: url('') no-repeat scroll top; background-size: 1600px auto;">', esc_url( header_image() )
		<?php $style = ' style="color:#' . get_header_textcolor() . ';"'; ?>
		<div class="hgroup">
			<h1><a id="name"<?php echo $style; ?> onclick="return false;" href="#"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 id="desc"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></h2>
		</div>
	</div>
<?php }
