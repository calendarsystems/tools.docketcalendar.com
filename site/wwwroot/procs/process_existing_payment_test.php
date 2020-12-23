<?php
require_once('Connections/docketData.php');
session_start();
ob_start();
// Include AuthnetCIM class. Nothing works without it!
require('../classes/AuthnetCIM.class.php');
require('../classes/register.php');

$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

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



$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


mysqli_select_db($docketData,$database_docketData);
$query_user = sprintf("SELECT * FROM users WHERE sessionID = '". session_id() ."'", GetSQLValueString($colname_user, "text"));
$user =mysqli_query($docketData,$query_user) or die(mysqli_error($docketData));
$row_user = mysqli_fetch_assoc($user);
$totalRows_user = mysqli_num_rows($user);



mysqli_select_db($docketData,$database_docketData);
$query_courts = sprintf("SELECT * FROM cart WHERE courttype <> 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$courts =mysqli_query($docketData,$query_courts) or die(mysqli_error($docketData));
$row_courts = mysqli_fetch_assoc($courts);
$totalRows_courts = mysqli_num_rows($courts);

mysqli_select_db($docketData,$database_docketData);
$query_state = sprintf("SELECT * FROM cart WHERE courttype = 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$state =mysqli_query($docketData,$query_state) or die(mysqli_error($docketData));
$row_state = mysqli_fetch_assoc($state);
$totalRows_state = mysqli_num_rows($state);

// get courts
if ($totalRows_courts > 0){
	do {
		$indCourts = $row_courts['systemid'].','.$indCourts;
	} while ($row_courts = mysqli_fetch_assoc($courts));
}

if ($totalRows_state > 0){
	do {
		$indState = $row_state['systemid'].','.$indState;
	} while ($row_state = mysqli_fetch_assoc($state));
}

echo $indCourts .'<BR>';
echo $indState .'<BR>';
echo 'firm: '. $row_user['firm'] .'<BR>';
echo 'atty count: '. $totalRows_attornys_cart .'<BR><BR>';
// Use try/catch so if an exception is thrown we can catch it and figure out what happened

		require '../include/Pest.php';
		$loginToken = $CRCloginToken;
		
		
		$userToken = new Pest($CRCurl);
		$userToken = $userToken->get('/users/'. $row_user['username'] .'?password='. $row_user['userpassword'] .'&soapREST=SOAP');
		$userToken = new SimpleXMLElement($userToken);
		$ParentLoginToken = $userToken;
		echo "ParentLoginToken: ". $ParentLoginToken;
		if ($totalRows_attornys_cart > 0) { // if there are more attorney's
			do {
				
				if (empty($row_attornys_cart['isActive'])){ // if new attorney
				echo '<BR>NEW: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

					include('inc_add_subuser_to_crc.php'); 
					
					
//					 // update users cart to mark subscribed courts
//					  $updateSQL = sprintf("UPDATE cart SET subscribed=%s WHERE sessionid=%s",
//								   GetSQLValueString($_SESSION['userid'], "int"),
//								   GetSQLValueString(session_id(), "text"));
//								   mysqli_select_db($docketData,$database_docketData);
//								   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
//					// update users attorneys to mark subscribed attorneys
//					  $updateSQL = sprintf("UPDATE attorneys SET isActive='1', user_id=%s WHERE sessionid=%s",
//								   GetSQLValueString($_SESSION['userid'],"int"),
//								   GetSQLValueString(session_id(), "text"));
//								   mysqli_select_db($docketData,$database_docketData);
//								   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
					
				}else{ // attorney is existing so just update
				
				echo  ' EXISTING: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

				
						
				} // end if new attorney
			} while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); 
		} // end if more attorney's

		

		
		
///////////////// end sub users user to CRC api /////////////////////////////////////////////////////////////////////////
		
  		// all set redirect
		//header('Location: ordercompleted.php');


?>