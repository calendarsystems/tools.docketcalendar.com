<?php 
require_once('/dev/Connections/docketData.php');
session_start();
session_unset();
session_destroy();
$_SESSION = array();
setcookie("PHPSESSID","",time()-3600,"/"); 
setcookie("oldone","",time()-3600,"/"); 
setcookie("masterCourt","",time()-3600,"/"); 
header ('Location: login');
?>






