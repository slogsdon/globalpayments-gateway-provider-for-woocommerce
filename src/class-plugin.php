<?php
/**
 * Returns information about the package and handles init.
 */

namespace GlobalPayments\WooCommercePaymentGatewayProvider;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Plugin {
	/**
	 * Version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Init the package.
	 */
	public static function init() {
	}

	/**
	 * Return the version of the package.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::VERSION;
	}

	/**
	 * Return the path to the package.
	 *
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	}
}
