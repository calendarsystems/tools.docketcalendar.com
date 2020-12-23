<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
session_start();
//global $docketDataSubscribe;
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

require('../globals/global_tools.php');

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


	$importDocketId = $_POST['importDocketId'];
	$flagValue = $_POST['flagValue'];
	
	$queryGetUserId = "SELECT user_id  FROM import_docket_calculator  WHERE  import_docket_id = ".$importDocketId."";
    $userData = mysqli_query($docketDataSubscribe,$queryGetUserId);
	while($rowData = mysqli_fetch_array($userData))
	{
		
		$queryUpdateUserToolOption = "UPDATE users_tool_option  SET do_not_recalculate_events = '".$flagValue."'  WHERE  user_id = ".$rowData['user_id']."";
		$userData = mysqli_query($docketDataSubscribe,$queryUpdateUserToolOption);
		$result = mysqli_query($docketDataSubscribe,$queryUpdateUserToolOption) or trigger_error("Query Failed! SQL: $queryUpdateUserToolOption - Error: ".mysqli_error(), E_USER_ERROR);
		
	}	
	$responseHtml="Updated successfully";		
	$result_html['html'] = $responseHtml;
	echo json_encode($result_html);