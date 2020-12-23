<?php
/* 
Template Name: trial completed
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	
require_once('Connections/docketData.php'); 
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
?>

<?php include('inc_top.php'); ?>

      	<h1>Activate Your Trial.</h1>
              <form action="process_payment.php" method="post">
              <h3>Free Trial Sign-Up Completed</h3>
              <p>Your trial account has been setup, please check your email to activate your account.</p>
              </form>
 <?php }
 
 genesis();
 
 ?>