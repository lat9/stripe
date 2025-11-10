<?php
// -----
// Note: This section was previously in the base stripe.php's
// pre_confirmation_check method.
//
global $order, $db, $stripe_select;

if (MODULE_PAYMENT_STRIPE_TEST_MODE === 'True') {
    $publishable_key = MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY;
    $secret_key = MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY;
    $test_mode = true;
} else {
    $publishable_key = MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY;
    $secret_key = MODULE_PAYMENT_STRIPE_SECRET_KEY;
    $test_mode = false;
}

$payment_currency = $order->info['currency'];
$Xi_currency = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
$Xiooo_currency = ['BHD', 'JOD', 'KWD', 'OMR',' TND'];

if (in_array($payment_currency, $Xi_currency) === true ) {
    $multiplied_by = 1;
    $decimal_places = 0;
} elseif (in_array($payment_currency, $Xiooo_currency) === true ) {
    $multiplied_by = 1000;
    $decimal_places = 2;
} else {
    $multiplied_by = 100;
    $decimal_places = 2;
}

if (isset($_SESSION['opc_saved_order_total'])) {
    $order_value = $_SESSION['opc_saved_order_total'];
} elseif (defined('MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE') && MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE === 'true' && MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER >= $order->info['total']) {
    $order_value = $order->info['total'] + MODULE_ORDER_TOTAL_LOWORDERFEE_FEE ;
} else {
    $order_value = $order->info['total'];
}
$amount_total = round($order_value * $order->info['currency_value'], $decimal_places) * $multiplied_by;

$fullname = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
$email = $order->customer['email_address'];
$user_id = $_SESSION['customer_id'];
$registered_customer = false;
$stripe_customer = $db->Execute("SELECT Stripe_Customers_id FROM " . TABLE_STRIPE . " WHERE customers_id = " . (int)$_SESSION['customer_id'] . " LIMIT 1");
if (!$stripe_customer->EOF) {
    $registered_customer = true;
}

require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($secret_key);

try {
    global $db, $output, $param_json;
    if ($registered_customer === false && $test_mode === false){
        $customer = \Stripe\Customer::create([
            'email' => $email,
            'name' => $fullname,
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

zen_define_default('MODULE_PAYMENT_STRIPE_LAYOUT', 'Tabs');
zen_define_default('TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED', 'Payment succeeded. Please wait a few seconds!');

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