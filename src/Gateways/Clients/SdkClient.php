<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Clients;

use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\WooCommercePaymentGatewayProvider\Data\PaymentTokenData;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\AbstractGateway;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\RequestArg;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\Requests\RequestInterface;
use WC_Payment_Token_CC;

defined( 'ABSPATH' ) || exit;

class SdkClient implements ClientInterface {
	/**
	 * Current request args
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Prepared builder args
	 *
	 * @var array
	 */
	protected $builder_args = [];

	protected $auth_transactions = [
		AbstractGateway::TXN_TYPE_AUTHORIZE,
		AbstractGateway::TXN_TYPE_SALE,
		AbstractGateway::TXN_TYPE_VERIFY,
	];

	protected $card_data = null;

	protected $previous_transaction = null;

	public function set_request( RequestInterface $request ) {
		$this->args = array_merge(
			$request->get_default_args(),
			$request->get_args()
		);
		$this->prepare_request_objects();
		return $this;
	}

	public function execute() {
		$this->configure_sdk();
		$builder = $this->get_transaction_builder();
		$this->prepare_builder( $builder );
		return $builder->execute();
	}

	protected function prepare_builder( TransactionBuilder $builder ) {
		foreach ( $this->builder_args as $name => $args ) {
			$method = 'with' . ucfirst( $name );

			if ( ! method_exists( $builder, $method ) ) {
				continue;
			}

			call_user_func_array( [ $builder, $method ], $args );
		}
	}

	/**
	 * Gets required builder for the transaction
	 *
	 * @return TransactionBuilder
	 */
	protected function get_transaction_builder() {
		/**
		 * Builder subject
		 *
		 * @var CreditCardData|Transaction $subject
		 */
		$subject =
			in_array( $this->args[ RequestArg::TXN_TYPE ], $this->auth_transactions, true )
			? $this->card_data : $this->previous_transaction;
		return $subject->{$this->args[ RequestArg::TXN_TYPE ]}();
	}

	protected function prepare_request_objects() {
		if ( $this->has_arg( RequestArg::AMOUNT ) ) {
			$this->builder_args['amount'] = [ $this->get_arg( RequestArg::AMOUNT ) ];
		}

		if ( $this->has_arg( RequestArg::CURRENCY ) ) {
			$this->builder_args['currency'] = [ $this->get_arg( RequestArg::CURRENCY ) ];
		}

		if ( $this->has_arg( RequestArg::CARD_DATA ) ) {
			/**
			 * Get the request's single- or multi-use token
			 *
			 * @var \WC_Payment_Token_Cc $token
			 */
			$token = $this->get_arg( RequestArg::CARD_DATA );
			$this->prepare_card_data( $token );

			if ( null !== $token && $token->get_meta( PaymentTokenData::KEY_SHOULD_SAVE_TOKEN, true ) ) {
				$this->builder_args['requestMultiUseToken'] = [ true ];
			}
		}

		if ( $this->has_arg( RequestArg::BILLING_ADDRESS ) ) {
			$this->prepare_address( AddressType::BILLING, $this->get_arg( RequestArg::BILLING_ADDRESS ) );
		}

		if ( $this->has_arg( RequestArg::SHIPPING_ADDRESS ) ) {
			$this->prepare_address( AddressType::SHIPPING, $this->get_arg( RequestArg::SHIPPING_ADDRESS ) );
		}
	}

	protected function prepare_card_data( WC_Payment_Token_CC $token = null ) {
		if ( null === $token ) {
			return;
		}

		$this->card_data           = new CreditCardData();
		$this->card_data->token    = $token->get_token();
		$this->card_data->expMonth = $token->get_expiry_month();
		$this->card_data->expYear  = $token->get_expiry_year();
	}

	protected function prepare_address( $address_type, array $data ) {
		$address       = new Address();
		$address->type = $address_type;

		$name = strtolower( $address_type ) . 'Address';

		$this->builder_args[ $name ] = [ $address, $address_type ];
	}

	protected function has_arg( $arg_type ) {
		return isset( $this->args[ $arg_type ] );
	}

	protected function get_arg( $arg_type ) {
		return $this->args[ $arg_type ];
	}

	protected function configure_sdk() {
		$config = new ServicesConfig();

		foreach ( $this->args[ RequestArg::SERVICES_CONFIG ] as $name => $value ) {
			if ( property_exists( $config, $name ) ) {
				$config->{$name} = $value;
			}
		}

		ServicesContainer::configure( $config );
	}
}
