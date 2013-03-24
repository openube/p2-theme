<?php
/**
 * shortcode definitions and changes to the default Wordpress shortcodes
 */
if ( ! class_exists('p2_shortcodes') ) :
/**
 * class used to add various shortcodes and content filters
 * and modify those defined in wordpress
 */
class p2_shortcodes
{
	/* hook into Wordpress */
	public static function register()
	{

		/* remove gallery style injection */
		add_filter('use_default_gallery_style', '__return_null');
		/* replace default gallery shortcode */
		remove_shortcode('gallery');
		add_shortcode( 'gallery', array('p2_shortcodes', 'gallery') );
		/* filter caption shortcode*/
		add_filter( 'img_caption_shortcode', array('p2_shortcodes', 'caption'), 10, 3);

	}

	/**
	 * Use <figure> and <figcaption> in images with captions
	 * @link http://justintadlock.com/archives/2011/07/01/captions-in-wordpress
	 */
	public static function caption($output, $attr, $content)
	{
		if (is_feed()) {
			return $output;
		}
		$defaults = array(
			'id'      => '',
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => ''
		);
		$attr = shortcode_atts($defaults, $attr);
		/* If the width is less than 1 or there is no caption, return the content wrapped between the [caption] tags */
		if ($attr['width'] < 1 || empty($attr['caption'])) {
			return $content;
		}
		/* Set up the attributes for the caption <figure> */
		$attributes  = (!empty($attr['id']) ? ' id="' . esc_attr($attr['id']) . '"' : '' );
		$attributes .= ' class="thumbnail wp-caption ' . esc_attr($attr['align']) . '"';
		$attributes .= ' style="width: ' . esc_attr($attr['width']) . 'px"';
		/* build output */
		$output  = '<figure' . $attributes .'>';
		$output .= do_shortcode($content);
		$output .= '<figcaption class="caption wp-caption-text">' . $attr['caption'] . '</figcaption>';
		$output .= '</figure>';
		return $output;
	}

	/**
	 * Clean up gallery_shortcode()
	 * @link http://twitter.github.com/bootstrap/components.html#thumbnails
	 */
	public static function gallery($attr)
	{
		$post = get_post();
		static $instance = 0;
		$instance++;

		if (!empty($attr['ids'])) {
			if (empty($attr['orderby'])) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}
		$output = apply_filters('post_gallery', '', $attr);
		if ($output != '') {
			return $output;
		}
		if (isset($attr['orderby'])) {
			$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
			if (!$attr['orderby']) {
				unset($attr['orderby']);
			}
		}

		extract(shortcode_atts(array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post->ID,
			'itemtag'    => '',
			'icontag'    => '',
			'captiontag' => '',
			'columns'    => 3,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => ''
		), $attr));

		$id = intval($id);

		if ($order === 'RAND') {
			$orderby = 'none';
		}

		if (!empty($include)) {
			$_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

			$attachments = array();
			foreach ($_attachments as $key => $val) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif (!empty($exclude)) {
			$attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
		} else {
			$attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
		}

		if (empty($attachments)) {
			return '';
		}

		if (is_feed()) {
			$output = "\n";
			foreach ($attachments as $att_id => $attachment) {
				$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
			}
			return $output;
		}

		$output = '<ul class="thumbnails gallery">';
		$i = 0;
		foreach ($attachments as $id => $attachment) {
			$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
			$output .= '<li>' . $link;
			if (trim($attachment->post_excerpt)) {
				$output .= '<div class="caption hidden">' . wptexturize($attachment->post_excerpt) . '</div>';
			}
			$output .= '</li>';
		}
		$output .= '</ul>';
		return $output;
	}
}
p2_shortcodes::register();
endif;