// @ts-check

(function ($) {
	var windowAny =
		/**
		 * Casting `window` to any
		 *
		 * @type {any}
		 */
		(window);
	var GlobalPayments = windowAny.GlobalPayments || {};

	GlobalPayments.WooCommerce = function (options) {
		this.id             = options.id;
		this.fieldOptions   = options.field_options;
		this.gatewayOptions = options.gateway_options;
		this.attachEventHandlers();
	};

	GlobalPayments.WooCommerce.prototype = {
		attachEventHandlers: function () {
			$( document.body ).on( 'updated_checkout', this.renderPaymentFields.bind( this ) );
			$( '#order_review' ).on( 'click', '.payment_methods input.input-radio', this.toggleSubmitButtons.bind( this ) );
		},

		getPlaceOrderButtonSelector: function () { return '#place_order'; },
		getSubmitButtonTargetSelector: function () { return '#' + this.id + '-card-submit'; },
		getPaymentMethodRadioSelector: function () { return '#order_review .payment_methods input.input-radio[value="' + this.id + '"]'; },

		/**
		 * Renders the payment fields using GlobalPayments.js. Each field is securely hosted on
		 * Global Payments' production servers.
		 *
		 * @returns
		 */
		renderPaymentFields: function () {
			if ( ! windowAny.GlobalPayments.configure ) {
				console.log( 'Warning! Payment fields cannot be loaded' );
				return;
			}

			if ( $( this.getSubmitButtonTargetSelector() ).length === 0 ) {
				this.createSubmitButtonTarget();
			}

			GlobalPayments.configure( this.gatewayOptions );

			var cardForm = GlobalPayments.ui.form(
				{
					fields: this.getFieldConfiguration(),
					styles: this.getStyleConfiguration()
				}
			);

			cardForm.on( 'token-success', this.handleResponse.bind( this ) );
			cardForm.on( 'token-error', this.handleErrors.bind( this ) );
			cardForm.on( 'error', this.handleErrors.bind( this ) );
			GlobalPayments.on( 'error', this.handleErrors.bind( this ) );
		},

		createSubmitButtonTarget: function () {
			var el           = document.createElement( 'div' );
			el.id            = this.getSubmitButtonTargetSelector().replace( '#', '' );
			el.className     = 'globalpayments ' + this.id + ' card-submit';
			el.style.display = $( this.getPaymentMethodRadioSelector() ).is( ':checked' )
				? 'block' : 'none';
			$( this.getPlaceOrderButtonSelector() ).after( el );
		},

		/**
		 * Swaps the default WooCommerce 'Place Order' button for our iframe-d button
		 * when one of our gateways is selected.
		 *
		 * @param {MouseEvent} e
		 */
		toggleSubmitButtons: function ( e ) {
			var target =
				/**
				 * Casting event target
				 *
				 * @type {HTMLInputElement}
				 */
				(e.currentTarget);

			if ( this.id === target.value ) {
				// our gateway was selected
				$( this.getSubmitButtonTargetSelector() ).show();
				$( this.getPlaceOrderButtonSelector() ).hide();
			} else {
				// another gateway was selected
				$( this.getSubmitButtonTargetSelector() ).hide();
				$( this.getPlaceOrderButtonSelector() ).show();
			}
		},

		/**
		 * Handles the tokenization response
		 *
		 * On valid payment fields, the tokenization response is added to the current
		 * state, and the order is placed.
		 *
		 * @param {object} response tokenization response
		 *
		 * @returns
		 */
		handleResponse: function ( response ) {
			if ( ! this.validateTokenResponse( response ) ) {
				return;
			}

			this.tokenResponse = JSON.stringify( response );
			this._placeOrder();
		},

		/**
		 * Places/submits the order to WooCommerce
		 *
		 * Attempts to click the default 'Place Order' button that is used by payment methods.
		 * This is to account for other plugins taking action based on that click event, even
		 * though there are usually better options. If anything fails during that process,
		 * we fall back to calling `this.placeOrder` manually.
		 *
		 * @returns
		 */
		_placeOrder: function () {
			try {
				var originalSubmit = $( this.getPlaceOrderButtonSelector() );
				if ( originalSubmit ) {
					originalSubmit.click();
					return;
				}
			} catch ( e ) {
				/* om nom nom */
			}

			this.placeOrder();
		},

		/**
		 * Validates the tokenization response
		 *
		 * @param {object} response tokenization response
		 *
		 * @returns {boolean} status of validations
		 */
		validateTokenResponse: function ( response ) {
			this.resetValidationErrors();

			var result = true;

			if (response.details) {
				var expirationDate = new Date( response.details.expiryYear, response.details.expiryMonth - 1 );
				var now            = new Date();
				var thisMonth      = new Date( now.getFullYear(), now.getMonth() );

				if ( ! response.details.expiryYear || ! response.details.expiryMonth || expirationDate < thisMonth ) {
					this.showValidationError( 'credit-card-expiration' );
					result = false;
				}
			}

			if ( response.details && ! response.details.cardSecurityCode ) {
				this.showValidationError( 'credit-card-cvv' );
				result = false;
			}

			return result;
		},

		/**
		 * Hides all validation error messages
		 *
		 * @returns
		 */
		resetValidationErrors: function () {
			$.each(
				$( '.' + this.id + ' .validation-error' ),
				/**
				 * Acts on each validation error message element
				 *
				 * @param {number} i
				 * @param {HTMLElement} el
				 */
				function (i, el) {
					el.style.display = 'none';
				}
			);
		},

		/**
		 * Shows the validation error for a specific payment field
		 *
		 * @param {string} fieldType Field type to show its validation error
		 *
		 * @returns
		 */
		showValidationError: function (fieldType) {
			console.log( 'show error for ' + fieldType );
			/**
			 * Grab the specific message to display
			 *
			 * @type {HTMLElement}
			 */
			var el = document.querySelector( '.' + this.id + ' .' + fieldType + ' .validation-error' );
			if ( el ) {
				el.style.display = 'initial';
			}
		},

		/**
		 * Handles errors from the payment field iframes
		 *
		 * @param {object} error Details about the error
		 *
		 * @returns
		 */
		handleErrors: function ( error ) {
			if ( ! error.reasons ) {
				return;
			}

			var numberOfReasons = error.reasons.length;

			for ( var i = 0; i < numberOfReasons; i++ ) {
				var reason = error.reasons[i];
				switch ( reason.code ) {
					case 'INVALID_CARD_NUMBER':
						this.showValidationError( 'credit-card-number' );
						break;
					default:
						break;
				}
			}
		},

		/**
		 * Gets payment field config
		 *
		 * @returns {object}
		 */
		getFieldConfiguration: function () {
			return {
				'card-number': {
					placeholder: this.fieldOptions['card-number-field'].placeholder,
					target: '#' + this.id + '-' + this.fieldOptions['card-number-field'].class
				},
				'card-expiration': {
					placeholder: this.fieldOptions['card-expiry-field'].placeholder,
					target: '#' + this.id + '-' + this.fieldOptions['card-expiry-field'].class
				},
				'card-cvv': {
					placeholder: this.fieldOptions['card-cvc-field'].placeholder,
					target: '#' + this.id + '-' + this.fieldOptions['card-cvc-field'].class
				},
				'submit': {
					text: this.getSubmitButtonText(),
					target: this.getSubmitButtonTargetSelector()
				}
			};
		},

		/**
		 * Gets payment field styles
		 *
		 * @returns {object}
		 */
		getStyleConfiguration: function () {
			var imageBase = 'https://api2.heartlandportico.com/securesubmit.v1/token/gp-1.3.0/assets';
			return {
				'html': {
					'font-size': '62.5%'
				},
				'body': {
					'font-size': '1.4rem'
				},
				'#secure-payment-field-wrapper': {
					'postition': 'relative'
				},
				'#secure-payment-field': {
					'-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
					'-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
					'-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
					'background-color': '#fff',
					'border': '1px solid #cecece',
					'border-radius': '2px',
					'box-shadow': 'none',
					'box-sizing': 'border-box',
					'display': 'block',
					'font-family': '"Roboto", sans-serif',
					'font-size': '11px',
					'font-smoothing': 'antialiased',
					'height': '35px',
					'margin': '5px 0 10px 0',
					'max-width': '100%',
					'outline': '0',
					'padding': '0 10px',
					'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
					'vertical-align': 'baseline',
					'width': '100%'
				},
				'#secure-payment-field:focus': {
					'border': '1px solid lightblue',
					'box-shadow': '0 1px 3px 0 #cecece',
					'outline': 'none'
				},
				'#secure-payment-field[type=button]': {
					'text-align': 'center',
					'text-transform': 'none',
					'white-space': 'nowrap',

					'background-image': 'none',
					'background': '#1979c3',
					'border': '1px solid #1979c3',
					'color': '#ffffff',
					'cursor': 'pointer',
					'display': 'inline-block',
					'font-family': '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
					'font-weight': '500',
					'padding': '14px 17px',
					'font-size': '1.8rem',
					'line-height': '2.2rem',
					'box-sizing': 'border-box',
					'vertical-align': 'middle',
					'margin': '0',
					'height': 'initial',
					'width': 'initial',
					'flex': 'initial',
					'position': 'absolute',
					'right': '0'
				},
				'#secure-payment-field[type=button]:focus': {
					'outline': 'none',

					'box-shadow': 'none',
					'background': '#006bb4',
					'border': '1px solid #006bb4',
					'color': '#ffffff'
				},
				'#secure-payment-field[type=button]:hover': {
					'background': '#006bb4',
					'border': '1px solid #006bb4',
					'color': '#ffffff'
				},
				'.card-cvv': {
					'background': 'transparent url(' + imageBase + '/cvv.png) no-repeat right',
					'background-size': '60px'
				},
				'.card-cvv.card-type-amex': {
					'background': 'transparent url(' + imageBase + '/cvv-amex.png) no-repeat right',
					'background-size': '60px'
				},
				'.card-number': {
					'background': 'transparent url(' + imageBase + '/logo-unknown@2x.png) no-repeat right',
					'background-size': '52px'
				},
				'.card-number.invalid.card-type-amex': {
					'background': 'transparent url(' + imageBase + '/amex-invalid.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '38px'
				},
				'.card-number.invalid.card-type-discover': {
					'background': 'transparent url(' + imageBase + '/discover-invalid.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '60px'
				},
				'.card-number.invalid.card-type-jcb': {
					'background': 'transparent url(' + imageBase + '/jcb-invalid.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '38px'
				},
				'.card-number.invalid.card-type-mastercard': {
					'background': 'transparent url(' + imageBase + '/mastercard-invalid.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '40px'
				},
				'.card-number.invalid.card-type-visa': {
					'background': 'transparent url(' + imageBase + '/visa-invalid.svg) no-repeat center',
					'background-position-x': '98%',
					'background-size': '50px'
				},
				'.card-number.valid.card-type-amex': {
					'background': 'transparent url(' + imageBase + '/amex.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '38px'
				},
				'.card-number.valid.card-type-discover': {
					'background': 'transparent url(' + imageBase + '/discover.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '60px'
				},
				'.card-number.valid.card-type-jcb': {
					'background': 'transparent url(' + imageBase + '/jcb.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '38px'
				},
				'.card-number.valid.card-type-mastercard': {
					'background': 'transparent url(' + imageBase + '/mastercard.svg) no-repeat center',
					'background-position-x': '98%',
					'background-size': '40px'
				},
				'.card-number.valid.card-type-visa': {
					'background': 'transparent url(' + imageBase + '/visa.svg) no-repeat right center',
					'background-position-x': '98%',
					'background-size': '50px'
				},
				'.card-number::-ms-clear': {
					'display': 'none',
				},
				'input[placeholder]': {
					'letter-spacing': '.5px',
				}
			};
		},

		/**
		 * Gets submit button text
		 *
		 * @returns {string}
		 */
		getSubmitButtonText: function () {
			return $( '#place_order' ).data( 'value' );
		}
	};

	new GlobalPayments.WooCommerce( windowAny.globalpayments_secure_payment_fields_params );
}(
	/**
	 * Cast window to any
	 *
	 * @type {any}
	 */
	(window).jQuery
));
