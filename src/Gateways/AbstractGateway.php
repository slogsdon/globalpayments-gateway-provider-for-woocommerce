<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

use GlobalPayments\WooCommercePaymentGatewayProvider\Plugin;

abstract class AbstractGateway extends \WC_Payment_Gateway_Cc {
	public function __construct() {
		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'add_payment_method',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
		);
	}

	abstract function frontend_gateway_options();

	public function woocommerce_credit_card_form_fields( $default_fields ) {
		$field_format = $this->secure_payment_field_html_format();
		$fields       = $this->secure_payment_fields();
		$result       = array();

		foreach ( $fields as $key => $field ) {
			$result[ $key ] = sprintf(
				$field_format,
				esc_attr( $this->id ),
				$field['class'],
				$field['label'],
				$field['messages']['validation']
			);
		}

		return $result;
	}

	public function tokenization_script() {
		parent::tokenization_script();

		wp_enqueue_style(
			'globalpayments-secure-payment-fields',
			Plugin::get_url( '/assets/frontend/css/globalpayments-secure-payment-fields.css' ),
			array(),
			WC()->version
		);

		wp_enqueue_script(
			'globalpayments-secure-payment-fields-lib',
			'https://api2.heartlandportico.com/securesubmit.v1/token/gp-1.3.0/globalpayments'
				. ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) . '.js',
			array(),
			WC()->version,
			true
		);
		wp_enqueue_script(
			'globalpayments-secure-payment-fields',
			Plugin::get_url( '/assets/frontend/js/globalpayments-secure-payment-fields.js' ),
			array( 'globalpayments-secure-payment-fields-lib', 'jquery' ),
			WC()->version,
			true
		);
		wp_localize_script(
			'globalpayments-secure-payment-fields',
			'globalpayments_secure_payment_fields_params',
			array(
				'id'              => $this->id,
				'gateway_options' => $this->frontend_gateway_options(),
				'field_options'   => $this->secure_payment_fields(),
			)
		);
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Gateway', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default' => 'yes',
			),
			'title'   => array(
				'title'       => __( 'Title', 'globalpayments-gateway-provider-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'globalpayments-gateway-provider-for-woocommerce' ),
				'default'     => __( 'Credit Card', 'globalpayments-gateway-provider-for-woocommerce' ),
				'desc_tip'    => true,
			),
		);
	}

	protected function secure_payment_fields() {
		return array(
			'card-number-field' => array(
				'class'       => 'card-number',
				'label'       => esc_html__( 'Credit Card Number', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( '•••• •••• •••• ••••', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' =>  esc_html__( 'Please enter a valid Credit Card Number', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
			'card-expiry-field' => array(
				'class'       => 'card-expiration',
				'label'       => esc_html__( 'Credit Card Expiration Date', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( 'MM / YYYY', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' =>  esc_html__( 'Please enter a valid Credit Card Expiration Date', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
			'card-cvc-field'    => array(
				'class'       => 'card-cvv',
				'label'       => esc_html__( 'Credit Card Security Code', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( '•••', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' =>  esc_html__( 'Please enter a valid Credit Card Security Code', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
		);
	}

	protected function secure_payment_field_html_format() {
		return (
			'<div class="form-row form-row-wide globalpayments %1$s %2$s">
				<label for="%1$s-%2$s">%3$s&nbsp;<span class="required">*</span></label>
				<div id="%1$s-%2$s"></div>
				<ul class="woocommerce_error woocommerce-error validation-error" style="display: none;">
					<li>%4$s</li>
				</ul>
			</div>'
		);
	}

	protected function add_hooks() {
		add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'woocommerce_credit_card_form_fields' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}
}
