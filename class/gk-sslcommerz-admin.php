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

		//add style here

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
		print __('Provide SSL Commerz credentials below:', $this->plugin_slug);
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
} 