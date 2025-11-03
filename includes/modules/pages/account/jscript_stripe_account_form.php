<?php
$sql = "SELECT Stripe_Customers_id FROM " . TABLE_STRIPE . " WHERE customers_id = " . (int)$_SESSION['customer_id'] . " LIMIT 1";
$stripe_customer = $db->Execute($sql);
if ($stripe_customer->EOF) {
    return;
}
?>
<script id="stripe-form">
    $(document).ready(function(){
        let stripeForm = '';
        stripeForm.append('<h2 style="color: #2254dd;  font-size: 24px;  font-weight: bold;">Stripe</h2>');
        stripeForm.append('<?= TEXT_STRIPE_CARD_INFORMATION ?>');
        
        stripeForm.append($('<form>', {'method': 'post'}));
        stripeForm.append($('<input>', {'id': 'btn_delete', 'type': 'submit', 'name': 'Delete', 'value': '<?= TEXT_DELETE_STRIPE ?>'}));
        
        $('#accountDefault').append(stripeForm);
    });
</script>
