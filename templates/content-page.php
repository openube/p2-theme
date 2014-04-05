<?php while (have_posts()) : the_post(); ?>
<article>
	<header class="page-header">
		<h2><?php the_title(); ?></h2>
	</header>

	<?php the_content(); ?>
  	
    <?php get_template_part('templates/entry-footer'); ?>
</article>
<?php endwhile; ?>