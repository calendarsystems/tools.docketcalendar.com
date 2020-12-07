<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
@ini_set('post_max_size', '2000M');		
@ini_set('max_input_time', 1800);
@ini_set('upload_max_filesize', '1000M');
@ini_set('max_execution_time', 0);
@ini_set("memory_limit", "-1");
set_time_limit(0);
session_start();
//global $docketDataSubscribe;
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
	$importdocketid=$_POST["docket_search_id"];
	$caseid= $_POST["caseid"];
	if (isset($_POST["systemId"]))
	{
		$systemId=$_POST["systemId"];
		$sqlStr = "AND eventid = '".$systemId."'"; 
	}
	else
	{
		$sqlStr ="";
	}
	
	$checkForInsertOrUpdate="SELECT eventdesc,eventColor,eventreminderval,eventpopupreminderval FROM updateeventsdesc WHERE importdocketid=".$importdocketid." AND caseid = ".$caseid." ".$sqlStr." LIMIT 1";
	$resultForCheck = mysqli_query($docketDataSubscribe,$checkForInsertOrUpdate);
	$rowcount=mysqli_num_rows($resultForCheck);
	
	if($rowcount > 0)
	{
		while($rowData = mysqli_fetch_array($resultForCheck ))
		{
			$responseHtml['eventdesc'] = $rowData['eventdesc'];
			$responseHtml['eventColor'] = $rowData['eventColor'];
			$responseHtml['eventreminderval'] = $rowData['eventreminderval'];
			$responseHtml['eventpopupreminderval'] = $rowData['eventpopupreminderval'];
		}
		
	}
	$result_html = $responseHtml;
	echo json_encode($result_html);


?>



