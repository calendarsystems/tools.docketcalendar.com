<?php
/* 
Template Name: order completed
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];

include('globals/global_courts.php');
?>

<?php include('inc_top.php'); ?>
<h1>Order Completed</h1>

              <h3>Thank you!</h3>
<?php


include ('include/inc_generic_mysql.php');



mysqli_free_result($cart);


?>

<?php
}

genesis();
?>