<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class HeartlandGateway extends AbstractGateway {
	/**
	 * Merchant location public API key
	 *
	 * Used for single-use tokenization on frontend
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * Merchant location secret API key
	 *
	 * Used for gateway transactions on backend
	 *
	 * @var string
	 */
	public $secret_key;

	public function configure_method_settings() {
		$this->id                 = 'globalpayments_heartland';
		$this->method_title       = __( 'Heartland', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the Heartland Portico gateway', 'globalpayments-gateway-provider-for-woocommerce' );
	}

	public function get_gateway_form_fields() {
		return [
			'public_key' => [
				'title'       => __( 'Public Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			],
			'secret_key' => [
				'title'       => __( 'Secret Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			],
		];
	}

	public function get_frontend_gateway_options() {
		return [
			'publicApiKey' => $this->public_key,
		];
	}

	public function get_backend_gateway_options() {
		return [
			'secretApiKey' => $this->secret_key,
		];
	}
}
