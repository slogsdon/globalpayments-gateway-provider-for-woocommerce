<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class GeniusGateway extends AbstractGateway {
	public function configure_method_settings() {
		$this->id                 = 'globalpayments_genius';
		$this->method_title       = __( 'TSYS Genius', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the TSYS Genius gateway', 'globalpayments-gateway-provider-for-woocommerce' );
	}

	public function get_gateway_form_fields() {
		return [];
	}

	public function get_frontend_gateway_options() {
		return [];
	}

	public function get_backend_gateway_options() {
		return [];
	}
}
