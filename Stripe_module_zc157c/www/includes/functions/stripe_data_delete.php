<?php
if(isset($_POST['Delete'])) {
  $sql = "DELETE FROM " . TABLE_STRIPE . " WHERE customers_id = " . (int)$_SESSION['customer_id'];
  $db->Execute($sql);
  }  
?>


<?php
function stripe_id_exist(){
  global $db,$registered_customer;
    $sql = "SELECT Stripe_Customers_id FROM " . TABLE_STRIPE . " WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'order by id DESC LIMIT 1";
    $stripe_customer = $db->Execute($sql);
    if ($stripe_customer->RecordCount() > 0) {
      $registered_customer = 'true';
    }else{
      $registered_customer = 'false';
    }
}
?>

