<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\HeartlandGiftCards;

use GlobalPayments\Api\PaymentMethods\GiftCard;

defined( 'ABSPATH' ) || exit;

// tacks on needed vars to Heartland's existing class
class HeartlandGiftCard extends GiftCard {
    public $gift_card_name;
    public $gift_card_id;
    public $temp_balance;
    public $used_amount;
}
