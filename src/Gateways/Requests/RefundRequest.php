<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;

defined( 'ABSPATH' ) || exit;

class RefundRequest extends AbstractRequest {
	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_REFUND;
	}

	public function get_args() {
		return array(
			RequestArg::AMOUNT      => null !== $this->order ? $this->order->get_total() : null,
			RequestArg::CURRENCY    => null !== $this->order ? $this->order->get_currency() : null,
			RequestArg::TXN_ID      => null,
			RequestArg::DESCRIPTION => $reason,
		);
	}
}
