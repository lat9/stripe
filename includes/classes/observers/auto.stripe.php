<?php
class zcObserverStripe
{
    public function __construct()
    {
        if (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS === 'True') {
            if (defined('MODULE_PAYMENT_STRIPE_TEST_MODE') && MODULE_PAYMENT_STRIPE_TEST_MODE === 'True') {
                global $messageStack;
                $messageStack->add('header', 'STRIPE IS IN TESTING MODE', 'warning');
            }
            
            global $current_page_base, $db;
            if ($current_page_base === FILENAME_ACCOUNT && isset($_POST['Delete'])) {
                $db->Execute("DELETE FROM " . TABLE_STRIPE . " WHERE customers_id = " . (int)$_SESSION['customer_id']);
                zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
            }
        }
    }
}
