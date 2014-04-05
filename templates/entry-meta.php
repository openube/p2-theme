<p class="entry-meta">
<?php
if ( 'post' == get_post_type() ) {
	printf('<span class="byline author vcard">%s <a href="%s" rel="author" class="fn">%s</a>, <time class="updated" datetime="%s" pubdate>%s</time></span>',
	__('Posted by', 'p2_theme'), 
	get_author_posts_url(get_the_author_meta('ID')),
	get_the_author(),
	get_the_time('c'),
	get_the_date());
}
if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
?>
 | <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'p2_theme' ), __( '1 Comment', 'p2_theme' ), __( '% Comments', 'p2_theme' ) ); ?></span>
<?php
}
edit_post_link( __( 'Edit', 'p2_theme' ), ' | <span class="edit-link">', '</span>' );
?>
</p>