<?php
/**
 * Class to handle SSLCommerz gateway integration
 *
 * @author GoodKoding
 * @author_url http://ghumkumar.com
 **/

class gk_sslcommerz_pages {
	private $version;
	private $plugin_slug;
	private $slug;
	private $options;

	public function __construct( $version, $plugin_slug ) {
		$this->version = $version;
		$this->plugin_slug = $plugin_slug;
	}

	public function initiate_pages() {
		if( get_option( 'permalink_structure' ) ) {
			$param = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
		} else {
			parse_str( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ), $params );
			$param = ( isset($params['page_id'] ) ? $params['page_id'] : false );
		}
		if( in_array( $param, array( 'gk-sslcommerz-cart', 'gk-sslcommerz-success', 'gk-sslcommerz-fail', 'gk-sslcommerz-cancel' ) ) ) {
			$this->slug = $param;
			$handler = str_replace('-', '_', $param);

			if( method_exists( $this, $handler ) ) {
				add_filter( 'the_posts', array( $this, $handler ) );
			} else {
				throw new Exception( __('Invalid page handler provided!', $this->plugin_slug) );
			}
		}
	}

	public function gk_sslcommerz_success( $posts ) {
		global $wp, $wp_query, $wpdb, $gk_sslcommerz_payments_table;

		if ( ( strcasecmp( $wp->request, $this->slug ) == 0 || $wp->query_vars['page_id'] == $this->slug ) )
		{
			$this->options = get_option( 'gk_sslcommerz_info' );

			$payment_status = 'unknown';
			$tran_id = isset( $_POST['tran_id'] ) ? esc_attr( $_POST['tran_id'] ) : null;

			if($tran_id !== null && strlen($tran_id) > 0) {
				$payment_data = array_map( 'esc_attr', $_POST );
				$payment_id = substr( $tran_id, 4, strlen( $tran_id ) );
				$val_id = isset( $_POST['val_id'] ) ? esc_attr( $_POST['val_id'] ) : '';
				$amount = isset( $_POST['amount'] ) ? esc_attr( $_POST['amount'] ) : 0;
				$store_amount = isset( $_POST['store_amount'] ) ? esc_attr( $_POST['store_amount'] ) : 0;
				$card_type = isset( $_POST['card_type'] ) ? esc_attr( $_POST['card_type'] ) : '';
				$card_no = isset( $_POST['card_no'] ) ? esc_attr( $_POST['card_no'] ) : '';

				$payment_query = $wpdb->prepare(
					'SELECT * FROM `' . $wpdb->prefix . $gk_sslcommerz_payments_table . '`
						WHERE
						idpayment = "%d"',
					intval($payment_id)
				);
				$payment = $wpdb->get_row( $payment_query );
				$form_data = unserialize( base64_decode( $payment->form_data ) );
				$submitted_data = unserialize( base64_decode( $payment->submitted_data ) );
                
				if($submitted_data['total'] == $amount) {

					$username = $this->options['gk_sslcommerz_username'];
					$password = $this->options['gk_sslcommerz_password'];
					$testbox = ( isset( $this->options['gk_sslcommerz_testbox'] ) && $this->options['gk_sslcommerz_testbox'] == 1 ) ? true : false;
					$url = $testbox ? 'https://www.sslcommerz.com.bd/validator/api/testbox/validationserverAPI.php' : 'https://www.sslcommerz.com.bd/validator/api/validationserverAPI.php';

					$val_id = urlencode( $val_id );
					$store_id = urlencode( $username );
					$store_passwd=urlencode( $password );
					$requested_url = ($url . "?val_id=".$val_id."&Store_Id=".$store_id."&Store_Passwd=".$store_passwd."&v=1&format=json");

					$handle = curl_init();
					curl_setopt($handle, CURLOPT_URL, $requested_url);
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
					$ssl_response = curl_exec($handle);
					$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

					if( $code == 200 && !( curl_errno( $handle ) ) ) {
						$result = json_decode( $ssl_response );
						$status = $result->status;
						$tran_date = $result->tran_date;
						$tran_id = $result->tran_id;
						$val_id = $result->val_id;
						$amount = $result->amount;
						$store_amount = $result->store_amount;
						$bank_tran_id = $result->bank_tran_id;
						$card_type = $result->card_type;
						$card_no = $result->card_no;
						$card_issuer = $result->card_issuer;
						$card_brand = $result->card_brand;
						$card_issuer_country = $result->card_issuer_country;
						$card_issuer_country_code = $result->card_issuer_country_code;

						$apiconnect = $result->APIConnect;
						$validated_on = $result->validated_on;
						$gw_version = $result->gw_version;

						if( in_array( strtoupper( $apiconnect ), array( 'INVALID_REQUEST', 'FAILED', 'INACTIVE' ) ) ) {
							$payment_status = 'failed';
						} elseif( in_array( strtoupper( $status ), array( 'INVALID_TRANSACTION' ) ) ) {
							$payment_status = 'failed';
						} elseif( in_array( strtoupper( $status ), array( 'VALIDATED', 'VALID' ) ) ) {
							$payment_status = 'success';
						} else {
							$payment_status = 'unknown';
						}

						$validation_data = json_decode( $ssl_response, true );
					} else {
						$validation_data = array(
							'error' => 'Payment was successful. Could not connect to validation server!'
						);
					}

					$wpdb->update(
						$wpdb->prefix . $gk_sslcommerz_payments_table,
						array(
							'payment_data' => base64_encode( serialize( $payment_data ) ),
							'validation_data' => base64_encode( serialize( $validation_data ) ),
							'validation_date' => date( 'Y-m-d H:i:s' ),
							'payment_status' => $payment_status
						),
						array(
							'idpayment' => intval( $payment_id )
						),
						array(
							'%s',
							'%s',
							'%s',
							'%s'
						),
						array(
							'%d'
						)
					);
					/**
					 * @ToDo: Send Email
					 */
				}
			}

			$success_page_id = $this->options['gk_sslcommerz_success_page'];
			if( $success_page_id && $success_page_id > 0 ) {
				wp_safe_redirect( get_permalink( $success_page_id ) );
				exit;
			}

			/**
			 * Generate the invoice based on the payment information
			 */

			$content = '';
			$validation_message = '';
			if( isset( $validation_data['error'] ) ) {
				$validation_message = __( $validation_data['error'], $this->plugin_slug );
			} else {
				if( $payment_status != 'success' ) {
					$validation_message = __( 'Some error validating the payment! Please contact the administrator to validate the payment manually.', $this->plugin_slug );
				}
			}

			if( strlen( $validation_message ) > 0 ) {
				$content .= '<div class="gk-sslcommerz-instructions message error">'.nl2br( $validation_message ).'</div>';
			}

			if( isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1 && !empty( $form_data['gk_sslcommerz_customer_info'] ) ) {
				$customer_info = $form_data['gk_sslcommerz_customer_info'];
				$content .= '<table class="gk-sslcommerz-address">';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __( 'Billing details', $this->plugin_slug );
				$content .= '</td>';
				$content .= '<td>';
				$content .= $customer_info['full_name'];
				$content .= '<br />' . $customer_info['email_address'];
				$content .= '<br />' . $customer_info['phone_number'];
				$content .= '<br />' . nl2br( $customer_info['billing_address'] );
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '</tbody>';
				$content .= '</table>';
			}

			$payment_description = isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? __($this->options['gk_sslcommerz_default_payment_description'], $this->plugin_slug) : '';
			$payment_description = isset( $form_data['gk_sslcommerz_collect_payment_description'] ) ? esc_attr( $form_data['gk_sslcommerz_collect_payment_description'] ) : $payment_description;

			$content .= '<table class="gk-sslcommerz-cart">';
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

			$post = new stdClass;
			$post->ID = -1;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt = current_time('mysql', 1);
			$post->post_content = $content;
			$post->post_title = __('Payment complete', $this->plugin_slug);
			$post->post_excerpt = '';
			$post->post_status = 'publish';
			$post->comment_status = 'closed';
			$post->ping_status = 'closed';
			$post->post_password = '';
			$post->post_name = $this->slug;
			$post->to_ping = '';
			$post->pinged = '';
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_content_filtered = '';
			$post->post_parent = 0;
			$post->guid = get_home_url('/' . $this->slug);
			$post->menu_order = 0;
			$post->post_tyle = 'page';
			$post->post_mime_type = '';
			$post->comment_count = 0;

			// set filter results
			$posts = array($post);

			// reset wp_query properties to simulate a found page
			$wp_query->is_page = TRUE;
			$wp_query->is_singular = TRUE;
			$wp_query->is_home = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;
			unset($wp_query->query['error']);
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404 = FALSE;
		}
        
        return ($posts);
	}

	public function gk_sslcommerz_fail( $posts ) {
		global $wp, $wp_query, $wpdb, $gk_sslcommerz_payments_table;

		if ( ( strcasecmp( $wp->request, $this->slug ) == 0 || $wp->query_vars['page_id'] == $this->slug ) )
		{
			$this->options = get_option( 'gk_sslcommerz_info' );

			$payment_status = 'failed';
			$tran_id = isset( $_POST['tran_id'] ) ? esc_attr( $_POST['tran_id'] ) : null;

			if($tran_id !== null && strlen($tran_id) > 0) {
				$payment_data = array_map( 'esc_attr', $_POST );
				$payment_id = substr( $tran_id, 4, strlen( $tran_id ) );
                
                $payment_query = $wpdb->prepare(
					'SELECT * FROM `' . $wpdb->prefix . $gk_sslcommerz_payments_table . '`
						WHERE
						idpayment = "%d"',
					intval($payment_id)
				);
				$payment = $wpdb->get_row( $payment_query );
				$form_data = unserialize( base64_decode( $payment->form_data ) );
				$submitted_data = unserialize( base64_decode( $payment->submitted_data ) );
                
				$validation_data = array(
					'error' => ( isset( $_POST['error'] ) ? esc_attr( $_POST['error'] ) : 'Payment returned to fail page' )
				);

				$wpdb->update(
					$wpdb->prefix . $gk_sslcommerz_payments_table,
					array(
						'payment_data' => base64_encode( serialize( $payment_data ) ),
						'validation_data' => base64_encode( serialize( $validation_data ) ),
						'validation_date' => date( 'Y-m-d H:i:s' ),
						'payment_status' => $payment_status
					),
					array(
						'idpayment' => intval( $payment_id )
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s'
					),
					array(
						'%d'
					)
				);
			}

			$fail_page_id = $this->options['gk_sslcommerz_fail_page'];
			if( $fail_page_id && $fail_page_id > 0 ) {
				wp_safe_redirect( get_permalink( $fail_page_id ) );
				exit;
			}

			/**
			 * Generate post with invoice template
			 */

			/**
			 * Generate the invoice based on the payment information
			 */

			$content = '';
			$validation_message = '';
			if( isset( $_POST['error'] ) ) {
				$validation_message = __( $validation_data['error'], $this->plugin_slug );
			} else {
				if( $payment_status != 'success' ) {
					$validation_message = __( 'Could not complete the payment.', $this->plugin_slug );
				}
			}

			if( strlen( $validation_message ) > 0 ) {
				$content .= '<div class="gk-sslcommerz-instructions message error">'.nl2br( $validation_message ).'</div>';
			}

			if( isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1 && !empty( $form_data['gk_sslcommerz_customer_info'] ) ) {
				$customer_info = $form_data['gk_sslcommerz_customer_info'];
				$content .= '<table class="gk-sslcommerz-address">';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __( 'Billing details', $this->plugin_slug );
				$content .= '</td>';
				$content .= '<td>';
				$content .= $customer_info['full_name'];
				$content .= '<br />' . $customer_info['email_address'];
				$content .= '<br />' . $customer_info['phone_number'];
				$content .= '<br />' . nl2br( $customer_info['billing_address'] );
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '</tbody>';
				$content .= '</table>';
			}

			$payment_description = isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? __($this->options['gk_sslcommerz_default_payment_description'], $this->plugin_slug) : '';
			$payment_description = isset( $form_data['gk_sslcommerz_collect_payment_description'] ) ? esc_attr( $form_data['gk_sslcommerz_collect_payment_description'] ) : $payment_description;

			$content .= '<table class="gk-sslcommerz-cart">';
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

			$post = new stdClass;
			$post->ID = -1;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt = current_time('mysql', 1);
			$post->post_content = $content;
			$post->post_title = __('Payment failed', $this->plugin_slug);
			$post->post_excerpt = '';
			$post->post_status = 'publish';
			$post->comment_status = 'closed';
			$post->ping_status = 'closed';
			$post->post_password = '';
			$post->post_name = $this->slug;
			$post->to_ping = '';
			$post->pinged = '';
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_content_filtered = '';
			$post->post_parent = 0;
			$post->guid = get_home_url('/' . $this->slug);
			$post->menu_order = 0;
			$post->post_tyle = 'page';
			$post->post_mime_type = '';
			$post->comment_count = 0;

			// set filter results
			$posts = array($post);

			// reset wp_query properties to simulate a found page
			$wp_query->is_page = TRUE;
			$wp_query->is_singular = TRUE;
			$wp_query->is_home = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;
			unset($wp_query->query['error']);
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404 = FALSE;
		}
        
        return ($posts);
	}

	public function gk_sslcommerz_cancel( $posts ) {
		global $wp, $wp_query, $wpdb, $gk_sslcommerz_payments_table;

		if ( strcasecmp( $wp->request, $this->slug ) == 0 || $wp->query_vars['page_id'] == $this->slug ) {

			$this->options = get_option( 'gk_sslcommerz_info' );

			$payment_status = 'cancelled';
			$tran_id = isset( $_POST['tran_id'] ) ? esc_attr( $_POST['tran_id'] ) : null;

			if($tran_id !== null && strlen($tran_id) > 0) {
				$payment_data    = array_map( 'esc_attr', $_POST );
				$payment_id      = substr( $tran_id, 4, strlen( $tran_id ) );
				$validation_data = array(
					'error' => ( isset( $_POST['error'] ) ? esc_attr( $_POST['error'] ) : 'User cancelled payment' )
				);

				$wpdb->update(
					$wpdb->prefix . $gk_sslcommerz_payments_table,
					array(
						'payment_data' => base64_encode( serialize( $payment_data ) ),
						'validation_data' => base64_encode( serialize( $validation_data ) ),
						'validation_date' => date( 'Y-m-d H:i:s' ),
						'payment_status' => $payment_status
					),
					array(
						'idpayment' => intval( $payment_id )
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s'
					),
					array(
						'%d'
					)
				);
			}

			$cancel_page_id = $this->options['gk_sslcommerz_cancel_page'];
			if( $cancel_page_id && $cancel_page_id > 0 ) {
				wp_safe_redirect( get_permalink( $cancel_page_id ) );
				exit;
			}
			wp_safe_redirect( home_url() );
			exit;
		}
        
        return ($posts);
	}

	public function gk_sslcommerz_cart( $posts ) {
		global $wp, $wp_query, $wpdb, $gk_sslcommerz_payments_table;

		if ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' &&
		    ( strcasecmp( $wp->request, $this->slug ) == 0 || $wp->query_vars['page_id'] == $this->slug ) )
		{

			$key = sanitize_text_field( $_POST['_gk-sslcommerz-key'] );
			if( !wp_verify_nonce( $_POST['_gk-sslcommerz-nonce'], 'gk-sslcommerz-shortcode' . $key ) ) {
				throw new Exception( __('Form validation failed!', $this->plugin_slug) );
			}

			wp_register_style('gk-sslcommerz-pages', plugins_url( '../css/pages.css', __FILE__ ));
			wp_enqueue_style('gk-sslcommerz-pages');

			$content = '';

			$this->options = get_option( 'gk_sslcommerz_info' );

			$username = $this->options['gk_sslcommerz_username'];
			$url = $this->options['gk_sslcommerz_url'];
			$testbox = ( isset( $this->options['gk_sslcommerz_testbox'] ) && $this->options['gk_sslcommerz_testbox'] == 1 ) ? true : false;

			$success_url = site_url( 'gk-sslcommerz-success' );
			$fail_url = site_url( 'gk-sslcommerz-fail' );
			$cancel_url = site_url( 'gk-sslcommerz-cancel' );
			if( !get_option( 'permalink_structure' ) ) {
				$success_url = site_url( '/?page_id=gk-sslcommerz-success' );
				$fail_url = site_url( '/?page_id=gk-sslcommerz-fail' );
				$cancel_url = site_url( '/?page_id=gk-sslcommerz-cancel' );
			}

			if( $testbox && !strstr( $url, 'testbox' ) ) {
				$url = rtrim( $url, '/' ) . '/testbox/';
			}

			$payment_description = isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? __($this->options['gk_sslcommerz_default_payment_description'], $this->plugin_slug) : '';

			$amount = floatval( $_POST['gk_sslcommerz_amount'] );
			$currency = esc_attr( $_POST['gk_sslcommerz_currency'] );
			$service_charge = 0;
			$service_charge_label = isset( $this->options['gk_sslcommerz_service_charge_label'] ) ? esc_attr( $this->options['gk_sslcommerz_service_charge_label'] ) : 'Service charge';
			$customer_info = isset( $_POST['gk_sslcommerz_customer_info'] ) ? array_map( "esc_attr", $_POST['gk_sslcommerz_customer_info'] ) : array();
			$payment_description = isset( $_POST['gk_sslcommerz_collect_payment_description'] ) ? esc_attr( $_POST['gk_sslcommerz_collect_payment_description'] ) : $payment_description;
			$total = $amount;

			if( isset( $this->options['gk_sslcommerz_service_charge'] ) && $this->options['gk_sslcommerz_service_charge'] > 0 ) {
				if( isset( $this->options['gk_sslcommerz_service_charge_type'] ) ) {
					switch( $this->options['gk_sslcommerz_service_charge_type'] ) {
						case 'percentage':
							$service_charge = floatval( $amount * ( $this->options['gk_sslcommerz_service_charge'] / 100 ) );
							if( isset( $this->options['gk_sslcommerz_maximum_service_charge'] ) && $this->options['gk_sslcommerz_maximum_service_charge'] > 0 ) {
								$service_charge = min( $service_charge, floatval( $this->options['gk_sslcommerz_maximum_service_charge'] ) );
							}
							$total = floatval( $amount + $service_charge );
							break;
						case 'amount':
							$service_charge = floatval( $this->options['gk_sslcommerz_service_charge'] );
							if( isset( $this->options['gk_sslcommerz_maximum_service_charge'] ) && $this->options['gk_sslcommerz_maximum_service_charge'] > 0 ) {
								$service_charge = min( $service_charge, floatval( $this->options['gk_sslcommerz_maximum_service_charge'] ) );
							}
							$total = floatval( $amount + $service_charge );
							break;
					}
				}
			}

			$form_data = base64_encode( serialize( $_POST ) );
			$submitted_data = base64_encode( serialize( array(
								'amount' => $amount,
								'currency' => $currency,
								'service_charge' => $service_charge,
								'service_charge_label' => $service_charge_label,
								'total' => $total
							) ) );
			$payment_date = date('Y-m-d H:i:s');

			$wpdb->insert(
				$wpdb->prefix . $gk_sslcommerz_payments_table,
				array(
					'form_data' => $form_data,
					'submitted_data' => $submitted_data,
					'payment_date' => $payment_date
				),
				array(
					'%s',
					'%s',
					'%s'
				)
			);

			$insert_id = $wpdb->insert_id;

			$transaction_id = strtoupper( substr( $username, 0, 3 ) ) . '-' . $insert_id;

			if( isset( $this->options['gk_sslcommerz_review_page_instruction'] ) ) {
				$content .= '<div class="gk-sslcommerz-instructions">'.nl2br( $this->options['gk_sslcommerz_review_page_instruction'] ).'</div>';
			}

			if( isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1 && !empty( $customer_info ) ) {
				$content .= '<table class="gk-sslcommerz-address">';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __( 'Billing details', $this->plugin_slug );
				$content .= '</td>';
				$content .= '<td>';
				$content .= $customer_info['full_name'];
				$content .= '<br />' . $customer_info['email_address'];
				$content .= '<br />' . $customer_info['phone_number'];
				$content .= '<br />' . nl2br( $customer_info['billing_address'] );
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '</tbody>';
				$content .= '</table>';
			}

			$content .= '<table class="gk-sslcommerz-cart">';
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
			$content .= number_format( $amount, 2 ) . $currency;
			$content .= '</td>';
			$content .= '</tr>';

			if( $service_charge > 0 ) {
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __( $service_charge_label, $this->plugin_slug );
				$content .= '</td>';
				$content .= '<td align="right">';
				$content .= number_format( $service_charge, 2 ) . $currency;
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
			$content .= number_format( $total, 2 ) . $currency;
			$content .= '</td>';
			$content .= '</tr>';
			$content .= '<tr>';
			$content .= '</tfoot>';
			$content .= '</table>';

			//The SSLCommerz Form
			$content .= '<form id="payment_gw" name="payment_gw" method="POST" action="'.$url.'">';
			$content .= '<input type="hidden" name="total_amount" value="'.number_format( $total, 2 ).'" />';
			$content .= '<input type="hidden" name="store_id" value="'.$username.'" />';
			$content .= '<input type="hidden" name="tran_id" value="'.$transaction_id.'" />';
			$content .= '<input type="hidden" name="success_url" value="'.$success_url.'" />';
			$content .= '<input type="hidden" name="fail_url" value="'.$fail_url.'" />';
			$content .= '<input type="hidden" name="cancel_url" value="'.$cancel_url.'" />';
			$content .= '<input type="hidden" name="version" value="2.00" />';

			//Payment info as cart
			$content .= '<input type="hidden" name="cart[0][product]" value="'.$payment_description.'" />';
			$content .= '<input type="hidden" name="cart[0][amount]" value="'.number_format( $amount, 2 ).'" />';
			if( $service_charge > 0 ) {
				$content .= '<input type="hidden" name="cart[1][product]" value="'.__( $service_charge_label, $this->plugin_slug ).'" />';
				$content .= '<input type="hidden" name="cart[1][amount]" value="'.number_format( $service_charge, 2 ).'" />';
			}

			$content .= '<input type="submit" name="submit" value="'.__('Pay now', $this->plugin_slug).'" />';
			$content .= '</form>';

			$post = new stdClass;
			$post->ID = -1;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt = current_time('mysql', 1);
			$post->post_content = $content;
			$post->post_title = __('Payment confirmation', $this->plugin_slug);
			$post->post_excerpt = '';
			$post->post_status = 'publish';
			$post->comment_status = 'closed';
			$post->ping_status = 'closed';
			$post->post_password = '';
			$post->post_name = $this->slug;
			$post->to_ping = '';
			$post->pinged = '';
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_content_filtered = '';
			$post->post_parent = 0;
			$post->guid = get_home_url('/' . $this->slug);
			$post->menu_order = 0;
			$post->post_tyle = 'page';
			$post->post_mime_type = '';
			$post->comment_count = 0;

			// set filter results
			$posts = array($post);

			// reset wp_query properties to simulate a found page
			$wp_query->is_page = TRUE;
			$wp_query->is_singular = TRUE;
			$wp_query->is_home = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;
			unset($wp_query->query['error']);
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404 = FALSE;
		}

		return ($posts);
	}
}