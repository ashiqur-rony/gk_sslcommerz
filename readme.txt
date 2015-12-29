=== SSLCommerz Payment Gateway ===
Contributors: goodkoding
Tags: payment gateway, payment, BDT, Taka, Visa, Master Card, Internet Banking, BKash, Mobile Banking
Requires at least: 3.9.2
Tested up to: 4.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate the SSLCommerz payment gateway to WordPress website.

== Description ==
This plugin enables the feature to integrate [SSLCommerz](https://sslcommerz.com.bd) payment gateway to WordPress website using widgets or shortcodes.

**Features**

* Integrate SSLCommerz payment gateway
* Ability to use both test mode and live mode
* Enable payment form using widget in any sidebar area
* Integrate payment form within form using shortcode
* Complete payment report from the admin side

== Installation ==
1. Upload "gk-sslcommerz" directory inside the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Go to "SSLCommerz" from the menu and set your SSLCommerz account information.

== Frequently Asked Questions ==
= Do I need an active SSLCommerz account? =
Yes. You need to subscribe and have an active contract with [SSLCommerz](https://sslcommerz.com.bd) to use this plugin. You can get a discounted subscription from [GoodKoding](https://goodkoding.com/).

= I don't have an SSLCommerz account. Can I still test this plugin? =
Absolutely. You can test the plugin and see how the widgets works. But to actually receive payments, you have to open an account.

= Which currencies can I receive payments from? =
SSLCommerz only process BDT (Bangladeshi Taka) transactions.

= What about security? =
It is recommended but not necessary to use SSL certificate on your website to receive payment. None of the card information will be collected or stored on your website. Customer (payee) will provide their sensitive information (card number, security code etc.) only on the bank's portal.

== Screenshots ==
1. Admin page to set SSLCommerz credentials. You can assess this by clicking the "SSL Commerz" menu at the admin side.
2. You can set the target pages based on payment status.
3. Set advanced options to fill the payment form.
4. Set if you want to add service charge over the payable amount.
5. Payment statistics page shows the list of payments along with their status.
6. This is how a typical payment form looks like on default WordPress theme.
7. This is the confirmation page before redirecting the user to the SSLCommerz gateway.

== Changelog ==
= 0.4 =
* Updated CSS and JS for admin side to work properly on version 4.4
= 0.3 =
* Implemented the payment form widget
= 0.2 =
* Implemented payment processing
= 0.1 =
* Implemented the admin area for the plugin