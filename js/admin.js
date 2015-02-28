/**
 * p2 theme admin scripts
 * @author Peter Edwards <Peter.Edwards@p-2.biz>
 * @package WordPress
 */
;(function($){

	/* configuration for image inputs */
 	var thumb_size = 'thumbnail',

	/* shortcut to set an input value */
	setInputValue = function(inputID, value)
	{
		$('#'+inputID).val(value);
	},

	/* shows/hides elements on the background form */
	checkBackgroundForm = function()
	{
		var ag = $('#p2_background_options_additional_gradient:checked').length,
			as = $('#p2_background_options_additional_single:checked').length,
			am = $('#p2_background_options_additional_multiple:checked').length,
			g1 = $('#p2_background_options_gradient1'),
			g2 = $('#p2_background_options_gradient2'),
			g3 = $('#p2_background_options_gradient3'),
			cs1 = $('#p2_background_options_colour_stop1'),
			cs2 = $('#p2_background_options_colour_stop2'),
			gt = $('#p2_background_options_gradient_type'),
			gd = $('#p2_background_options_gradient_direction'),
			si = $('#p2_background_options_single_image'),
			mi = $('#p2_background_options_multiple_image'),
			br = $('#p2_background_options_background_repeat'),
			bp = $('#p2_background_options_background_position'),
			st = $('#p2_background_options_slide_transition'),
			sp = $('#p2_background_options_slide_pause'),
			to_show = [],
			to_hide = [],
			type = null;
		if (ag) {
			/* hide the image fields */
			to_hide.push(si,mi,br,bp,st,sp);
			/* show relevant gradient controls for type */
			to_show.push(g1,g2,gt);
			type = gt.val();
			switch (type) {
				case 'horizontal3':
				case 'vertical3':
					to_show.push(g3,cs2);
					to_hide.push(gd,cs1);
					break;
				case 'horizontal2':
				case 'vertical2':
					to_show.push(cs1,cs2);
					to_hide.push(g3,gd);
					break;
				case 'radial':
					to_hide.push(cs1,cs2,gd,g3);
					break;
				case 'directional':
					to_show.push(gd);
					to_hide.push(cs1,cs2,g3);
					break;
			}
		} else if (as) {
			/* hide the gradient fields */
			to_hide.push(g1,g2,g3,cs1,cs2,gt,gd,mi,st,sp);
			/* show single image field */
			to_show.push(si,br);
			/* if repeat is set to stretch or repeat, don't show position */
			if (br.val() == "stretch" || br.val() == "repeat") {
				to_hide.push(bp);
			} else {
				to_show.push(bp);
			}
		} else if (am) {
			/* hide the gradient fields */
			to_hide.push(g1,g2,g3,cs1,cs2,gt,gd,si,bp,br);
			to_show.push(mi,st,sp);

		} else {
			to_hide.push(g1,g2,g3,cs1,cs2,gt,gd,si,mi,br,bp,st,sp);
		}
		/* hide/show fields */
		$.each(to_hide, function(){
			this.parents('tr').hide();
		});
		$.each(to_show, function(){
			this.parents('tr').show();
		});
	};
	/* set of checkboxes where only one can be selected */
	$('.chooseOne').on('click', function(){
		var val = $(this).val();
		$('.chooseOne').each(function(){
			if ($(this).attr("value") != val) {
				$(this).attr('checked', false);
			}
		});
	});
	/* checkboxes/radios which alter the form layout */
	$('.checkOnClick').on('click', function(){
		checkBackgroundForm();
	});
	/* select lists/inputs which alter the form layout */
	$('.checkOnChange').on('change', function(){
		checkBackgroundForm();
	});
	/* set up form on page load */
	checkBackgroundForm();

	/* activate colour pickers */
	if ($('.color-picker-hex').length) {
		$('.color-picker-hex').wpColorPicker();
	}

 	/* make image preview sortable */
 	$('.media-selection-preview').sortable({
		update:function(){
			setInputValue( $(this).data('inputid'), $(this).sortable("toArray", {attribute:"data-imageid"}) );
		},
		helper: 'clone',
		items: 'div.image-container'
	});

	/* removes an image from a selection */
	$('.media-selection-preview').on('click', 'a.remove-image', function(e){
		e.preventDefault();
		var inputID = $(this).parent().data('inputid');
		var imageID = $(this).parent().data('imageid');
		var currentImages = $('#'+inputID).val().split(','),
			newSlides = [];
		for (i = 0; i < currentImages.length; i++) {
			if (currentImages[i] != imageID) {
				newSlides.push(currentImages[i]);
			}
		}
		setInputValue( inputID, newSlides.join(',') );
		$(this).parents('.image-container').remove();
	});

	/**
	 * activate media uploader to select multiple images for a slideshow
	 */
	$(document).on('click', '.mediaBrowserButtonImages', function(e) {
		e.preventDefault();
		/* if there is a frame created, use it */
		if ( frame ) {
			frame.open();
			return;
		}
		/* get the hidden input ID from the button's inputid data attribute */
		var inputID = $(this).data('inputid'),
		/* open the wp.media frame with our localised title */
		frame = wp.media.frames.file_frame = wp.media({
			title : p2theme_msg.select_multiple,
			multiple : true,
			button : { text : p2theme_msg.select_multiple },
		});
		/* set the handler for the close event which gets the selection and saves the IDs to the hidden field */
 		frame.on('close',function() {
 			/* get the selection object */
			var selection = frame.state().get('selection'),
			/* array variable to hold new image IDs */
			imageIDs = [],
			/* variable to hold new HTML for the preview */
			newImages = '';
			/* maps a function to each selected image which constructs the preview and saves the ID */
			selection.map( function( attachment ) {
				var image = attachment.toJSON(),
				imageURL = (image.sizes && image.sizes[thumb_size])? image.sizes[thumb_size].url: image.url;
				if (image.id) {
					newImages += '<div class="image-container" data-imageid="'+image.id+'"><div data-inputid="'+inputID+'" data-imageid="'+image.id+'" class="image-inner"><img src="'+imageURL+'" /><a class="remove-image" href="#" title="'+p2theme_msg.deleteimage+'">&#61826;</a></div></div>';
					imageIDs.push(image.id);
				}
			});
			if (imageIDs.length) {
				/* populate hidden input and preview */
				setInputValue( inputID, imageIDs.join(',') );
				$('#'+inputID+'-preview').html(newImages).sortable("refresh");
			} else {
				/* reset hidden input and empty preview */
				setInputValue( inputID, '' );
				$('#'+inputID+'-preview').html('<p>'+p2theme_msg.empty_multiple+'</p>');
			}
		});
		/* opens the wp.media frame and selects the appropriate images */
		frame.on('open', function() {
			/* get the image IDs from the hidden input */
			var imgIDs = $('#'+inputID).val().split(',');
			/* get the selection object for the wp.media frame */
			var selection = frame.state().get('selection');
			if (imgIDs && imgIDs.length) {
				/* add each image to the selection */
				$.each(imgIDs, function(idx, val) {
					attachment = wp.media.attachment(val);
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				});
			}
		});
		frame.open();
	});

	/**
	 * open media browser to select a single image and insert its URL
	 */
	$(document).on('click', '.mediaBrowserButtonImage', function(e) {
		e.preventDefault();
		/* if there is a frame created, use it */
		if ( imgframe ) {
			imgframe.open();
			return;
		}
		/* get the hidden input ID from the button's inputid data attribute */
		var inputID = $(this).data('inputid'),
		/* open the wp.media frame with our localised title */
		imgframe = wp.media.frames.file_frame = wp.media({
			title : p2theme_msg.select_single,
			multiple : false,
			button : { text : p2theme_msg.select_single },
		});
		/* use the select event to update the page in real time */
 		imgframe.on('select',function() {
			/* get selection and save to hidden input field */
			var image = imgframe.state().get('selection').first().toJSON(),
			imageID = image.id,
			imageURL = (image.sizes && image.sizes[thumb_size])? image.sizes[thumb_size].url: image.url,
			newImage = '<div class="image-container" data-imageid="'+image.id+'"><div data-inputid="'+inputID+'" data-imageid="'+image.id+'" class="image-inner"><img src="'+imageURL+'" /><a class="remove-image" href="#" title="'+p2theme_msg.deleteimage+'">&#61826;</a></div></div>';
			setInputValue( inputID, imageID );
			$('#'+inputID+'-preview').html(newImage);
		});
		imgframe.on('open', function() {
			/* pre-select image */
			var imgID = $('#'+inputID).val();
			if (imgID !== '' && parseInt(imgID) > 0) {
				var selection = imgframe.state().get('selection');
				attachment = wp.media.attachment(imgID);
				attachment.fetch();
				selection.add( attachment ? [ attachment ] : [] );
			}
		});
		imgframe.open();
	});

}(jQuery));




