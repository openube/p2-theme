/**
 * p2 theme admin scripts for wordpress theme customiser
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @package WordPress
 */
( function( $ ) {

	// Update site title
	wp.customize( 'blogname', function( value ) {
		value.bind( function( newval ) {
			$( '#site-header a' ).text( newval );
		} );
	} );
	
	// Update title color
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( newval ) {
			$('#site-header a').css('color', newval );
		} );
	} );

	// Update background color
	wp.customize( 'background_color', function( value ) {
		value.bind( function( newval ) {
			$('body').css('background-color', newval );
		} );
	} );
	
	// containers for settings and values (retrieved over JSON)
	var cs = [],
		cv = [];

	// get all customizer settings and values, and activate the customizer
	$.getJSON(
		ajaxurl,
		{action: 'customiser_options'},
		function(data, textStatus, xhr) {
			cs = data.settings;
			cv = data.values;
			for (var i = 0; i < cs.length; i++) {
				if (cs[i].customiser) {
					if (cs[i].settings.length) {
						for (var j = 0; j < cs[i].settings.length; j++) {
							if (cv[cs[i].settings[j].name]) {
								cs[i].settings[j].value = cv[cs[i].settings[j].name];
							}
							wp.customize( 'p2_options['+cs[i].settings[j].name+']', function( value ) {
								var setting = cs[i].settings[j];
								value.bind( function( newval ) {
									changeSetting(setting, newval);
								} );
							} );
						}
					}
				}
			}
		}
	);

	// this function is called when the customizer interface updates a value
	function changeSetting(setting, newval)
	{
		for (var i = 0; i < cs.length; i++) {
			if (cs[i].settings.length) {
				for (var j = 0; j < cs[i].settings.length; j++) {
					if (setting.name == cs[i].settings[j].name) {
						cs[i].settings[j].value = newval;
					}
				}
			}
		}
		updateSettings();
	}

	// this function updates all elements in the customizer with values
	// the order this is carried out in is important (least specific rules first)
	function updateSettings()
	{
		for (var i = 0; i < cs.length; i++) {
			if (cs[i].settings.length) {
				for (var j = 0; j < cs[i].settings.length; j++) {
					setting = cs[i].settings[j];
					if (setting.selector) {
						/* selector is defined on this setting */
						if (typeof setting.selector === "string") {
							/* single selector */
							var prop = setting.property? setting.property: 'color';
							var val = setting.value_fmt? setting.value_fmt.replace('%s', setting.value): setting.value;
							$(setting.selector).css(prop, val);
						} else {
							/* multiple selector */
							for(var k = 0; k < setting.selector.length; k++) {
								var prop = setting.property && setting.property[k]? setting.property[k]: 'color';
								var val = setting.value_fmt && setting.value_fmt[k]? setting.value_fmt[k].replace('%s', setting.value): setting.value;
								$(setting.selector[k]).css(prop, val);
							}
						}
					} else {
						/* no selector - special cases */
						switch(setting.name) {
							case 'top_navbar_vertical':
								if (setting.value == 'navbar-fixed-top') {
									$('top_navigation').removeClass('navbar-fixed-bottom').addClass('navbar-fixed-top');
								} else {
									$('top_navigation').removeClass('navbar-fixed-top').addClass('navbar-fixed-bottom');
								}
								break;
							case 'top_navbar_colour':
								if (setting.value == 'navbar-default') {
									$('top_navigation').removeClass('navbar-inverse').addClass('navbar-default');
								} else {
									$('top_navigation').removeClass('navbar-default').addClass('navbar-inverse');
								}
								break;
							case 'header_navbar_colour':
								if (setting.value == 'navbar-default') {
									$('header_navigation').removeClass('navbar-inverse').addClass('navbar-default');
								} else {
									$('header_navigation').removeClass('navbar-default').addClass('navbar-inverse');
								}
								break;
						}
					}
				}
			}
		}
	}
} )( jQuery );