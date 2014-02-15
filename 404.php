<?php get_template_part('templates/page', 'header'); ?>

<div class="alert">
  <?php _e('Sorry, but the page you were trying to view does not exist.', 'p2'); ?>
</div>

<p><?php _e('It looks like this was the result of either:', 'p2'); ?></p>
<ul>
  <li><?php _e('a mistyped address', 'p2'); ?></li>
  <li><?php _e('an out-of-date link', 'p2'); ?></li>
</ul>

<?php get_search_form(); ?>
