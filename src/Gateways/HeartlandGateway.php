<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;

defined( 'ABSPATH' ) || exit;

class HeartlandGateway extends AbstractGateway {
	public $gateway_provider = GatewayProvider::PORTICO;

	/**
	 * Merchant location public API key
	 *
	 * Used for single-use tokenization on frontend
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * Merchant location secret API key
	 *
	 * Used for gateway transactions on backend
	 *
	 * @var string
	 */
	public $secret_key;

	public function configure_method_settings() {
		$this->id                 = 'globalpayments_heartland';
		$this->method_title       = __( 'Heartland', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the Heartland Portico gateway', 'globalpayments-gateway-provider-for-woocommerce' );
	}

	public function get_first_line_support_email() {
		return 'securesubmitcert@e-hps.com';
	}

	public function get_gateway_form_fields() {
		return array(
			'public_key' => array(
				'title'       => __( 'Public Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			),
			'secret_key' => array(
				'title'       => __( 'Secret Key', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => '',
			),
		);
	}

	public function get_frontend_gateway_options() {
		return array(
			'publicApiKey' => $this->public_key,
		);
	}

	public function get_backend_gateway_options() {
		return array(
			'secretApiKey' => $this->secret_key,
		);
	}

	protected function is_transaction_active( TransactionSummary $details ) {
		// @phpcs:ignore WordPress.NamingConventions.ValidVariableName
		return 'A' === $details->transactionStatus;
	}

	/**
	 * returns decline message for display to customer
	 * 
	 * @param string $response_code
	 *
	 * @return string
	 */
	public function get_decline_message( string $response_code ) {
		switch ($response_code) {
			case '02':
			case '03':
			case '04':
			case '05':
			case '41':
			case '43':
			case '44':
			case '51':
			case '56':
			case '61':
			case '62':
			case '62':
			case '63':
			case '65':
			case '78':
				return 'The card was declined.';
			case '06':
			case '07':
			case '12':
			case '15':
			case '19':
			case '52':
			case '53':
			case '57':
			case '58':
			case '76':
			case '77':
			case '96':
			case 'EC':
				return 'An error occured while processing the card.';
			case '13':
				return 'Must be greater than or equal 0.';
			case '14':
				return 'The card number is incorrect.';
			case '54':
				return 'The card has expired.';
			case '55':
				return 'The pin is invalid.';
			case '75':
				return 'Maximum number of pin retries exceeded.';
			case '80':
				return 'Card expiration date is invalid.';
			case '86':
				return 'Can\'t verify card pin number.';
			case '91':
				return 'The card issuer timed-out.';
			case 'EB':
			case 'N7':
				return 'The card\'s security code is incorrect.';
			case 'FR':
				return 'Possible fraud detected.';
			default:
				return 'An error occurred while processing the card.';
		}
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
}
