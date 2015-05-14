<?php
/**
 * Template page for SSL Commerz plugin's options
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 **/
?>
<div class="wrap gk-sslcommerz-options">
	<h2><?php _e('Payment Details', $this->plugin_slug); ?></h2>
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
		if( $payment ):
			$form_data = unserialize( base64_decode( $payment->form_data ) );
			$submitted_data = unserialize( base64_decode( $payment->submitted_data ) );
			$validation_data = unserialize( base64_decode( $payment->validation_data ) );

			$content = '';

			if( strlen( $validation_data['error'] ) > 0 ) {
				$content .= '<div class="gk-sslcommerz-instructions notice error"><p>' . nl2br( $validation_data['error'] ) . '</p></div>';
			} elseif( $payment->payment_status == 'success' ) {
				$content .= '<div class="gk-sslcommerz-instructions notice updated"><p>' . __( 'Payment was successful', $this->plugin_slug ) . '</p></div>';
			} else {
				$content .= '<div class="gk-sslcommerz-instructions notice warning"><p>' . __( 'Payment status: ' . $payment->payment_status, $this->plugin_slug ) . '</p></div>';
			}

			$content .= '<table class="gk-sslcommerz-invoice gk-sslcommerz-address">';
			$content .= '<tbody>';
			$content .= '<tr>';
			$content .= '<th>';
			$content .= '<strong>' . __( 'Payment date', $this->plugin_slug ) . '</strong>';
			$content .= '</th>';
			$content .= '<td>';
			$content .= date('d F, Y h:iA', strtotime( $payment->payment_date ) );
			$content .= '</td>';
			$content .= '</tr>';

			if( isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1 && !empty( $form_data['gk_sslcommerz_customer_info'] ) ) {
				$customer_info = $form_data['gk_sslcommerz_customer_info'];
				$content .= '<tr>';
				$content .= '<th>';
				$content .= __( 'Billing details', $this->plugin_slug );
				$content .= '</th>';
				$content .= '<td>';
				$content .= $customer_info['full_name'];
				$content .= '<br />' . $customer_info['email_address'];
				$content .= '<br />' . $customer_info['phone_number'];
				$content .= '<br />' . nl2br( $customer_info['billing_address'] );
				$content .= '</td>';
				$content .= '</tr>';
			}
			$content .= '</tbody>';
			$content .= '</table>';

			$payment_description = isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? __($this->options['gk_sslcommerz_default_payment_description'], $this->plugin_slug) : '';
			$payment_description = isset( $form_data['gk_sslcommerz_collect_payment_description'] ) ? esc_attr( $form_data['gk_sslcommerz_collect_payment_description'] ) : $payment_description;

			$content .= '<table class="gk-sslcommerz-invoice gk-sslcommerz-payment">';
			$content .= '<thead>';
			$content .= '<tr>';
			$content .= '<th>';
			$content .= __('Payment detail', $this->plugin_slug);
			$content .= '</th>';
			$content .= '<th align="right">';
			$content .= __('Amount', $this->plugin_slug);
			$content .= '</th>';
			$content .= '</tr>';
			$content .= '</thead>';
			$content .= '<tbody>';
			$content .= '<tr>';
			$content .= '<td>';
			$content .= $payment_description;
			$content .= '</td>';
			$content .= '<td align="right">';
			$content .= number_format( ( isset( $submitted_data['amount'] ) ? $submitted_data['amount'] : 0 ), 2 ) . ( isset( $submitted_data['currency'] ) ? $submitted_data['currency'] : 'BDT' );
			$content .= '</td>';
			$content .= '</tr>';

			if( $submitted_data['service_charge'] > 0 ) {
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __( $submitted_data['service_charge_label'], $this->plugin_slug );
				$content .= '</td>';
				$content .= '<td align="right">';
				$content .= number_format( $submitted_data['service_charge'], 2 ) . ( isset( $submitted_data['currency'] ) ? $submitted_data['currency'] : 'BDT' );
				$content .= '</td>';
				$content .= '</tr>';
			}
			$content .= '</tbody>';
			$content .= '<tfoot>';
			$content .= '<tr>';
			$content .= '<td>';
			$content .= __('Total', $this->plugin_slug);
			$content .= '</td>';
			$content .= '<td align="right">';
			$content .= number_format( ( isset( $submitted_data['total'] ) ? $submitted_data['total'] : 0 ), 2 ) . ( isset( $submitted_data['currency'] ) ? $submitted_data['currency'] : 'BDT' );
			$content .= '</td>';
			$content .= '</tr>';
			$content .= '<tr>';
			$content .= '</tfoot>';
			$content .= '</table>';

			print $content;

		else:
	?>
			<div class="gk-sslcommerz-msg"><p><?php _e( 'Payment details was not found!', $this->plugin_slug ); ?></p></div>
	<?php
		endif;
	?>
</div>