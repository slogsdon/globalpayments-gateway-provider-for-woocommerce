<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\ThreeDSecure;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\AbstractRequest;

defined('ABSPATH') || exit;

class CheckEnrollmentRequest extends AbstractRequest {
	const NOT_ENROLLED = 'NOT_ENROLLED';

	const NO_RESPONSE = 'NO_RESPONSE';

	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_CHECK_ENROLLMENT;
	}

	public function get_args() {
		return array();
	}

	public function do_request() {
		$response    = [];
		$requestData = $this->data;
		try {
			if ( isset( $requestData->tokenResponse ) ) {
				$tokenResponse = json_decode( $requestData->tokenResponse );
				$token = $tokenResponse->paymentReference;
			} else {
				$tokenResponse = \WC_Payment_Tokens::get( $requestData->wcTokenId );
				$token = $tokenResponse->get_token();
			}

			$paymentMethod = new CreditCardData();
			$paymentMethod->token = $token;

			$threeDSecureData = Secure3dService::checkEnrollment($paymentMethod)
				->withAmount($requestData->amount)
				->withCurrency($requestData->currency)
				->execute();
			$response["enrolled"] = $threeDSecureData->enrolled ?? self::NOT_ENROLLED;
			$response['version'] = $threeDSecureData->getVersion();
			$response["serverTransactionId"] = $threeDSecureData->serverTransactionId ?? '';
			$response["methodUrl"] = $threeDSecureData->issuerAcsUrl ?? '';
			$response['methodData'] = $threeDSecureData->payerAuthenticationRequest ?? '';
		} catch (\Exception $e) {
			$response = [
				'error'    => true,
				'message'  => $e->getMessage(),
				'enrolled' =>  self::NO_RESPONSE,
			];
		}


		wp_send_json( $response );
	}
}