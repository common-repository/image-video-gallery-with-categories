<?php
function neo_ivg_load_style(){
    wp_enqueue_script('jquery');
    wp_enqueue_style( 'neo_load_custom_style' , plugins_url( '../css/gallery_category.css', __FILE__ ) );
    wp_enqueue_style( 'neo_load_fancy_style' , plugins_url( '../fancybox-master/dist/jquery.fancybox.css', __FILE__ ) );
    wp_enqueue_style( 'neo_load_fancy_core_style' , plugins_url( '../fancybox-master/src/css/core.css', __FILE__ ) );
    wp_enqueue_style( 'neo_fontawesome_style' , plugins_url( '../css/fontawesome/css/all.css', __FILE__ ) );
    wp_enqueue_script( 'neo_load_custom_script', plugins_url( '../js/loadimages.js', __FILE__ ),  array(), '1.1.0', true );
    wp_enqueue_script( 'neo_load_fancy_script', plugins_url( '../fancybox-master/dist/jquery.fancybox.js', __FILE__ ),  array(), '1.1.0', true );
    wp_enqueue_script( 'neo_load_fancy_core_script', plugins_url( '../fancybox-master/src/js/core.js', __FILE__ ),  array(), '1.1.0', true );

}
add_action( 'wp_enqueue_scripts','neo_ivg_load_style' );

function neo_ivg_gallery_view_shortcode( $atts ){
    $list = '';
    global $wpdb;
    $galleryId = absint( $atts['id'] );
    $admin_url = admin_url( 'admin-ajax.php' );
    $query = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_category WHERE ID IN ( SELECT category_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id='$galleryId' ) ",ARRAY_A );
    $list .= '<div class="neo_gallery_shortcode">
                <div class="neo_gallery_shortcode_inner">';
                    $list .= '<div class="neo_category_section">';
                        $list .= '<ul class="neo_gallery_category_all " id="'.$galleryId.'" ajaxurl = "'. esc_url( $admin_url ).'">';
                            $list .= '<li><a class="neo_category current_category" id="all">All</a></li>';
                            foreach( $query as $category ){
                                $list .= '<li><a class="neo_category" id="'.$category['ID'].'" href= "#" >'. esc_attr__( $category['category_name'] ).'</a></li>';
                            }
                        $list .= '</ul>';
                    $list .= '</div>';
                    $list .= '<div class="neo_loading_data">';
                        $sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id = %d ORDER BY ID DESC LIMIT 8", $galleryId ) ,ARRAY_A );

                        foreach( $sql as $imageCategory ){
                            $imageCategoryId = $imageCategory['ID'];
                            $imageId = $imageCategory['image_id'];
                            $imageQuery = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d", $imageId ), ARRAY_A );

                            foreach ( $imageQuery as  $imageData ){
                                if( $imageData['file_type'] == 'video/mp4' ){
                                    $image_url =$imageData['cover_image'];
                                    $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                                    $attachment_id = attachment_url_to_postid( $image_url );
                                    $list .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' .esc_url( $imageData['url'] ). '">'.wp_get_attachment_image($attachment_id, 'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                                }
                                else{
                                    $image_url =$imageData['image'];
                                    $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                                    $attachment_id = attachment_url_to_postid( $image_url );
                                    $list .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ) . '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' . esc_url( $imageData['image'] ). '" data-fancybox="gallery" rel="group">'.wp_get_attachment_image($attachment_id, 'medium').'</a> </div>';

                                }
                            }
                        }
                    $list .= '</div>';
                    $list .= '<div class = "neo_category_loading"></div>';
                    $list .= '<div id="loadMore" lastID="'.esc_attr__( $imageCategoryId ).'" style="display: none;"> <img  class="neo_loader" src="'.plugin_dir_url( dirname( __FILE__ ) ) . 'img/spinner.gif'.'"></div>'
                .'</div>'
            .' </div>';
    return $list;
}
add_shortcode( 'gallery_view_with_category','neo_ivg_gallery_view_shortcode' );

function neo_ivg_image_shortcode( $atts ){
    $images = '';
    global $wpdb;
    $galleryId = $atts['id'];
    $query = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_category WHERE ID IN ( SELECT category_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id='$galleryId' )",ARRAY_A );

    $images .= '<div class="neo_page_gallery">'
        .'<div class="neo_page_gallery_innner">';

            foreach( $query as $row ){
                $categoryId = $row['ID'];
                $sql = "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID IN( SELECT image_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE category_id='$categoryId' AND gallery_id='$galleryId' ) ORDER BY ID DESC ";
                $rows = $wpdb->get_results( $sql, ARRAY_A );
                foreach ( $rows as $imageData ){
                    if( $imageData['file_type'] == 'image/jpeg' || $imageData['file_type'] == 'image/png' ){
                        $image_url =$imageData['image'];
                        $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                        $attachment_id = attachment_url_to_postid( $image_url );
                         $images .= '<div class="neo_page_images"><a class="fancybox" data-fancybox="gallery" rel="group" href="'.esc_url( $imageData['image'] ).'" >'.wp_get_attachment_image($attachment_id, 'medium').'</a></div>';
                    }
                    else{
                        $image_url =$imageData['cover_image'];
                        $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                        $attachment_id = attachment_url_to_postid( $image_url );
                        $images .= '<div class="neo_page_images"><a class="fancybox" data-fancybox="gallery" rel="group" href="'.esc_url( $imageData['url'] ).'">'.wp_get_attachment_image($attachment_id, 'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                    }
                }
            }
        $images .= '</div>'
    .'</div>';
    return $images;
}
add_shortcode( 'gallery_view_without_category','neo_ivg_image_shortcode' );
