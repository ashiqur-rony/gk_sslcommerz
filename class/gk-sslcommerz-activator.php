<?php
/**
 * Class to handle SSLCommerz gateway plugin activation
 *
 * @author GoodKoding
 * @author_url http://goodkoding.com
 **/

class gk_sslcommerz_activator {
	public static function  activate() {

		global $wpdb, $gk_sslcommerz_db_version, $gk_sslcommerz_payments_table;

		if(!isset($gk_sslcommerz_payments_table)) {
			return false;
		}

		$installed_version = get_option( "gk_sslcommerz_db_version" );
		if ( $installed_version == $gk_sslcommerz_db_version ) {
			return true;
		}

		$table_name = $wpdb->prefix . $gk_sslcommerz_payments_table;
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * idpayment, int, primary key
		 * idform, int, reference to form
		 * form_data, text, submitted form data
		 * submitted_data, text, data sent to sslcommerz
		 * payment_data, text, return data from sslcommerz
		 * validation_data, text, return data from payment validation from sslcommerz
		 * payment_date, datetime, form submission time
		 * validation_date, datetime, payment validation time
		 * edit_date, datetime, time of manually validating a payment
		 * edited_by, int, user id of editing user
		 * payment_status, enum, [pending, cancelled, failed, success, unknown]
		 */

		$sql = "CREATE TABLE $table_name (
					idpayment bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					idform bigint(20) UNSIGNED DEFAULT 0 NOT NULL,
					form_data text NOT NULL,
					submitted_data text NOT NULL,
					payment_data text,
					validation_data text,
					payment_date datetime NOT NULL,
					validation_date datetime,
					edit_date datetime,
					edited_by bigint(20) UNSIGNED,
					payment_status enum('pending', 'cancelled', 'failed', 'success', 'unknown') DEFAULT 'pending' NOT NULL,
					is_archived tinyint(2) NOT NULL DEFAULT '0',
					PRIMARY KEY (idpayment)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'gk_sslcommerz_db_version', $gk_sslcommerz_db_version );
	}
}