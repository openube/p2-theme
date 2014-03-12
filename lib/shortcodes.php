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
		add_shortcode( 'gallery', array(__CLASS__, 'gallery') );
		/* filter caption shortcode*/
		add_filter( 'img_caption_shortcode', array(__CLASS__, 'caption'), 10, 3);
		/* filter content for tabs */
        add_filter('the_content', array(__CLASS__, 'filter_content'));


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

		$output = sprintf('<div id="carousel-%d" class="carousel slide" data-ride="carousel">', $instance);
		$output .= '<ol class="carousel-indicators">';
		$counter = 0;
		$items = '';
		foreach ($attachments as $id => $attachment) {
			$active = ($counter === 0)? 'active': '';
			$output .= sprintf('<li data-target="#carousel-%d" data-slide-to="%d" class="%s"></li>', $instance, $counter, $active);
			$src = wp_get_attachment_image_src($id, $size, false, false);
        	$items .= sprintf('<div class="item %s"><img data-src="%s" alt="%s"></div>', $active, $src[0], esc_attr($attachment->post_title));
		}
		$output .= sprintf('</ol><div class="carousel-inner">%s</div>', $items);
		$output .= sprintf('<a class="left carousel-control" href="#carousel-%d" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>', $instance);
		$output .= sprintf('<a class="right carousel-control" href="#carousel-%d" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>', $instance);
		$output .= '</div>';
		return $output;
	}

	/****************************************
	 * Tabs and Accordions                  *
	 ****************************************/

	/* keep track of used strings for IDs */
	static private $used_names = array();

	/* content filter */
	public static function filter_content($content) {
        // Run the loop, starting at level 1
        $content = self::loop($content, 1, 'acc');
        $content = self::loop($content, 1, 'tab');

        return $content;
    }

    // $c is content, $l is the current tab level
    private static function loop($c, $l, $t) 
    {
        // The tab pattern - ain't it pretty
        $pattern = '/^.*\['.$t.':{'.$l.'}([^:][^\]]*)\].*$/im';

        // Find first occurernce of this level.
        // If none found return passed content / If found get first match
        if (!preg_match($pattern, $c, $first_matches, PREG_OFFSET_CAPTURE)) {
            return $c;
        } else {
            $first = $first_matches[0][1];
        }

        // Find current level tab end or show an error message
        if (preg_match('/^.*\['.$t.':{'.$l.'}END\].*$/im', $c, $last_matches, PREG_OFFSET_CAPTURE)) {
            $last = $last_matches[0][1];
        } else {
            die('<div class="smut-error"><p>No valid end '.$t.': ['.$t.str_repeat(':', $l).'END]</p><p>Started at "'.$first_matches[1][0].'" '.$t.'</p></div>');
        }

        // Pre becomes anything before first tab
        $pre = substr($c, 0, $first);
        // Post becomes anything after end of tabs
        $post = substr($c, $last+strlen($last_matches[0][0]));

        // $inner becomes anything in between $pre and $post
        $string = substr($c, $first, $last-$first);

        // Get all matches for tabs: goes into $matches, if none found return content to avoid unnecessary work
        if (!preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE)) {
            return $c;
        }

        if ($t === 'tab') {
            // Setup link list
            $link_list = '<ul class="nav nav-tabs">';
        }

        // Inner keeps track of content
        $inner = '';

        $n = count($matches[0]);

        for ($i = 0; $i < $n; $i++) {

            // Title from second set of preg_matches
            $title = $matches[1][$i][0];

            // Get unique nav id
            $nav_id = self::make_nav_id($title);

            // Start of div
            $extras = ($i == 0)? ' in active': '';
            $inner .= "\n".'<div id="'.$nav_id.'" class="tab-pane fade ' . $extras . ' level-' . $l . '">';

            if ($t === 'tab') {
                // Add to list of links to precede the div
                $link_list .= '<li><a href="#'.$nav_id.'">'.$title.'</a></li>';
            }

            // Get substring to itterate over
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = isset($matches[0][$i+1][1]) ? $matches[0][$i+1][1] : strlen($string)-1;
            $stop = $end - $start;

            // Substring to check for more tabs
            $inner .= self::loop(substr($string, $start, $stop), $l+1, $t);

            // End of div
            $inner .= "\n".'</div>';
        }

        // Add all content before, div start before
        $pre .= "\n\n".'<div class="smut-'.$t.'s level-'.$l.'">';

        if ($t === 'tab') {
            // End link list
            $link_list .= '</ul>';
            // Add link list to pre
            $pre .= $link_list;
        }

        // Add div end and loop through following content for other tabs
        $post = "\n\n".'</div>' . self::loop($post, $l, $t);

        // Combine the pre-tab content, tab content, and post-tab content
        $c = $pre . $inner . $post;

        // Return the tab content
        return $c;
    }

    // Create unique alphanumeric id tags
    static private function make_nav_id($t) {

        // Remove all non alpha-numeric characters (and any end-hyphens)
        $t = htmlspecialchars_decode($t);
        $t = str_replace('&', 'and', $t);
        $t = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($t));
        $t = preg_replace('/(^-)|(-$)/', '', $t);
        $title = $t;

        $n = 1;

        // While id is already in use (generally won't run)
        while (in_array($title, self::$used_names)) {
            // Add a number to end of id until becomes unique
            $title = $t.'-'.$n;
            $n++;
        }

        // Add id to list of used 'id's
        self::$used_names[] = $title;

        return $title;
    }

}
p2_shortcodes::register();
endif;