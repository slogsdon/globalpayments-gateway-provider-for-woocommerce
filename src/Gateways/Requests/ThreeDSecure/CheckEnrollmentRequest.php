<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\ThreeDSecure;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\Secure3dService;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\AbstractRequest;

defined('ABSPATH') || exit;

class CheckEnrollmentRequest extends AbstractRequest {
	const NOT_ENROLLED = 'NOT_ENROLLED';

	public function get_transaction_type() {
		return AbstractGateway::TXN_TYPE_CHECK_ENROLLMENT;
	}

	public function get_args() {
		return array();
	}

	public function do_request() {
		$requestData = $this->data;
		$tokenResponse = json_decode($requestData->tokenResponse);

		$paymentMethod = new CreditCardData();
		$paymentMethod->token = $tokenResponse->paymentReference;

		$threeDSecureData = Secure3dService::checkEnrollment($paymentMethod)
			->withAmount($requestData->amount)
			->withCurrency($requestData->currency)
			->execute();
		$response["enrolled"] = $threeDSecureData->enrolled ?? self::NOT_ENROLLED;
		$response['version'] = $threeDSecureData->getVersion();
		$response["serverTransactionId"] = $threeDSecureData->serverTransactionId ?? '';
		$response["methodUrl"] = $threeDSecureData->issuerAcsUrl ?? '';
		$response['methodData'] = $threeDSecureData->payerAuthenticationRequest ?? '';

		wp_send_json($response);
	}
}