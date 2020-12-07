<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');

 ?>

<?php
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


	$caseId = $_POST['caseid'];
	
	$query_case_data = "SELECT case_jurisdiction,case_location,case_customtext FROM docket_cases  WHERE  case_id = ".$caseId."";
    $caseData = mysqli_query($docketDataSubscribe,$query_case_data);
    $totalRows_casedata = mysqli_num_rows($caseData);
	
	if($totalRows_casedata > 0)
	{
		while($rowData = mysqli_fetch_array($caseData))
		{
			$caseJurisdiction = $rowData['case_jurisdiction'];
			$caseLocation = $rowData['case_location'];
			$caseCustomtext = $rowData['case_customtext'];
		}
	}
	 $result_html['caseJurisdiction'] = $caseJurisdiction;
	  $result_html['caseLocation'] = $caseLocation;
	   $result_html['caseCustomtext'] = $caseCustomtext;
	 
	echo json_encode($result_html);