<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

defined( 'ABSPATH' ) || exit;

class SaleRequest extends AuthorizationRequest {
	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_SALE;
	}
}
