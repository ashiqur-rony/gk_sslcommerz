<?php
/**
 * Uninstalls the plugin
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit('Are you supposed to be here?');

require_once 'includes/gk-sslcommerz-settings.php';

delete_option( 'gk_sslcommerz_info' );
delete_site_option( 'gk_sslcommerz_info' );
delete_option( 'gk_sslcommerz_db_version' );
delete_site_option( 'gk_sslcommerz_db_version' );

//drop a custom db table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}$gk_sslcommerz_payments_table" );