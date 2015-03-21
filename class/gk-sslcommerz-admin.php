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

	public function __construct( $version, $plugin_slug ) {
		$this->version = $version;
		$this->plugin_slug = $plugin_slug;
	}

	public function enqueue_styles() {

		//add style here

	}

	public function add_admin_menu() {
		add_menu_page( __('SSL Commerz', $this->plugin_slug), __('SSL Comerz', $this->plugin_slug), 'activate_plugins', 'gk_sslcommerz_options', array($this, 'sslcommerz_options'), 'dashicons-money', 81 );
	}

	public function sslcommerz_options() {
		require_once plugin_dir_path( __FILE__ ) . '../partials/admin_option_page.php';
	}
} 