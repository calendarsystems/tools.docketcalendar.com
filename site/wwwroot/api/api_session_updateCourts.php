<?php require_once('Connections/docketData.php'); 
session_start();
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


mysqli_select_db($docketData,$database_docketData);
$query_cartStateSub = sprintf("SELECT DISTINCT
cart.id,
cart.sessionid,
cart.systemid,
cart.courttype,
courts.courtSystem_Description,
court_pricing.Price
FROM
cart
Inner Join courts ON cart.systemid = courts.courtSystem_SystemID
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype = 'state'");
$cartStateSub = mysqli_query($docketData,$query_cartStateSub) or die(mysqli_error($docketData));
$row_cartStateSub = mysqli_fetch_assoc($cartStateSub);
$totalRows_cartStateSub = mysqli_num_rows($cartStateSub);


mysqli_select_db($docketData,$database_docketData);
$query_cartSub = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.sessionid = '". session_id() ."' AND cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype <> 'state'");
$cartSub = mysqli_query($docketData,$query_cartSub) or die(mysqli_error($docketData));
$row_cartSub = mysqli_fetch_assoc($cartSub);
$totalRows_cartSub = mysqli_num_rows($cartSub);

$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s  and isActive =1  or sessionid = '". session_id() ."'  and isActive =1  ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart = mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


// get SubscribedStates


		do { 
			$stateIDs = $stateIDs . $row_cartStateSub['systemid']. ',';
		} while ($row_cartStateSub = mysqli_fetch_assoc($cartStateSub));
		
// get SubscribedCourts
		do { 
			$courtIDs = $courtIDs . $row_cartSub['systemid']. ',';
		} while ($row_cartSub = mysqli_fetch_assoc($cartSub));






require 'Pest.php';


$loginToken = $CRCloginToken;

// loop through each user to update /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($totalRows_attornys_cart > 0) { // if there are more attorney's
		do {
				include('api_session_updateCourts_user.php'); 
		} while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); 
} // end if more attorney's



?>