<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
include( plugin_dir_path(__FILE__).'/class-neo-image-video-gallery.php');

class NeoImageVideoGallery_Plugin{

    static $instance;
    public $gallery_obj;
    public $options;
    public  $category_obj;

    /*
     * Constructor will create menu
     */

    public function __construct() {
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action('admin_enqueue_scripts', [$this, 'load_admin_css'] );
    }

    function load_admin_css(){
        // Unfortunately we can't just enqueue our scripts here - it's too early. So register against the proper action hook to do it
        wp_enqueue_style( 'neo_load_admin_custom_style' , plugins_url('css/custom.css', __FILE__));
        wp_enqueue_media();
        wp_enqueue_script('my_custom_script', plugins_url('js/admin.js', __FILE__));
    }


    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    public function register_menu() {
        $icon_url = plugin_dir_url( __FILE__ ) . 'img/Background.png';
        $hook = add_menu_page(
            'Image & Video Gallery',
            'Image & Video Gallery',
            'manage_options',
            'gallery_table_class',
            [ $this, 'neo_ivg_gallery_page' ],
            $icon_url
        );
        $mainMenu = add_submenu_page(
            'gallery_table_class',
            'Gallery List',
            'Gallery List',
            'manage_options',
            'gallery_table_class',
            [$this,'neo_ivg_gallery_page']
        );
        $menu = add_submenu_page(
            'NULL',
            'New Gallery',
            'New Gallery',
            'manage_options',
            'add_new_class',
            [$this,'neo_ivg_add_new_gallery']
        );
        $subMenu = add_submenu_page(
            'NULL',
            'Add Image',
            'Add Image',
            'manage_options',
            'add_image_class',
            [$this,'neo_ivg_add_image']
        );
        $subMenu1 = add_submenu_page(
            'NULL',
            'Add Video',
            'Add Video',
            'manage_options',
            'add_video_class',
            [$this,'neo_ivg_add_video']
        );
        $delSubMenu = add_submenu_page(
            'NULL',
            'Image',
            'Delete Image',
            'manage_options',
            'delete_image_class',
            [$this,'neo_ivg_delete_image']
        );
        $editSubMenu = add_submenu_page(
            'NULL',
            'Image',
            'Edit Image',
            'manage_options',
            'edit_image_class',
            [$this,'neo_ivg_edit_image']
        );
        $catMenu = add_submenu_page(
            'gallery_table_class',
            'Category',
            'Category',
            'manage_options',
            'category_class',
            [$this,'neo_ivg_category']
        );
        $catSubMenu = add_submenu_page(
            'NULL',
            'New Category',
            'Add Category',
            'manage_options',
            'add_category_class',
            [$this,'neo_ivg_add_new_category']
        );
        $settingMenu =add_submenu_page(
            'gallery_table_class',
            'Help',
            'Help',
            'manage_options',
            'help_class',
            [$this,'neo_ivg_settings']
        );
        add_action( "load-$hook", [ $this, 'screen_option' ] );
        add_action( "load-$mainMenu", [ $this, 'screen_option' ] );
        add_action( "load-$menu", [ $this, 'screen_option' ] );
        add_action( "load-$subMenu", [ $this, 'screen_option' ] );
        add_action( "load-$subMenu1", [ $this, 'screen_option' ] );
        add_action( "load-$catMenu", [ $this, 'screen_option' ] );
        add_action( "load-$catSubMenu", [ $this, 'screen_option' ] );
        add_action( "load-$settingMenu", [ $this, 'screen_option' ] );
        add_action( "load-$delSubMenu", [ $this, 'screen_option' ] );
        add_action( "load-$editSubMenu", [ $this, 'screen_option' ] );
    }

    public function screen_option() {

        $option = 'per_page';
        $args   = [
            'label'   => 'Gallery',
            'default' => 15,
            'option'  => 'gallery_per_page'
        ];

        add_screen_option( $option, $args );
        $this->gallery_obj = new NeoImageVideoGallery();
    }

    public  function  neo_ivg_gallery_page(){
    ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Galleries</h1>
            <a href="?page=add_new_class" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <div id="poststuff" >
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content" style="width: 135%;">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->gallery_obj->prepare_items();
                                $this->gallery_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
    <?php
    }

    public function neo_ivg_add_new_gallery(){
        $this->gallery_obj->neo_ivg_create_gallery_form();
        $this->gallery_obj->neo_ivg_submit_gallery_form();
    }

    public function neo_ivg_add_image(){
        $this->gallery_obj->neo_ivg_image_form();
        $this->gallery_obj->neo_ivg_save_image();
        $this->gallery_obj->neo_ivg_image_display();
    }

    public function neo_ivg_add_video(){
        $this->category_obj=new NeoImageVideoGallery_Category();
        $this->category_obj->neo_ivg_videoForm();
        $this->category_obj->neo_ivg_save_video();
        $this->gallery_obj->neo_ivg_image_display();
    }

    public function neo_ivg_delete_image(){
        $this->gallery_obj->neo_ivg_delete_gallery_image();
    }

    public function neo_ivg_edit_image(){
        $this->gallery_obj->neo_ivg_edit_gallery_image();
        $this->gallery_obj->neo_ivg_save_edited_image();
    }

    public function neo_ivg_category(){
        $this->category_obj=new NeoImageVideoGallery_Category();
        $this->category_obj->category_display();
    }

    public function neo_ivg_add_new_category(){
        $this->category_obj=new NeoImageVideoGallery_Category();
        $this->category_obj->neo_ivg_create_category_form();
        $this->category_obj->neo_ivg_save_category();
    }

    public function neo_ivg_settings(){
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Help / Shortcodes</h1>
            <div id="poststuff" >
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content" style="width: 135%;">
                        <div class="meta-box-sortables ui-sortable">

                            <table id="neo_help">
                                <tbody>
                                <tr>
                                    <td>[gallery_view_with_category id='<gallery_id>']</td>
                                    <td>Use this shortcode to display image gallery with category filter. Replace <gallery_id> with your gallery id.</td>
                                </tr>
                                <tr>
                                    <td>[gallery_view_without_category id='<gallery_id>']</td>
                                    <td>Use this shortcode to display image gallery without category filter. Replace <gallery_id> with your gallery id.</td>
                                </tr>
                                </tbody>
                            </table>
                            <p><strong>* You can get gallery_id from the Gallery List</strong></p>
                            <p><strong>For support and queries please contact: <a href="mailto:support@neoiits.com">support@neoiits.com</a></strong></p>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
       <?php
    }

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}

add_action( 'plugins_loaded', function () {
    NeoImageVideoGallery_Plugin::get_instance();
} );