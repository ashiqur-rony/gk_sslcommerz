<?php
/*
 * Plugin Name:       SSLCommerz Payment Gateway
 * Plugin URI:        https://github.com/goodkoding/gk_sslcommerz
 * Description:       Integrate the SSLCommerz payment gateway to WordPress website.
 * Version:           0.1
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
	gk_sslcommerz_deactivator::activate();
}