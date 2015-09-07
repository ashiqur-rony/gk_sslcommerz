<?php
/**
 * Template page for SSL Commerz plugin's options
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 **/
?>
<div class="wrap gk-sslcommerz-options">
	<h2><?php _e('Archive Payment', $this->plugin_slug); ?></h2>
	<?php
		settings_errors();

		if( !empty( $error ) ) {
			echo '<div class="error">';
			foreach( $error as $e ) {
				echo '<p>' . $e. '</p>';
			}
			echo '</div>';
		}

		$query = $wpdb->prepare(
			'SELECT * FROM `' . $wpdb->prefix . $gk_sslcommerz_payments_table . '` WHERE
				is_archived = %d
				AND
				idpayment = %d ',
			0,
			$payment_id
		);
		$payment = $wpdb->get_row( $query );

		if( $payment ) :
			$transaction_id = strtoupper( substr( $this->options['gk_sslcommerz_username'], 0, 3 ) ) . '-' . $payment->idpayment;

			$wpdb->update(
				$wpdb->prefix . $gk_sslcommerz_payments_table,
				array(
					'is_archived' => 1
				),
				array(
					'idpayment' => $payment_id
				),
				array(
					'%d'
				),
				array(
					'%d'
				)
			);
	?>
			<div class="gk-sslcommerz-instructions notice updated"><p><?php printf( __( 'Payment %s from %s has been archived.', $this->plugin_slug ), $transaction_id, date('d F, Y h:iA', strtotime( $payment->payment_date ) ) ); ?></p></div>
	<?php
		else:
	?>
			<div class="gk-sslcommerz-msg"><p><?php _e( 'Payment details was not found!', $this->plugin_slug ); ?></p></div>
	<?php
		endif;
	?>
</div>