<?php require_once '../Connections/docketData.php';
require_once('../Connections/docketDataSubscribe.php');
session_start();
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//echo "<pre>"; print_r($_POST); exit();
global $docketData;
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	   
	   $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
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
if(isset($_POST))
{
    $userId = $_POST['userid'];
    $cmbJurisdictions = $_POST['cmbJurisdictions'];
    $cmbTriggers = $_POST['cmbTriggers'];
    $txtTriggerDate = $_POST['txtTriggerDate'];
    $txtTime = $_POST['txtTime'];
    $cmbServiceTypes = $_POST['cmbServiceTypes'];
    $isServed = $_POST['isServed'];
    $isTimeRequired = $_POST['isTimeRequired'];
    $cmbMatter = $_POST['cmbMatter_exist'];
    $authToken = $_POST['auth_token'];
    $import_docket_id = $_POST['import_docket_id'];

    $TriggerItem = $_POST['hidden_trigger_item'];

    $sort_date = $_POST['sort_date'];
    if(isset($_POST['events']))
    {
        $serialize_events = serialize($_POST['events']);
    } else {
        $serialize_events = '';
    }

    if($cmbServiceTypes != 0)
    {
      $ServiceType = $_POST['hidden_service_type'];
    } else {
      $ServiceType = "";
    }

    if($authToken == "")
    {
        $url = "/google-login";
        $status = 0;
    } else {
        $url = "/update-import-calendar";
        $status = 1;
    }
    $meridiem = "";
    if($txtTime != '')
    {
      $exp_time = explode(" ",$txtTime);
      $txtTime = $exp_time[0].":00";
      $meridiem = trim($exp_time[1]);
    }

   $updateSQL = "UPDATE import_docket_calculator SET access_token = '".mysqli_real_escape_string($docketDataSubscribe,$authToken)."', case_id = '".mysqli_real_escape_string($docketDataSubscribe,$cmbMatter)."', jurisdiction = '".mysqli_real_escape_string($docketDataSubscribe,$cmbJurisdictions)."', trigger_item = '".mysqli_real_escape_string($docketDataSubscribe,$cmbTriggers)."', trigger_date = '".mysqli_real_escape_string($docketDataSubscribe,$txtTriggerDate)."', triggerItem = '".mysqli_real_escape_string($docketDataSubscribe,$TriggerItem)."', serviceType = '".mysqli_real_escape_string($docketDataSubscribe,$ServiceType)."', trigger_time = '".mysqli_real_escape_string($docketDataSubscribe,$txtTime)."', meridiem = '".mysqli_real_escape_string($docketDataSubscribe,$meridiem)."', service_type = ".mysqli_real_escape_string($docketDataSubscribe,$cmbServiceTypes).", sort_date = ".mysqli_real_escape_string($docketDataSubscribe,$sort_date).", events = '".$serialize_events."' WHERE import_docket_id = '".$import_docket_id."'  ";
    $result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);

    $_SESSION['docket_search_id'] = $import_docket_id;

    if($authToken == "")
    {
        header("Location: ".$url."");
    } else {
        header("Location: ".$url."");
    }
    exit();
}  else {
    header("Location: /update-docket-calendar?id=".$import_docket_id);
    exit();
}

