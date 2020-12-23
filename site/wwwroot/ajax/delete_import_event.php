<?php 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
ini_set('max_execution_time', 1200);
ini_set('memory_limit', '520M');
set_time_limit(1200);
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
$capi = new GoogleCalendarApi();
$eventId = $_GET['eventId'];
$trigger_item = $_GET['triggerId'];
$case_id = $_GET['case_id'];
	if($eventId)
	{
		$getAllImportDocketId="SELECT import_docket_id,calendar_id,access_token FROM import_docket_calculator WHERE case_id = ".$case_id." AND trigger_item=".$trigger_item."";
		$AllImportIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);
		while($rowImportIdData = mysqli_fetch_assoc($AllImportIdData))
		{
			
			 $getAllEventsIdForImportDocketId="SELECT event_id from  import_events WHERE import_event_id = ".$eventId."";
			$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
			while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
			{
			
				$data = $capi->DeleteCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],$rowImportIdData['calendar_id'],$rowImportIdData['access_token']);
				
				$queryFromDeleteCustomEvents = "DELETE FROM case_events WHERE case_event_id = ".$eventId."";
						 mysqli_query($docketDataSubscribe,$queryFromDeleteCustomEvents);
						 
				 $queryFromDeleteImportEvents = "DELETE FROM import_events WHERE import_event_id = ".$eventId."";
					     mysqli_query($docketDataSubscribe,$queryFromDeleteImportEvents);	
				
			}
			$deleteEventsTable = "DELETE from events WHERE eventid  = ".$eventId."";
			$result2 = mysqli_query($docketDataSubscribe,$deleteEventsTable) or trigger_error("Query Failed! SQL: $deleteEventsTable - Error: ".mysqli_error(), E_USER_ERROR);	
			
		}
		echo $result_html = "Deleted Event Successfully.";		
					
	}else
	{
		echo $result_html = "No Event DATA";	
	}
?>
<script src="../jquery/js/jquery-1.8.3.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    // Handler for .ready() called.
	
    window.setTimeout(function () {
        location.href = "http://googledocket.com/docket-cases";
    }, 1000);
	
});
</script>
