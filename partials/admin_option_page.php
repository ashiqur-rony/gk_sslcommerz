<?php
/**
 * Template page for SSL Commerz plugin's options
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 **/
?>
<div class="wrap gk-sslcommerz-options">
	<h2><?php _e('SSL Commerz Options', $this->plugin_slug); ?></h2>
	<?php settings_errors(); ?>
	<?php
	if( isset( $this->options['gk_sslcommerz_username'] ) && strlen( $this->options['gk_sslcommerz_username'] ) > 0 ):
	?>
		<div class="gk-sslcommerz-msg">
			<p>To show the default payment form within any page or post add <code>[gk-sslcommerz]</code> in the content.</p>
			<p>To show the default payment form using template file write this code: <code>&lt;?php echo do_shortcode("[gk-sslcommerz]"); ?&gt;</code>.</p>
		</div>
	<?php
	endif;
	?>
	<form method="post" action="options.php" class="gk-sslcommerz-admin-form">
		<?php
		settings_fields( 'gk_sslcommerz' );
		do_settings_sections( 'gk-sslcommerz-options' );
		submit_button();
		?>
	</form>
</div>