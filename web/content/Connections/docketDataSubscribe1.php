<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
// MySQL Information
/*
global $docketDataSubscribe;
$hostname_docketDataSubscribe = "mariadb-066.wc1.dfw3.stabletransit.com";
$database_docketDataSubscribe = "375786_dlsub";
$username_docketDataSubscribe = "375786_dlsub";
$password_docketDataSubscribe = "D0cketLaw123";

$docketDataSubscribe = mysqli_connect($hostname_docketDataSubscribe, $username_docketDataSubscribe, $password_docketDataSubscribe,$database_docketDataSubscribe);
if (!$docketDataSubscribe) {
  die("Database connection failed: " . mysqli_connect_error($docketDataSubscribe));
}
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$GLOBALS['docketDataSubscribe'] = $docketDataSubscribe;
$GLOBALS['database_docketData'] = $database_docketDataSubscribe;

$db_select = mysqli_select_db($docketDataSubscribe, $database_docketDataSubscribe);
if (!$db_select) {
  die("Database selection failed: " . mysqli_connect_error($docketDataSubscribe));
}
*/

$hostname_docketDataSubscribe = "mariadb-066.wc1.dfw3.stabletransit.com";
$database_docketDataSubscribe = "375786_dlsub";
$username_docketDataSubscribe = "375786_dlsub";
$password_docketDataSubscribe = "D0cketLaw123";
$docketUserDataSubscribe = mysqli_connect($hostname_docketDataSubscribe, $username_docketDataSubscribe, $password_docketDataSubscribe,$database_docketDataSubscribe) or trigger_error(mysqli_connect_error(), E_USER_ERROR);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
mysqli_select_db( $docketUserDataSubscribe,$database_docketDataSubscribe);

$GLOBALS['docketDataSubscribe'] = $docketUserDataSubscribe;
$GLOBALS['database_docketData'] = $database_docketDataSubscribe;
?>