<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class GeniusGateway extends AbstractGateway {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'globalpayments_genius';
		$this->method_title       = __( 'TSYS Genius', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the TSYS Genius gateway', 'globalpayments-gateway-provider-for-woocommerce' );

		$this->init_form_fields();
		$this->init_settings();

		$this->has_fields = true;
		$this->title      = $this->get_option( 'title' );
		$this->enabled    = $this->get_option( 'enabled' );

		$this->add_hooks();
	}
}
