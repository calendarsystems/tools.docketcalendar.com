<?php
require_once('Connections/docketDataSubscribe.php');

$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
session_start();
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($docketDataSubscribe, $theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketDataSubscribe,$theValue) : mysqli_escape_string($docketDataSubscribe,$theValue);

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

if (@$_GET['state'] == ''){
    $_SESSION['state'] = 'United States';
}else{
    $_SESSION['state'] = $_GET['state'];
}

$colname_userInfo = "-1";
if (isset($_SESSION['userid'])) {
  $colname_userInfo = $_SESSION['userid'];
}


$query_userInfo = sprintf("SELECT * FROM users WHERE id = %s", GetSQLValueString($docketDataSubscribe,$colname_userInfo, "int"));
$userInfo = mysqli_query($docketDataSubscribe,$query_userInfo);
$totalRows_userInfo = mysqli_num_rows($userInfo);

if($totalRows_userInfo > 0)
{
    $row_userInfo = mysqli_fetch_assoc($userInfo);
}


$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}

$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s  and isActive is null  or sessionid = '". session_id() ."'  and isActive is null  ORDER BY name ASC", GetSQLValueString($docketDataSubscribe,$colname_attornys_cart, "int"));
$attornys_cart = mysqli_query($docketDataSubscribe,$query_attornys_cart);
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


$query_attornys_sub = sprintf("SELECT * FROM attorneys WHERE user_id = %s  and isActive=1  or sessionid = '". session_id() ."'  and isActive=1  ORDER BY name ASC", GetSQLValueString($docketDataSubscribe,$colname_attornys_cart, "int"));
$attornys_sub  = mysqli_query($docketDataSubscribe, $query_attornys_sub);
$row_attornys_sub  = mysqli_fetch_assoc($attornys_sub );
$totalRows_attornys_sub  = mysqli_num_rows($attornys_sub );



$query_cartState = sprintf("SELECT DISTINCT
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
WHERE cart.subscribed = '0' AND cart.courttype = 'state' and cart.sessionid = %s", GetSQLValueString($docketDataSubscribe,session_id(), "text"));
$cartState = mysqli_query($docketDataSubscribe, $query_cartState);
$row_cartState = mysqli_fetch_assoc($cartState);
$totalRows_cartState = mysqli_num_rows($cartState);


$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE  cart.subscribed = 0 AND cart.courttype <> 'state' and cart.sessionid = %s", GetSQLValueString($docketDataSubscribe,session_id(), "text"));
$cart = mysqli_query($docketDataSubscribe, $query_cart);
$cart2 = mysqli_query($docketDataSubscribe, $query_cart);
$row_cart = mysqli_fetch_assoc($cart);
$totalRows_cart = mysqli_num_rows($cart);


if (isset($_SESSION['userid'])){

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
	$cartStateSub = mysqli_query($docketDataSubscribe, $query_cartStateSub);
	$row_cartStateSub = mysqli_fetch_assoc($cartStateSub);
	$totalRows_cartStateSub = mysqli_num_rows($cartStateSub);


	$query_cartSub = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.sessionid = '". session_id() ."' AND cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype <> 'state'");
	$cartSub = mysqli_query($docketDataSubscribe, $query_cartSub);
	$row_cartSub = mysqli_fetch_assoc($cartSub);
	$totalRows_cartSub = mysqli_num_rows($cartSub);
}


?>