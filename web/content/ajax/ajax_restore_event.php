<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$queryRestoreArchiveEvent = "DELETE FROM docket_cases_archive  
 WHERE userid =".$_SESSION['userid']." AND caseid = ".$_POST['case_id']." AND triggerid = '".$_POST['trigger_item']."' AND eventid='".$_POST['case_event_id']."'";
$resultQuery = mysqli_query($docketDataSubscribe,$queryRestoreArchiveEvent);

$queryUpdateEvents="UPDATE events SET status = 1 WHERE caseid=".$_POST['case_id']." AND triggerid = '".$_POST['trigger_item']."' AND eventid = '".$_POST['case_event_id']."'";
$resultQueryUpdateEvents = mysqli_query($docketDataSubscribe,$queryUpdateEvents);

$queryGetDataofEventId = "SELECT * FROM case_events WHERE case_event_id='".$_POST['case_event_id']."'";
$resultQueryGetDataofEventId = mysqli_query($docketDataSubscribe,$queryGetDataofEventId);
while($rowDataQueryGetDataofEventId = mysqli_fetch_assoc($resultQueryGetDataofEventId))
{
	$summary = $rowDataQueryGetDataofEventId['short_name'];
	$description = $rowDataQueryGetDataofEventId['description'];
	$location = $rowDataQueryGetDataofEventId['location'];
	$status= $rowDataQueryGetDataofEventId['status'];
	$eventDate = $rowDataQueryGetDataofEventId['event_date'];
}
$getAllImportDocketIdForCase = "SELECT import_docket_id,calendar_id FROM import_docket_calculator WHERE case_id = ".$_POST['case_id']."";
	$dataAllImportDocketIdForCase = mysqli_query($docketDataSubscribe,$getAllImportDocketIdForCase);
	while($rowAllImportDocketIdForCase=mysqli_fetch_array($dataAllImportDocketIdForCase))
		{
			$calendar_id = $rowAllImportDocketIdForCase['calendar_id'];
			$importDocketId = $rowAllImportDocketIdForCase['import_docket_id'];
		}
	$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
	$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
	while($row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo))
	{
		$reminder_minutes = $row_authOptionInfo['reminder_minutes'];
		$eventColor = $row_authOptionInfo['eventColor'];
	}
	$all_day=1;
	$capi = new GoogleCalendarApi();
	$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);
	$attendee="";
	$expTime = explode(" ", $eventDate);
	$eventTime = $expTime[0];				
	$date_array = array("start_time" => $eventTime, "end_time" => $eventTime, "event_date" =>$eventTime);
$eventValue = $capi->CreateCalendarEvent(''.$calendar_id.'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendee,$description,$reminder_minutes,$eventColor,$location,$status);

$queryUpdateEventsData="UPDATE import_events SET event_id ='".$eventValue."' WHERE import_event_id = '".$_POST['case_event_id']."'";
$resultUpdateEventsData = mysqli_query($docketDataSubscribe,$queryUpdateEventsData);

$result_html['html'] = "Event Successfully Restore";
echo json_encode($result_html);