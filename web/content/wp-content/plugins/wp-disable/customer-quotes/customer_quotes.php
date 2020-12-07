<?php
/*
Plugin Name: Customer Quote
Description: List the customer quotes
Version: 1
Author: Vinithkumar
License: Clarion Technologies
 */

require_once ABSPATH . '/wp-content/plugins/customer-quotes/class-quote-list.php';

register_activation_hook(__FILE__, 'Wh_install');
register_deactivation_hook(__FILE__, 'Wh_deactivate');
add_action('admin_menu', 'Wh_addlinks');

// +---------------------------------------------------------------------------+
// | Create table on activation                                                |
// +---------------------------------------------------------------------------+

function Wh_install() {

	global $wpdb;

	$txt_short = "Thank you for installing the Customer Quotes plugin.";
	$txt_long = "Thank you for installing the Customer Quotes plugin.  You can manage the quotes through the admin area under the Quotes tab.";
}

// +---------------------------------------------------------------------------+
// | Uninstall plugin                                                          |
// +---------------------------------------------------------------------------+

function Wh_deactivate() {
	global $wpdb;

	$Wh_deldata = get_option('Wh_deldata');

	delete_option("Wh_version");
	unregister_Wh_options();
}

// +---------------------------------------------------------------------------+
// | Create admin links                                                        |
// +---------------------------------------------------------------------------+

function Wh_addlinks() {

// Create top-level menu and appropriate sub-level menus:
	add_menu_page('customer_quote', 'Quotes', 'manage_options', 'quote-page', 'Wh_adminpage', plugins_url('/customer-quotes/quote.png'));
}

function Wh_adminpage() {
	echo "<h1>Manage Customers Quote</h1>";
	$Quote_List = new Quote_List();
	$Quote_List->prepare_items();
	$Quote_List->display();
}

?>