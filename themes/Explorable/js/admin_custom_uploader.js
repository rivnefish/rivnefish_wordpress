jQuery(document).ready(function() {
	var fileInput = '';

	jQuery('.upload_image_button').click(function() {
		fileInput = jQuery(this).prev('input');
		//console.log(fileInput);

		et_upload_field_name = 'Header Background Image';
		et_tb_interval = setInterval( function() {
			jQuery('#TB_iframeContent').contents().find('.savesend .button').val('Use for ' + et_upload_field_name);
		}, 2000 );

		formfield = jQuery('#et_single_header_bg').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	// user inserts file into post. only run custom if user started process using the above process
	// window.send_to_editor(html) is how wp would normally handle the received data

	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function(html){

		if (fileInput) {
			clearInterval( et_tb_interval );
			fileurl = jQuery('img',html).attr('src');

			fileInput.val(fileurl);

			tb_remove();

		} else {
			window.original_send_to_editor(html);
		}
	};

});