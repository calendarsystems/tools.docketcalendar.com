<?php 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
session_start();
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


$updateCaseCustomText ="UPDATE docket_customtext SET case_customtext='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."',trigger_customtext='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."',event_custometext='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."',trigger_customtextlevel = 1,event_customtextlevel=1 WHERE case_id = ".$_POST['case_id']."";
$resultupdateCaseCustomText = mysqli_query($docketDataSubscribe,$updateCaseCustomText);

$updateSQL = "UPDATE docket_cases SET modified_by='".mysqli_real_escape_string($docketDataSubscribe,$_SESSION['author_id'])."'
			WHERE case_id = ".$_POST['case_id']." ";
			mysqli_query($docketDataSubscribe,$updateSQL);

$responseHtml = "Successfully updated case";
echo $responseHtml;
