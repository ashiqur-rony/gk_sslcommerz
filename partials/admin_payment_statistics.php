<?php
/**
 * Template page for SSL Commerz plugin's options
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 **/
?>
<div class="wrap gk-sslcommerz-options">
	<h2><?php _e('Payment Statistics', $this->plugin_slug); ?></h2>
	<?php
		settings_errors();

		if( !empty( $error ) ) {
			echo '<div class="error">';
			foreach( $error as $e ) {
				echo '<p>' . $e. '</p>';
			}
			echo '</div>';
		}

	if( $archive == 1 ):
	?>
		<div class="gk-sslcommerz-instructions notice updated">
			<p>
				<?php _e( 'You are seeing the archived items.', $this->plugin_slug ); ?> <a href="<?php echo admin_url( 'admin.php?page=gk-sslcommerz-payment-statistics' ); ?>"><?php _e( 'Go back to regular list', $this->plugin_slug ); ?></a>.
			</p>
		</div>
	<?php
	else:
	?>
		<div class="gk-sslcommerz-instructions">
			<p class="alignright">
				<a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=gk-sslcommerz-payment-statistics&do=list&archive=1' ); ?>"><?php _e( 'See the archived payments', $this->plugin_slug ); ?></a>
			</p>
		</div>
	<?php
	endif;
	$query = $wpdb->prepare(
			'SELECT * FROM `' . $wpdb->prefix . $gk_sslcommerz_payments_table . '` WHERE
				is_archived = %d
				ORDER BY ' . $order_by . ' ' . $order . '
				LIMIT %d, %d ',
			$archive,
			( ( $page - 1 ) * $limit ), $limit
		);
		$payments = $wpdb->get_results( $query );
		if( $payments ):
	?>
			<table class="payments-table widefat fixed">
				<thead>
					<tr>
						<th>
							<?php _e( 'Transaction ID', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Payment Date', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Payment Detail', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Payment Amount', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Payment Status', $this->plugin_slug ); ?>
						</th>
						<th>
							<?php _e( 'Action', $this->plugin_slug ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$row_count = 1;
					foreach( $payments as $payment ):
						$transaction_id = strtoupper( substr( $this->options['gk_sslcommerz_username'], 0, 3 ) ) . '-' . $payment->idpayment;

						$form_data = unserialize( base64_decode( $payment->form_data ) );
						$submitted_data = unserialize( base64_decode( $payment->submitted_data ) );
						$class = '';
						$row_count % 2 == 0 ? $class = 'even' : $class = 'odd';
						$class .= ' '.$payment->payment_status;
				?>
						<tr class="<?php echo $class; ?>">
							<td>
								<?php echo $transaction_id; ?>
							</td>
							<td>
								<?php echo date( 'd F, Y (h:i a)', strtotime( $payment->payment_date ) ); ?>
							</td>
							<td>
								<?php echo $form_data['gk_sslcommerz_collect_payment_description']; ?>
							</td>
							<td>
								<?php echo $submitted_data['amount'] . $submitted_data['currency']; ?>
							</td>
							<td>
								<?php echo ucfirst( $payment->payment_status ); ?>
							</td>
							<td>
								<?php
									$view_url = admin_url( 'admin.php?page=gk-sslcommerz-payment-statistics&do=invoice&id=' . $payment->idpayment );
									$pdf_url = admin_url( 'admin.php?page=gk-sslcommerz-payment-statistics&do=pdf&id=' . $payment->idpayment );
									$archive_url = admin_url( 'admin.php?page=gk-sslcommerz-payment-statistics&do=archive&id=' . $payment->idpayment );
								?>
								<a href="<?php echo $view_url; ?>" title="<?php _e( 'View detail', $this->plugin_slug ); ?>">
									<span class="dashicons dashicons-analytics"></span>
								</a>
								<a href="<?php echo $archive_url; ?>" title="<?php _e( 'Archive', $this->plugin_slug ); ?>">
									<span class="dashicons dashicons-archive"></span>
								</a>
							</td>
						</tr>
				<?php
						$row_count++;
					endforeach;
				?>
				</tbody>
			</table>


			<div class="tablenav bottom">
				<?php
				$payment_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}{$gk_sslcommerz_payments_table} WHERE is_archived = %d", $archive ) );
				?>
				<div class="tablenav-pages">
					<span class="displaying-num">
						<?php printf( _n( '1 item', '%s items', $payment_count, $this->plugin_slug ), $payment_count ); ?>
					</span>
				<?php
				if( $payment_count > $limit ) {
					?>
					<span class="pagination-links">
					<?php
					$total_pages = ceil( $payment_count / $limit );
					$link_params = $_GET;

					$link_params['p'] = 1;
					$first_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == 1 ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $first_params ) . '" class="' . $disabled . '" title="' . __( 'First page', $this->plugin_slug ) . '">&laquo;</a>';
					$link_params['p'] = max( 1, ( $page - 1 ) );
					$prev_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == 1 ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $prev_params ) . '" class="' . $disabled . '" title="' . __( 'Previous page', $this->plugin_slug ) . '">&lsaquo;</a>';

					echo ' <span class="paging-input">';
					echo $page . ' ' . __( 'of', $this->plugin_slug );
					echo ' <span class="total-pages">' . $total_pages . '</span>';
					echo '</span> ';

					$link_params['p'] = min( $total_pages, ( $page + 1 ) );
					$prev_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == $total_pages ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $prev_params ) . '" class="' . $disabled . '" title="' . __( 'Next page', $this->plugin_slug ) . '">&rsaquo;</a>';
					$link_params['p'] = $total_pages;
					$last_params = http_build_query( $link_params );
					$disabled = '';
					if( $page == $total_pages ) {
						$disabled = ' disabled ';
					}
					echo '<a href="' . admin_url( 'admin.php?' . $last_params ) . '" class="' . $disabled . '" title="' . __( 'Last page', $this->plugin_slug ) . '">&raquo;</a>';

					?>
					</span>
					<?php
				}
				?>
				</div>
			</div>
	<?php
		else:
	?>
			<div class="gk-sslcommerz-msg"><p><?php _e( 'No payment to display!', $this->plugin_slug ); ?></p></div>
	<?php
		endif;
	?>
</div>