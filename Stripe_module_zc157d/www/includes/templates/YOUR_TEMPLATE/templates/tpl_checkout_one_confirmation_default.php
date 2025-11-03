<?php
// -----
// Part of the One-Page Checkout plugin, provided under GPL 2.0 license by lat9 (cindy@vinosdefrutastropicales.com).
// Copyright (C) 2013-2024, Vinos de Frutas Tropicales.  All rights reserved.
//
// Last updated: OPC v2.5.0
//

// -----
// The "display: none;" on the loading icon enables that to "not display" if javascript is disabled in the customer's browser.  The
// page's jscript_main.php handling will "show" that when javascript is enabled and we're not forcing the confirmation-page's display.
//
?>
<div class="centerColumn" id="checkoutOneConfirmation<?php echo ($confirmation_required === true) ? 'Display' : ''; ?>">
<?php
// -----
// If the current payment method requires that the confirmation page be displayed ...
//
if ($confirmation_required === true) {
?>
    <h1 id="checkoutConfirmDefaultHeading"><?php echo HEADING_TITLE; ?></h1>
<?php
    if ($messageStack->size('redemptions') > 0) {
        echo $messageStack->output('redemptions');
    }
    if ($messageStack->size('checkout_confirmation') > 0) {
        echo $messageStack->output('checkout_confirmation');
    }
    if ($messageStack->size('checkout') > 0) {
        echo $messageStack->output('checkout');
    }
?>

    <div id="checkoutBillto" class="back">
        <h2 id="checkoutConfirmDefaultBillingAddress"><?php echo HEADING_BILLING_ADDRESS; ?></h2>
<?php
    if ($flagDisablePaymentAddressChange === false) {
?>
        <div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
<?php
    }
?>

        <address><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'); ?></address>

        <h3 id="checkoutConfirmDefaultPayment"><?php echo HEADING_PAYMENT_METHOD; ?></h3>
        <h4 id="checkoutConfirmDefaultPaymentTitle"><?php echo $payment_title; ?></h4>
<?php
    if (!empty($confirmation_title)) {
?>
        <div class="important"><?php echo $confirmation_title; ?></div>
<?php
    }
    if (!empty($confirmation_fields)) {
?>
        <div class="important">
<?php
        foreach ($confirmation_fields as $next_field) {
?>
            <div class="back"><?php echo $next_field['title']; ?></div>
            <div><?php echo $next_field['field']; ?></div>
<?php
        }
?>

        </div>
<?php
    }
?>
        <br class="clearBoth">

<!-- Display a payment form -->
    <form id="payment-form">
    <div id="payment-head" style="color: #2254dd;  font-size: 24px;  font-weight: bold; margin:24px 0 12px;">Stripe</div>    
    <div id="payment-element">
        <!--Stripe.js injects the Payment Element-->
      </div>
<!-- end -Display a payment form -->
    </div>

<?php
    if ($_SESSION['sendto'] != false) {
?>
    <div id="checkoutShipto" class="forward">
        <h2 id="checkoutConfirmDefaultShippingAddress"><?php echo HEADING_DELIVERY_ADDRESS; ?></h2>
        <div class="buttonRow forward"><?php echo '<a href="' . $editShippingButtonLink . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>

        <address><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'); ?></address>
<?php
        if (!empty($order->info['shipping_method'])) {
?>
        <h3 id="checkoutConfirmDefaultShipment"><?php echo HEADING_SHIPPING_METHOD; ?></h3>
        <h4 id="checkoutConfirmDefaultShipmentTitle"><?php echo $order->info['shipping_method']; ?></h4>
<?php
        }
?>
    </div>
<?php
    }
?>
    <br class="clearBoth">
    <hr>

    <h2 id="checkoutConfirmDefaultHeadingComments"><?php echo HEADING_ORDER_COMMENTS; ?></h2>
    <div class="buttonRow forward"><?php echo  '<a href="' . zen_href_link(FILENAME_CHECKOUT_ONE, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
    <div><?php echo (empty($order->info['comments']) ? NO_COMMENTS_TEXT : nl2br(zen_output_string_protected($order->info['comments'])) . zen_draw_hidden_field('comments', $order->info['comments'])); ?></div>
    <br class="clearBoth">
    <hr>

    <h2 id="checkoutConfirmDefaultHeadingCart"><?php echo HEADING_PRODUCTS; ?></h2>

    <div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_SHOPPING_CART, '', 'SSL') . '">' . zen_image_button (BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></div>
    <br class="clearBoth">
<?php
    if ($flagAnyOutOfStock) {
        if (STOCK_ALLOW_CHECKOUT === 'true') {
?>
    <div class="messageStackError"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></div>
<?php
        } else { 
?>
    <div class="messageStackError"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></div>
<?php
        } //endif STOCK_ALLOW_CHECKOUT
    } //endif flagAnyOutOfStock 
?>
    <table id="cartContentsDisplay">
        <tr class="cartTableHeading">
            <th scope="col" id="ccQuantityHeading"><?php echo TABLE_HEADING_QUANTITY; ?></th>
            <th scope="col" id="ccProductsHeading"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
<?php
  // If there are tax groups, display the tax columns for price breakdown
    if (count($order->info['tax_groups']) > 1) {
?>
            <th scope="col" id="ccTaxHeading"><?php echo HEADING_TAX; ?></th>
<?php
    }
?>
            <th scope="col" id="ccTotalHeading"><?php echo TABLE_HEADING_TOTAL; ?></th>
        </tr>
<?php
    // now loop thru all products to display quantity and pric
    for ($i = 0, $n = count($order->products); $i < $n; $i++) {
?>
        <tr class="<?php echo $order->products[$i]['rowClass']; ?>">
            <td  class="cartQuantity"><?php echo $order->products[$i]['qty']; ?>&nbsp;x</td>
            <td class="cartProductDisplay"><?php echo $order->products[$i]['name'] . $stock_check[$i]; ?>
<?php
        // if there are attributes, loop thru them and display one per line
        if (isset($order->products[$i]['attributes']) && count($order->products[$i]['attributes']) > 0) {
?>
                <ul class="cartAttribsList">
<?php
            for ($j = 0, $n2 = count($order->products[$i]['attributes']); $j < $n2; $j++) {
?>
                    <li><?php echo $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])); ?></li>
<?php
            } // end loop
?>
                </ul>
<?php
        } // endif attribute-info
    
        if (isset($posStockMessage)) {
            echo '<br>' . $posStockMessage[$i];
        }
?>
            </td>
<?php
        // display tax info if exists
        if (count($order->info['tax_groups']) > 1)  {
?>
            <td class="cartTotalDisplay"><?php echo zen_display_tax_value($order->products[$i]['tax']); ?>%</td>
<?php
        }  // endif tax info display
?>
            <td class="cartTotalDisplay"><?php echo $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']); ?>
<?php
        if ($order->products[$i]['onetime_charges'] != 0) {
            echo '<br> ' . $currencies->display_price($order->products[$i]['onetime_charges'], $order->products[$i]['tax'], 1);
        }
?>
            </td>
        </tr>
<?php
    }  // end for loopthru all products 
?>
    </table>
    <hr>
<?php
}  //-Display confirmation information, if required

// -----
// Some payment modules (notably firstdata_hco) make use of the $order_totals object which has been set by the page's header processing.
//
if (MODULE_ORDER_TOTAL_INSTALLED) {
    if ($confirmation_required === true) {
?>
    <div id="orderTotals"><?php $order_total_modules->output(); ?></div>
<?php
    }
}
?>
<!--------  stripe  -------->
      <div id="payment-message" class="hidden"></div>
      <button id="submit">
      <div class="spinner hidden" id="spinner"></div>
        <span id="button-text"><?php echo BUTTON_CONFIRM_ORDER_ALT; ?></span>
      </button>
      <div id="payment-foot"><?php echo TEXT_PAYMENT_STRIPE_MODULE_CORP; ?></div>
      </form>   
<!--------end-stripe-------->
    <br class="clearBoth" />
<?php
// -----
// Now, display the form that actually submits this order.
//
echo zen_draw_form('checkout_confirmation', $form_action_url, 'post', 'id="checkout_confirmation"' . ($confirmation_required ? ' onsubmit="submitonce();"' : ''));
?>
    <div id="checkoutOneConfirmationButtons">
<?php
// -----
// Add the selected payment module's final HTML to the display.
//
echo $payment_process_button;
?>
        <div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_CONFIRM_ORDER, BUTTON_CONFIRM_ORDER_ALT, 'name="btn_submit" id="btn_submit"'); ?></div>
        <div class="clearBoth"></div>
    </div>
<?php echo '</form>'; ?>

    <div id="checkoutOneConfirmationLoading" style="display: none;"><?php echo ((CHECKOUT_ONE_CONFIRMATION_INSTRUCTIONS === '') ? '' : (CHECKOUT_ONE_CONFIRMATION_INSTRUCTIONS . '<br><br>')) . zen_image($template->get_template_dir(CHECKOUT_ONE_CONFIRMATION_LOADING, DIR_WS_TEMPLATE, $current_page_base ,'images') . '/' . CHECKOUT_ONE_CONFIRMATION_LOADING, CHECKOUT_ONE_CONFIRMATION_LOADING_ALT); ?></div>
</div>

<!--------stripe-------->

<?php
$jason_stripe_select = json_encode($stripe_select);
$jason_sess_life = json_encode($SESS_LIFE);
$timeoutURL = '"' . HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=time_out"';
?>
<script>

var stripe_select = JSON.parse('<?php echo $jason_stripe_select; ?>'); 
var sess_life = JSON.parse('<?php echo $jason_sess_life; ?>'); 
var timeoutURL = JSON.parse('<?php echo $timeoutURL; ?>'); 

if (stripe_select === "True") {

    document.getElementById('btn_submit').style.display ="none";
    document.getElementById('checkout_confirmation').style.display ="none";
    document.getElementById('payment-form','submit').display ="block";
    setTimeout(function(){window.location.href = timeoutURL;}, sess_life*1000);


  }else{
    document.getElementById('btn_submit').display ="block";
    document.getElementById('checkout_confirmation').display ="block";
    document.getElementById('payment-form','submit').style.display ="none";

  }

</script>
<!--------end-stripe-------->  