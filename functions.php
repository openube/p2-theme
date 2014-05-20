<?php
/**
 * Theme functions
 * These are split into separate files and included here.
 * Child themes can override these files by including their own versions
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @version 1.0
 */

locate_template('lib/options.php', true, true);		/* Configuration options */
locate_template('lib/cleanup.php', true, true);		/* cleanup functions */
locate_template('lib/setup.php', true, true);		/* Theme setup */
locate_template('lib/navigation.php', true, true);	/* Navigation definitions and mods */
locate_template('lib/comments.php', true, true);	/* Custom comments mods */
locate_template('lib/scripts.php', true, true);		/* Scripts and stylesheets */
locate_template('lib/shortcodes.php', true, true);  /* Shortcodes */
locate_template('lib/background.php', true, true);  /* Custom Background */
