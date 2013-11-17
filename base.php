<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <!--[if lt IE 7]><div class="alert">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</div><![endif]-->

  <?php
    do_action('get_header');
    get_template_part('templates/header');
  ?>

  <div class="container" role="document">
    <div class="content row">
      <div class="main <?php echo p2::column_classes(); ?>" role="main">
        <?php include p2::template_path(); ?>
      </div><!-- /.main -->
      <?php p2::display_sidebars(); ?>
    </div><!-- /.content -->
  </div><!-- /.wrap -->

<?php get_template_part('templates/footer'); ?>