<?php
/**
 * Uninstalls the WP jScrollPane options when an uninstall has been requested
 * from the WordPress admin plugin dashboard
 */

 // If uninstall/delete not called from WordPress then exit
if( ! defined ( 'ABSPATH' ) && ! defined ( 'WP_UNINSTALL_PLUGIN' ) )
        exit ();

// Delete shadowbox option from options table
delete_option('wpjsp','','','no');
delete_option('wpjsp-mouse','','','no');
?>