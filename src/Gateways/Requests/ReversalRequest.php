<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;

defined( 'ABSPATH' ) || exit;

class ReversalRequest extends AbstractRequest {
	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_REVERSAL;
	}

	public function get_args() {
		$new_amount = null;

		if ( null !== $this->order ) {
			$original_amount = wc_format_decimal( $order->get_total(), 2 );
			$total_refunded  = wc_format_decimal( $order->get_total_refunded(), 2 );
			$new_amount      = wc_format_decimal( $original_amount - $total_refunded, 2 );
		}

		return array(
			RequestArg::AMOUNT      => null !== $this->order ? $this->order->get_total() : null,
			RequestArg::CURRENCY    => null !== $this->order ? $this->order->get_currency() : null,
			RequestArg::AUTH_AMOUNT => $new_amount,
			RequestArg::TXN_ID      => null,
			RequestArg::DESCRIPTION => $reason,
		);
	}
}
