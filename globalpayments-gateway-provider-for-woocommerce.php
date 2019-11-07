<?php
/**
 * Plugin Name: Global Payments Gateway Provider for WooCommerce
 * Plugin URI: https://github.com/globalpayments/globalpayments-gateway-provider-for-woocommerce
 * Description: This extension allows WooCommerce to use the available Global Payments payment gateways. All card data is tokenized using the respective gateway's tokenization service.
 * Version: 1.0.0
 * Requires PHP: 5.5.9
 * WC tested up to: 3.3.1
 * Author: Global Payments
 * Author URI: https://github.com/globalpayments/globalpayments-gateway-provider-for-woocommerce
*/

defined( 'ABSPATH' ) || exit;

if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
	return;
}

/**
 * Autoload SDK.
 *
 * The package autoloader includes version information which prevents classes in this feature plugin
 * conflicting with WooCommerce core.
 *
 * We want to fail gracefully if `composer install` has not been executed yet, so we are checking for the autoloader.
 * If the autoloader is not present, let's log the failure and display a nice admin notice.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	include_once $autoloader;
	add_action( 'plugins_loaded', array( \GlobalPayments\WooCommercePaymentGatewayProvider\Plugin::class, 'init' ) );
}
