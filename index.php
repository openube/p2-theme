<?php 
get_template_part('templates/page', 'header'); 

$format = have_posts() ? get_post_format() : false;
get_template_part('templates/content', $format);
?>