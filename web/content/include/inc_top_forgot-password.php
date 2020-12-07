<?php require_once('Connections/docketData.php'); ?>

<?php session_start();

	include('globals/global_courts.php');
include ('include/inc_generic_mysql.php');

setcookie('oldone',session_id());
//$_SESSION['old_sid'] = 'test';
 ?>
