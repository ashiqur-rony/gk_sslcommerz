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
		add_menu_page( __('SSL Commerz', $this->plugin_slug), __('SSL Comerz', $this->plugin_slug), 'activate_plugins', 'gk-sslcommerz-options', array($this, 'sslcommerz_options'), 'dashicons-money', 81 );
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
			'ssl_username',
			__('SSL Commerz Username', $this->plugin_slug),
			array( $this, 'username_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'ssl_password',
			__('SSL Commerz Password', $this->plugin_slug),
			array( $this, 'password_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'ssl_url',
			__('Payment URL', $this->plugin_slug),
			array( $this, 'url_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_credentials'
		);

		add_settings_field(
			'ssl_testbox',
			__('Enable Testbox', $this->plugin_slug),
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
			'ssl_success_page',
			__('Success Page', $this->plugin_slug),
			array( $this, 'success_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
		);

		add_settings_field(
			'ssl_fail_page',
			__('Failed Page', $this->plugin_slug),
			array( $this, 'fail_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
		);

		add_settings_field(
			'ssl_cancel_page',
			__('Cancelled Page', $this->plugin_slug),
			array( $this, 'cancel_page_callback' ),
			'gk-sslcommerz-options',
			'gk_sslcommerz_pages'
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
	 * Sanitize the settings fields if necessary
	 * @param $input array
	 * @return array
	 */
	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['ssl_username'] ) )
			$new_input['ssl_username'] = sanitize_text_field( $input['ssl_username'] );

		if( isset( $input['ssl_password'] ) )
			$new_input['ssl_password'] = sanitize_text_field( $input['ssl_password'] );

		if( isset( $input['ssl_url'] ) )
			$new_input['ssl_url'] = esc_url( $input['ssl_url'] );

		if( isset( $input['ssl_testbox'] ) && $input['ssl_testbox'] == 1 )
			$new_input['ssl_testbox'] = 1;
		else
			$new_input['ssl_testbox'] = 0;

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
	 * Get the settings option array and print one of its values
	 */
	public function username_callback()
	{
		printf(
			'<input type="text" id="ssl_username" name="gk_sslcommerz_info[ssl_username]" value="%s" />',
			isset( $this->options['ssl_username'] ) ? esc_attr( $this->options['ssl_username']) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function password_callback()
	{
		printf(
			'<input type="password" id="ssl_password" name="gk_sslcommerz_info[ssl_password]" value="%s" />',
			isset( $this->options['ssl_password'] ) ? esc_attr( $this->options['ssl_password']) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function url_callback()
	{
		printf(
			'<input type="url" id="ssl_url" name="gk_sslcommerz_info[ssl_url]" value="%s" /><br />%s',
			isset( $this->options['ssl_url'] ) ? esc_attr( $this->options['ssl_url']) : 'https://www.sslcommerz.com.bd/gwprocess/',
			'<small>'.__('Default URL is: https://www.sslcommerz.com.bd/gwprocess/', $this->plugin_slug).'</small>'
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function testbox_callback()
	{
		printf(
			'<input type="checkbox" id="ssl_testbox" name="gk_sslcommerz_info[ssl_testbox]" value="1" %s /><br />%s',
			(!isset( $this->options['ssl_testbox'] ) || $this->options['ssl_testbox'] == 1) ? 'checked="checked"' : '',
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
			'name'                  => 'ssl_success_page',
			'id'                    => null, // string
			'show_option_none'      => __('None', $this->plugin_slug), // string
			'show_option_no_change' => null, // string
			'option_none_value'     => 0, // string
		);
		printf(
			'%s<br />%s',
			wp_dropdown_pages($args),
			'<small>'.__('Where the user will be redirected to after successful payment. If none selected user will be redirected to home page.', $this->plugin_slug).'</small>'
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
			'name'                  => 'ssl_fail_page',
			'id'                    => null, // string
			'show_option_none'      => __('None', $this->plugin_slug), // string
			'show_option_no_change' => null, // string
			'option_none_value'     => 0, // string
		);
		printf(
			'%s<br />%s',
			wp_dropdown_pages($args),
			'<small>'.__('Where the user will be redirected to after failed payment. If none selected user will be redirected to home page.', $this->plugin_slug).'</small>'
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
			'name'                  => 'ssl_cancel_page',
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
} 