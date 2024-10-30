<?php
    /******
     * Plugin Name: Image & Video Gallery with categories
     * Description: Team NEO glad to launch image & video gallery plugin. This plugin will help the WordPress users to achieve a perfect gallery for their website.We have tested the plugin on different platforms and browsers.
     * version: 1.0.2
     * Author: NEOIITS Technologies Pvt. Ltd.
     *  Author URI: http://neoiits.com/
     */

     /*
        This program is free software; you can redistribute it and/or
        modify it under the terms of the GNU General Public License
        as published by the Free Software Foundation; either version 2
        of the License, or (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

        Copyright 2020 NEOIITS Technology Pvt. Ltd.
    */

    add_action('init', 'do_output_buffer');
    function do_output_buffer() {
       ob_start();
    }

    define( 'NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN', __FILE__ );

    define( 'NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN_BASENAME', plugin_basename( NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN ) );

    define( 'NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN_NAME', trim( dirname( NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN_BASENAME ), '/' ) );

    define( 'NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN_DIR', untrailingslashit( dirname( NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN ) ) );

    define( 'NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN_URL', untrailingslashit( plugins_url( '', NEO_IMAGEVIDEOGALLERYWITHCATEGORIES_PLUGIN ) ) );

    if ( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    global $wpdb;
    $tableName=$wpdb->prefix.'neo_ivg_gallery';
    if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {            //table not in database. Create new table
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tableName (ID INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, gallery_name VARCHAR(100) NOT NULL, shortcode VARCHAR(300) NULL) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    $tableName=$wpdb->prefix.'neo_ivg_gallery_images';
    if( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tableName (ID INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, image VARCHAR(5000) NULL, cover_image VARCHAR (5000) NULL ,title VARCHAR(100) NULL, url VARCHAR(5000) NULL , file_type VARCHAR(100) NULL ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    $tableName = $wpdb->prefix . 'neo_ivg_category';
    if ( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tableName (ID INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_name VARCHAR(300) NOT NULL) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }

    $tableName = $wpdb->prefix . 'neo_ivg_image_category';
    if ( $wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName ) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tableName (ID INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT(10) NOT NULL, image_id INT(10) NOT NULL,gallery_id INT(10) NOT  NULL ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }

    include( plugin_dir_path(__FILE__).'/class-neo-image-video-gallery-plugin.php');
    include(plugin_dir_path(__FILE__).'/template/neo-image-video-gallery-shortcode.php');
    include(plugin_dir_path(__FILE__).'/template/function.php');