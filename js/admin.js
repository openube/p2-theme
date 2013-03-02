/**
 * p2 theme admin scripts
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @package WordPress
 */
;(function($){
	if ($('.color-picker-hex').length) {
		var default_palette = $('#p2_default_palette').val().split(",");
		$('.color-picker-hex').wpColorPicker({'palettes':default_palette,'change':colorpickerChange,'clear':colorpickerChange});
	}
	function colorpickerChange(){}
	if ($('.editor').length) {
		$('.editor').each(function(){

		});
	}
}(jQuery));




