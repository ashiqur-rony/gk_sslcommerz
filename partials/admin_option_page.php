<?php
/**
 * Template page for SSL Commerz plugin's options
 *
 * @author GoodKoding
 * @url http://goodkoding.com
 **/
?>
<div class="wrap">
	<h2><?php _e('SSL Commerz Options', $this->plugin_slug); ?></h2>
	<form method="post" action="options.php" class="gk-sslcommerz-admin-form">
		<?php
		settings_fields( 'gk_sslcommerz' );
		do_settings_sections( 'gk-sslcommerz-options' );
		submit_button();
		?>
	</form>
</div>