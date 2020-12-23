<?php require_once('Connections/docketData.php');
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

require 'Pest.php';
$loginToken = $CRCloginToken;


$userToken = new Pest($CRCurl);
$userToken = $userToken->get('/users/'.$_GET['username'].'?password='.$_GET['password'].'&soapREST=REST');
$userToken = new SimpleXMLElement($userToken);
$userLoginToken = $userToken;

echo $userLoginToken.'<BR>';

// get user
$user = new Pest($CRCurl);
$user = $user->get('/user?loginToken='.$userLoginToken);

$user = new SimpleXMLElement($user);
echo $user->ContactName.'<BR>';
echo $user->{'SoftwareName'}.'<BR>';
echo $user->Login;
echo '<BR><BR>';
echo 'Individual Court Systems Subscribed to:<BR>';
foreach($user->Subscription->Jurisdictions->Jurisdiction as $Sub) {
	echo 'court: '. $Sub->{'Description'}. '<BR>';
}
echo '<BR>State Systems Subscribed to:<BR>';
foreach($user->Subscription->CourtSystems->GenericTypeExt as $Sub) {
	echo 'court: '. $Sub->{'Description'}. '<BR>';
}



?>