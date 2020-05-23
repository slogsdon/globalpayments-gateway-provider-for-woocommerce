<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\gc;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use stdClass;


defined( 'ABSPATH' ) || exit;

class HeartlandGatewayGift extends HeartlandGateway {

    protected $temp_balance;

    protected $gift_card_pin_submitted;

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

	/**
	 * Adds Heartland gift card fields 
	 */
	public function payment_fields() {
		parent::payment_fields();

		if ( $this->allow_gift_cards === true ) {
			include_once 'wp-content\plugins\globalpayments-gateway-provider-for-woocommerce\src\Gateways\HMS-fields.php';

			// wp_enqueue_script('test', '/wp-content/plugins/globalpayments-gateway-provider-for-woocommerce/assets/frontend/js/test.js', array('jquery'), false, true);

			// SecureSubmit custom CSS
			wp_enqueue_style('heartland-gift-cards', '/wp-content/plugins/globalpayments-gateway-provider-for-woocommerce/assets/frontend/css/heartland-gift-cards.css');
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
        $this->gift_card->temp_balance   = floatval($this->temp_balance);
        // $this->gift_card->pin            = $this->gift_card_pin;

        WC()->session->set('securesubmit_gift_card_object', $this->gift_card);
    }

    protected function giftCardName($gift_card_number)
    {
        $digits_to_display = 5;
        $last_digits       = substr($gift_card_number, $digits_to_display * - 1);

        return __( 'Gift Card', 'wc_securesubmit' ) . ' ' . $last_digits;
    }

    protected function updateGiftCardCartTotal()
    {
        $gift_card_object_entered = WC()->session->get('securesubmit_gift_card_object');
        if (is_null($gift_card_object_entered)) {
            $gift_card_object_entered = (object)array();
        }

        $gift_card_object_applied = WC()->session->get('securesubmit_gift_card_applied');
        if (is_null($gift_card_object_applied)) {
            $gift_card_object_applied = (object)array();
        }

        $original_total = $this->getOriginalCartTotal();

        $securesubmit_data = WC()->session->get('securesubmit_data');
        if (!is_object($securesubmit_data)) {
            $securesubmit_data = new stdClass();
        }

        $securesubmit_data->original_total = $original_total;
        WC()->session->set('securesubmit_data', $securesubmit_data);

        $this->updateGiftCardTotals();

        if (is_object($gift_card_object_entered) && count(get_object_vars($gift_card_object_entered)) > 0) {
            if ($gift_card_object_entered->temp_balance === '0.00') {
                WC()->session->__unset('securesubmit_gift_card_object');

                $zero_balance_message = apply_filters(
                    'securesubmit_zero_balance_message',
                    sprintf(
                        __('%s has a balance of zero and could not be applied to this order.', 'wc_securesubmit'),
                        $gift_card_object_entered->gift_card_name
                    )
                );

                wc_add_notice($zero_balance_message, 'error');
            } else {
                if (!(is_object($gift_card_object_applied) && count(get_object_vars($gift_card_object_applied)) > 0)) {
                    $gift_card_object_applied = new stdClass;
                }

                $gift_card_object_entered->used_amount                               = $this->giftCardUsageAmount();
                $gift_card_object_applied->{$gift_card_object_entered->gift_card_id} = $gift_card_object_entered;

                WC()->session->set('securesubmit_gift_card_applied', $gift_card_object_applied);
                WC()->session->__unset('securesubmit_gift_card_object');
            }
        }

        return $gift_card_object_applied;
    }

    protected function getOriginalCartTotal()
    {
        $cart_totals = WC()->session->get('cart_totals');
        $original_total = round(
            array_sum(
                array(
                    (!empty($cart_totals['subtotal']) ? $cart_totals['subtotal'] : 0),
                    (!empty($cart_totals['subtotal_tax']) ? $cart_totals['subtotal_tax'] : 0),
                    (!empty($cart_totals['shipping_total']) ? $cart_totals['shipping_total'] : 0),
                    (!empty($cart_totals['shipping_tax']) ? $cart_totals['shipping_tax'] : 0),
                    (!empty($cart_totals['fee_total']) ? $cart_totals['fee_total'] : 0),
                    (!empty($cart_totals['fee_tax']) ? $cart_totals['fee_tax'] : 0),
                )
            ),
            2
        );
        return $original_total;
    }

    protected function updateGiftCardTotals()
    {
        $gift_cards_applied = WC()->session->get('securesubmit_gift_card_applied');
        $securesubmit_data  = WC()->session->get('securesubmit_data');

        $original_total = $this->getOriginalCartTotal();
        $remaining_total = $original_total;

        if (is_object($gift_cards_applied) && count(get_object_vars($gift_cards_applied)) > 0) {
            foreach ($gift_cards_applied as $gift_card) {
                $order_total_after_gift_card = $remaining_total - $gift_card->temp_balance;

                if ($order_total_after_gift_card > 0) {
                    $gift_card->used_amount = $gift_card->temp_balance;
                } else {
                    $gift_card->used_amount = $remaining_total;
                }

                $gift_cards_applied->{$gift_card->gift_card_id} = $gift_card;
                $remaining_total = $remaining_total - $gift_card->used_amount;
            }
        }

        WC()->session->set('securesubmit_gift_card_applied', $gift_cards_applied);
    }

    protected function giftCardUsageAmount($updated = false)
    {
        if ($updated) {
            $cart_total       = $this->getTotalMinusSecureSubmitGiftCards();
            $gift_card_object = $this->applied_gift_card;
        } else {
            $cart_totals = WC()->session->get('cart_totals');
            $cart_total = round($cart_totals['total'], 2);
            $gift_card_object = WC()->session->get('securesubmit_gift_card_object');
        }

        if (round($gift_card_object->temp_balance, 2) <= $cart_total) {
            $gift_card_applied_amount = $gift_card_object->temp_balance;
        } else {
            $gift_card_applied_amount = $cart_total;
        }

        return $gift_card_applied_amount;
    }

    public function addGiftCards()
    {
        // TODO: Add warnings and success messages
        // $gift_cards_allowed = $this->giftCardsAllowed();
        $gift_cards_allowed = true;

        // No gift cards if there are subscription products in the cart
        if ($gift_cards_allowed) {
            $original_total = $this->getOriginalCartTotal();
            $gift_card_object_applied = $this->updateGiftCardCartTotal();

            if (is_object($gift_card_object_applied) && count(get_object_vars($gift_card_object_applied)) > 0) {
                $securesubmit_data = WC()->session->get('securesubmit_data');
                $securesubmit_data->original_total = $original_total;
                WC()->session->set('securesubmit_data', $securesubmit_data);

                $message           = __( 'Total Before Gift Cards', 'wc_securesubmit' );

                $order_total_html  = '<tr id="securesubmit_order_total" class="order-total">';
                $order_total_html .= '<th>' . $message . '</th>';
                $order_total_html .= '<td data-title="' . esc_attr($message) . '">' . wc_price($original_total) . '</td>';
                $order_total_html .= '</tr>';

                echo apply_filters('securesubmit_before_gift_cards_order_total', $order_total_html, $original_total, $message);

                foreach ($gift_card_object_applied as $applied_gift_card) {
                    $remove_link = '<a href="#" id="' . $applied_gift_card->gift_card_id . '" class="securesubmit-remove-gift-card">(Remove)</a>';

                    $gift_card_html  = '<tr class="fee">';
                    $gift_card_html .= '<th>' . $applied_gift_card->gift_card_name . ' ' . $remove_link . '</th>';
                    $gift_card_html .= '<td data-title="' . esc_attr($applied_gift_card->gift_card_name) . '">' . wc_price($applied_gift_card->used_amount) . '</td>';
                    $gift_card_html .= '</tr>';

                    echo apply_filters('securesubmit_gift_card_used_total', $gift_card_html, $applied_gift_card->gift_card_name, $remove_link, $applied_gift_card->used_amount);
                }
            }
        } else {
            $applied_cards = WC()->session->get('securesubmit_gift_card_applied');

            $this->removeAllGiftCardsFromSession();

            if (is_object($applied_cards) && count(get_object_vars($applied_cards)) > 0) {
                wc_add_notice(__('Sorry, we are unable to allow gift cards to be used when purchasing a subscription. Any gift cards already applied to the order have been cleared', 'wc_securesubmit'), 'notice');
            }
        }
    }

    public function updateOrderTotal($cart_total, $cart_object)
    {
        $gift_cards = WC()->session->get('securesubmit_gift_card_applied');

        if (is_object($gift_cards) && count(get_object_vars($gift_cards)) > 0) {
            $gift_card_totals = $this->getGiftCardTotals();
            $cart_total = $cart_total + $gift_card_totals;
        }

        return $cart_total;
    }

    protected function getGiftCardTotals()
    {
        $this->updateGiftCardTotals();

        $gift_cards = WC()->session->get('securesubmit_gift_card_applied');

        if (!empty($gift_cards)) {
            $total = 0;

            foreach ($gift_cards as $gift_card) {
                $total -= $gift_card->used_amount;
            }

            return $total;
        }
    }

    public function processGiftCardSale($card_number, $card_pin, $used_amount)
    {
        $card            = $this->giftCardObject($card_number, $card_pin);
        $rounded_amount  = round($used_amount, 2);
        $positive_amount = abs($rounded_amount);

        $response = $card->charge($positive_amount)
            ->withCurrency('USD')
            ->execute();

        return $response;
    }

    public function removeAllGiftCardsFromSession()
    {
        WC()->session->__unset('securesubmit_gift_card_applied');
        WC()->session->__unset('securesubmit_gift_card_object');
        WC()->session->__unset('securesubmit_data');
    }

    public function removeGiftCard($removed_card = null)
    {
        if (isset($_POST['securesubmit_card_id']) && empty($removed_card)) {
            $removed_card = $_POST['securesubmit_card_id'];
        }

        $applied_cards = WC()->session->get('securesubmit_gift_card_applied');

        unset($applied_cards->{$removed_card});

        if (count((array) $applied_cards) > 0) {
            WC()->session->set('securesubmit_gift_card_applied', $applied_cards);
        } else {
            WC()->session->__unset('securesubmit_gift_card_applied');
        }

        if (isset($_POST['securesubmit_card_id']) && empty($removed_card)) {
            echo '';
            wp_die();
        }
    }

}
