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
$case_id = $_GET['case_id'];
$trigger_item = $_GET['triggerId'];
	if($case_id)
	{
		$getAllImportDocketId="SELECT import_docket_id,calendar_id,access_token FROM import_docket_calculator WHERE case_id = ".$case_id." AND trigger_item=".$trigger_item."";
		$AllImportIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);
		while($rowImportIdData = mysqli_fetch_assoc($AllImportIdData))
		{
			
			$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id  from  import_events WHERE import_docket_id = ".$rowImportIdData['import_docket_id']."";
			$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
			while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
			{
			
				$data = $capi->DeleteCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],$rowImportIdData['calendar_id'],$rowImportIdData['access_token']);
				
				 $queryFromDeleteCustomEvents = "DELETE FROM case_events WHERE case_event_id = ".$rowEventsIdForImportDocketIdData['import_event_id'] ."";
						 mysqli_query($docketDataSubscribe,$queryFromDeleteCustomEvents);
						 
				 $queryFromDeleteImportEvents = "DELETE FROM import_events WHERE import_event_id = ".$rowEventsIdForImportDocketIdData['import_event_id']."";
					     mysqli_query($docketDataSubscribe,$queryFromDeleteImportEvents);	
				
				
			}
			/*DELETE from Import Docket Table */
			 $queryDeleteFrom="DELETE FROM import_docket_calculator WHERE import_docket_id = ".$rowImportIdData['import_docket_id']."";
			 mysqli_query($docketDataSubscribe,$queryDeleteFrom);	
			 
			$deleteEventsTable = "DELETE from events WHERE triggerid  = ".$trigger_item." AND caseid = ".$case_id."";
			$result2 = mysqli_query($docketDataSubscribe,$deleteEventsTable) or trigger_error("Query Failed! SQL: $deleteEventsTable - Error: ".mysqli_error(), E_USER_ERROR);	
			 
		}
		
		echo $result_html = "Deleted Trigger Successfully.";		
					
	}else
	{
		echo $result_html = "No Trigger DATA";	
	}
?>
<script src="../jquery/js/jquery-1.8.3.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    // Handler for .ready() called.
	/*
    window.setTimeout(function () {
        location.href = "http://googledocket.com/docket-cases";
    }, 1000);
	*/
});
</script>
