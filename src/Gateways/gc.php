<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\ServicesConfig;

class gc extends GiftCard {
    public $gift_card_name;

    public $gift_card_id;

    public $temp_balance;

    public $used_amount;

    protected function configureServiceContainer() {
        $config = new ServicesConfig();
        $config->secretApiKey = $this->get_backend_gateway_options()['secretApiKey'];
        $config->developerId = "123456";
        $config->versionNumber = "1234";

        ServicesContainer::configure($config);
    }

	/**
	 * returns gift-specific decline message for display to customer
	 * 
	 * @param string $response_code
	 *
	 * @return string
	 */
	public function get_gift_decline_message( string $response_code ) {
		switch ($response_code) {
			case '1':
			case '2':
			case '11':
				return 'An unknown gift error has occurred.';
			case '3':
			case '8':
				return 'The card data is invalid.';
			case '4':
				return 'The card has expired.';
			case '5':
			case '12':
				return 'The card was declined.';
			case '6':
			case '7':
			case '10':
				return 'An error occurred while processing this card.';
			case '9':
				return 'Must be greater than or equal 0.';
			case '13':
				return 'The amount was partially approved.';
			case '14':
				return 'The pin is invalid';			
			default:
				return 'An error occurred while processing the gift card.';
		}
	}

	// do base gift card stuff
	public function applyGiftCard() {
        // $this->gift_card_submitted     = $_POST['gift_card_number'];
		// $this->gift_card_pin_submitted = $_POST['gift_card_pin'];
		$gift_card_balance = $this->gift_card_balance(
            $_POST['gift_card_number'],
            $_POST['gift_card_pin']
        );

        if ($gift_card_balance['error']) {
            echo json_encode(array(
                'error' => 1,
                'message' => $gift_card_balance['message'],
            ));
        } else {
            $this->temp_balance = $gift_card_balance['message'];

            $this->addGiftCardToCartSession();
            $this->updateGiftCardCartTotal();
            echo json_encode(array(
                'error'   => 0,
                'balance' => html_entity_decode(get_woocommerce_currency_symbol()) . $gift_card_balance['message'],
            ));
        }

        wp_die();
	}

	// stolen
	public function gift_card_balance($gift_card_number, $gift_card_pin)
    {
        $this->configureServiceContainer();

        if (empty($gift_card_pin)) {
            return array(
                'error'   => true,
                'message' => "PINs are required. Please enter a PIN and click apply again.",
            );
        }

        $this->gift_card = $this->giftCardObject($gift_card_number, $gift_card_pin);

        try {
            $response = $this->gift_card->balanceInquiry()
                ->execute(); // need the service container for this to work
        } catch (Exception $e) {
            return array(
                'error'   => true,
                'message' => "The gift card number you entered is either incorrect or not yet activated.",
            );
        }

        // wc_clear_notices(); // I don't know what this does

        return array(
            'error' => false,
            'message' => $response->balanceAmount,
        );
	}
	
	protected function giftCardObject($gift_card_number, $gift_card_pin)
    {
        $gift_card         = new gc();
        $gift_card->number = $gift_card_number;
        $gift_card->pin    = $gift_card_pin;

        return $gift_card;
    }

    protected function addGiftCardToCartSession()
    {
        $this->gift_card->gift_card_name = $this->giftCardName($this->gift_card->number);
        $this->gift_card->gift_card_id   = sanitize_title($this->gift_card->gift_card_name);
        $this->gift_card->pin            = $this->gift_card_pin_submitted;

        WC()->session->set('securesubmit_gift_card_object', $this->gift_card);
    }

    protected function giftCardName($gift_card_number)
    {
        $digits_to_display = 5;
        $last_digits       = substr($gift_card_number, $digits_to_display * - 1);

        return __( 'Gift Card', 'wc_securesubmit' ) . ' ' . $last_digits;
    }

}