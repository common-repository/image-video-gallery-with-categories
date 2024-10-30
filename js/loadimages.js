jQuery(document).ready(function() {
    jQuery('.neo_gallery_category_all li a').click(function(e){
        e.preventDefault();
        jQuery('.neo_gallery_category_all li a').removeClass('current_category');
        jQuery(this).addClass('current_category');
    });

    jQuery('.neo_category').click(function () {
        jQuery('.image-load').show();
        var lastID = jQuery('#loadMoreCat').attr('lastID');
        var gallery_id=jQuery('.neo_gallery_category_all').attr('id');
        var post_id = jQuery(this).attr('id');
        ajaxurl = jQuery('.neo_gallery_category_all').attr('ajaxurl');
        var action = 'neo_ivg_load_images';

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {action: action, post_id: post_id, gallery_id: gallery_id, last_id: lastID},
            dataType: 'json',
            encode: true,
            async: true,
            success: function (data) {
                jQuery('.image-load').hide();
                if (data.status == 'success') {
                    jQuery('#loadMore').remove();
                    jQuery('.neo_loading_data').html( data.message );

                }
                if (data.status == 'error') {
                    jQuery('.neo_loading_data').html( data.message );
                }
            }
        })
    });

    jQuery('ul.neo_gallery_category_all li').click( function() {
        if (jQuery(this).is(':first-child')) {

            jQuery(document).ready(function () {
                var id = jQuery('ul.neo_gallery_category_all li a').attr('id');
                var galleryId = jQuery('.neo_gallery_category_all').attr('id');
                ajaxurl = jQuery('.neo_gallery_category_all').attr('ajaxurl');
                var action = 'neo_ivg_load_all_images';

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {action: action, id: id, galleryId: galleryId},
                    dataType: 'json',
                    encode: true,
                    async: true,
                    beforeSend: function () {

                    },
                    success: function (data) {
                        if (data.status == 'success') {
                            jQuery('.neo_loading_data').html(data.message);

                        }
                        if (data.status == 'error') {
                            jQuery('.neo_loading_data').html(data.message);
                        }
                    }
                });
            });

        }
    });
});
jQuery(document).ready(function(){
    jQuery(window).scroll(function() {
        var lastID = jQuery('#loadMore').attr('lastID');
        NeoImageVideoGalleryOnLoad(lastID);
    })
});

jQuery(document).ready(function(){
    jQuery('ul.neo_gallery_category_all li').click( function(){

        if ( jQuery(this).is(':first-child') ){
            jQuery(document).ready(function(){
                jQuery(window).scroll(function() {
                    var lastID = jQuery('#loadMore').attr('lastID');
                    NeoImageVideoGalleryOnLoad(lastID);
                })
            });
        }
        else{
            jQuery(document).ready(function(){
                jQuery(window).scroll(function() {
                    var lastId = jQuery('#loadMoreCat').attr('lastID');
                    if ((jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) && (lastId != 0)) {

                        var catId = jQuery('#loadMoreCat').attr('catid');
                        var galleryId = jQuery('.neo_gallery_category_all').attr('id');
                        var action = 'neo_ivg_load_category_images';

                        ajaxurl = jQuery('.neo_gallery_category_all').attr('ajaxurl');
                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: { action: action,  catId: catId, galleryId: galleryId, lastId: lastId },
                            dataType: 'json',
                            encode: true,
                            async: true,
                            beforeSend:function(){
                                jQuery('#loadMoreCat').show();
                            },
                            success: function (data) {
                                if(data.status == 'success'){
                                    jQuery('#loadMoreCat').remove();
                                    jQuery('.neo_loading_data').append(data.message);

                                }
                                if(data.status == 'error'){

                                }
                            }
                        })
                    }

                });
            });
        }
    })
});
function NeoImageVideoGalleryOnLoad(lastID){
    if ((jQuery(window).scrollTop() == jQuery(document).height() - jQuery(window).height()) && (lastID != 0)) {

        ajaxurl = jQuery(".neo_gallery_category_all").attr('ajaxurl');
        var postId = jQuery('.neo_gallery_category_all li a:first').attr('id');
        var gallery_id = jQuery('.neo_gallery_category_all').attr('id');
        var action = 'neo_ivg_load_images';

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { action: action,  post_id: postId, gallery_id: gallery_id, last_id: lastID },
            dataType: 'json',
            encode: true,
            async: true,
            beforeSend:function(){
                jQuery('#loadMore').show();
            },
            success: function (data) {
                if(data.status == 'success'){
                    jQuery('#loadMore').remove();
                    jQuery('.neo_loading_data').append(data.message);
                    jQuery('.image-load').hide();

                }
                if(data.status == 'error'){
                    jQuery('.neo_load_images').append(data.message);
                }
            }
        })
    }
}