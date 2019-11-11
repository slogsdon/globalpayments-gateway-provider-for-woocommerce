<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Handlers;

class PaymentTokenHandler extends AbstractHandler {
	protected $card_type_map = array(
		'mastercard' => 'mastercard',
		'visa'       => 'visa',
		'discover'   => 'discover',
		'amex'       => 'american express',
		'diners'     => 'diners',
		'jcb'        => 'jcb',
	);

	public function handle() {
		if ( ! $this->response->token ) {
			return;
		}

		$gateway        = $this->get_request_data( 'payment_method' );
		$token_response = json_decode( stripslashes( $this->get_request_data( $gateway )['token_response'] ) );

		$token = new WC_Payment_Token_CC();
		$token->set_token( $this->response->token );
		$token->set_user_id( get_current_user_id() );

		if ( isset( $token_response->details ) ) {
			if ( isset( $token_response->details->cardLast4 ) ) {
				$token->set_last4( $token_response->details->cardLast4 );
			}

			if ( isset( $token_response->details->expiryYear ) ) {
				$token->set_expiry_year( $token_response->details->expiryYear );
			}

			if ( isset( $token_response->details->expiryMonth ) ) {
				$token->set_expiry_month( $token_response->details->expiryMonth );
			}

			if ( isset( $token_response->details->cardType ) ) {
				$token->set_card_type( $this->card_type_map[ $token_response->details->cardType ] );
			}
		}

		$token->save();
	}
}
