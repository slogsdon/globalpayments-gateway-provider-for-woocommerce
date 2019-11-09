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
		return array();
	}

	public function get_frontend_gateway_options() {
		return array( 'publicApiKey' => 'pkapi_cert_jKc1FtuyAydZhZfbB3' );
	}
}
