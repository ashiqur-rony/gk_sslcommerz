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
	<div class="updated">
		<p>
			We'll register settings and add them here.
		</p>
	</div>
	<form name="ssl-options" method="post" action="options.php">
		<fieldset>
			<legend><?php _e('SSL Commerz Credentials', $this->plugin_slug); ?></legend>
			<label for="live_username"><?php _e('Username', $this->plugin_slug); ?></label>
			<input type="text" id="live_username" name="gk_sslcommerz[live_username]" placeholder="<?php _e('Username for live account', $this->plugin_slug); ?>" />
			<label for="live_password"><?php _e('Password', $this->plugin_slug); ?></label>
			<input type="password" id="live_password" name="gk_sslcommerz[live_password]" placeholder="<?php _e('Password for live account', $this->plugin_slug); ?>" />
			<label for="test_username"><?php _e('Testbox Username', $this->plugin_slug); ?></label>
			<input type="text" id="test_username" name="gk_sslcommerz[test_username]" placeholder="<?php _e('Username for testbox', $this->plugin_slug); ?>" />
			<label for="test_password"><?php _e('Testbox Password', $this->plugin_slug); ?></label>
			<input type="password" id="test_password" name="gk_sslcommerz[test_password]" placeholder="<?php _e('Password for testbox', $this->plugin_slug); ?>" />
		</fieldset>
	</form>
</div>