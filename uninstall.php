<?php
if (! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}neo_ivg_gallery" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}neo_ivg_gallery_images" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}neo_ivg_category" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}neo_ivg_image_category" );



