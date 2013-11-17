#!/bin/sh
# script and program locations
THEMEDIR=$(dirname $0)
YUI="/usr/bin/yui-compressor --type js --charset UTF-8"
LESS="/usr/bin/lessc"
# CSS compilation
$LESS --yui-compress $THEMEDIR/css/less/style.less > $THEMEDIR/css/style.min.css
$LESS $THEMEDIR/css/less/style.less > $THEMEDIR/css/style.css
$LESS $THEMEDIR/css/less/editor-style.less > $THEMEDIR/css/editor-style.css
# JS compilation
cat $THEMEDIR/js/src/affix.js $THEMEDIR/js/src/alert.js $THEMEDIR/js/src/button.js $THEMEDIR/js/src/carousel.js $THEMEDIR/js/src/collapse.js $THEMEDIR/js/src/dropdown.js $THEMEDIR/js/src/modal.js $THEMEDIR/js/src/popover.js $THEMEDIR/js/src/scrollspy.js $THEMEDIR/js/src/tab.js $THEMEDIR/js/src/tooltip.js $THEMEDIR/js/src/transition.js $THEMEDIR/js/src/scripts.js > $THEMEDIR/js/scripts.js
$YUI $THEMEDIR/js/scripts.js -o $THEMEDIR/js/scripts.min.js