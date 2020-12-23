<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
ini_set('max_execution_time', 1200);
ini_set('memory_limit', '520M');
set_time_limit(1200);
session_start();
//global $docketDataSubscribe;
$capi = new GoogleCalendarApi();
	$case_id = $_GET['case_id'];
	$importEventId=array();
	$caseEventId = array();
	$ImportDocketIdDataArr = array();
	
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

	
	if($case_id)
	{
		$query_importEvents = "SELECT i.import_docket_id,i.access_token,e.authenticator,c.event_date,c.short_name,dc.case_matter,c.case_event_id,e.event_docket,e.has_child,i.triggerItem FROM docket_cases dc
					INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
					INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
					INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
					WHERE dc.user_id = ".$_SESSION['userid']." AND dc.case_id = ".$case_id."  ORDER BY c.import_event_id desc";
					
					$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
					$totalRows_importEvents = mysqli_num_rows($ImportEvents);
					
		while($rowCaseEventId = mysqli_fetch_assoc($ImportEvents))
		{
			$case_event_id = $rowCaseEventId['case_event_id'];
					array_push($caseEventId , $case_event_id);
					$query_importEvents = "SELECT e.event_id,i.calendar_id
					FROM docket_cases dc
					INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
					INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
					INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
					WHERE c.case_event_id = '".$case_event_id."'";
					$searchInfo = mysqli_query($docketDataSubscribe,$query_importEvents);
					$totalRows_searchInfo = mysqli_num_rows($searchInfo);
					while($row_searchInfo = mysqli_fetch_array($searchInfo))
					{
						$importEventIdVal =  $row_searchInfo['event_id'];
						array_push($importEventId , $importEventIdVal);
						if($totalRows_searchInfo > 0)
						{	
					
								$data = $capi->DeleteCalendarEvent($row_searchInfo['event_id'],$row_searchInfo['calendar_id'],$_SESSION['access_token']);						
						}
					}
		}
		
					for($i=0;$i < sizeof($caseEventId);$i++)
					{
						 $query_deleteCase = "DELETE FROM case_events WHERE case_event_id = '".$caseEventId[$i] ."'";
						 mysqli_query($docketDataSubscribe,$query_deleteCase);
						
					}
					
					
					
					
					
					for($j=0;$j < sizeof($importEventId);$j++)
					{
						 $query_deleteEvents = "DELETE FROM import_events WHERE event_id = '".$importEventId[$j]."' ";
					     mysqli_query($docketDataSubscribe,$query_deleteEvents);	
					}	
					
					
				$getAllImportDocketId = "SELECT import_docket_id from import_docket_calculator where case_id = ".$case_id."";
				$ImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);

				while($rowCaseImportId = mysqli_fetch_assoc($ImportDocketIdData))
				{
					$ImportDocketIdVal = $rowCaseImportId['import_docket_id'];
					$query_deleteImportDocketId = "DELETE FROM import_docket_calculator WHERE import_docket_id = '".$ImportDocketIdVal ."'";
					mysqli_query($docketDataSubscribe,$query_deleteImportDocketId);
				}
				
			
			$query_deleteCasesAttendees = "DELETE FROM  docket_cases_attendees WHERE case_id = '".$case_id."'";
			mysqli_query($docketDataSubscribe,$query_deleteCasesAttendees);
			
			$query_deleteCasesUser = "DELETE FROM  docket_cases_users WHERE case_id = '".$case_id."'";
			mysqli_query($docketDataSubscribe,$query_deleteCasesUser);
			
			$query_deleteInfoCases = "DELETE FROM docket_cases WHERE case_id = '".$case_id."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfoCases);
			
			echo $result_html = "Deleted Successfully.";		
					
	}
		
	
	//$result_html['html'] = "Deleted Successfully.";
	//echo json_encode($result_html);
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
