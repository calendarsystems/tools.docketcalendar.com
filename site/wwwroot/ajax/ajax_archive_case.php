<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$queryUpdateArchiveCase = "UPDATE docket_cases_archive  SET case_delete =2 WHERE userid = ".$_SESSION['userid']." AND caseid = ".$_POST['case_id']."";
$resultQuery = mysqli_query($docketDataSubscribe,$queryUpdateArchiveCase)or trigger_error("Query Failed! SQL: $queryUpdateArchiveCase - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);

$capi = new GoogleCalendarApi();
$case_id = $_POST['case_id'];
	if($case_id)
	{
		$getAllImportDocketId="SELECT import_docket_id,calendar_id,access_token FROM import_docket_calculator WHERE case_id = ".$case_id."";
		$AllImportIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);
		while($rowImportIdData = mysqli_fetch_assoc($AllImportIdData))
		{
			
			$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id  from  import_events WHERE import_docket_id = ".$rowImportIdData['import_docket_id']."";
			$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
			while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
			{
				$data = $capi->DeleteCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],$rowImportIdData['calendar_id'],$_SESSION['access_token']);
			}
			
		}
		$updateEventsTableForArchive="UPDATE events SET status = 2 WHERE caseid = ".$case_id."";
		$AllEventsTableForArchive = mysqli_query($docketDataSubscribe,$updateEventsTableForArchive);
	}	
		
		
 $result_html['html'] = "Case Successfully Archived";
 echo json_encode($result_html);
	
      ?>
     