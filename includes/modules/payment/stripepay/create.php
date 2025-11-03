<?php

require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($secret_key);

try {
global $db,$output,$param_json;
  if ($registered_customer == false && $test_mode == false){
 
    $customer = \Stripe\Customer::create([
    'email' => $email,
    'name'   => $fullname,
    ]);

    $stripeCustomerID = $customer->id;  
    
   $sql = "INSERT INTO " . TABLE_STRIPE . " (id,customers_id,Stripe_Customers_id)  VALUES (NULL,:custID, :stripeCID )";
    $sql = $db->bindVars($sql, ':custID', $_SESSION['customer_id'], 'integer');
    $sql = $db->bindVars($sql, ':stripeCID', $stripeCustomerID, 'string');
    $db->Execute($sql);

}elseif ($test_mode == false){
    $stripeCustomerID = $stripe_customer->fields['stripe_customers_id'];
}


  // Create a PaymentIntent with amount and currency
if ($test_mode == false){
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount_total,
        'currency' => $payment_currency,
        'customer' => $stripeCustomerID,
        'automatic_payment_methods' => [
        'enabled' => true,
        ],
    ]);
}else{
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
    $clientS_json =json_encode(['error' => $e->getMessage()]);
}
   
$jason_publishable_key = json_encode($publishable_key);
$jason_PaymentSuccess = json_encode(TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED);
$jason_FormLayout = json_encode(MODULE_PAYMENT_STRIPE_LAYOUT);
$confirmationURL = '"' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=checkout_confirmation"';
$jason_confirmationULR = json_encode($confirmationURL);

//---comments---
if($order->info['comments']!=""){
$order_add_comment = $order->info['comments'];
$_SESSION['order_add_comment'] = $order_add_comment;
}else{
$_SESSION['order_add_comment'] = "";
}
    $_SESSION['paymentIntent'] = $paymentIntent['id'];

//echo $paymentIntent['id'];
//------------
?>
<script>
   'use strict';
    var clientS = JSON.parse('<?php echo $clientS_json; ?>'); 
    var PublishableKey = JSON.parse('<?php echo $jason_publishable_key; ?>'); 
    var confirmationURL = JSON.parse('<?php echo $jason_confirmationULR; ?>'); 
    var PaymentSuccess = JSON.parse('<?php echo $jason_PaymentSuccess; ?>'); 
    var FormLayout = JSON.parse('<?php echo $jason_FormLayout; ?>'); 

</script>