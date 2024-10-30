jQuery(document).ready( function() {
    jQuery('#neo_remove_img').hide();
    var mediaUploader;
    jQuery('#pb-file').click( function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            }, multiple: false });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            jQuery('#background_image').val(attachment.url);
            jQuery('#neo_remove_img').show();
            var src= attachment.url;
            var htmlstring = '<img src="' + src +  '">';
            jQuery('.neo_image .neo_chosen_image').append(htmlstring );
            jQuery('#pb-file').prop( 'disabled', true );
        });
        mediaUploader.open();

    });
    jQuery('#neo_remove_img').click( function() {
        jQuery('#background_image').val('');
        jQuery('.neo_chosen_image').remove();
        jQuery('#pb-file').removeAttr( 'disabled' );
        jQuery('#neo_remove_img').hide();
    })
});
jQuery(document).ready( function(){
    var mediaUploader;
    jQuery('.neo_previous_img').show();

    jQuery('#neo_choose_file').click( function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            }, multiple: false });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            jQuery('#background_image').val(attachment.url);
            jQuery('.neo_previous_img').hide();
            jQuery('#neo_edit_remove_img').show();
            var src= attachment.url;
            var htmlstring = '<img src="' + src +  '" class="neo_updated_img">';
            jQuery('.neo_image .neo_show_image').append(htmlstring );
        });
        mediaUploader.open();
    });
    jQuery('#neo_edit_remove_img').click( function() {
        jQuery('#background_image').val('');
        jQuery('.neo_previous_img').remove();
        jQuery('.neo_updated_img').remove();
        jQuery('#neo_edit_remove_img').hide();
    })
});

