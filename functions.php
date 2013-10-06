<?php
/**
 * Theme functions
 * These are split into separate files and included here.
 * Child themes can override these files by including their own versions
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */
require_once locate_template('/lib/options.php');		/* Configuration options */
require_once locate_template('/lib/cleanup.php');		/* cleanup functions */
require_once locate_template('/lib/setup.php');			/* Theme setup */
require_once locate_template('/lib/navigation.php');	/* Navigation definitions and mods */
require_once locate_template('/lib/comments.php');		/* Custom comments mods */
require_once locate_template('/lib/scripts.php');		/* Scripts and stylesheets */
