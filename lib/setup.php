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


	}
	

	/* Backwards compatibility for older than PHP 5.3.0
	if (!defined('__DIR__')) { define('__DIR__', dirname(__FILE__)); }

	// Define helper constants
	$get_theme_name = explode('/themes/', get_template_directory());

	define('WP_BASE',                   wp_base_dir());
	define('THEME_NAME',                next($get_theme_name));
	define('RELATIVE_PLUGIN_PATH',      str_replace(site_url() . '/', '', plugins_url()));
	define('FULL_RELATIVE_PLUGIN_PATH', WP_BASE . '/' . RELATIVE_PLUGIN_PATH);
	define('RELATIVE_CONTENT_PATH',     str_replace(site_url() . '/', '', content_url()));
	define('THEME_PATH',                RELATIVE_CONTENT_PATH . '/themes/' . THEME_NAME);*/
}
add_action( 'after_setup_theme', array('p2_theme_setup', 'register') );

<?php
/**
 * Implements a custom header
 * @package WordPress
 */

/**
 * Sets up the WordPress core custom header arguments and settings.
 *
 * @uses add_theme_support() to register support for 3.4 and up.
 * @uses p2_header_style() to style front-end.
 * @uses p2_admin_header_style() to style wp-admin form.
 * @uses p2_admin_header_image() to add custom markup to wp-admin form.
 */
function p2_custom_header_setup() {
	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'     => '220e10',
		'default-image'          => '',

		// Set height and width, with a maximum value for the width.
		'height'                 => 230,
		'width'                  => 1600,

		// Callbacks for styling the header and the admin preview.
		'wp-head-callback'       => 'p2_header_style',
		'admin-head-callback'    => 'p2_admin_header_style',
		'admin-preview-callback' => 'p2_admin_header_image',
	);

	add_theme_support( 'custom-header', $args );

}
add_action( 'after_setup_theme', 'p2_custom_header_setup' );

/**
 * Styles the header text displayed on the blog.
 *
 * get_header_textcolor() options: Hide text (returns 'blank'), or any hex value.
 *
 * @since Twenty Thirteen 1.0
 */
function p2_header_style() {
	$header_image = get_header_image();
	$text_color   = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	if ( empty( $header_image ) && $text_color == get_theme_support( 'custom-header', 'default-text-color' ) )
		return;

	// If we get this far, we have custom styles.
	?>
	<style type="text/css">
	<?php
		if ( ! empty( $header_image ) ) :
	?>
		.site-header {
			background: url("<?php header_image(); ?>") no-repeat scroll top;
			background-size: 1600px auto;
		}
	<?php
		endif;

		// Has the text been hidden?
		if ( ! display_header_text() ) :
	?>
		.site-title,
		.site-description {
			position: absolute !important;
			clip: rect(1px 1px 1px 1px); /* IE7 */
			clip: rect(1px, 1px, 1px, 1px);
		}
	<?php
			if ( empty( $header_image ) ) :
	?>
		.site-header hgroup {
			min-height: 0;
		}
	<?php
			endif;

		// If the user has set a custom color for the text, use that.
		elseif ( $text_color != get_theme_support( 'custom-header', 'default-text-color' ) ) :
	?>
		.site-title,
		.site-description {
			color: #<?php echo esc_attr( $text_color ); ?>;
		}
	<?php endif; ?>
	</style>
	<?php
}

/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * @since Twenty Thirteen 1.0
 */
function p2_admin_header_style() {
	$header_image = get_header_image();
?>
	<style type="text/css">
	.appearance_page_custom-header #headimg {
		border: none;
		-webkit-box-sizing: border-box;
		-moz-box-sizing:    border-box;
		box-sizing:         border-box;
		<?php
		if ( ! empty( $header_image ) ) {
			echo 'background: url("' . esc_url( $header_image ) . '") no-repeat scroll top; background-size: 1600px auto;';
		} ?>
		padding: 0 20px;
	}
	#headimg .hgroup {
		-webkit-box-sizing: border-box;
		-moz-box-sizing:    border-box;
		box-sizing:         border-box;
		margin: 0 auto;
		max-width: 1040px;
		<?php
		if ( ! empty( $header_image ) || display_header_text() ) {
			echo 'min-height: 230px;';
		} ?>
		width: 100%;
	}
	<?php if ( ! display_header_text() ) : ?>
	#headimg h1,
	#headimg h2 {
		position: absolute !important;
		clip: rect(1px 1px 1px 1px); /* IE7 */
		clip: rect(1px, 1px, 1px, 1px);
	}
	<?php endif; ?>
	#headimg h1 {
		font: bold 60px/1 'Bitter', Georgia, serif;
		margin: 0;
		padding: 58px 0 10px;
	}
	#headimg h1 a {
		text-decoration: none;
	}
	#headimg h1 a:hover {
		text-decoration: underline;
	}
	#headimg h2 {
		font: 200 italic 24px 'Source Sans Pro', Helvetica, sans-serif;
		margin: 0;
		text-shadow: none;
	}
	.default-header img {
		max-width: 230px;
		width: auto;
	}
	</style>
<?php
}

/**
 * Outputs markup to be displayed on the Appearance > Header admin panel.
 * This callback overrides the default markup displayed there.
 *
 * @since Twenty Thirteen 1.0
 */
function p2_admin_header_image() {
	?>
	<div id="headimg" style="background: url('<?php esc_url( header_image() ); ?>') no-repeat scroll top; background-size: 1600px auto;">
		<?php $style = ' style="color:#' . get_header_textcolor() . ';"'; ?>
		<div class="hgroup">
			<h1><a id="name"<?php echo $style; ?> onclick="return false;" href="#"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 id="desc"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></h2>
		</div>
	</div>
<?php }
