<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

use WC_Order;

defined( 'ABSPATH' ) || exit;

abstract class AbstractRequest implements RequestInterface {
	/**
	 * Gateway ID
	 *
	 * @var string
	 */
	public $gateway_id;

	/**
	 * Current WooCommerce order object
	 *
	 * @var WC_Order
	 */
	public $order;

	/**
	 * Gateway config
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * POST request data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Instantiates a new request
	 *
	 * @param string $gateway_id
	 * @param WC_Order $order
	 * @param array $config
	 */
	public function __construct( $gateway_id, WC_Order $order = null, array $config = array() ) {
		$this->gateway_id = $gateway_id;
		$this->order      = $order;
		$this->config     = $config;

		$this->data = $this->get_request_data();
	}

	public function get_default_args() {
		return array(
			RequestArg::SERVICES_CONFIG => $this->config,
			RequestArg::TXN_TYPE        => $this->get_transaction_type(),
		);
	}

	public function get_request_data( $key = null ) {
		if ( null === $key ) {
			// WooCommerce should verify nonce during its checkout handling
			// phpcs:ignore WordPress.Security.NonceVerification
			return $_POST;
		}

		return wc_clean( $this->data[ $key ] );
	}
}
