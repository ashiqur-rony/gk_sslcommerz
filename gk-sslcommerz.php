<?php
/*
 * Plugin Name:       SSLCommerz Payment Gateway
 * Plugin URI:        https://github.com/goodkoding/gk_sslcommerz
 * Description:       Integrate the SSLCommerz payment gateway to WordPress website.
 * Version:           0.3
 * Author:            GoodKoding
 * Author URI:        http://goodkoding.com
 * Text Domain:       gk-sslcommerz
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
if ( ! defined( 'WPINC' ) ) {
	die('Are you supposed to be here?');
}

/**
 * Load the default settings
 */
require_once 'includes/gk-sslcommerz-settings.php';

/**
 * Hook plugin activation
 */
register_activation_hook( __FILE__, 'activate_gk_sslcommerz' );
function activate_gk_sslcommerz() {
	require_once plugin_dir_path( __FILE__ ) . 'class/gk-sslcommerz-activator.php';
	gk_sslcommerz_activator::activate();
}

/**
 * Hook plugin deactivation
 */
register_deactivation_hook( __FILE__, 'deactivate_gk_sslcommerz' );
function deactivate_gk_sslcommerz() {
	require_once plugin_dir_path( __FILE__ ) . 'class/gk-sslcommerz-deactivator.php';
	gk_sslcommerz_deactivator::deactivate();
}

/**
 * Hook to check plugin updates
 */
add_action( 'plugins_loaded', 'gk_sslcommerz_update_db_check' );
function gk_sslcommerz_update_db_check() {
	global $gk_sslcommerz_db_version;
	if ( get_site_option( 'gk_sslcommerz_db_version' ) != $gk_sslcommerz_db_version ) {
		activate_gk_sslcommerz();
	}
}

/**
 * Load the plugin and start the magic!
 */
function gk_sslcommerz_start() {
	require_once plugin_dir_path( __FILE__ ) . 'class/gk-sslcommerz.php';
	$gksslcommerz = new gk_sslcommerz();
	$gksslcommerz->run();
}
gk_sslcommerz_start();