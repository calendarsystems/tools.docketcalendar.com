<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$queryInsertArchiveEvent = "INSERT INTO docket_cases_archive  (userid,caseid,triggerid,eventid,event_delete,case_delete) VALUES (".$_SESSION['userid'].",".$_POST['caseid'].",'".$_POST['trigger_item']."','".$_POST['case_event_id']."',2,1)";

$resultQuery = mysqli_query($docketDataSubscribe,$queryInsertArchiveEvent);

$queryUpdateEvents="UPDATE events SET status = 2 WHERE caseid=".$_POST['caseid']." AND triggerid = '".$_POST['trigger_item']."' AND eventid = '".$_POST['case_event_id']."'";
$resultQueryUpdateEvents = mysqli_query($docketDataSubscribe,$queryUpdateEvents);
$capi = new GoogleCalendarApi();
$caseEventId = $_POST['case_event_id'];
$trigger_item = $_POST['trigger_item'];
$case_id = $_POST['caseid'];
	if($caseEventId)
	{
		$getAllImportDocketId="SELECT import_docket_id,calendar_id,access_token FROM import_docket_calculator WHERE case_id = ".$case_id." AND trigger_item=".$trigger_item."";
		$AllImportIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);
		while($rowImportIdData = mysqli_fetch_assoc($AllImportIdData))
		{
			
			$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id from  import_events WHERE import_event_id = ".$caseEventId."";
			 
			$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
			while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
			{
				
				$data = $capi->DeleteCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],$rowImportIdData['calendar_id'],$_SESSION['access_token']);
				$updateEventsTableForArchive="UPDATE events SET status = 2 WHERE caseid = ".$case_id." AND eventIdvalue =".$rowEventsIdForImportDocketIdData['import_event_id']."";
				$AllEventsTableForArchive = mysqli_query($docketDataSubscribe,$updateEventsTableForArchive);
			}
		}
			
	}else
	{
		$result_html['html'] = "No Event DATA";	
	}

$result_html['html'] = "Event Successfully Archived";
echo json_encode($result_html);

      ?>
     