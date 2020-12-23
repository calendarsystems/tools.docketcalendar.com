<?php require_once('../Connections/docketData.php'); 
//https://github.com/educoder/pest
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];


if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	   $docketData = $GLOBALS['docketData'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketData,$theValue) : mysqli_escape_string($docketData,$theValue);

	  switch ($theType) {
		case "text":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;    
		case "long":
		case "int":
		  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
		  break;
		case "double":
		  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
		  break;
		case "date":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;
		case "defined":
		  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
		  break;
	  }
	  return $theValue;
	}
}
require ('../include/Pest.php');
$login = $_POST['user_name'];
$loginToken = $CRCloginToken;
$user = new Pest($CRCurl);

$user = $user->get('/user/login?loginToken='.$loginToken.'&login='.$login);

$user = new SimpleXMLElement($user);
$response = $user;
if ($response == 'true'){
	echo "no";
}else{
	echo "yes";
}
?>