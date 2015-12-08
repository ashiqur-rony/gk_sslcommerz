<?php
/**
 * Class to handle widgets for SSLCommerz form.
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 */

if(!class_exists('gk_sslcommerz')) {
	include plugin_dir_path( __FILE__ ) . '/gk-sslcommerz.php';
}

class gk_sslcommerz_widget extends WP_Widget {

	private $version;
	private $plugin_slug;

	public function __construct() {
		$this->version = gk_sslcommerz::getStaticVersion();
		$this->plugin_slug = gk_sslcommerz::getStaticPluginSlug();

		parent::__construct( 'gk_sslcommerz_widget', __( 'SSLCommerz Payment', $this->plugin_slug ), array( 'description' => __( 'Widget to display SSLCommerz payment form', $this->plugin_slug ) ) );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo do_shortcode( '[gk-sslcommerz]' );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Make a payment', $this->plugin_slug );
		}

		require_once plugin_dir_path( __FILE__ ) . '../partials/admin_widget_form.php';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

	public function add_widget_init() {
		register_widget( 'gk_sslcommerz_widget' );
	}

} 