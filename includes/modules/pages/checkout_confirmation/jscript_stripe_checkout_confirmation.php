<?php
if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS === 'True' && $stripe_select === 'True') {
    // -----
    // Gather the order data-related values needed by checkout.js.
    //
    require DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/stripepay/create.php';
?>
<script src="https://js.stripe.com/clover/stripe.js"></script>
<script id="stripe-form">
$(document).ready(function(){
    let stripeForm = $('<form>', {'id': 'payment-form'});
    stripeForm.append('<div id="payment-head" style="color: #2254dd;  font-size: 24px;  font-weight: bold; margin:24px 0 12px;">Stripe</div>');
    stripeForm.append('<div id="payment-element"><!--Stripe.js injects the Payment Element--></div>');
    stripeForm.append('<div id="payment-message" class="hidden"></div>');
    stripeForm.append('<button id="submit"><div class="spinner hidden" id="spinner"></div><span id="button-text"><?= BUTTON_CONFIRM_ORDER_ALT ?></span></button>');
    stripeForm.append('<div id="payment-foot"><?= TEXT_PAYMENT_STRIPE_MODULE_CORP;?></div>');

    $('#checkout_confirmation').before(stripeForm);

    <?= file_get_contents('includes/checkout.js') ?>

    $('div.confirm-order, #checkoutConfirmationDefault-btn-toolbar').hide();
});
</script>
<?php
}
