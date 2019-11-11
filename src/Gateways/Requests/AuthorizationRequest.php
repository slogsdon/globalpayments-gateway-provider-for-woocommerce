<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;

defined( 'ABSPATH' ) || exit;

class AuthorizationRequest extends AbstractRequest {
	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_AUTHORIZE;
	}

	public function get_args() {
		$gateway        = $this->get_request_data( 'payment_method' );
		$token_response = json_decode( stripslashes( $this->get_request_data( $gateway )['token_response'] ) );

		return array(
			RequestArg::AMOUNT    => $this->order->get_total(),
			RequestArg::CURRENCY  => $this->order->get_currency(),
			RequestArg::CARD_DATA =>
				array(
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName
					'token' => $token_response->paymentReference,
				),
		);
	}
}
