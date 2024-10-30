<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once(plugin_dir_path(__FILE__) . '/class-neo-image-video-gallery.php');

class NeoImageVideoGallery_Category extends WP_List_Table
{
    public  $category_object;

    public function __construct() {
        parent::__construct([
            'singular' => __( 'Category', 'cp' ), //singular name of the listed records
            'plural' => __( 'Categories', 'cp' ), //plural name of the listed records
            'ajax' => false //should this table support ajax?

        ]);
    }

    /*
     * Display the list of category
     */
    public  function  category_display(){
        $this->category_object=new NeoImageVideoGallery_Category();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Categories</h1>
            <a href="?page=add_category_class" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content" style="width: 135%;">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->category_object->prepare_items();
                                $this->category_object->display();
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
    <?php
    }

    /*
     * Prepare the items for the table to process
     */
    public function prepare_items()  {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->neo_ivg_category_table_data();
        $perPage = 15;
        $currentPage = $this->get_pagenum();
        $totalItems = count( $data );
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice( $data, ( ($currentPage-1) * $perPage ) ,$perPage );
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $data;
        $this->process_bulk_action();
    }

    function column_cb( $item ) {
        return sprintf(  '<input type="checkbox" name="bulk-delete[]" value="%s" />', absint( $item['ID'] ) );
    }

    /*
     * Override the parent columns method. Defines the columns to use in your listing table
     */
    public function get_columns() {
        $columns = array(
            'cb'                      => '<input type="checkbox" />',
            'ID'                      => 'ID',
            'category_name'           => 'Category Name'
        );
        return $columns;
    }

    /*
     * Define which columns are hidden
     */
    public function get_hidden_columns(){
        return array();
    }

    /*
     * Define the sortable columns
     */
    public function get_sortable_columns(){
        return array( 'ID' => array( 'ID', false ) );
    }

    public function no_items() {
        _e( 'No Category available.', 'cp' );
    }

    function column_category_name( $item ) {
        $delete_nonce = wp_create_nonce( 'cp_delete' );
        $title = '<strong>' . esc_attr__( $item['category_name'] ). ' </strong>';
        $actions = [
            'delete' => sprintf( '<a href="?page=%s&action=%s&category=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
        ];
        return $title . $this->row_actions( $actions );
    }

    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'Delete'
        ];
        return $actions;
    }

    /*
     * Get the category table data
     */
    private function neo_ivg_category_table_data() {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}neo_ivg_category";
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $result;
    }

    /*
     * Delete the category
     */
    public static function neo_ivg_delete_category( $id ) {
        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}neo_ivg_category", ['ID' => $id ], ['%d'] );
    }

    /*
     * Define what data to show on each column of the table
     */
    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'ID':
            case 'category_name':

                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    /*
     * Process bulk action delete
     */
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            // In our file that handles the request, verify the nonce.
            if ( !wp_verify_nonce( $nonce, 'cp_delete' ) ) {
                die( 'Can not delete the category' );
            }
            else {
                self::neo_ivg_delete_category( absint( $_GET['category'] ) );

                wp_redirect( esc_url( add_query_arg() ) );
                exit;
            }
        }
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )  || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {
            $deleteIds = esc_sql( $_POST['bulk-delete'] );
            foreach ( $deleteIds as $id ) {
                self::neo_ivg_delete_category( absint( $id ) );
            }
            wp_redirect( esc_url( add_query_arg() ) );
            exit;
        }
    }

    /*
     * Create new category form
     */
    public function neo_ivg_create_category_form(){
        ?>
        <div class="wrap">
            <h1>Create New Category</h1>
            <div id="create-category" >
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content" >
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post" action="" class="category-form" >
                                <table class="form-table">
                                    <tbody>
                                        <tr class="form-field form-required">
                                            <th scope="row"> <label class="">Category Name</label><span class="description"> (required)</span></th>
                                            <td><input type="text" name="category-name" required style="width: 35%"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="submit ">
                                    <input type="submit" name="category-submit"  class="button button-primary" value="Create Category">
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

    /*
     * save category into database
     */
    public function neo_ivg_save_category(){
        if( isset( $_POST['category-submit'] ) && !empty( $_POST['category-submit'] )){
            global $wpdb;
            $name = ( isset( $_POST['category-name'] ) && !empty( $_POST['category-name'] ) ) ? sanitize_text_field( $_POST['category-name'] ) : ' ';

            $check = $wpdb->get_var( $wpdb->prepare( "SELECT category_name FROM  {$wpdb->prefix}neo_ivg_category WHERE category_name= %s", $name ) );
            if( $check == '' ) {
                $results = $wpdb->insert( "{$wpdb->prefix}neo_ivg_category", array( 'category_name' => $name ), array( '%s' ) );
                if( $results ){
                    wp_redirect( admin_url( 'admin.php?page=category_class' ), 301 );
                }
                else{
                    echo '<h4 style="color: #FF0000;">Something went wrong, Please try again!</h4>';
                }
            }
            else{
                echo '<h4 style="color: #FF0000;">Category already exists</h4>';
            }
        }
    }

    /*
     *  Video form to add video in gallery.
     */
    public function neo_ivg_videoForm(){
        global $wpdb;
        $galleryId = absint( $_GET['id'] );
        $galleryName = $wpdb->get_var( $wpdb->prepare( "SELECT gallery_name FROM  {$wpdb->prefix}neo_ivg_gallery WHERE ID= %d", $galleryId ) );
        echo '<h2>Add New Video to '.esc_attr( $galleryName ).' </h2>';
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
                            <form method="post" action="" class="image-form " enctype="multipart/form-data" style="margin: 20px 0; padding: 0 20px; border: 1px solid;">
                                <table class="form-table">
                                    <tbody>
                                        <tr class="form-field form-required">
                                            <th scope="row"><label class="">Video Name</label><span class="description"> </span> </th>
                                            <td><input type="text" name="title"  style="width:35%;"></td>
                                        </tr>
                                        <tr class="form-field form-required">
                                            <th scope="row"><label class="">Cover Image</label><span class="description"> (required)</span> </th>
                                            <td class="neo_image">
                                                <div class="neo_chosen_image">

                                                </div>
                                                <!--<input type="file" class="file-image" name="cover_image" id="pb-file" required style="width:35%;">-->
                                                <input id="background_image" type="hidden" name="cover_image" value="<?php echo get_option('background_image'); ?>" />
                                                <button type="button" class="file-image" name="image-name" id="pb-file" >Upload Image</button>
                                                <button type="button" id="neo_remove_img">&times;</button>
                                            </td>
                                        </tr>
                                        <tr class="form-field form-required">
                                            <th scope="row"><label class="">Youtube Video URL</label><span class="description">( required)</span> </th>
                                            <td><input type="text" name="url" required style="width:35%;"></td>
                                        </tr>
                                        <tr class="form-field form-required">
                                            <th scope="row"><label class="">Category</label><span class="description">(required)</span></th>
                                            <td>
                                                <div id="category" class="postbox cat" style="width:35%;">
                                                    <div class="inside" style="padding: 0 15px;">
                                                        <div class="categorydiv">
                                                            <ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
                                                                <?php
                                                                foreach( $category as $categoryData ){
                                                                    echo '<li id="" class="popular-category"><input type="checkbox" name="image_category[]" class="justone" value="'.absint( $categoryData['ID'] ).'" >'.esc_attr__( $categoryData['category_name'] ).' </li>';
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
                                    <input type="submit" name="video-submit" class="button button-primary" value="Save Video">
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

    public function neo_ivg_save_video(){
        if( isset( $_POST['video-submit'] ) && !empty( $_POST['video-submit'] ) ) {
            if( isset( $_POST['cover_image' ]) && !empty( $_POST['cover_image'] ) ){
                if ( isset( $_POST['image_category'] ) && !empty( $_POST['image_category'] ) ) {
                    global $wpdb;
                    $result = '';
                    $galleryId = absint( $_GET['id'] ) ;
                    $title = ( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) ? sanitize_text_field( $_POST['title'] ) : '';
                    $url = ( isset($_POST['url']) && !empty( $_POST['url'] ) ) ? filter_var( $_POST['url'], FILTER_SANITIZE_URL ) : '' ;
                    $imageName = ( isset( $_FILES['cover_image' ]) && !empty( $_FILES['cover_image'] ) ) ? sanitize_file_name( $_FILES['cover_image']['name'] ) : '' ;
                    //$cover_image = $upload_url .$imageName;
                    $file = ( isset( $_POST['cover_image'] ) && !empty( $_POST['cover_image'] ) ) ? sanitize_text_field( $_POST['cover_image'] ) : '';
                    $wp_filetype = wp_check_filetype( $file, null );
                    if( $wp_filetype['type'] == 'image/jpeg' || $wp_filetype['type'] == 'image/png'){
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
                            $data = array( 'image'=> ' ', 'cover_image'=> $file, 'title'=> $title, 'url'=> $link, 'file_type'=> 'video/mp4' );
                            $format= array( '%s','%s','%s','%s','%s' );
                            $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery_images", $data, $format );
                            $imageId = $wpdb->insert_id;
                            foreach( $_POST['image_category'] as $imageCategory ){
                                $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageId, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                            }
                        }
                        else{
                            echo '<h2 style="color: #FF0000;">Please enter youtube video url.</h2>';
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
                echo '<h2 style="color: #FF0000;">Please select cover image.</h2>';
            }
        }

        /*if( isset( $_POST['video-submit'] ) && !empty( $_POST['video-submit'] ) ) {
            if ( isset( $_POST['image_category'] ) && !empty( $_POST['image_category'] ) ) {
                global $wpdb;
                $result = '';
                $uploads = wp_upload_dir();
                $upload_path = $uploads['basedir'];
                $upload_url = $uploads['baseurl'];
                $upload_url = $upload_url . '/gallery/';
                $upload_path = $upload_path . '/gallery/';
                if ( !file_exists( $upload_path ) ) {
                    mkdir( $upload_path, 0777, true );
                }
                $galleryId = absint( $_GET['id'] ) ;
                $title = ( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) ? sanitize_text_field( $_POST['title'] ) : '';
                $url = ( isset($_POST['url']) && !empty( $_POST['url'] ) ) ? filter_var( $_POST['url'], FILTER_SANITIZE_URL ) : '' ;
                $imageName = ( isset( $_FILES['cover_image' ]) && !empty( $_FILES['cover_image'] ) ) ? sanitize_file_name( $_FILES['cover_image']['name'] ) : '' ;
                $cover_image = $upload_url .$imageName;

                if( isset( $_FILES['cover_image'] ) ){
                    $fileTmp = ( isset( $_FILES['cover_image' ]) && !empty( $_FILES['cover_image'] ) ) ? $_FILES['cover_image']['tmp_name'] : '';
                    $fileExt = strtolower( end( explode( '.', $imageName) ) );
                    $extensions = array( "jpeg", "jpg", "png" );
                    if ( in_array( $fileExt, $extensions ) === false ) {
                        echo '<h4 style="color: #FF0000;"> Extension not allowed, Please choose a image of .jpeg, .jpg, .png format.</h4>';
                    }
                    else{
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
                            $data = array( 'image'=> ' ', 'cover_image'=> $cover_image, 'title'=> $title, 'url'=> $link, 'file_type'=> 'video/mp4' );
                            $format= array( '%s','%s','%s','%s','%s' );
                            $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_gallery_images", $data, $format );
                            $imageId = $wpdb->insert_id;
                            foreach( $_POST['image_category'] as $imageCategory ){
                                $result .= $wpdb->insert( "{$wpdb->prefix}neo_ivg_image_category", array( 'category_id'=>$imageCategory, 'image_id'=>$imageId, 'gallery_id'=>$galleryId ), array( '%s', '%s', '%s' ) );
                            }
                            move_uploaded_file( $fileTmp , $upload_path . $imageName );
                        }
                        else{
                            echo '<h4 style="color: #FF0000;">Please enter youtube video url.</h4>';
                        }
                    }
                }
            }
            else{
                echo '<h4 style="color: #FF0000;">Category is must.</h4>';
            }
        }*/
    }
}
