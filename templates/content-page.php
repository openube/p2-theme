<?php while (have_posts()) : the_post(); ?>
	<article>
		<header class="page-header">
			<h2><?php the_title(); ?></h2>
		</header>

  		<?php the_content(); ?>
  	
  	<?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
  </article>
<?php endwhile; ?>