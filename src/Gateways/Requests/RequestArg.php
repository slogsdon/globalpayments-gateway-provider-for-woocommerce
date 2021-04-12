<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

abstract class RequestArg {
	const AMOUNT           = 'AMOUNT';
	const BILLING_ADDRESS  = 'BILLING_ADDRESS';
	const CARD_DATA        = 'CARD_DATA';
	const CARD_HOLDER_NAME = 'CARD_HOLDER_NAME';
	const CURRENCY         = 'CURRENCY';
	const PARES            = 'PARES';
	const PERMISSIONS      = 'PERMISSIONS';
	const SERVER_TRANS_ID  = 'SERVER_TRANS_ID';
	const SERVICES_CONFIG  = 'SERVICES_CONFIG';
	const SHIPPING_ADDRESS = 'SHIPPING_ADDRESS';
	const TXN_TYPE         = 'TXN_TYPE';
	const GATEWAY_ID       = 'GATEWAY_ID';
	const DESCRIPTION      = 'DESCRIPTION';
	const AUTH_AMOUNT      = 'AUTH_AMOUNT';
}
