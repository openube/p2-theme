<footer class="entry-footer">
<?php
if ( 'post' == get_post_type() ) {
	the_tags('<ul class="entry-tags"><li>','</li><li>','</li></ul>');
}
if ( is_single() ) {
	wp_link_pages(array('before' => '<nav><ul class="pagination">', 'after' => '</ul></nav>'));
}
?>
</footer>