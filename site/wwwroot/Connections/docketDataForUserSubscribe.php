<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
session_start();
// MySQL Information
global $docketUserDataSubscribe;
$URLADDRESS = $_SERVER['SERVER_NAME'];
$hostname_docketDataSubscribe = "98.129.229.121";
$database_docketDataSubscribe = "375786_dlsub";
$username_docketDataSubscribe = "375786_dlsub";
$pass_docketDataSubscribe = "D0cketLaw123";


$docketUserDataSubscribe = mysqli_connect($hostname_docketDataSubscribe, $username_docketDataSubscribe, $pass_docketDataSubscribe,$database_docketDataSubscribe);
if (!$docketUserDataSubscribe) {
  die("Database connection failed: " . mysqli_connect_error($docketUserDataSubscribe));
}
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$db_select = mysqli_select_db($docketUserDataSubscribe, $database_docketDataSubscribe);
if (!$db_select) {
  die("Database selection failed: " . mysqli_connect_error($docketUserDataSubscribe));
}

$GLOBALS['docketUserDataSubscribe'] = $docketUserDataSubscribe;
$GLOBALS['docketUserDataSubscribeDatabase'] = $database_docketDataSubscribe;
?>
