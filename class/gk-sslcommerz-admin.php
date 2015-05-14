<?php
/**
 * Class to handle SSLCommerz gateway integration admin side
 *
 * @author GoodKoding
 * @author_url http://ghumkumar.com
 **/

class gk_sslcommerz_admin {

	private $version;
	private $plugin_slug;
	private $options;

	public function __construct( $version, $plugin_slug ) {
		$this->version = $version;
		$this->plugin_slug = $plugin_slug;
	}

	public function enqueue_styles() {
		wp_register_style('gk-sslcommerz-admin', plugins_url( '../css/admin.css', __FILE__ ));
		wp_enqueue_style('gk-sslcommerz-admin');

		wp_register_script('gk-sslcommerz-admin-js', plugins_url( '../js/admin.js', __FILE__ ), array('jquery'), '', true);
		wp_enqueue_script('gk-sslcommerz-admin-js');
	}

	/**
	 * Add administrative option menu
	 */
	public function add_admin_menu() {
		add_menu_page( __('SSL Commerz', $this->plugin_slug), __('SSL Commerz', $this->plugin_slug), 'activate_plugins', 'gk-sslcommerz-options', array($this, 'sslcommerz_options'), 'dashicons-money', 81 );
		add_submenu_page( 'gk-sslcommerz-options', __('Payment Statistics', $this->plugin_slug), __('Payment Statistics', $this->plugin_slug), 'activate_plugins', 'gk-sslcommerz-payment-statistics', array($this, 'sslcommerz_payment_statistics') );

		//Rename the submenu
		global $submenu;
		if( isset( $submenu['gk-sslcommerz-options'] ) && isset( $submenu['gk-sslcommerz-options'][0] ) ) {
			$submenu['gk-sslcommerz-options'][0][0] = __( 'Payment Settings', $this->plugin_slug );
			$submenu['gk-sslcommerz-options'][0][3] = __( 'Payment Settings', $this->plugin_slug );
		}
	}

	/**
	 * Add footer text on the admin side
	 */
	public function add_admin_footer() {
		echo sprintf( __( 'This <a href="%s" target="_blank">WordPress</a> plugin was developed by <a href="%s" target="_blank">GoodKoding</a>', $this->plugin_slug ), '//wordpress.org', 'http://goodkoding.com' );
	}

	/**
	 * Register the option fields for the plugin
	 */
	public function add_admin_init() {
		register_setting(
			'gk_sslcommerz',
			'gk_sslcommerz_info',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'gk_sslcommerz_credentials',
			__('SSL Commerz Credentials', $this->plugin_slug),
			array( $this, 'credentials_info' ),
			'gk-sslcommerz-options'
		);

		add_settings_field(
			'gk_sslcommerz_username',
			__('SSL Commerz Username', $this->plugin_slug),
			array( $this, 'username_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'gk_sslcommerz_password',
			__('SSL Commerz Password', $this->plugin_slug),
			array( $this, 'password_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'gk_sslcommerz_url',
			__('Payment URL', $this->plugin_slug),
			array( $this, 'url_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'gk_sslcommerz_testbox',
			__('Enable Testbox?', $this->plugin_slug),
			array( $this, 'testbox_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_section(
			'gk_sslcommerz_pages',
			__('Target Pages', $this->plugin_slug),
			array( $this, 'pages_info' ),
			'gk-sslcommerz-options'
		);

		add_settings_field(
			'gk_sslcommerz_success_page',
			__('Success Page', $this->plugin_slug),
			array( $this, 'success_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
		);

		add_settings_field(
			'gk_sslcommerz_fail_page',
			__('Failed Page', $this->plugin_slug),
			array( $this, 'fail_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
		);

		add_settings_field(
			'gk_sslcommerz_cancel_page',
			__('Cancelled Page', $this->plugin_slug),
			array( $this, 'cancel_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
		);

		add_settings_section(
			'gk_sslcommerz_form_options',
			__('Payment Form Options', $this->plugin_slug),
			array( $this, 'form_options_info' ),
			'gk-sslcommerz-options'
		);

		add_settings_field(
			'gk_sslcommerz_collect_customer_info',
			__('Collect Customer Info?', $this->plugin_slug),
			array( $this, 'customer_info_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_collect_payment_description',
			__('Collect Payment Description?', $this->plugin_slug),
			array( $this, 'payment_description_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_default_payment_description',
			__('Default Payment Description', $this->plugin_slug),
			array( $this, 'default_payment_description_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_autofill_fields',
			__('Autofill Payment Fields?', $this->plugin_slug),
			array( $this, 'autofill_fields_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_autofill_source',
			__('Source of Autofill Variables', $this->plugin_slug),
			array( $this, 'autofill_source_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_autofill_amount_variable',
			__('Autofill Amount Variable', $this->plugin_slug),
			array( $this, 'autofill_amount_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_field(
			'gk_sslcommerz_autofill_payment_info_variable',
			__('Autofill Payment Info Variable', $this->plugin_slug),
			array( $this, 'autofill_payment_info_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_form_options'
		);

		add_settings_section(
			'gk_sslcommerz_advanced',
			__('Advanced Settings', $this->plugin_slug),
			array( $this, 'advanced_settings_info' ),
			'gk-sslcommerz-options'
		);

		add_settings_field(
			'gk_sslcommerz_service_charge',
			__('Service Charge', $this->plugin_slug),
			array( $this, 'service_charge_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);

		add_settings_field(
			'gk_sslcommerz_service_charge_type',
			__('Service Charge Type', $this->plugin_slug),
			array( $this, 'service_charge_type_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);

		add_settings_field(
			'gk_sslcommerz_maximum_service_charge',
			__('Maximum Service Charge', $this->plugin_slug),
			array( $this, 'maximum_service_charge_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);

		add_settings_field(
			'gk_sslcommerz_service_charge_label',
			__('Service Charge Label', $this->plugin_slug),
			array( $this, 'service_charge_label_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);

		add_settings_field(
			'gk_sslcommerz_review_page_instruction',
			__('Instruction on Review Page', $this->plugin_slug),
			array( $this, 'review_page_instruction_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);

		add_settings_field(
			'gk_sslcommerz_payment_button_text',
			__('Text on Payment Button', $this->plugin_slug),
			array( $this, 'payment_button_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_advanced'
		);
	}

	/**
	 * Display the options page for SSLCommerz plugin
	 */
	public function sslcommerz_options() {
		$this->options = get_option( 'gk_sslcommerz_info' );
		require_once plugin_dir_path( __FILE__ ) . '../partials/admin_option_page.php';
	}

	/**
	 * Display the list of payments received
	 */
	public function sslcommerz_payment_statistics() {
		global $wpdb, $gk_sslcommerz_payments_table;

		$this->options = get_option( 'gk_sslcommerz_info' );
		$action = isset( $_GET['do'] ) ? esc_attr( $_GET['do'] ) : 'list';
		$error = array();
		$page = isset( $_GET['p'] ) ? intval( $_GET['p'] ) : 1;
		$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 25;
		$order_by = isset( $_GET['order_by'] ) ? intval( $_GET['order_by'] ) : 'date';
		$order = isset( $_GET['order'] ) ? intval( $_GET['order'] ) : 'DESC';
		$payment_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		if( $order_by == 'date' ) {
			$order_by = 'payment_date';
		} else {
			$order_by = 'payment_status';
		}

		if( $payment_id == 0 && $action != 'list' ) {
			$action = 'list';
			$error[] = __( 'Invalid payment reference.', $this->plugin_slug );
		}

		switch( $action ) {
			case 'edit':
				break;

			case 'invoice':
				require_once plugin_dir_path( __FILE__ ) . '../partials/admin_payment_invoice.php';
				break;

			case 'archive':
				break;

			case 'list':
			default:
				require_once plugin_dir_path( __FILE__ ) . '../partials/admin_payment_statistics.php';
				break;
		}
	}

	/**
	 * Sanitize the settings fields if necessary
	 * @param $input array
	 * @return array
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['gk_sslcommerz_username'] ) ) {
			$new_input['gk_sslcommerz_username'] = sanitize_text_field( $input['gk_sslcommerz_username'] );
		}

		if( isset( $input['gk_sslcommerz_password'] ) ) {
			$new_input['gk_sslcommerz_password'] = sanitize_text_field( $input['gk_sslcommerz_password'] );
		}

		if( isset( $input['gk_sslcommerz_url'] ) ) {
			$new_input['gk_sslcommerz_url'] = esc_url( $input['gk_sslcommerz_url'] );
		}

		if( isset( $input['gk_sslcommerz_testbox'] ) && $input['gk_sslcommerz_testbox'] == 1 ) {
			$new_input['gk_sslcommerz_testbox'] = 1;
		} else {
			$new_input['gk_sslcommerz_testbox'] = 0;
		}

		if( isset( $input['gk_sslcommerz_success_page'] ) ) {
			$new_input['gk_sslcommerz_success_page'] = absint( $input['gk_sslcommerz_success_page'] );
		}

		if( isset( $input['gk_sslcommerz_fail_page'] ) ) {
			$new_input['gk_sslcommerz_fail_page'] = absint( $input['gk_sslcommerz_fail_page'] );
		}

		if( isset( $input['gk_sslcommerz_cancel_page'] ) ) {
			$new_input['gk_sslcommerz_cancel_page'] = absint( $input['gk_sslcommerz_cancel_page'] );
		}

		if( isset( $input['gk_sslcommerz_collect_customer_info'] ) && $input['gk_sslcommerz_collect_customer_info'] == 1 ) {
			$new_input['gk_sslcommerz_collect_customer_info'] = 1;
		} else {
			$new_input['gk_sslcommerz_collect_customer_info'] = 0;
		}

		if( isset( $input['gk_sslcommerz_collect_payment_description'] ) && $input['gk_sslcommerz_collect_payment_description'] == 1 ) {
			$new_input['gk_sslcommerz_collect_payment_description'] = 1;
		} else {
			$new_input['gk_sslcommerz_collect_payment_description'] = 0;
		}

		if( isset( $input['gk_sslcommerz_default_payment_description'] ) ) {
			$new_input['gk_sslcommerz_default_payment_description'] = sanitize_text_field( $input['gk_sslcommerz_default_payment_description'] );
		}

		if( isset( $input['gk_sslcommerz_autofill_fields'] ) && $input['gk_sslcommerz_autofill_fields'] == 1 ) {
			$new_input['gk_sslcommerz_autofill_fields'] = 1;
		} else {
			$new_input['gk_sslcommerz_autofill_fields'] = 0;
		}

		if( isset( $input['gk_sslcommerz_autofill_source'] ) ) {
			$new_input['gk_sslcommerz_autofill_source'] = sanitize_text_field( $input['gk_sslcommerz_autofill_source'] );
		}

		if( isset( $input['gk_sslcommerz_autofill_amount_variable'] ) ) {
			$new_input['gk_sslcommerz_autofill_amount_variable'] = sanitize_text_field( $input['gk_sslcommerz_autofill_amount_variable'] );
		}

		if( isset( $input['gk_sslcommerz_autofill_payment_info_variable'] ) ) {
			$new_input['gk_sslcommerz_autofill_payment_info_variable'] = sanitize_text_field( $input['gk_sslcommerz_autofill_payment_info_variable'] );
		}

		if( isset( $input['gk_sslcommerz_service_charge'] ) ) {
			$new_input['gk_sslcommerz_service_charge'] = floatval( $input['gk_sslcommerz_service_charge'] );
		}

		if( isset( $input['gk_sslcommerz_service_charge_type'] ) ) {
			$new_input['gk_sslcommerz_service_charge_type'] = sanitize_text_field( $input['gk_sslcommerz_service_charge_type'] );
		}

		if( isset( $input['gk_sslcommerz_maximum_service_charge'] ) ) {
			$new_input['gk_sslcommerz_maximum_service_charge'] = floatval( $input['gk_sslcommerz_maximum_service_charge'] );
		}

		if( isset( $input['gk_sslcommerz_service_charge_label'] ) ) {
			$new_input['gk_sslcommerz_service_charge_label'] = sanitize_text_field( $input['gk_sslcommerz_service_charge_label'] );
		}

		if( isset( $input['gk_sslcommerz_review_page_instruction'] ) ) {
			$new_input['gk_sslcommerz_review_page_instruction'] = sanitize_text_field( $input['gk_sslcommerz_review_page_instruction'] );
		}

		if( isset( $input['gk_sslcommerz_payment_button_text'] ) ) {
			$new_input['gk_sslcommerz_payment_button_text'] = sanitize_text_field( $input['gk_sslcommerz_payment_button_text'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function credentials_info()
	{
		print __('Set the SSL Commerz credentials from here. These values are used to authenticate the merchant while processing the payment.', $this->plugin_slug);
	}

	/**
	 * Print the Section text
	 */
	public function pages_info()
	{
		print __('Select the pages for different payment status. User will be redirected to these pages based on their payment status.', $this->plugin_slug);
	}

	/**
	 * Print the Section text
	 */
	public function form_options_info()
	{
		print __('Set the form options. These parameters determine which values to collect and how to populate fields.', $this->plugin_slug);
	}

	/**
	 * Print the Section text
	 */
	public function advanced_settings_info()
	{
		print __('Set the advanced form options. Service charge for payment, payment confirmation page message etc. can be set from here.', $this->plugin_slug);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function username_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_username" name="gk_sslcommerz_info[gk_sslcommerz_username]" required="required" value="%s" />',
			isset( $this->options['gk_sslcommerz_username'] ) ? esc_attr( $this->options['gk_sslcommerz_username']) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function password_callback()
	{
		printf(
			'<input type="password" id="gk_sslcommerz_password" name="gk_sslcommerz_info[gk_sslcommerz_password]" required="required" value="%s" />',
			isset( $this->options['gk_sslcommerz_password'] ) ? esc_attr( $this->options['gk_sslcommerz_password']) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function url_callback()
	{
		printf(
			'<input type="url" id="gk_sslcommerz_url" name="gk_sslcommerz_info[gk_sslcommerz_url]" required="required" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_url'] ) ? esc_attr( $this->options['gk_sslcommerz_url']) : 'https://www.sslcommerz.com.bd/gwprocess/',
			'<small>'.__('Default URL is: https://www.sslcommerz.com.bd/gwprocess/', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function testbox_callback()
	{
		printf(
			'<input type="checkbox" id="gk_sslcommerz_testbox" name="gk_sslcommerz_info[gk_sslcommerz_testbox]" value="1" %s /><br />%s',
			(!isset( $this->options['gk_sslcommerz_testbox'] ) || $this->options['gk_sslcommerz_testbox'] == 1) ? 'checked="checked"' : '',
			'<small>'.__('Un-check this to enable live payment.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function success_page_callback()
	{
		$args = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => isset( $this->options['ssl_success_page'] ) ? $this->options['ssl_success_page'] : 0,
			'echo'                  => 0,
			'name'                  => 'gk_sslcommerz_success_page',
			'id'                    => null, // string
			'show_option_none'      => __('None', $this->plugin_slug), // string
			'show_option_no_change' => null, // string
			'option_none_value'     => 0, // string
		);
		printf(
			'%s<br />%s',
			wp_dropdown_pages($args),
			'<small>'.__('Where the user will be redirected to after successful payment. If none selected a page with successful payment invoice will be displayed.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function fail_page_callback()
	{
		$args = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => isset( $this->options['ssl_fail_page'] ) ? $this->options['ssl_fail_page'] : 0,
			'echo'                  => 0,
			'name'                  => 'gk_sslcommerz_fail_page',
			'id'                    => null, // string
			'show_option_none'      => __('None', $this->plugin_slug), // string
			'show_option_no_change' => null, // string
			'option_none_value'     => 0, // string
		);
		printf(
			'%s<br />%s',
			wp_dropdown_pages($args),
			'<small>'.__('Where the user will be redirected to after failed payment. If none selected a page with failed payment invoice will be displayed.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function cancel_page_callback()
	{
		$args = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => isset( $this->options['ssl_cancel_page'] ) ? $this->options['ssl_cancel_page'] : 0,
			'echo'                  => 0,
			'name'                  => 'gk_sslcommerz_cancel_page',
			'id'                    => null, // string
			'show_option_none'      => __('None', $this->plugin_slug), // string
			'show_option_no_change' => null, // string
			'option_none_value'     => 0, // string
		);
		printf(
			'%s<br />%s',
			wp_dropdown_pages($args),
			'<small>'.__('Where the user will be redirected to after cancelling the payment. If none selected user will be redirected to home page.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function customer_info_callback()
	{
		printf(
			'<input type="checkbox" id="gk_sslcommerz_collect_customer_info" name="gk_sslcommerz_info[gk_sslcommerz_collect_customer_info]" value="1" %s /><br />%s',
			(isset( $this->options['gk_sslcommerz_collect_customer_info'] ) && $this->options['gk_sslcommerz_collect_customer_info'] == 1) ? 'checked="checked"' : '',
			'<small>'.__('Check this field to collect customer information.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function payment_description_callback()
	{
		printf(
			'<input type="checkbox" id="gk_sslcommerz_collect_payment_description" name="gk_sslcommerz_info[gk_sslcommerz_collect_payment_description]" value="1" %s /><br />%s',
			(isset( $this->options['gk_sslcommerz_collect_payment_description'] ) && $this->options['gk_sslcommerz_collect_payment_description'] == 1) ? 'checked="checked"' : '',
			'<small>'.__('Let the user (customer) set the payment description?', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function default_payment_description_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_default_payment_description" name="gk_sslcommerz_info[gk_sslcommerz_default_payment_description]" value="%s" /><br />%s',
			(isset( $this->options['gk_sslcommerz_default_payment_description'] ) ? $this->options['gk_sslcommerz_default_payment_description'] : ''),
			'<small>'.__('Default payment description if not collected from user (customer) or parameters.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function autofill_fields_callback()
	{
		printf(
			'<input type="checkbox" id="gk_sslcommerz_autofill_fields" name="gk_sslcommerz_info[gk_sslcommerz_autofill_fields]" value="1" %s /><br />%s',
			(isset( $this->options['gk_sslcommerz_autofill_fields'] ) && $this->options['gk_sslcommerz_autofill_fields'] == 1) ? 'checked="checked"' : '',
			'<small>'.__('Check this to autofill payment fields from URL or form post values.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function autofill_source_callback()
	{
		printf(
			'<select id="gk_sslcommerz_autofill_source" name="gk_sslcommerz_info[gk_sslcommerz_autofill_source]">
				<option value="">%s</option>
				<option value="get" ' . ((isset( $this->options['gk_sslcommerz_autofill_source'] ) && $this->options['gk_sslcommerz_autofill_source'] == 'get') ? 'selected="selected"' : '') . '>%s</option>
				<option value="post" ' . ((isset( $this->options['gk_sslcommerz_autofill_source'] ) && $this->options['gk_sslcommerz_autofill_source'] == 'post') ? 'selected="selected"' : '') . '>%s</option>
			</select><br />%s',
			__( 'None', $this->plugin_slug ),
			__( 'URL Parameter ($_GET)', $this->plugin_slug ),
			__( 'Post Parameter ($_POST)', $this->plugin_slug ),
			'<small>'.__('Whether to look for the values in <code>$_GET</code> or <code>$_POST</code> of request parameters.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function autofill_amount_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_autofill_amount_variable" name="gk_sslcommerz_info[gk_sslcommerz_autofill_amount_variable]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_autofill_amount_variable'] ) ? esc_attr( $this->options['gk_sslcommerz_autofill_amount_variable']) : '',
			'<small>'.__('Select the parameter name for amount variable. If the amount values is in <code>$_POST[amount]</code> then write <code>amount</code> in this field.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function autofill_payment_info_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_autofill_payment_info_variable" name="gk_sslcommerz_info[gk_sslcommerz_autofill_payment_info_variable]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_autofill_payment_info_variable'] ) ? esc_attr( $this->options['gk_sslcommerz_autofill_payment_info_variable']) : '',
			'<small>'.__('Select the parameter name for payment info variable. If the payment info is in <code>$_POST[info]</code> then write <code>info</code> in this field.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function service_charge_callback()
	{
		printf(
			'<input type="number" min="0" step="any" id="gk_sslcommerz_service_charge" name="gk_sslcommerz_info[gk_sslcommerz_service_charge]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_service_charge'] ) ? esc_attr( $this->options['gk_sslcommerz_service_charge']) : 0,
			'<small>'.__('Set the service charge for the payment processing. Zero means no service charge.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function service_charge_type_callback()
	{
		printf(
			'<select id="gk_sslcommerz_service_charge_type" name="gk_sslcommerz_info[gk_sslcommerz_service_charge_type]">
				<option value="percentage" ' . ((isset( $this->options['gk_sslcommerz_service_charge_type'] ) && $this->options['gk_sslcommerz_service_charge_type'] == 'percentage') ? 'selected="selected"' : '') . '>%s</option>
				<option value="amount" ' . ((isset( $this->options['gk_sslcommerz_service_charge_type'] ) && $this->options['gk_sslcommerz_service_charge_type'] == 'amount') ? 'selected="selected"' : '') . '>%s</option>
			</select><br />%s',
			__( '% (Percentage of total)', $this->plugin_slug ),
			__( 'Fixed (Fixed amount)', $this->plugin_slug ),
			'<small>'.__('Set how the service charge will be calculated.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function maximum_service_charge_callback()
	{
		printf(
			'<input type="number" min="0" step="any" id="gk_sslcommerz_maximum_service_charge" name="gk_sslcommerz_info[gk_sslcommerz_maximum_service_charge]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_maximum_service_charge'] ) ? esc_attr( $this->options['gk_sslcommerz_maximum_service_charge']) : 0,
			'<small>'.__('Zero means no limit.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function service_charge_label_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_service_charge_label" name="gk_sslcommerz_info[gk_sslcommerz_service_charge_label]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_service_charge_label'] ) ? esc_attr( $this->options['gk_sslcommerz_service_charge_label']) : '',
			'<small>'.__('Label of the service charge (e.g. Processing fee or Shipping charge).', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function review_page_instruction_callback()
	{
		printf(
			'<textarea id="gk_sslcommerz_review_page_instruction" name="gk_sslcommerz_info[gk_sslcommerz_review_page_instruction]">%s</textarea><br />%s',
			isset( $this->options['gk_sslcommerz_review_page_instruction'] ) ? esc_attr( $this->options['gk_sslcommerz_review_page_instruction']) : '',
			'<small>'.__('Set the text you want to show the users on the payment review page.', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function payment_button_callback()
	{
		printf(
			'<input type="text" id="gk_sslcommerz_payment_button_text" name="gk_sslcommerz_info[gk_sslcommerz_payment_button_text]" value="%s" /><br />%s',
			isset( $this->options['gk_sslcommerz_payment_button_text'] ) ? esc_attr( $this->options['gk_sslcommerz_payment_button_text']) : '',
			'<small>'.__('Text to display on the <code>Pay now</code> button.', $this->plugin_slug).'</small>'
		);
	}
} 