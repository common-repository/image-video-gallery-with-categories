<?php
function neo_ivg_load_images(){
    $result = array();
    $data= '';
    $categoryId = '';
    $allNumRows = '';
    global $wpdb;

    if( $_POST['action'] == 'neo_ivg_load_images' ){
        $postId = sanitize_text_field( $_POST['post_id'] );
        $galleryId = absint( $_POST['gallery_id'] );
        $lastId = absint( $_POST['last_id'] );
        if ( !empty($postId) && !empty($galleryId) ){
            if( $postId =='all' ){
                $catResult = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_category" ,ARRAY_A );

                foreach ( $catResult as $catData ){
                    $id = $catData['ID'];
                    $count = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) as num_rows FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id= %d AND ID < %d ORDER BY ID DESC ", $galleryId, $lastId ) ,ARRAY_A );
                    foreach( $count as $total ){
                       $allNumRows = $total['num_rows'];
                    }
                    $showLimit = 20;
                    $imageCategoryQuery = $wpdb->get_results( $wpdb->prepare( "SELECT  * FROM {$wpdb->prefix}neo_ivg_image_category WHERE category_id='$id' AND gallery_id = %d AND ID < %d ORDER BY ID DESC LIMIT %d" , $galleryId, $lastId, $showLimit ) , ARRAY_A );

                    if( !empty( $imageCategoryQuery ) ){

                        foreach ( $imageCategoryQuery as $imageCategory ){
                            $imageId = $imageCategory['image_id'];
                            $categoryId = $imageCategory['ID'];
                            $imageQuery = $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d",$imageId ) , ARRAY_A);

                            foreach ( $imageQuery as  $imageData ){

                                if( $imageData['file_type'] == 'video/mp4' ){
                                    $image_url =$imageData['cover_image'];
                                    $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                                    $attachment_id = attachment_url_to_postid( $image_url );
                                    $data .= '<div class="neo_category_images" id="' . esc_attr__( $categoryId ) . '"><a class="fancybox" href="' . esc_url( $imageData['url'] ). '" data-fancybox="gallery" rel="group">'.wp_get_attachment_image($attachment_id,'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                                }
                                else{
                                    $image_url =$imageData['image'];
                                    $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                                    $attachment_id = attachment_url_to_postid( $image_url );
                                    $data .= '<div class="neo_category_images" id="' . esc_attr__( $categoryId ) . '"><a class="fancybox" href="' . esc_url( $imageData['image'] ). '" data-fancybox="gallery" rel="group">'.wp_get_attachment_image($attachment_id,'medium').'</a> </div>';
                                }
                            }
                        }

                        if( $allNumRows > $showLimit ){
                            $data .= '<div id="loadMore" lastID="'.esc_attr__( $categoryId ).'" style="display: none;"></div>';
                        }
                        else{
                            $data .= '<div id="loadMore" lastID="0" style="display: none;"></div>';
                        }
                    }
                    else{
                        $data .= '<div id="loadMore" lastID="0" style="display: none;"></div>';
                    }
                }
            }
            else{
                 $data .= '<div class="neo_load_categoryImages">';
                 $sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id = %d AND category_id = %d ORDER BY ID DESC LIMIT 8",$galleryId, $postId ),ARRAY_A );

                 foreach( $sql as $imageData ){
                     $imageCategoryId = $imageData['ID'];
                     $imageId = $imageData['image_id'];
                     $imageQuery = $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d",$imageId ) , ARRAY_A);
                     foreach ( $imageQuery as $images ){
                         if( $images['file_type'] == 'video/mp4' ){
                             $image_url =$images['cover_image'];
                             $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                             $attachment_id = attachment_url_to_postid( $image_url );
                             $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' . esc_url( $images['url'] ) . '">'.wp_get_attachment_image($attachment_id,'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                         }
                         else{
                             $image_url =$images['image'];
                             $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                             $attachment_id = attachment_url_to_postid( $image_url );
                             $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" href="' .esc_url( $images['image'] ). '" data-fancybox="gallery" rel="group">'.wp_get_attachment_image($attachment_id,'medium').'</a> </div>';
                         }
                     }
                 }
                 $data .= '<div id="loadMoreCat" lastID="'. esc_attr__( $imageCategoryId ).'" catID="'. esc_attr__( $postId ).'"  style="display: none;"> <img  class="neo_loader" src="'.plugin_dir_url( dirname( __FILE__ ) ) . 'img/spinner.gif'.'"></div>';
                 $data .= '</div>';
            }
            $result['status'] = 'success';
            $result['message'] = $data;
            echo json_encode( $result );
        }
        else{
            $result['status'] = 'error';
            $result['message'] = 'Error';
            echo json_encode( $result );
        }
    }
    else{
        $result['status'] = 'error';
        $result['message'] = 'Invalid action';
        echo json_encode( $result );
    }
    die();
}

add_action( 'wp_ajax_neo_ivg_load_images','neo_ivg_load_images' );
add_action( 'wp_ajax_nopriv_neo_ivg_load_images', 'neo_ivg_load_images' );

function neo_ivg_load_category_images(){
    $result = array();
    $data = '';
    $allNumRows = '';
    global $wpdb;

    if( $_POST['action'] == 'neo_ivg_load_category_images' ) {
        $catId =  absint( $_POST['catId'] );
        $galleryId = absint( $_POST['galleryId'] );
        $lastId = absint( $_POST['lastId'] );

        if ( !empty( $catId ) && !empty( $galleryId ) ) {
            $count = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) as num_rows FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id = %d AND category_id =%d AND ID < %d ORDER BY ID DESC", $galleryId, $catId,$lastId ),ARRAY_A );

            foreach( $count as $total ){
                $allNumRows = $total['num_rows'];
            }
            $showLimit = 20;
            $imageCategoryQuery = $wpdb->get_results( $wpdb->prepare( "SELECT  * FROM {$wpdb->prefix}neo_ivg_image_category WHERE category_id = %d AND gallery_id = %d AND ID < %d ORDER BY ID DESC LIMIT %d", $catId , $galleryId, $lastId , $showLimit ) , ARRAY_A );

            if( !empty( $imageCategoryQuery ) ){

                foreach( $imageCategoryQuery as $imageCategory ){
                    $imageId = $imageCategory['image_id'];
                    $imageCategoryId = $imageCategory['ID'];

                    $imageQuery = $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d",$imageId ) , ARRAY_A);

                    foreach( $imageQuery as $imageData ){
                        if( $imageData['file_type'] =='video/mp4' ){
                            $image_url =$imageData['cover_image'];
                            $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                            $attachment_id = attachment_url_to_postid( $image_url );
                            $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' .esc_url( $imageData['url'] ). '">'.wp_get_attachment_image($attachment_id,'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                        }
                        else{
                            $image_url =$imageData['image'];
                            $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                            $attachment_id = attachment_url_to_postid( $image_url );
                            $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' .esc_url( $imageData['image'] ). '" >'.wp_get_attachment_image($attachment_id,'medium').'</a> </div>';
                        }
                    }
                }
                if( $allNumRows > $showLimit ){
                    $data .= '<div id="loadMoreCat" lastID="'. esc_attr__( $imageCategoryId ).'" catID="'.esc_attr__( $catId ).'" style="display: none;"></div>';
                }
                else{
                    $data .='<div id="loadMoreCat" lastID="0" catID="'.esc_attr__( $catId ).'" style="display: none;"></div>';
                }
            }
            else{
                $data .= '<div id="loadMoreCat" lastID="0" catID="'.esc_attr__( $catId ).'" style="display: none;"></div>';
            }
            $result['status'] = 'success';
            $result['message'] = $data;
            echo json_encode( $result );
        }
        else{
            $result['status'] = 'error';
            $result['message'] = 'Error';
            echo json_encode( $result );
        }
    }
    else{
        $result['status'] = 'error';
        $result['message'] = 'Invalid action';
        echo json_encode( $result );
    }
    die();
}
add_action( 'wp_ajax_neo_ivg_load_category_images','neo_ivg_load_category_images' );
add_action( 'wp_ajax_nopriv_neo_ivg_load_category_images', 'neo_ivg_load_category_images' );

function neo_ivg_load_all_images(){
    global $wpdb;
    $result = array();
    $data = '';

    if( $_POST['action'] == 'neo_ivg_load_all_images' ) {
        $postId =  _sanitize_text_fields( $_POST['id'] );
        $galleryId = absint( $_POST['galleryId'] );

        if ( !empty( $postId ) && !empty( $galleryId ) ) {
            if ( $postId == 'all') {
                $sql = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id = %d ORDER BY ID DESC LIMIT 8" , $galleryId ),ARRAY_A );

                foreach( $sql as $imageCategory ) {
                    $imageCategoryId = $imageCategory['ID'];
                    $imageId = $imageCategory['image_id'];
                    $imageQuery = $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d",$imageId ) , ARRAY_A);

                    foreach ( $imageQuery as $imageData ) {
                        if ( $imageData['file_type'] == 'video/mp4' ) {
                            $image_url =$imageData['cover_image'];
                            $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                            $attachment_id = attachment_url_to_postid( $image_url );
                            $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' .esc_url( $imageData['url'] ). '" >'.wp_get_attachment_image($attachment_id,'medium').'<div id="neo_icon"><i class="far fa-play-circle"></i></div></a></div>';
                        }
                        else {
                            $image_url =$imageData['image'];
                            $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                            $attachment_id = attachment_url_to_postid( $image_url );
                            $data .= '<div class="neo_category_images" id="' . esc_attr__( $imageCategoryId ). '"><a class="fancybox" data-fancybox="gallery" rel="group" href="' .esc_url( $imageData['image'] ). '">'.wp_get_attachment_image($attachment_id,'medium').'</a> </div>';
                        }
                    }
                }
                $data .= '<div id="loadMore" lastID="'. esc_attr__( $imageCategoryId ).'" style="display: none;"> <img  class="neo_loader" src="'.plugin_dir_url( dirname( __FILE__ ) ) . 'img/spinner.gif'.'"></div>';
            }
            $result['status'] = 'success';
            $result['message'] = $data;
            echo json_encode( $result );
        }
        else{
            $result['status'] = 'error';
            $result['message'] = 'Invalid data';
            echo json_encode( $result );
        }
    }
    else{
        $result['status'] = 'error';
        $result['message'] = 'Invalid action';
        echo json_encode( $result );
    }
    die();
}
add_action( 'wp_ajax_neo_ivg_load_all_images','neo_ivg_load_all_images' );
add_action( 'wp_ajax_nopriv_neo_ivg_load_all_images', 'neo_ivg_load_all_images' );