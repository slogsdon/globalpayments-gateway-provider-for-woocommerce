<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

use stdClass;
use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/*
 *
 */

class gcOrder {

    public function __construct() {

    }

    public function addItemsToPostOrderDisplay( $rows, $order_object ) {

        $order_id = $order_object->id;

        $applied_gift_cards = unserialize( get_post_meta( $order_id, '_securesubmit_used_card_data', TRUE ) );
        $original_balance   = get_post_meta( $order_id, '_securesubmit_original_reported_total', TRUE );

        if ( ! empty ( $applied_gift_cards ) ) {

            $rows = $this->buildOrderRows( $rows, $original_balance, $applied_gift_cards );

        }

        return $rows;

    }

	public function addItemsToOrderDisplay( $rows, $order_object ) {
		if (null == WC()->session) {
			return $rows;
		}

		$securesubmit_data = WC()->session->get( 'securesubmit_data' );
		$applied_cards     = WC()->session->get( 'securesubmit_gift_card_applied' );

        if ( ! empty( $applied_cards ) ) {

            // buildOrderRows( $rows, $order_total, $applied_cards )
            $rows = $this->buildOrderRows( $rows, $securesubmit_data->original_total, $applied_cards );

        }

        return $rows;

    }

    public static function processGiftCardPayment( $order_id ) {

        $applied_gift_card      = WC()->session->get( 'securesubmit_gift_card_applied' ); // not going here
        $securesubmit_data      = WC()->session->get( 'securesubmit_data' );
        $order_awaiting_payment = $order_id;
        $giftcard_gateway       = new HeartlandGatewayGift();
        $gift_card_sales        = array();

        foreach ( $applied_gift_card as $gift_card ) {

            $gift_card_number       = $gift_card->number;
            $gift_card_pin          = $gift_card->pin;
            $gift_card_temp_balance = $gift_card->temp_balance;
            $gift_card_balance      = $giftcard_gateway->gift_card_balance( $gift_card_number, $gift_card_pin );

            if ( $gift_card_balance[ 'message' ] < $gift_card_temp_balance ) {

                $giftcard_gateway->removeGiftCard( $gift_card->gift_card_id );

                $balance_message = sprintf( __( 'The %s now has a lower balance than when it was originally applied to the order. It has been removed from the order. Please add it to the order again.', 'wc_securesubmit' ), $gift_card->gift_card_name );

                // Void the already done transactions if any
                $giftcard_gateway->processGiftCardVoid( $gift_card_sales, $order_awaiting_payment );

                throw new Exception( $balance_message );

            }

            $sale_response = $giftcard_gateway->processGiftCardSale( $gift_card_number, $gift_card_pin, $gift_card->used_amount );

            if ( ! isset( $sale_response->responseCode ) || $sale_response->responseCode !== '00' ) {

                $sale_response_message = sprintf( __( 'The %s was not able to be processed.', 'wc_securesubmit' ), $gift_card->gift_card_name );

                // Void the already done transactions if any
                if ( ! empty( $gift_card_sales ) ) {

                    $giftcard_gateway->processGiftCardVoid( $gift_card_sales, $order_awaiting_payment );

                }

                throw new Exception( $sale_response_message );

            }

            $used_amount_positive = $gift_card->used_amount * - 1;

            $gift_card_sales[ $gift_card->gift_card_id ] = new stdClass();

            $gift_card_sales[ $gift_card->gift_card_id ]->gift_card_name    = $gift_card->gift_card_name;
            $gift_card_sales[ $gift_card->gift_card_id ]->gift_card_id      = $gift_card->gift_card_id;
            $gift_card_sales[ $gift_card->gift_card_id ]->transaction_id    = $sale_response->transactionId;
            $gift_card_sales[ $gift_card->gift_card_id ]->remaining_balance = $sale_response->balanceAmount;
            $gift_card_sales[ $gift_card->gift_card_id ]->used_amount       = $used_amount_positive;

        }

        update_post_meta( $order_awaiting_payment, '_securesubmit_used_card_data', serialize( $gift_card_sales ) );
        update_post_meta( $order_awaiting_payment, '_securesubmit_original_reported_total', $securesubmit_data->original_total );

        foreach ( $gift_card_sales as $gift_card_sale ) {

            $balance_used = wc_price( $gift_card_sale->used_amount );

            $note_text = sprintf( __( '%s was used on this order with a total used amount of %s. Transaction ID: %s ', 'wc_securesubmit' ), $gift_card_sale->gift_card_name, $balance_used, $gift_card_sale->transaction_id );

            $order = new WC_Order( $order_awaiting_payment );
            $order->add_order_note( $note_text );

        }

        $giftcard_gateway->removeAllGiftCardsFromSession();

    }

    public function processGiftCardsZeroTotal( $order_id, $posted ) {
        $appliedCards = WC()->session->get('securesubmit_gift_card_applied');

        if ( empty( $posted[ 'payment_method' ] ) ) {

            $this->processGiftCardPayment( $order_id );

        } 

    }

    protected function buildOrderRows( $rows, $order_total, $applied_cards ) {

        $index_of_order_total = array_search( 'order_total', array_keys( $rows ) );

        $gift_card_array[ 'original_total' ] = array(
            'label' => __( 'Total before Gift Cards', 'wc_securesubmit' ),
            'value' => wc_price( $order_total ),
        );

        foreach ( $applied_cards as $card ) {

            $gift_card_array[ $card->gift_card_id ] = array(
                'label' => $card->gift_card_name,
                'value' => wc_price( $card->used_amount * - 1 ),
            );

        }

        $rows_first_part = array_slice( $rows, 0, $index_of_order_total, TRUE );
        $rows_last_part  = array_slice( $rows, $index_of_order_total, PHP_INT_MAX, TRUE );

        $rows = array_merge( $rows_first_part, $gift_card_array, $rows_last_part );

        return $rows;

    }



}
