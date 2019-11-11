<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests;

abstract class RequestArg {
	const AMOUNT           = 'AMOUNT';
	const BILLING_ADDRESS  = 'BILLING_ADDRESS';
	const CARD_DATA        = 'CARD_DATA';
	const CURRENCY         = 'CURRENCY';
	const REQUEST_TOKEN    = 'REQUEST_TOKEN';
	const SERVICES_CONFIG  = 'SERVICES_CONFIG';
	const SHIPPING_ADDRESS = 'SHIPPING_ADDRESS';
	const TXN_TYPE         = 'TXN_TYPE';
}
