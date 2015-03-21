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

	public function __construct() {

		$this->plugin_slug = 'gk-sslcommerz';
		$this->version = '0.1';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_shortcodes();
		$this->define_widgets();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'gk-sslcommerz-loader.php';
		$this->loader = new gk_sslcommerz_loader();

	}

	private function define_admin_hooks() {

		$admin = new gk_sslcommerz_admin( $this->get_version(), $this->get_plugin_slug() );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );

	}

	private function define_shortcodes() {

	}

	private function define_widgets() {

	}

	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}
} 