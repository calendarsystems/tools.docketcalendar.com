<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$queryInsertArchiveTrigger = "INSERT INTO docket_cases_archive  (userid,caseid,triggerid,trigger_delete,case_delete) VALUES (".$_SESSION['userid'].",".$_POST['case_id'].",'".$_POST['trigger_item']."',2,1)";
$resultQuery = mysqli_query($docketDataSubscribe,$queryInsertArchiveTrigger);
$case_id = $_POST['case_id'];
$trigger_item = $_POST['trigger_item'];
$capi = new GoogleCalendarApi();
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

				$data = $capi->DeleteCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],$rowImportIdData['calendar_id'],$_SESSION['access_token']);	
				$updateEventsTableForArchive="UPDATE events SET status = 2 WHERE caseid = ".$case_id." AND eventIdvalue =".$rowEventsIdForImportDocketIdData['import_event_id']."";
				$AllEventsTableForArchive = mysqli_query($docketDataSubscribe,$updateEventsTableForArchive);
			}

			 
		}
			
	}else
	{
		$result_html['html'] = "No Trigger DATA";	
	}
$result_html['html'] = "Event Successfully Archived";
echo json_encode($result_html);
	
      ?>
     