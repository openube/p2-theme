<?php get_template_part('templates/page', 'header'); ?>

<div class="alert">
  <?php _e('Sorry, but the page you were trying to view does not exist.', 'p2_theme'); ?>
</div>

<p><?php _e('It looks like this was the result of either:', 'p2_theme'); ?></p>
<ul>
  <li><?php _e('a mistyped address', 'p2_theme'); ?></li>
  <li><?php _e('an out-of-date link', 'p2_theme'); ?></li>
</ul>

<?php get_search_form(); ?>
