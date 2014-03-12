<?php 
/* get theme options */
$theme_options = p2_theme_options::get_theme_options();
/* fixed navbar */
if (has_nav_menu('top_navigation')) : 
?>
<!-- Fixed navbar -->
<div class="navbar <?php echo $theme_options['top_navbar_colour'] . ' ' . $theme_options['top_navbar_vertical']; ?>" id="top_navigation" role="navigation">
  <div class="container">
    <div class="header row">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <nav class="navbar-collapse collapse">
        <?php
        wp_nav_menu(array('theme_location' => 'top_navigation', 'menu_class' => 'nav navbar-nav'));
        ?>
      </nav>
    </div>
  </div>
</div>
<?php endif; ?>
<header class="banner" id="masthead" role="banner">
  <div class="container">

    <div id="site-header">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo('name'); ?></a>
    </div>
    
    <?php if (has_nav_menu('header_navigation')) : ?>
    <div class="header-row">
    	<nav class="navbar <?php echo $theme_options['header_navbar_colour']; ?>" id="header_navigation" role="navigation">
        	<?php wp_nav_menu(array('theme_location' => 'header_navigation', 'menu_class' => 'nav navbar-nav')); ?>
    	</nav>
    </div>
    <?php endif; ?> 

  </div>
</header>
<div class="content">
	<div class="container">
