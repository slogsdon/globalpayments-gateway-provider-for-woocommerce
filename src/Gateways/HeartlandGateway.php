<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class HeartlandGateway extends AbstractGateway {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'globalpayments_heartland';
		$this->method_title       = __( 'Heartland', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the Heartland Portico gateway', 'globalpayments-gateway-provider-for-woocommerce' );

		$this->init_form_fields();
		$this->init_settings();

		$this->has_fields        = true;
		$this->title             = $this->get_option( 'title' );
		$this->enabled           = $this->get_option( 'enabled' );
		$this->secret_key        = $this->get_option( 'secret_key' );
		$this->public_key        = $this->get_option( 'public_key' );
		$this->payment_action    = $this->get_option( 'payment_action' );
		$this->txn_descriptor    = $this->get_option( 'txn_descriptor' );
		$this->allow_card_saving = $this->get_option( 'allow_card_saving' ) === 'yes';

		$this->add_hooks();
	}

	public function init_form_fields() {
		parent::init_form_fields();

		$this->form_fields = array_merge(
			$this->form_fields,
			array(
				'public_key'        => array(
					'title'       => __( 'Public Key', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
					'default'     => '',
				),
				'secret_key'        => array(
					'title'       => __( 'Secret Key', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Get your API keys from your Heartland Online Payments account.', 'globalpayments-gateway-provider-for-woocommerce' ),
					'default'     => '',
				),
				'allow_card_saving' => array(
					'title'       => __( 'Allow Card Saving', 'globalpayments-gateway-provider-for-woocommerce' ),
					'label'       => __( 'Allow Card Saving', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => 'Note: to use the card saving feature, you must have multi-use tokenization enabled on your Heartland account.',
					'default'     => 'no',
				),
				'payment_action'    => array(
					'title'       => __( 'Payment Action', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose whether you wish to capture funds immediately, authorize payment only for a delayed capture or verify and capture when the order ships.', 'globalpayments-gateway-provider-for-woocommerce' ),
					'default'     => 'sale',
					'desc_tip'    => true,
					'options'     => array(
						'sale'          => __( 'Capture', 'globalpayments-gateway-provider-for-woocommerce' ),
						'authorization' => __( 'Authorize', 'globalpayments-gateway-provider-for-woocommerce' ),
						'verify'        => __( 'Verify', 'globalpayments-gateway-provider-for-woocommerce' ),
					),
				),
				'txn_descriptor'    => array(
					'title'             => __( 'Order Transaction Descriptor', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'              => 'text',
					'description'       => sprintf(
						/* translators: %s: Email address of support team */
						__( 'During a Capture or Authorize payment action, this value will be passed along as the TxnDescriptor. Please contact <a href="mailto:%s?Subject=WooCommerce%%20TxnDescriptor Option">support</a> with any question regarding this option.', 'globalpayments-gateway-provider-for-woocommerce' ),
						'securesubmitcert@e-hps.com'
					),
					'default'           => '',
					'class'             => 'txn_descriptor',
					'custom_attributes' => array(
						'maxlength' => 18,
					),
				),
			)
		);
	}

	public function frontend_gateway_options() {
		return array( 'publicApiKey' => 'pkapi_cert_jKc1FtuyAydZhZfbB3' );
	}
}
