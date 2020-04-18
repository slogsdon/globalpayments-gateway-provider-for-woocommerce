<?php

include_once 'wp-content/plugins/globalpayments-gateway-provider-for-woocommerce/src/Gateways/class-wc-gateway-securesubmit-giftcards.php';

if (true) : // Allow customers to pay with Heartland gift cards ?>
    <fieldset>
          <!-- Start Gift Card -->
          <div class="securesubmit-content gift-card-content">
                <div class="form-row form-row-wide" id="gift-card-row">
                      <label id="gift-card-label" for="gift-card-number"><?php _e('Use a gift card', 'wc_securesubmit'); ?></label>
                      <div id="gift-card-input">
                            <input type="tel" placeholder="Gift card" id="gift-card-number" value="5022440000000000098" class="input-text">
                            <input type="tel" placeholder="PIN" id="gift-card-pin" value="1234" class="input-text">
                            <p id="gift-card-error"></p>
                            <p id="gift-card-success"></p>
                      </div>
                      <button id="apply-gift-card" class="button"><?php _e('Apply', 'wc_securesubmit'); ?></button>
<?php
      $html = '<script data-cfasync="false" type="text/javascript">';
      $html .= 'if( typeof ajaxurl === "undefined") { ';
      $html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
      $html .= '}';
      $html .= '</script>';

      echo $html;
?>

<script data-cfasync="false">

      jQuery("#apply-gift-card").on('click', function (event) {
            event.preventDefault();
            applyGiftCard();
      });

      function applyGiftCard () {
            // jQuery
            // .ajax({
            //       url: ajaxurl,
            //       type: 'POST',
            //       data: {
            //       action: 'use_gift_card',
            //       gift_card_number: jQuery('#gift-card-number').val(),
            //       gift_card_pin: jQuery('#gift-card-pin').val(),
            //       },
            // })
            // .success(processGiftCardResponse);

            var httpRequest = new XMLHttpRequest();

            var gift_card_number = document.getElementById('gift-card-number').value;
            var gift_card_pin = document.getElementById('gift-card-pin').value;


            var post_string = 'action=use_gift_card&gift_card_number=' + gift_card_number + '&gift_card_pin=' + gift_card_pin;

            httpRequest.open('POST', ajaxurl, false);
            httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            // httpRequest.send({
            //       action: 'use_gift_card',
            //       gift_card_number: jQuery('#gift-card-number').val(),
            //       gift_card_pin: jQuery('#gift-card-pin').val(),
            //       });
            // httpRequest.send('action=use_gift_card&gift_card_number=5022440000000000098&gift_card_pin=1234');
            httpRequest.send(post_string);

            httpRequest.onreadystatechange = function(){
            console.log('oh shit son I got a response back');
            };

      };

</script>
                </div>
                <div class="clear"></div>
          </div>
          <!-- End Gift Card -->
    </fieldset>
<?php endif; ?>