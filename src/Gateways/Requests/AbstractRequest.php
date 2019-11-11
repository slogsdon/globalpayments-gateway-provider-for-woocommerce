<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

use WC_Order;

defined( 'ABSPATH' ) || exit;

abstract class AbstractRequest implements RequestInterface {
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
	 * Request multi-use token
	 *
	 * @var bool
	 */
	protected $request_token;

	/**
	 * Instantiates a new request
	 *
	 * @param WC_Order $order
	 * @param array $config
	 * @param bool $request_token
	 */
	public function __construct( WC_Order $order, array $config, $request_token = false ) {
		$this->order         = $order;
		$this->config        = $config;
		$this->request_token = $request_token;

		$this->data = $this->get_request_data();
	}

	public function get_default_args() {
		return array(
			RequestArg::SERVICES_CONFIG => $this->config,
			RequestArg::TXN_TYPE        => $this->get_transaction_type(),
			RequestArg::REQUEST_TOKEN   => $this->request_token,
		);
	}

	public function get_request_data( $key = null ) {
		if ( null === $key ) {
			// WooCommerce should verify nonce during its checkout handling
			// phpcs:ignore WordPress.Security.NonceVerification
			return $_POST;
		}

		return $this->data[ $key ];
	}
}
