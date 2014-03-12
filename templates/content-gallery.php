<?php
/**
 * template for displaying an archive of gallery posts
 */
if (!have_posts()) : ?>
  <div class="alert">
    <?php _e('Sorry, no results were found.', 'p2_theme'); ?>
  </div>
  <?php get_search_form(); ?>
<?php endif; ?>
<section class="galleries">
<?php while (have_posts()) : the_post(); ?>
  <figure <?php post_class(); ?>>
    <?php if (has_post_thumbnail()) : ?>
    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
    <?php endif; ?>
    <figcaption>
      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <?php get_template_part('templates/entry-meta'); ?>
      <div class="entry-summary">
        <?php the_excerpt(); ?>
      </div>
      <?php get_template_part('templates/entry-footer'); ?>
    </figcaption>
  </figure>
</section>
<?php endwhile; ?>

<?php if ($wp_query->max_num_pages > 1) : ?>
  <nav class="post-nav">
    <ul class="pagination">
      <?php if (get_next_posts_link()) : ?>
        <li class="previous"><?php next_posts_link(__('&larr; Older posts', 'p2_theme')); ?></li>
      <?php endif; ?>
      <?php if (get_previous_posts_link()) : ?>
        <li class="next"><?php previous_posts_link(__('Newer posts &rarr;', 'p2_theme')); ?></li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>