<?php
$define = [
    'MODULE_PAYMENT_STRIPE_TEXT_TITLE' => 'Stripe Payments : Credit Card',
    'MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION' => 'Stripe Secure Payments : Credit Card',
    'MODULE_PAYMENT_STRIPE_TEXT_NOTICES_TO_CUSTOMER' => '',
    'TEXT_PAYMENT_STRIPE_SUCCESS'=>'Payment succeeded! Please wait a few seconds.',

];

if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS == 'True') {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = 'Stripe Payment Module <br> When testing interactively, use a test Visa card 4242 4242 4242 4242.<br><br>IMPORTANT: After changing the API key, You must follow the steps below<br> .
Go to Admin => Tools =>Install SQL patches page. Upload the erase_stripe_recordes.sql file or paste php code TRUNCATE ` stripe `; in the "Enter the queryto be executed:"text box. and press "Send" button.';
} else {
    $define['MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://stripe.com/">Click here to Sign Up for an Account </a> <br><br><strong>Reqirements<br>&nbsp;Stripe API Publishable key<br>&nbsp;Stripe API Secret key<br>&nbsp;Stripe API Publishable test key<br>&nbsp;Stripe API Secret test key<br></strong> ';
}



return $define;

