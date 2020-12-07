<?php
//require_once('Connections/docketData.php');
session_start();
session_unset();
session_destroy();
$_SESSION = array();
setcookie("PHPSESSID","",time()-3600,"/"); 
setcookie("oldone","",time()-3600,"/");
//echo "<pre>"; print_r($_SESSION); exit();
header ('Location: /login');
?>