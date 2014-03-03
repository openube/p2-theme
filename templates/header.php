<?php if (has_nav_menu('top_navigation')) : ?>
<!-- Fixed navbar -->
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
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
    <nav class="navbar navbar-default" role="navigation">
      <?php wp_nav_menu(array('theme_location' => 'header_navigation', 'menu_class' => 'nav navbar-nav')); ?>
    </nav>
    <?php endif; ?> 

  </div>
</header>
<div class="content">
	<div class="container">
