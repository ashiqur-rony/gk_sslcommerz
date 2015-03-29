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

	public function gk_sslcommerz_cart( $posts ) {
		global $wp, $wp_query;

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
			$password = $this->options['gk_sslcommerz_password'];
			$url = $this->options['gk_sslcommerz_url'];
			$testbox = ( isset( $this->options['gk_sslcommerz_testbox'] ) && $this->options['gk_sslcommerz_testbox'] == 1 ) ? true : false;

			$success_url = 'gk-sslcommerz-success';
			$fail_url = 'gk-sslcommerz-fail';
			$cancel_url = 'gk-sslcommerz-cancel';
			if( !get_option( 'permalink_structure' ) ) {
				$success_url = site_url( '/?page_id=gk-sslcommerz-success' );
				$fail_url = site_url( '/?page_id=gk-sslcommerz-fail' );
				$cancel_url = site_url( '/?page_id=gk-sslcommerz-cancel' );
			}

			$transaction_id = strtoupper( substr( $username, 0, 3 ) ) . '-' . rand(99, 999);
			/**
			 * @todo Save data into table and generate transaction id
			 */

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
			$content .= '<input type="hidden" name="total_amount" value="'.number_format( $amount, 2 ).'" />';
			$content .= '<input type="hidden" name="store_id" value="'.$username.'" />';
			$content .= '<input type="hidden" name="trans_id" value="'.$transaction_id.'" />';
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