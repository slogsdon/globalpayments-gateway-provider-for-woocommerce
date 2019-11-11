<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

defined( 'ABSPATH' ) || exit;

class VerifyRequest extends AbstractRequest {
	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_VERIFY;
	}

	public function get_args() {
		$gateway        = $this->get_request_data( 'payment_method' );
		$token_response = json_decode( stripslashes( $this->get_request_data( $gateway )['token_response'] ) );

		return array(
			RequestArg::CARD_DATA =>
				array(
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName
					'token' => $token_response->paymentReference,
				),
		);
	}
}
