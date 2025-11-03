<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Sep 10 Modified in v1.5.8 $
 */
  class stripe extends base {

      /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     * @var string
     */
    public $code;
    /**
     * $description is a soft name for this payment method
     * @var string 
     */
    public $description;
    /**
     * $email_footer is the text to me placed in the footer of the email
     * @var string
     */
    public $email_footer;
    /**
     * $enabled determines whether this module shows or not... during checkout.
     * @var boolean
     */
    public $enabled;
    /**
     * $order_status is the order status to set after processing the payment
     * @var int
     */
    public $order_status;
    /**
     * $title is the displayed name for this order total method
     * @var string
     */
    public $title;
    /**
     * $sort_order is the order priority of this payment module when displayed
     * @var int
     */
    public $sort_order;

// class constructor
function __construct() {
  global $order;

  $this->code = 'stripe';
  $this->title = MODULE_PAYMENT_STRIPE_TEXT_TITLE;
  $this->description = MODULE_PAYMENT_STRIPE_TEXT_DESCRIPTION;
  $this->sort_order = defined('MODULE_PAYMENT_STRIPE_SORT_ORDER') ? MODULE_PAYMENT_STRIPE_SORT_ORDER : null;
  $this->enabled = (defined('MODULE_PAYMENT_STRIPE_STATUS') && MODULE_PAYMENT_STRIPE_STATUS == 'True');

  if (null === $this->sort_order) return false;

  if (IS_ADMIN_FLAG === true && (MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY == '' || MODULE_PAYMENT_STRIPE_SECRET_KEY == '' || MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY == '' || MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY == '' )) $this->title .= '<span class="alert"> (not configured - stripe publishable key and secret key)</span>';

  if (IS_ADMIN_FLAG === true && (MODULE_PAYMENT_STRIPE_TEST_MODE == 'True')) $this->title .= '<span class="alert"> (Stripe is in testing mode)</span>';

  if (IS_ADMIN_FLAG === true && (strpos(MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY,'_test_') !== false ) == true || (strpos(MODULE_PAYMENT_STRIPE_SECRET_KEY,'_test_') !== false ) == true) $this->title .= '<span class="alert"> (Test key entered in API publishable key or secret key )</span>';

  if (IS_ADMIN_FLAG === true && (strpos(MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY,'_test_') !== false ) == false || (strpos(MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY,'_test_') !== false ) == false) $this->title .= '<span class="alert"> (Test key not entered in the test mode field)</span>';

  if ((int)MODULE_PAYMENT_STRIPE_STATUS_ID > 0) {
    $this->order_status = MODULE_PAYMENT_STRIPE_STATUS_ID;
  
  }

  if (is_object($order)) $this->update_status();
  }

// class methods
function update_status() {
  global $order, $db,$amount,$payment_currency;

  if ($this->enabled && (int)MODULE_PAYMENT_STRIPE_ZONE > 0 && isset($order->billing['country']['id'])) {
    $check_flag = false;
    $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_STRIPE_ZONE . "' and zone_country_id = '" . (int)$order->billing['country']['id'] . "' order by zone_id");
    while (!$check->EOF) {
      if ($check->fields['zone_id'] < 1) {
        $check_flag = true;
        break;
      } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
        $check_flag = true;
        break;
      }
      $check->MoveNext();
    }

    if ($check_flag == false) {
      $this->enabled = false;
    }
  }

  // other status checks?
  if ($this->enabled) {
    // other checks here
  }
}

function javascript_validation() {
  return false;
}

function selection() {
  return array('id' => $this->code,
               'module' => $this->title);
}

function pre_confirmation_check() {
  global $order, $db,$stripeCustomerID,$user_id,$stripe_select,$order_total_modules;

  if (MODULE_PAYMENT_STRIPE_TEST_MODE === 'True') {
      $publishable_key = MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY;
      $secret_key = MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY;
      $test_mode = true;
    }else{
      $publishable_key = MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY;
      $secret_key = MODULE_PAYMENT_STRIPE_SECRET_KEY;
      $test_mode = false;
    }

  $payment_currency = $order->info['currency'];
  $Xi_currency = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
  $Xiooo_currency = ['BHD','JOD','KWD','OMR','TND'];

  if (in_array($payment_currency,$Xi_currency) == true ) {
      $multiplied_by = 1;
      $decimal_places = 0;
    } elseif (in_array($payment_currency,$Xiooo_currency) == true ) {
      $multiplied_by = 1000;
      $decimal_places = 2;
    }else{
      $multiplied_by = 100;
      $decimal_places = 2;
    }

  if ( isset($_SESSION['opc_saved_order_total'])) {
     $order_value = $_SESSION['opc_saved_order_total'];
    }else{

      if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true' && MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER >= $order->info['total']) {
         $order_value = $order->info['total'] + MODULE_ORDER_TOTAL_LOWORDERFEE_FEE ;
        } else{
         $order_value = $order->info['total'];
        }
    }
  $amount_total=round($order_value * $order->info['currency_value'],$decimal_places)*$multiplied_by;
  
  $fullname = $order->billing['firstname'].= $order->billing['lastname'];
  $email = $order->customer['email_address'];
  $user_id = $_SESSION['customer_id'];
  $registered_customer = false;
  $stripe_customer = $db->Execute("SELECT stripe_customers_id FROM " . TABLE_STRIPE . " WHERE customers_id = '" .$_SESSION['customer_id'] . "' order by id DESC LIMIT 1");
  if ($stripe_customer->RecordCount() > 0) {
  $registered_customer = true;
}
   $stripe_select = 'True';


if ($_SESSION['paymentIntent'] == '' ){
require_once 'stripepay/create.php' ;
}

}
function confirmation() {
  return false;
}

function process_button() {
  return false;
}

function before_process() {
  global $order;
    $order_comment = $_SESSION['order_add_comment']."\n Stripe ID:";
    $order_comment = $order_comment . $_SESSION['paymentIntent'];
     $order->info['comments'] = $order_comment;
}

function after_process() {
     unset($_SESSION['order_add_comment']);
     unset($_SESSION['paymentIntent']);
    }

function get_error() {
  return false;
}

function check() {
  global $db;

  if (!isset($this->_check)) {
    $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_STRIPE_STATUS'");
    $this->_check = $check_query->RecordCount();
  }
  return $this->_check;
}

function install() {
  global $db, $messageStack;
  if (defined('MODULE_PAYMENT_STRIPE_STATUS')) {
    $messageStack->add_session('StMoneyOrder module already installed.', 'error');
    zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=stripe', 'NONSSL'));
    return 'failed';
  }

  $db-> execute("DROP TABLE IF EXISTS " . DB_PREFIX  . "stripe ;");  
  $db-> execute("CREATE TABLE  " . DB_PREFIX  . "stripe(id INT(11) AUTO_INCREMENT PRIMARY KEY,customers_id INT(11),Stripe_Customers_id VARCHAR(32))");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES('Enable Stripe Secure Payment Module', 'MODULE_PAYMENT_STRIPE_STATUS', 'True', 'Do you want to accept Stripe Secure Payment?', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ', NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES
  ('API Publishable Key:', 'MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY', '', 'Enter API Publishable Key provided by stripe', 6, 1, NULL, now(), NULL, NULL, NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Sort order of display.', 'MODULE_PAYMENT_STRIPE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', 6, 1, NULL, now(), NULL, NULL, NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES
  ('Payment Zone', 'MODULE_PAYMENT_STRIPE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 1, NULL, now(), 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Set Order Status', 'MODULE_PAYMENT_STRIPE_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', 6, 1, NULL, now(), 'zen_get_order_status_name', 'zen_cfg_pull_down_order_statuses(', NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES
  ('API Secret Key:', 'MODULE_PAYMENT_STRIPE_SECRET_KEY', '', 'Enter API Secret Key provided by stripe', 6, 1, NULL, now(), 'zen_cfg_password_display', NULL, NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Test Mode - API Publishable Test Key:', 'MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY', '', 'Enter API Publishable Test Key provided by stripe', 6, 1, NULL, now(), NULL, NULL, NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES
  ('Test Mode - API Secret Test Key:', 'MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY', '', 'Enter API Secret Test Key provided by stripe', 6, 1, NULL, now(), 'zen_cfg_password_display', NULL, NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Test Mode Stripe Secure Payment Module', 'MODULE_PAYMENT_STRIPE_TEST_MODE', 'True', 'Enter your Stripe API test publishable key and secret key.\r\nNote: Don\'t forget to set it to False after testing.', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option(array(\'True\', \'False\'), ', NULL)");
  
  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Payment Succeeded Message:', 'TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED', 'Payment succeeded. Please wait a few seconds!', 'The message will be displayed after payment succeeded. If you do not want to display it, leave it blank.', 6, 1, NULL, now(), NULL, NULL , NULL)");

  $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`, `use_function`, `set_function`, `val_function`) VALUES      
  ('Form Layout', 'MODULE_PAYMENT_STRIPE_LAYOUT', 'Tabs', 'Select stripe layout Tabs or Accordion.', 6, 1, NULL, now(), NULL, 'zen_cfg_select_option(array(\'Tabs\', \'Accordion\'), ', NULL)");


}

function remove() {
  global $db;
  $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
}

function keys() {

  return array('MODULE_PAYMENT_STRIPE_STATUS', 'MODULE_PAYMENT_STRIPE_TEST_MODE','MODULE_PAYMENT_STRIPE_ZONE', 'MODULE_PAYMENT_STRIPE_STATUS_ID', 'MODULE_PAYMENT_STRIPE_SORT_ORDER', 'MODULE_PAYMENT_STRIPE_PUBLISHABLE_KEY','MODULE_PAYMENT_STRIPE_SECRET_KEY','MODULE_PAYMENT_STRIPE_PUBLISHABLE_TEST_KEY','MODULE_PAYMENT_STRIPE_SECRET_TEST_KEY','MODULE_PAYMENT_STRIPE_LAYOUT','TEXT_PAYMENT_STRIPE_PAYMENTSUCCEEDED');
}
}
