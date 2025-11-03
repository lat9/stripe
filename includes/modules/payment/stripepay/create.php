<?php
require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($secret_key);

try {
    global $db, $output, $param_json;
    if ($registered_customer === false && $test_mode === false){
        $customer = \Stripe\Customer::create([
            'email' => $email,
            'name'   => $fullname,
        ]);

        global $stripeCustomerID;
        $stripeCustomerID = $customer->id;  

        $sql = "INSERT INTO " . TABLE_STRIPE . " (id, customers_id, Stripe_Customers_id)  VALUES (NULL,:custID, :stripeCID )";
        $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
        $sql = $db->bindVars($sql, ':stripeCID', $stripeCustomerID, 'string');
        $db->Execute($sql);
    } elseif ($test_mode === false){
        $stripeCustomerID = $stripe_customer->fields['stripe_customers_id'];
    }


    // Create a PaymentIntent with amount and currency
    if ($test_mode === false){
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount_total,
            'currency' => $payment_currency,
            'customer' => $stripeCustomerID,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    } else {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount_total,
            'currency' => $payment_currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);
    }

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    $clientS_json = json_encode($output); 

} catch (Error $e) {
    http_response_code(500);
    $clientS_json = json_encode(['error' => $e->getMessage()]);
}
   
$jason_publishable_key = json_encode($publishable_key);
$jason_PaymentSuccess = json_encode(TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED);
$jason_FormLayout = json_encode(strtolower(MODULE_PAYMENT_STRIPE_LAYOUT));

global $current_page_base;
if (defined('FILENAME_CHECKOUT_ONE_CONFIRMATION') && $current_page_base === FILENAME_CHECKOUT_ONE_CONFIRMATION) {
    $confirmationURL = zen_href_link(FILENAME_CHECKOUT_ONE_CONFIRMATION, '', 'SSL');
} else {
    $confirmationURL = zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL');
}
$jason_confirmationULR = json_encode($confirmationURL);

//---comments---
if ($order->info['comments'] !== ''){
    $_SESSION['order_add_comment'] = $order->info['comments'];
} else {
    $_SESSION['order_add_comment'] = '';
}

$_SESSION['paymentIntent'] = $paymentIntent['id'];

//echo $paymentIntent['id'];
//------------
?>
<script id="stripe-data">
   'use strict';
    var clientS = JSON.parse('<?= $clientS_json ?>'); 
    var PublishableKey = JSON.parse('<?= $jason_publishable_key ?>'); 
    var confirmationURL = JSON.parse('<?= $jason_confirmationULR ?>'); 
    var PaymentSuccess = JSON.parse('<?= $jason_PaymentSuccess ?>'); 
    var FormLayout = JSON.parse('<?= $jason_FormLayout ?>'); 
</script>