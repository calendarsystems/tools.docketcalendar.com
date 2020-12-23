<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
require_once('../googleCalender/settings.php');
 
session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

require('../globals/global_tools.php');
global $docketData;
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
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
				$importDocketId = $_POST['importDocketId'];
				if($importDocketId)
				{
					$txtTriggerDate = mysqli_real_escape_string($docketDataSubscribe,$_POST['TriggerDate']);
					$txtTime = mysqli_real_escape_string($docketDataSubscribe,$_POST['TriggerTime']);
					$meridiem = "";
					if($txtTime != '')
					{
					  $exp_time = explode(" ",$txtTime);
					  $txtTime = $exp_time[0].":00";
					  $meridiem = trim($exp_time[1]);
					}	
					$updateSQL = "UPDATE import_docket_calculator SET trigger_date = '".$txtTriggerDate."',trigger_time='".$txtTime."',meridiem='".$meridiem."' WHERE import_docket_id  = ".$importDocketId." ";
					$result2 = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);

                $responseHtml = "<span style='color:green;'>Successfully updated.</span>";
                $result_html['html'] = $responseHtml;
                echo json_encode($result_html);
            } else {
             $result_html['html'] = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
             echo json_encode($result_html);
            }
    
?>

