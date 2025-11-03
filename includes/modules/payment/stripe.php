<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Sep 10 Modified in v1.5.8 $
 */
class stripe extends base
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     * @var string
     */
    public string $code;
    /**
     * $description is a soft name for this payment method
     * @var string 
     */
    public string $description;
    /**
     * $email_footer is the text to me placed in the footer of the email
     * @var string
     */
    public string $email_footer;
    /**
     * $enabled determines whether this module shows or not... during checkout.
     * @var boolean
     */
    public bool $enabled;
    /**
     * $order_status is the order status to set after processing the payment
     * @var int
     */
    public int $order_status;
    /**
     * $title is the displayed name for this order total method
     * @var string
     */
    public string $title;
    /**
     * $sort_order is the order priority of this payment module when displayed
     * @var int|null
     */
    public int ?$sort_order;

    // class constructor
    public function __construct()
    {
        global $order;

        $this->code = 'stripe';
        $this->title = MODULE_PAYMENT_STRIPE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_STRIPE_SORT_ORDER') ? (int)MODULE_PAYMENT_STRIPE_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return;
        }

        $this->enabled = (MODULE_PAYMENT_STRIPE_STATUS === 'True');
        if (IS_ADMIN_FLAG === true) {
            if (MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY === '' || MODULE_PAYMENT_STRIPE_SECRET_KEY === '' || MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY === '' || MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY === '' ) {
                $this->title .= '<span class="alert"> (not configured - stripe publishable key and secret key)</span>';
            }

            if (MODULE_PAYMENT_STRIPE_TEST_MODE === 'True') {
                $this->title .= '<span class="alert"> (Stripe is in testing mode)</span>';
            }

            if (strpos(MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY, '_test_') !== false || strpos(MODULE_PAYMENT_STRIPE_SECRET_KEY, '_test_') !== false) {
                $this->title .= '<span class="alert"> (Test key entered in API publishable key or secret key )</span>';
            }

            if (strpos(MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY, '_test_') === false || strpos(MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY, '_test_') === false) {
                $this->title .= '<span class="alert"> (Test key not entered in the test mode field)</span>';
            }
        }

        if ((int)MODULE_PAYMENT_STRIPE_STATUS_ID > 0) {
            $this->order_status = (int)MODULE_PAYMENT_STRIPE_STATUS_ID;
        }

        if (is_object($order)) {
            $this->update_status();
        }
    }

    // class methods
    public function update_status()
    {
        global $order, $db;

        if ($this->enabled && (int)MODULE_PAYMENT_STRIPE_ZONE > 0 && isset($order->billing['country']['id'])) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                  WHERE geo_zone_id = " . (int)MODULE_PAYMENT_STRIPE_ZONE . "
                    AND zone_country_id = " . (int)($order->billing['country']['id'] ?? 0) . "
                  ORDER BY zone_id"
            );
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
            }
        }
    }

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return ['id' => $this->code, 'module' => $this->title];
    }

    public function pre_confirmation_check()
    {
        global $order, $db, $user_id, $stripe_select;

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
        $stripe_customer = $db->Execute("SELECT stripe_customers_id FROM " . TABLE_STRIPE . " WHERE customers_id = " . $_SESSION['customer_id'] . " LIMIT 1");
        if (!$stripe_customer->EOF) {
            $registered_customer = true;
        }
        $stripe_select = 'True';

        if ($_SESSION['paymentIntent'] == '' ){
            require_once 'stripepay/create.php' ;
        }
    }

    public function confirmation()
    {
        return false;
    }

    public function process_button()
    {
        return false;
    }

    public function before_process()
    {
        global $order;
        $order_comment = $_SESSION['order_add_comment'] . "\n Stripe ID:" . $_SESSION['paymentIntent'];
        $order->info['comments'] = $order_comment;
    }

    function after_process()
    {
        unset($_SESSION['order_add_comment'], $_SESSION['paymentIntent']);
    }

    public function get_error()
    {
        return false;
    }

    public function check()
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_STRIPE_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    public function install() 
    {
        global $db, $messageStack;

        $db->Execute("DROP TABLE IF EXISTS " . DB_PREFIX  . "stripe ;");  
        $db->Execute("CREATE TABLE  " . DB_PREFIX  . "stripe (id INT(11) AUTO_INCREMENT PRIMARY KEY, customers_id INT(11), Stripe_Customers_id VARCHAR(32))");

        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function, val_function)
             VALUES
                ('Enable Stripe Secure Payment Module', 'MODULE_PAYMENT_STRIPE_STATUS', 'True', 'Do you want to accept Stripe Secure Payment?', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option([\'True\', \'False\'], ', NULL),

                ('API Publishable Key:', 'MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY', '', 'Enter API Publishable Key provided by stripe', 6, 1, NULL, now(), NULL, NULL, NULL),

                ('Sort order of display.', 'MODULE_PAYMENT_STRIPE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 1, NULL, now(), NULL, NULL, NULL),

                ('Payment Zone', 'MODULE_PAYMENT_STRIPE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 1, NULL, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', NULL),
                
                ('Set Order Status', 'MODULE_PAYMENT_STRIPE_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', 6, 1, NULL, now(), 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(', NULL),

                ('API Secret Key:', 'MODULE_PAYMENT_STRIPE_SECRET_KEY', '', 'Enter API Secret Key provided by stripe', 6, 1, NULL, now(), 'zen_cfg_password_display', NULL, NULL),

                ('Test Mode - API Publishable Test Key:', 'MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY', '', 'Enter API Publishable Test Key provided by stripe', 6, 1, NULL, now(), NULL, NULL, NULL),

                ('Test Mode - API Secret Test Key:', 'MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY', '', 'Enter API Secret Test Key provided by stripe', 6, 1, NULL, now(), 'zen_cfg_password_display', NULL, NULL),

                ('Test Mode Stripe Secure Payment Module', 'MODULE_PAYMENT_STRIPE_TEST_MODE', 'True', 'Enter your Stripe API test publishable key and secret key.\r\nNote: Don\'t forget to set it to False after testing.', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option([\'True\', \'False\'], ', NULL),
  
                ('Payment Succeeded Message:', 'TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED', 'Payment succeeded. Please wait a few seconds!', 'The message will be displayed after payment succeeded. If you do not want to display it, leave it blank.', 6, 1, NULL, now(), NULL, NULL , NULL),

                ('Form Layout', 'MODULE_PAYMENT_STRIPE_LAYOUT', 'Tabs', 'Select stripe layout Tabs or Accordion.', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option(['Tabs\', \'Accordion\'] ', NULL)"
        );
    }

    public function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() 
    {
        return [
            'MODULE_PAYMENT_STRIPE_STATUS',
            'MODULE_PAYMENT_STRIPE_TEST_MODE',
            'MODULE_PAYMENT_STRIPE_ZONE',
            'MODULE_PAYMENT_STRIPE_STATUS_ID',
            'MODULE_PAYMENT_STRIPE_SORT_ORDER',
            'MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY',
            'MODULE_PAYMENT_STRIPE_SECRET_KEY',
            'MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY',
            'MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY',
            'MODULE_PAYMENT_STRIPE_LAYOUT',
            'TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED',
        ];
    }
}
