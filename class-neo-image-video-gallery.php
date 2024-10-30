<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

include ( plugin_dir_path( __FILE__ ) .'/class-neo-image-video-gallery-category.php');

class NeoImageVideoGallery extends WP_List_Table{

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Gallery', 'gp' ), //singular name of the listed records
            'plural'   => __( 'Gallery', 'gp' ), //plural name of the listed records
            'ajax'     => false //should this table support ajax?

        ] );
    }

    /*
     * fetch the galleries
     */
    public static function neo_ivg_get_gallery( $per_page = 10, $page_number = 1 ) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery";
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $result;
    }

    /*
     * deletes the gallery and  gallery-images
     */
    public static function neo_ivg_delete_gallery( $id ) {
        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}neo_ivg_gallery",  [ 'ID' => $id ],  [ '%d' ] );
        $query = $wpdb->query( "DELETE FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID IN( SELECT image_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id='$id' ) " );
        $wpdb->delete( "{$wpdb->prefix}neo_ivg_image_category", ['gallery_id' => $id],[ '%d' ] );
    }

    /*
     * counts the number of gallery
     */
    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}neo_ivg_gallery";
        return $wpdb->get_var( $sql );
    }

    /*
     * Gallery form
     */
    public function neo_ivg_create_gallery_form()    {
        ?>
        <div class="wrap">
            <h1>Create New Gallery</h1>
            <div id="create-gallery">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post" action="" class="gallery-form">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="form-field form-required">
                                            <th scope="row"><label class="">Gallery Name</label><span class="description"> (required)</span> </th>
                                            <td><input type="text" name="gallery-name" required style="width:35%;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="submit ">
                                    <input type="submit" name="gallery-submit" class="button button-primary" value="Create Gallery">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    /**
     *  Submit gallery from and insert into database
     */
    public function neo_ivg_submit_gallery_form(){
        if ( isset( $_POST['gallery-submit'] ) && !empty( $_POST['gallery-submit'] )) {
            global $wpdb;
            $result = '';
            $galleryName = ( isset( $_POST['gallery-name'] ) && !empty( $_POST['gallery-name'] ) ) ? sanitize_text_field( $_POST['gallery-name'] ) : '';
            $check = $wpdb->get_var( $wpdb->prepare( "SELECT gallery_name FROM  {$wpdb->prefix}neo_ivg_gallery WHERE gallery_name= %s", $galleryName ) );
            if ( $check == '' ) {
                $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery", array( 'gallery_name' => $galleryName ), array( '%s' ) );
                $id = $wpdb->insert_id;
                $short = '[gallery_view_with_category id=' . $id.']';
                $result .= $wpdb->update("{$wpdb->prefix}neo_ivg_gallery", array( 'shortcode'=>$short ), array( 'ID'=>$id ), array(' %s' ), array( '%d' ) );
                if ( $result ) {
                    wp_redirect( '?page=add_image_class&id="'.absint( $id ).'"',301 );
                }
                else {
                    echo '<h4 style="color: #FF0000;">Something went wrong, Please try again</h4>';
                }
            }
            else{
                echo '<h4 style="color: #FF0000;">Already exists! Please take another gallery name</h4>';
            }
        }
    }

    /*
     * add images to gallery and store it in uploads/gallery
     */
    public function neo_ivg_image_form(){
        global $wpdb;
        $galleryId = absint( $_GET['id'] );
        $galleryName = $wpdb->get_var( $wpdb->prepare( "SELECT gallery_name FROM  {$wpdb->prefix}neo_ivg_gallery WHERE ID= %d", $galleryId ) );
        echo '<h2>Add New Images to '. esc_attr__( $galleryName ). '</h2>';
        $category = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_category",ARRAY_A );
        ?>
        <div class ="wrap">
            <div id="add_image" >
                <div id="post-body" class="metabox-holder columns-2">
                     <div id="post-body-content" >
                         <div class="neo_file_option">
                             <a href="?page=add_image_class&id=<?php echo $galleryId; ?>" class="page-title-action">Add Image</a>
                             <a href="?page=add_video_class&id=<?php echo $galleryId; ?>" class="page-title-action">Add video</a>
                         </div>
                         <div class="meta-box-sortables ui-sortable">
                             <form method="post" action="" class="image-form " enctype="multipart/form-data" >
                                 <table class="form-table">
                                     <tbody>
                                     <tr class="form-field form-required">
                                         <th scope="row"><label class="">Image Name</label></th>
                                         <td><input type="text" name="image-title" style="width:35%;"></td>
                                     </tr>
                                     <tr class="form-field form-required">
                                         <th scope="row"><label class="">Image</label><span class="description"> (required)</span></th>
                                         <td class="neo_image">
                                            <div class="neo_chosen_image">

                                            </div>
                                             <input id="background_image" type="hidden" name="image" value="<?php echo get_option('background_image'); ?>" />
                                             <button type="button" class="file-image" name="image-name" id="pb-file" >Upload Image</button>
                                             <button type="button" id="neo_remove_img">&times;</button>
                                         </td>
                                     </tr>
                                     <tr class="form-field form-required">
                                         <th scope="row"><label class="">Category</label><span class="description"> (required)</span></th>
                                         <td>
                                             <div id="category" class="postbox cat" style="width:35%;">
                                                 <div class="inside" style="padding: 0 15px;">
                                                     <div class="categorydiv">
                                                         <ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
                                                             <?php
                                                             foreach( $category as $categoryData ){
                                                                 echo '<li id="" class="popular-category"><input type="checkbox" name="image_category[]" class="justone" value="'.absint( $categoryData['ID'] ).'" >'. esc_attr__( $categoryData['category_name'] ).' </li>';
                                                             }
                                                             ?>
                                                         </ul>
                                                     </div>
                                                 </div>
                                             </div>
                                         </td>
                                     </tr>
                                     </tbody>
                                 </table>
                                 <p class="submit ">
                                     <input type="submit" name="image-submit" class="button button-primary" value="Save Image">
                                 </p>
                             </form>
                         </div>
                     </div>
                </div>
            </div>
        </div>
        <?php
    }

    /***
     * Save image to database and in wp-content/uploads
     */
    public function neo_ivg_save_image(){
        if( isset( $_POST['image-submit'] ) && !empty( $_POST['image-submit'] ) ) {
            if ( isset( $_POST['image'] ) && !empty( $_POST['image'])){
                if ( isset( $_POST['image_category'] ) && !empty( $_POST['image_category'] ) ) {
                    global $wpdb;
                    $result ='';
                    $galleryId = absint( $_GET['id'] );
                    $title = ( isset( $_POST['image-title'] ) && !empty( $_POST['image-title'] ) ) ? sanitize_text_field( $_POST['image-title'] ) : '';
                    $imageName = ( isset( $_FILES['image-name'] ) && !empty( $_FILES['image-name'] ) ) ? sanitize_file_name( $_FILES['image-name'] ['name'] ) : '';
                    $image_url = ( isset( $_POST['image'] ) && !empty( $_POST['image'] ) ) ? sanitize_text_field( $_POST['image'] ) : '';

                    $wp_filetype = wp_check_filetype( $image_url, null );
                    if( $wp_filetype['type'] == 'image/jpeg' || $wp_filetype['type'] == 'image/png'){

                        $data = array(  'image' => $image_url,  'title' => $title, 'file_type'=> $wp_filetype['type'] );
                        $format = array( '%s', '%s', '%s' );
                        $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery_images", $data, $format );
                        $imageId = $wpdb->insert_id;
                        foreach( $_POST['image_category'] as $imageCategory ){
                            $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageId, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                        }
                    }
                    else{
                        echo '<h2 style="color: #FF0000;">Extension not allowed, Please choose a image of .jpeg, .jpg, .png format.</h2>';
                    }

                }
                else{
                    echo '<h2 style="color: #FF0000;">Please select category.</h2>';
                }
            }
            else{
                echo '<h2 style="color: #FF0000;">Please select image.</h2>';
            }
        }


        /*if( isset( $_POST['image-submit'] ) && !empty( $_POST['image-submit'] ) ) {
            if ( isset( $_POST['image_category'] ) && !empty( $_POST['image_category'] ) ) {
                global $wpdb;
                $result ='';
                $uploads = wp_upload_dir();
                $upload_path = $uploads['basedir'];
                $upload_url = $uploads['baseurl'];
                $upload_url = $upload_url . '/gallery/';
                $upload_path = $upload_path . '/gallery/';
                if ( !file_exists( $upload_path ) ) {
                    mkdir( $upload_path, 0777, true );
                }
                $galleryId = absint( $_GET['id'] );
                $title = ( isset( $_POST['image-title'] ) && !empty( $_POST['image-title'] ) ) ? sanitize_text_field( $_POST['image-title'] ) : '';
                $imageName = ( isset( $_FILES['image-name'] ) && !empty( $_FILES['image-name'] ) ) ? sanitize_file_name( $_FILES['image-name'] ['name'] ) : '';
                $image = $upload_url . $imageName;

                if ( isset( $_POST['image'] ) && !empty( $_POST['image'])){
                    $file = ( isset( $_POST['image'] ) && !empty( $_POST['image'] ) ) ? sanitize_text_field( $_POST['image'] ) : '';
                    $wp_filetype = wp_check_filetype( $file, null );
                    $data = array(  'image' => $file,  'title' => $title, 'file_type'=> $wp_filetype['type'] );
                    $format = array( '%s', '%s', '%s' );
                    // $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery_images", $data, $format );
                    $imageId = $wpdb->insert_id;
                    foreach( $_POST['image_category'] as $imageCategory ){
                        //$result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageId, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                    }
                }
                else{
                    echo '<h2 style="color: #FF0000;">Please select image.</h2>';
                }


              if( isset ( $_FILES['image-name']) ){
                    $fileTmp = ( isset( $_FILES['image-name'] ) && !empty( $_FILES['image-name'] ) ) ? $_FILES['image-name']['tmp_name'] : '';
                    $fileType = ( isset( $_FILES['image-name'] ) && !empty( $_FILES['image-name'] ) ) ? $_FILES['image-name']['type'] : '' ;
                    $fileExt = strtolower( end( explode( '.', $imageName ) ) );
                    $extensions = array( "jpeg", "jpg", "png");

                    if ( in_array( $fileExt, $extensions ) === false ) {
                        echo '<h4 style="color: #FF0000;"> Extension not allowed, Please choose a image of .jpeg, .jpg, .png format.</h4>';
                    }
                    else {
                        $file = ( isset( $_POST['image'] ) && !empty( $_POST['image'] ) ) ? sanitize_text_field( $_POST['image'] ) : '';
                        $data = array(  'image' => $file,  'title' => $title, 'file_type'=> $fileType );
                        $format = array( '%s', '%s', '%s' );
                        $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery_images", $data, $format );
                        $imageId = $wpdb->insert_id;
                        foreach( $_POST['image_category'] as $imageCategory ){
                            $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageId, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                        }
                        move_uploaded_file( $fileTmp , $upload_path . $imageName );
                    }
                }
            }
            else{
                echo '<h2 style="color: #FF0000;">Please select category.</h2>';
            }
        }*/
    }

    /*
     * displays the images of current gallery
     */
    public function neo_ivg_image_display(){
        global $wpdb;
        $imagesData= '';
        $galleryId = absint( $_GET['id'] );
        $query = "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID IN ( SELECT image_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE gallery_id='$galleryId' ) ORDER BY ID DESC ";
        $galleryName = $wpdb->get_var( $wpdb->prepare( "SELECT gallery_name FROM  {$wpdb->prefix}neo_ivg_gallery WHERE ID= %d", $galleryId ) );
        $galleryData = $wpdb->get_results( $query, ARRAY_A );

        $imagesData .= '<div class = "neo_show_gallery_images">'
            .'<div class = "neo_show_gallery_images_inner">';
                 $imagesData .='<h2 style="text-align: center;">Images of '.esc_attr__( $galleryName ).'</h2>';

                 foreach( $galleryData as $galleryImages ){

                     $imagesData .= '<div class="neo_image_show_gird">'

                         .'<div class="neo_gallery_image_show">';
                              if( $galleryImages['file_type'] == 'image/jpeg' || $galleryImages['file_type'] == 'image/png' ){
                                  $image_url =$galleryImages['image'];
                                  $post_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url' AND post_type='attachment' AND post_status = 'inherit'" );
                                  $attachment_id = attachment_url_to_postid( $image_url );
                                  $imagesData .= wp_get_attachment_image($attachment_id,'medium');
                              }
                              else{
                                  $image_url =$galleryImages['cover_image'];
                                  $attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid='$image_url'" );
                                  $attachment_id = attachment_url_to_postid( $image_url );
                                  if( !empty($galleryImages['url'] ) ){
                                      $imagesData .='<a class="" href="'.esc_url( $galleryImages['url'] ).'" target="_blank">'.wp_get_attachment_image($attachment_id,'medium').'</a>';
                                  }
                                  else{
                                      $imagesData .='<a class="" href="'.esc_url( $galleryImages['image'] ).'" target="_blank">'.wp_get_attachment_image($attachment_id,'medium').'</a>';
                                  }
                              }
                         $imagesData.='</div>'
                         .'<div class="neo_gallery_image_action">
                              <a href="?page=edit_image_class&id='. absint( $galleryImages['ID'] ).'&gallery_id='.absint( $galleryId ).'">Edit</a> <span>/</span>
                              <a href="?page=delete_image_class&id='. absint( $galleryImages['ID'] ).'">Delete</a>
                         </div>'
                    .'</div>';
                 }
        $imagesData .= '</div>'
        .'</div>';
        echo $imagesData;
    }

    /*
     * Delete individual images from gallery
     */
    public function neo_ivg_delete_gallery_image(){
        $imageID = absint( $_GET['id'] );
        $result = '';
        global $wpdb;
        $galleryId = $wpdb->get_var( $wpdb->prepare( "SELECT gallery_id FROM  {$wpdb->prefix}neo_ivg_image_category WHERE image_id= %d", $imageID ) );
        $result .= $wpdb->get_results( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}neo_ivg_image_category WHERE image_id = %d",$imageID ));
        $result .= $wpdb->get_results( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID = %d",$imageID ));
        if($result){
            wp_redirect( '?page=add_image_class&id="'.absint( $galleryId ).'"' );
        }
    }

    public  function neo_ivg_edit_gallery_image(){
        $imageID = absint( $_GET['id'] );
        $galleryID = absint( $_GET['gallery_id'] );
        global $wpdb;
        $result = '';
        $imageData = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_gallery_images WHERE ID='$imageID'",ARRAY_A );
        $category = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}neo_ivg_category",ARRAY_A );
        $imageCategory = $wpdb->get_results( "SELECT category_name FROM {$wpdb->prefix}neo_ivg_category WHERE ID IN(SELECT category_id FROM {$wpdb->prefix}neo_ivg_image_category WHERE image_id = $imageID)",ARRAY_A );
        $cat_selected = array();
        foreach ($imageCategory as $image_data) {
            $c_data = $image_data;
            $cat_data = implode(' ', $c_data);
            array_push($cat_selected, $cat_data);
        }

        if( !empty($imageData) ){
            foreach($imageData as $image){
                ?>
                <div class ="wrap">
                    <div id="add_image" >
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content" >
                                <div class="neo_file_option">
                                    <?php
                                    if( !empty( $image['url'] ) ){
                                        echo '<h2>Edit Video</h2>';
                                    }
                                    else{
                                        echo '<h2>Edit Image</h2>';
                                    }
                                    ?>
                                </div>
                                <div class="meta-box-sortables ui-sortable">
                                    <form method="post" action="" class="image-form " enctype="multipart/form-data" >
                                        <table class="form-table">
                                            <tbody>
                                            <tr class="form-field form-required">
                                                <th scope="row"><label class="">
                                                        <?php
                                                        if( !empty( $image['url'] ) ){
                                                            echo 'Video Name';
                                                        }
                                                        else{
                                                            echo 'Image Name';
                                                        }
                                                        ?>
                                                    </label></th>
                                                <td><input type="text" name="title" style="width:35%;" value="<?php echo $image['title']  ?>"></td>
                                            </tr>
                                            <?php
                                             if( !empty($image['cover_image']) ){
                                                 ?>
                                                 <tr class="form-field form-required">
                                                     <th scope="row"><label class="">Cover Image</label><span class="description"> (required)</span></th>
                                                     <td class="neo_image">
                                                         <div class="neo_show_image">
                                                             <div class="neo_previous_img">
                                                                 <img src="<?php echo $image['cover_image']; ?>" class="">
                                                             </div>
                                                         </div>
                                                         <input id="background_image" type="hidden" name="cover_image" value="<?php echo $image['cover_image']; ?>" />
                                                         <button type="button" class="file-image" name="image-name" id="neo_choose_file">Upload Image</button>
                                                         <button type="button" id="neo_edit_remove_img">&times;</button>
                                                     </td>
                                                 </tr>
                                             <?php
                                             }
                                             else{
                                                 ?>
                                                 <tr class="form-field form-required">
                                                     <th scope="row"><label class="">Image</label><span class="description"> (required)</span></th>
                                                     <td class="neo_image">
                                                         <div class="neo_show_image">
                                                             <div class="neo_previous_img">
                                                                 <img src="<?php echo $image['image']; ?>" class="">
                                                             </div>
                                                         </div>
                                                         <input id="background_image" type="hidden" name="image" value="<?php echo $image['image']; ?>" />
                                                         <button type="button" class="file-image" name="image-name" id="neo_choose_file">Upload Image</button>
                                                         <button type="button" id="neo_edit_remove_img">&times;</button>
                                                     </td>
                                                 </tr>
                                             <?php
                                             }

                                             if( !empty( $image['url'] ) ){
                                                 ?>
                                                 <tr class="form-field form-required">
                                                     <th scope="row"><label class="">Youtube Video URL</label><span class="description">( required)</span> </th>
                                                     <td><input type="text" name="url" required style="width:35%;" value="<?php echo $image['url']; ?>"></td>
                                                 </tr>
                                             <?php
                                             }
                                            ?>
                                            <tr class="form-field form-required">
                                                <th scope="row"><label class="">Category</label><span class="description"> (required)</span></th>
                                                <td>
                                                    <div id="category" class="postbox cat" style="width:35%;">
                                                        <div class="inside" style="padding: 0 15px;">
                                                            <div class="categorydiv">
                                                                <ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
                                                                    <?php
                                                                    foreach( $category as $categoryData ){
                                                                        $check = ( in_array($categoryData['category_name'], $cat_selected) ) ? 'checked' : '' ;
                                                                        echo '<li id="" class="popular-category"><input type="checkbox" name="image_category[]" class="justone" value="'.absint( $categoryData['ID'] ).'" '. $check. '>'. esc_attr__( $categoryData['category_name'] ).' </li>';
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <p class="submit ">
                                            <input type="hidden" name="gallery_id" value="<?php echo $galleryID; ?>">
                                            <input type="submit" name="edit-submit" class="button button-primary" value="Save Image">
                                        </p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
        }
    }

    public function neo_ivg_save_edited_image(){
        if( isset( $_POST['edit-submit'] ) && !empty( $_POST['edit-submit'] ) ) {
            if ( isset( $_POST['image'] ) && !empty( $_POST['image']) ||  isset( $_POST['cover_image'] ) && !empty( $_POST['cover_image'])){
                if ( isset( $_POST['image_category'] ) && !empty( $_POST['image_category'] ) ) {
                    global $wpdb;
                    $imageID = absint( $_GET['id'] );
                    $galleryId = absint( $_POST['gallery_id'] );
                    $result ='';
                    $file = ( isset( $_POST['image'] ) && !empty( $_POST['image'] ) ) ? sanitize_text_field( $_POST['image'] ) : '';
                    $coverfile = ( isset( $_POST['cover_image'] ) && !empty( $_POST['cover_image'] ) ) ? sanitize_text_field( $_POST['cover_image'] ) : '';
                    $wp_filetype = wp_check_filetype( $file, null );
                    $cover_filetype = wp_check_filetype( $coverfile, null );
                    $url = ( isset($_POST['url']) && !empty( $_POST['url'] ) ) ? filter_var( $_POST['url'], FILTER_SANITIZE_URL ) : '' ;

                    if( $wp_filetype['type'] == 'image/jpeg' || $wp_filetype['type'] == 'image/png' ||  $cover_filetype['type'] == 'image/jpeg' || $cover_filetype['type'] == 'image/png'){
                        $youtube_url = preg_match_all('@(https?://)?(?:www\.)?(youtu(?:\.be/([-\w]+)|be\.com/watch\?v=([-\w]+)))\S*@im',$url, $matches);
                        if( $youtube_url == 1 && filter_var( $url, FILTER_VALIDATE_URL ) ){
                            $values ='';
                            if ( preg_match( '/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $ids ) ){
                                $values = $ids[1];
                            }
                            else if ( preg_match( '/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $ids ) ){
                                $values = $ids[1];
                            }
                            else if ( preg_match( '/youtube\.com\/v\/([^\&\?\/]+)/', $url, $ids ) ){
                                $values = $ids[1];
                            }
                            else if ( preg_match( '/youtu\.be\/([^\&\?\/]+)/', $url, $ids ) ){
                                $values = $ids[1];
                            }
                            $link= 'https://www.youtube.com/embed/'.$values ;
                            $result .= $wpdb->query("UPDATE {$wpdb->prefix}wp_neo_ivg_image_category SET `url`='$link',`file_type`='video/mp4' WHERE ID='$imageID'");

                        }
                        else{
                            echo '<h2 style="color: #FF0000;">Please enter youtube video url.</h2>';
                        }
                        $result .= $wpdb->query("DELETE FROM {$wpdb->prefix}neo_ivg_image_category WHERE image_id='$imageID' ");

                        foreach( $_POST['image_category'] as $imageCategory ){
                            $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageID, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                        }
                        foreach($_POST as $key=>$values){
                            $result .= $wpdb->query("UPDATE {$wpdb->prefix}neo_ivg_gallery_images SET $key='$values' WHERE ID='$imageID'");
                        }

                        if($result){
                            wp_redirect( '?page=add_image_class&id="'.absint( $galleryId ).'"' );
                        }
                    }
                    else{
                        echo '<h2 style="color: #FF0000;">Extension not allowed, Please choose a image of .jpeg, .jpg, .png format.</h2>';
                    }

                }
                else{
                    echo '<h2 style="color: #FF0000;">Please select category.</h2>';
                }
            }
            else{
                echo '<h2 style="color: #FF0000;">Please select image.</h2>';
            }
        }
    }

    public function no_items() {
        _e( 'No Gallery available.', 'gp' );
    }

    function column_gallery_name( $item ) {
        $delete_nonce = wp_create_nonce( 'gp_delete' );
        $title = '<strong><a class="gallery-name" href="?page=add_image_class&id='.absint( $item['ID'] ).'">' .esc_attr__(  $item['gallery_name'] ). '</a> </strong>';
        $actions = [
            'edit' => sprintf( '<a href="?page=add_image_class&id='.absint( $item['ID'] ).'">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit' ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&gallery=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
        ];
        return $title . $this->row_actions( $actions );
    }

    /**
     * Define what data to show on each column of the table
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'ID';
            case 'gallery_name':
            case 'shortcode':
                return $item[ $column_name ];
            default:
                return  $item ;  //Show the whole array for troubleshooting purposes
        }
    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', absint( $item['ID'] ) );
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
    */
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'ID'    => __( 'Gallery ID', 'gp' ),
            'gallery_name'    => __( 'Gallery Name', 'gp' ),
            'shortcode' => __('Shortcode', 'gp' ),
        ];
        return $columns;
    }

    /**
     * Define the sortable columns
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'ID' => array( 'GalleryID', true ),
            'gallery_name' => array( 'Gallery Name', false )
        );
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    /**
     * Prepare the items for the table to process
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();
        $per_page     = $this->get_items_per_page( 'gallery_per_page', 15 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();
        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );
        $this->items = self::neo_ivg_get_gallery( $per_page, $current_page );
    }

    /*
     * Process bulk action
     */
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            // In our file that handles the request, verify the nonce.
            if ( ! wp_verify_nonce( $nonce, 'gp_delete' ) ) {
                die( 'Can not delete the gallery' );
            }
            else {
                self::neo_ivg_delete_gallery( absint( $_GET['gallery'] ) );

                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            }
        }
        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {
            $deleteIds = esc_sql( $_POST['bulk-delete'] );
            // loop over the array of record IDs and delete them
            foreach ( $deleteIds as $id ) {
                self::neo_ivg_delete_gallery( absint( $id ) );
            }
            wp_redirect( esc_url( add_query_arg() ) );
            exit;
        }
    }
}
