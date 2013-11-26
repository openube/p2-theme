#!/bin/sh
# script and program locations
THEMEDIR=$(dirname $0)
BS=$THEMEDIR/bootstrap/
YUI="/usr/bin/yui-compressor"
LESS="/usr/bin/lessc"
# CSS compilation
$LESS $THEMEDIR/css/less/style.less > $THEMEDIR/css/style.css
$YUIC --type css $THEMEDIR/css/style.css -o $THEMEDIR/css/style.min.css
$LESS $THEMEDIR/css/less/editor-style.less > $THEMEDIR/css/editor-style.css
# JS compilation
cat $BS/js/affix.js $BS/js/alert.js $BS/js/button.js $BS/js/carousel.js $BS/js/collapse.js $BS/js/dropdown.js $BS/js/modal.js $BS/js/popover.js $BS/js/scrollspy.js $BS/js/tab.js $BS/js/tooltip.js $BS/js/transition.js $THEMEDIR/js/p2.js > $THEMEDIR/js/scripts.js
$YUI --type js --charset UTF-8 $THEMEDIR/js/scripts.js -o $THEMEDIR/js/scripts.min.js
