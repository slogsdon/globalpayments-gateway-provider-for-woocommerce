<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

use WC_Payment_Gateway_CC;
use WC_Order;
use GlobalPayments\Api\Entities\Transaction;

use GlobalPayments\WooCommercePaymentGatewayProvider\Plugin;

/**
 * Shared gateway method implementations
 */
abstract class AbstractGateway extends WC_Payment_Gateway_Cc {
	const TXN_TYPE_AUTHORIZE = 'authorize';
	const TXN_TYPE_SALE      = 'charge';
	const TXN_TYPE_VERIFY    = 'verify';

	/**
	 * Payment method enabled status
	 *
	 * This should be a boolean value, but inner WooCommerce checks require
	 * this remain as a string value with possible options `yes` and `no`.
	 *
	 * @var string
	 */
	public $enabled;

	/**
	 * Payment method title shown to consumer
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Action to perform on checkout
	 *
	 * Possible actions:
	 *
	 * - `authorize` - authorize the card without auto capturing
	 * - `sale` - authorize the card with auto capturing
	 * - `verify` - verify the card without authorizing
	 *
	 * @var string
	 */
	public $payment_action;

	/**
	 * Transaction descriptor to list on consumer's bank account statement
	 *
	 * @var string
	 */
	public $txn_descriptor;

	/**
	 * Control of WooCommerce's card storage (tokenization) support
	 *
	 * @var bool
	 */
	public $allow_card_saving;

	/**
	 * Gateway HTTP client
	 *
	 * @var Clients\ClientInterface
	 */
	protected $client;

	public function __construct() {
		$this->has_fields = true;
		$this->supports   = array(
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
		$this->client     = new Clients\SdkClient();

		$this->configure_method_settings();
		$this->init_form_fields();
		$this->init_settings();
		$this->configure_merchant_settings();
		$this->add_hooks();
	}

	/**
	 * Sets the necessary WooCommerce payment method settings for exposing the
	 * gateway in the WooCommerce Admin.
	 *
	 * @return
	 */
	abstract public function configure_method_settings();

	/**
	 * Required options for proper client-side configuration.
	 *
	 * @return array
	 */
	abstract public function get_frontend_gateway_options();

	/**
	 * Required options for proper server-side configuration.
	 *
	 * @return array
	 */
	abstract public function get_backend_gateway_options();

	/**
	 * Custom admin options to configure the gateway-specific credentials, features, etc.
	 *
	 * @return array
	 */
	abstract public function get_gateway_form_fields();

	/**
	 * Sets the configurable merchant settings for use elsewhere in the class
	 *
	 * @return
	 */
	public function configure_merchant_settings() {
		$this->title             = $this->get_option( 'title' );
		$this->enabled           = $this->get_option( 'enabled' );
		$this->payment_action    = $this->get_option( 'payment_action' );
		$this->txn_descriptor    = $this->get_option( 'txn_descriptor' );
		$this->allow_card_saving = $this->get_option( 'allow_card_saving' ) === 'yes';

		foreach ( $this->get_gateway_form_fields() as $key => $options ) {
			if ( ! property_exists( $this, $key ) ) {
				continue;
			}

			$value = $this->get_option( $key );

			if ( 'checkbox' === $options['type'] ) {
				$value = 'yes' === $value;
			}

			$this->{$key} = $value;
		}
	}

	/**
	 * Hook into `woocommerce_credit_card_form_fields` filter
	 *
	 * Replaces WooCommerce's default card inputs for empty container elements
	 * for our secure payment fields (iframes).
	 *
	 * @return array
	 */
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

	/**
	 * Enqueues tokenization scripts from Global Payments and WooCommerce
	 *
	 * @return
	 */
	public function tokenization_script() {
		// WooCommerce's scripts for handling stored cards
		parent::tokenization_script();

		// Global Payments styles for client-side tokenization
		wp_enqueue_style(
			'globalpayments-secure-payment-fields',
			Plugin::get_url( '/assets/frontend/css/globalpayments-secure-payment-fields.css' ),
			array(),
			WC()->version
		);

		// Global Payments scripts for handling client-side tokenization
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
				'gateway_options' => $this->get_frontend_gateway_options(),
				'field_options'   => $this->secure_payment_fields(),
			)
		);
	}

	/**
	 * Configures shared gateway options
	 *
	 * @return
	 */
	public function init_form_fields() {
		$this->form_fields = array_merge(
			array(
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
			),
			$this->get_gateway_form_fields(),
			array(
				'payment_action'    => array(
					'title'       => __( 'Payment Action', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose whether you wish to capture funds immediately, authorize payment only for a delayed capture, or verify and capture when the order ships.', 'globalpayments-gateway-provider-for-woocommerce' ),
					'default'     => 'sale',
					'desc_tip'    => true,
					'options'     => array(
						self::TXN_TYPE_SALE      => __( 'Authorize + Capture', 'globalpayments-gateway-provider-for-woocommerce' ),
						self::TXN_TYPE_AUTHORIZE => __( 'Authorize only', 'globalpayments-gateway-provider-for-woocommerce' ),
						self::TXN_TYPE_VERIFY    => __( 'Verify only', 'globalpayments-gateway-provider-for-woocommerce' ),
					),
				),
				'allow_card_saving' => array(
					'title'       => __( 'Allow Card Saving', 'globalpayments-gateway-provider-for-woocommerce' ),
					'label'       => __( 'Allow Card Saving', 'globalpayments-gateway-provider-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => 'Note: to use the card saving feature, you must have multi-use tokenization enabled on your Heartland account.',
					'default'     => 'no',
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

	/**
	 * Configuration for the secure payment fields. Used on server- and
	 * client-side portions of the integration.
	 *
	 * @return array
	 */
	protected function secure_payment_fields() {
		return array(
			'card-number-field' => array(
				'class'       => 'card-number',
				'label'       => esc_html__( 'Credit Card Number', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( '•••• •••• •••• ••••', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' => esc_html__( 'Please enter a valid Credit Card Number', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
			'card-expiry-field' => array(
				'class'       => 'card-expiration',
				'label'       => esc_html__( 'Credit Card Expiration Date', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( 'MM / YYYY', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' => esc_html__( 'Please enter a valid Credit Card Expiration Date', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
			'card-cvc-field'    => array(
				'class'       => 'card-cvv',
				'label'       => esc_html__( 'Credit Card Security Code', 'globalpayments-gateway-provider-for-woocommerce' ),
				'placeholder' => esc_html__( '•••', 'globalpayments-gateway-provider-for-woocommerce' ),
				'messages'    => array(
					'validation' => esc_html__( 'Please enter a valid Credit Card Security Code', 'globalpayments-gateway-provider-for-woocommerce' ),
				),
			),
		);
	}

	public function save_payment_method_checkbox() {
		if ( ! $this->allow_card_saving ) {
			return;
		}

		parent::save_payment_method_checkbox();
	}

	/**
	 * The HTML template string for a secure payment field
	 *
	 * Format directives:
	 *
	 * 1) Gateway ID
	 * 2) Field CSS class
	 * 3) Field label
	 * 4) Field validation message
	 *
	 * @return string
	 */
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

	/**
	 * Adds necessary gateway-specific hooks
	 *
	 * @return
	 */
	protected function add_hooks() {
		// hooks always active for the gateway
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( 'no' === $this->enabled ) {
			return;
		}

		// hooks only active when the gateway is enabled
		add_filter( 'woocommerce_credit_card_form_fields', array( $this, 'woocommerce_credit_card_form_fields' ) );

		if ( is_add_payment_method_page() ) {
			add_filter( 'wp_enqueue_scripts', array( $this, 'tokenization_script' ) );
		}
	}

	/**
	 * Handle payment functions
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order         = new WC_Order( $order_id );
		$request       = $this->prepare_request( $this->payment_action, $order );
		$response      = $this->submit_request( $request );
		$is_successful = $this->handle_response( $request, $response );

		return array(
			'result'   => $is_successful ? 'success' : 'failure',
			'redirect' => $is_successful ? $this->get_return_url( $order ) : false,
		);
	}

	/**
	 * Handle adding new cards via 'My Account'
	 *
	 * @return array
	 */
	public function add_payment_method() {
		$request       = $this->prepare_request( self::TXN_TYPE_VERIFY );
		$response      = $this->submit_request( $request );
		$is_successful = $this->handle_response( $request, $response );

		return array(
			'result'   => $is_successful ? 'success' : 'failure',
			'redirect' => wc_get_endpoint_url( 'payment-methods' ),
		);
	}

	/**
	 * Creates the necessary request based on the transaction type
	 *
	 * @param WC_Order $order
	 * @param string $txn_type
	 *
	 * @return Requests\RequestInterface
	 */
	protected function prepare_request( $txn_type, WC_Order $order = null ) {
		$map = array(
			self::TXN_TYPE_AUTHORIZE => Requests\AuthorizationRequest::class,
			self::TXN_TYPE_SALE      => Requests\SaleRequest::class,
			self::TXN_TYPE_VERIFY    => Requests\VerifyRequest::class,
		);

		if ( ! isset( $map[ $txn_type ] ) ) {
			throw new \Exception( 'Cannot perform transaction' );
		}

		$request = $map[ $txn_type ];
		return new $request( $this->id, $order, $this->get_backend_gateway_options() );
	}

	/**
	 * Executes the prepared request
	 *
	 * @param Requests\RequestInterface $request
	 *
	 * @return Transaction
	 */
	protected function submit_request( Requests\RequestInterface $request ) {
		return $this->client->set_request( $request )->execute();
	}

	/**
	 * Reacts to the transaction response
	 *
	 * @param Requests\RequestInterface $request
	 * @param Transaction $response
	 *
	 * @return bool
	 */
	protected function handle_response( Requests\RequestInterface $request, Transaction $response ) {
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName
		if ( '00' !== $response->responseCode ) {
			return false;
		}

		$handlers = array(
			Handlers\PaymentActionHandler::class,
			Handlers\DelayedAuthorizationHandler::class,
			Handlers\PaymentTokenHandler::class,
		);

		foreach ( $handlers as $handler ) {
			/**
			 * Current handler
			 *
			 * @var Handlers\HandlerInterface $h
			 */
			$h = new $handler( $request, $response );
			$h->handle();
		}

		return true;
	}
}
