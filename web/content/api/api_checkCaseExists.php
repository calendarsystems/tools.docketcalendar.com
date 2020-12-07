<?php 
require_once('../Connections/docketData.php'); 
require_once('../Connections/docketDataSubscribe.php');
session_start();
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
$colname_checkCase = $_POST['case_name'];
$createdBy = $_POST['createdby'];
if($colname_checkCase)
{
	$querycaseNameInfo = "SELECT case_matter FROM docket_cases WHERE case_matter LIKE BINARY '".$colname_checkCase."' and user_id = '".$_SESSION['userid']."' and created_by ='".$createdBy."'";
	$caseInfo = mysqli_query($docketDataSubscribe,$querycaseNameInfo);
	$totalRowscheckCaseName = mysqli_num_rows($caseInfo);
}

 if ($totalRowscheckCaseName == 0) { 
echo "yes";
 }else{
echo "no"; 
 }
  
  
  ?>
