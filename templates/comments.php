<?php
	if (post_password_required()) {
		return;
	}

 if (have_comments()) : ?>
	<section id="comments">
		<h3><?php printf(_n('One Response to &ldquo;%2$s&rdquo;', '%1$s Responses to &ldquo;%2$s&rdquo;', get_comments_number(), 'p2_theme'), number_format_i18n(get_comments_number()), get_the_title()); ?></h3>

		<ol class="media-list">
			<?php wp_list_comments(array('walker' => new p2_Walker_Comment)); ?>
		</ol>

		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
		<nav>
			<ul class="pagination">
				<?php if (get_previous_comments_link()) : ?>
					<li class="previous"><?php previous_comments_link(__('&larr; Older comments', 'p2_theme')); ?></li>
				<?php endif; ?>
				<?php if (get_next_comments_link()) : ?>
					<li class="next"><?php next_comments_link(__('Newer comments &rarr;', 'p2_theme')); ?></li>
				<?php endif; ?>
			</ul>
		</nav>
		<?php endif; ?>

		<?php if (!comments_open() && !is_page() && post_type_supports(get_post_type(), 'comments')) : ?>
		<div class="alert">
			<?php _e('Comments are closed.', 'p2_theme'); ?>
		</div>
		<?php endif; ?>
	</section><!-- /#comments -->
<?php endif; ?>

<?php if (!have_comments() && !comments_open() && !is_page() && post_type_supports(get_post_type(), 'comments')) : ?>
	<section id="comments">
		<div class="alert">
			<?php _e('Comments are closed.', 'p2_theme'); ?>
		</div>
	</section><!-- /#comments -->
<?php endif; ?>

<?php if (comments_open()) : ?>
	<section id="respond">
		<h3><?php comment_form_title(__('Leave a Reply', 'p2_theme'), __('Leave a Reply to %s', 'p2_theme')); ?></h3>
		<p class="cancel-comment-reply"><?php cancel_comment_reply_link(); ?></p>
		<?php if (get_option('comment_registration') && !is_user_logged_in()) : ?>
			<p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.', 'p2_theme'), wp_login_url(get_permalink())); ?></p>
		<?php else : ?>
			<form class="form-horizontal" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
				<?php if (is_user_logged_in()) : ?>
					<p>
						<?php printf(__('Logged in as <a href="%s/wp-admin/profile.php">%s</a>.', 'p2_theme'), get_option('siteurl'), $user_identity); ?>
						<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php __('Log out of this account', 'p2_theme'); ?>"><?php _e('Log out &raquo;', 'p2_theme'); ?></a>
					</p>
				<?php else : ?>
					<label for="author" class="control-label"><?php _e('Name', 'p2_theme'); if ($req) _e(' (required)', 'p2_theme'); ?></label>
					<input class="form-control" type="text" class="text" name="author" id="author" value="<?php echo esc_attr($comment_author); ?>" size="22" <?php if ($req) echo 'aria-required="true"'; ?>>
					<label for="email" class="control-label"><?php _e('Email (will not be published)', 'p2_theme'); if ($req) _e(' (required)', 'p2_theme'); ?></label>
					<input class="form-control" type="email" class="text" name="email" id="email" value="<?php echo esc_attr($comment_author_email); ?>" size="22" <?php if ($req) echo 'aria-required="true"'; ?>>
					<label for="url" class="control-label"><?php _e('Website', 'p2_theme'); ?></label>
					<input class="form-control" type="url" class="text" name="url" id="url" value="<?php echo esc_attr($comment_author_url); ?>" size="22">
				<?php endif; ?>
				<label for="comment" class="control-label"><?php _e('Comment', 'p2_theme'); ?></label>
				<textarea class="form-control" name="comment" id="comment" class="input-xlarge" rows="5" aria-required="true"></textarea>
				<p><input class="btn btn-primary" name="submit" type="submit" id="submit" value="<?php _e('Submit Comment', 'p2_theme'); ?>"></p>
				<?php comment_id_fields(); ?>
				<?php do_action('comment_form', $post->ID); ?>
			</form>
		<?php endif; ?>
	</section><!-- /#respond -->
<?php endif; ?>