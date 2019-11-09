<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class HeartlandGateway extends AbstractGateway {
	public function configure_method_settings() {
		$this->id                 = 'globalpayments_heartland';
		$this->method_title       = __( 'Heartland', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the Heartland Portico gateway', 'globalpayments-gateway-provider-for-woocommerce' );
	}

	public function configure_merchant_settings() {
		parent::configure_merchant_settings();

		$this->secret_key = $this->get_option( 'secret_key' );
		$this->public_key = $this->get_option( 'public_key' );
	}

	public function get_gateway_form_fields() {
		return array(
			'public_key'        => array(
				'title'       => __( 'Public Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			),
			'secret_key'        => array(
				'title'       => __( 'Secret Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			),
		);
	}

	public function get_frontend_gateway_options() {
		return array( 'publicApiKey' => $this->public_key );
	}
}
