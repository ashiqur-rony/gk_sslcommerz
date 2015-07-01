<?php
/**
 * Class to handle SSLCommerz gateway integration
 *
 * @author GoodKoding
 * @author_url http://ghumkumar.com
 **/

class gk_sslcommerz {

	protected $loader;
	protected $plugin_slug;
	protected $version;
	protected static $static_plugin_slug;
	protected static $static_version;
	private $options;

	public function __construct() {

		$this->plugin_slug = 'gk-sslcommerz';
		$this->version = '0.3';

		self::setStaticPluginSlug($this->plugin_slug);
		self::setStaticVersion($this->version);

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_shortcodes();
		$this->define_widgets();
		$this->define_pages();

	}

	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-loader.php';
		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-pages.php';
		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-widget.php';
		$this->loader = new gk_sslcommerz_loader();
	}

	private function define_admin_hooks() {
		$admin = new gk_sslcommerz_admin( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'add_admin_init' );

		if( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'gk-sslcommerz-options', 'gk-sslcommerz-payment-statistics' ) ) ) {
			$this->loader->add_action( 'admin_footer_text', $admin, 'add_admin_footer' );
		}
	}

	private function define_shortcodes() {
		add_shortcode('gk-sslcommerz', array($this, 'gk_sslcommerz_shortcode'));
	}

	private function define_widgets() {
		$widget = new gk_sslcommerz_widget( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'widgets_init', $widget, 'add_widget_init' );
	}

	private function define_pages() {
		$pages = new gk_sslcommerz_pages( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'init', $pages, 'initiate_pages' );
	}

	public function run() {
		$this->loader->run();
	}

	public static function setStaticVersion( $version ) {
		self::$static_version = $version;
	}

	public static function getStaticVersion() {
		return self::$static_version;
	}

	public static function setStaticPluginSlug( $plugin_slug ) {
		self::$static_plugin_slug = $plugin_slug;
	}

	public static function getStaticPluginSlug() {
		return self::$static_plugin_slug;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	public function gk_sslcommerz_shortcode($atts = array(), $content = null) {

		wp_register_style('gk-sslcommerz-form', plugins_url( '../css/form.css', __FILE__ ));
		wp_enqueue_style('gk-sslcommerz-form');

		$this->options = get_option( 'gk_sslcommerz_info' );
		if( get_option( 'permalink_structure' ) ) {
			$action = site_url( 'gk-sslcommerz-cart' );
		} else {
			$action = site_url( '/?page_id=gk-sslcommerz-cart' );
		}


		$key = rand(99, 9999);
		$autofill = (isset( $this->options['gk_sslcommerz_autofill_fields'] ) && $this->options['gk_sslcommerz_autofill_fields'] == 1) ? true : false;
		$amount = 0.00;
		$payment_description = isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? __($this->options['gk_sslcommerz_default_payment_description'], $this->plugin_slug) : '';
		$amount_variable =  isset( $this->options['gk_sslcommerz_autofill_amount_variable'] ) ? esc_attr( $this->options['gk_sslcommerz_autofill_amount_variable']) : '';
		$payment_info_variable =  isset( $this->options['gk_sslcommerz_autofill_payment_info_variable'] ) ? esc_attr( $this->options['gk_sslcommerz_autofill_payment_info_variable']) : '';
		$submit_text = isset( $this->options['gk_sslcommerz_payment_button_text'] ) ? esc_attr( $this->options['gk_sslcommerz_payment_button_text'] ) : 'Submit';

		if( $autofill && isset( $this->options['gk_sslcommerz_autofill_source'] ) ) {
			switch( isset( $this->options['gk_sslcommerz_autofill_source'] ) ) {
				case 'get':
					if(strlen($amount_variable) > 0 && isset($_GET[$amount_variable]) && strlen($_GET[$amount_variable]) > 0) {
						$amount = floatval($_GET[$amount_variable]);
					}
					if(strlen($payment_info_variable) > 0 && isset($_GET[$payment_info_variable]) && strlen($_GET[$payment_info_variable]) > 0) {
						$payment_description = esc_attr($_GET[$payment_info_variable]);
					}
					break;
				case 'post':
					break;
			}
		}

		$form = '';
		$form .= '<form method="post" action="' . $action . '" class="gk-sslcommerz-payment-form">';

		if( $content !== null ) {
			$form .= esc_attr( $content );
		}

		$form .= '<input type="hidden" name="_gk-sslcommerz-nonce" value="' . wp_create_nonce( 'gk-sslcommerz-shortcode' . $key ) . '" />';
		$form .= '<input type="hidden" name="_gk-sslcommerz-key" value="' . $key . '" />';

		if( isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1 ) {
			$form .= '<div class="gk-form-group">';
			$form .= '<label for="gk_sslcommerz_customer_info_full_name">'.__( 'Full name', $this->plugin_slug ).'</label>';
			$form .= '<input type="text" required name="gk_sslcommerz_customer_info[full_name]" id="gk_sslcommerz_customer_info_full_name" placeholder="'.__('Full name', $this->plugin_slug).'" />';
			$form .= '</div>';

			$form .= '<div class="gk-form-group">';
			$form .= '<label for="gk_sslcommerz_customer_info_email_address">'.__( 'Email address', $this->plugin_slug ).'</label>';
			$form .= '<input type="email" required name="gk_sslcommerz_customer_info[email_address]" id="gk_sslcommerz_customer_info_email_address" placeholder="'.__('Email address', $this->plugin_slug).'" />';
			$form .= '</div>';

			$form .= '<div class="gk-form-group">';
			$form .= '<label for="gk_sslcommerz_customer_info_phone_number">'.__( 'Phone number', $this->plugin_slug ).'</label>';
			$form .= '<input type="text" required name="gk_sslcommerz_customer_info[phone_number]" id="gk_sslcommerz_customer_info_phone_number" placeholder="'.__('Phone number', $this->plugin_slug).'" />';
			$form .= '</div>';

			$form .= '<div class="gk-form-group">';
			$form .= '<label for="gk_sslcommerz_customer_info_billing_address">'.__( 'Billing address', $this->plugin_slug ).'</label>';
			$form .= '<textarea required name="gk_sslcommerz_customer_info[billing_address]" id="gk_sslcommerz_customer_info_billing_address" placeholder="'.__('Billing address', $this->plugin_slug).'"></textarea>';
			$form .= '</div>';
		}

		if( isset( $this->options['gk_sslcommerz_collect_payment_description'] ) && $this->options['gk_sslcommerz_collect_payment_description'] == 1 ) {
			$form .= '<div class="gk-form-group">';
			$form .= '<label for="gk_sslcommerz_collect_payment_description">'.__( 'Payment description', $this->plugin_slug ).'</label>';
			$form .= '<input type="text" name="gk_sslcommerz_collect_payment_description" id="gk_sslcommerz_collect_payment_description" placeholder="'.__('Payment detail', $this->plugin_slug).'" value="'.$payment_description.'" />';
			$form .= '</div>';
		}

		$form .= '<div class="gk-form-group">';
		$form .= '<label for="gk_sslcommerz_amount">'.__( 'Amount', $this->plugin_slug ).'</label>';
		$form .= '<input type="number" id="gk_sslcommerz_amount" name="gk_sslcommerz_amount" min="1" step="any" placeholder="'.__('Amount in BDT', $this->plugin_slug).'" value="'.$amount.'" />';
		$form .= '</div>';

		$form .= '<input type="hidden" name="gk_sslcommerz_currency" value="BDT" />';
		$form .= '<input type="submit" value="'.__($submit_text, $this->plugin_slug).'" />';
		$form .= '</form>';
		return $form;
	}
} 