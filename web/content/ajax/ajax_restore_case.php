<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$queryRestoreArchiveCase = "DELETE FROM docket_cases_archive WHERE userid = ".$_SESSION['userid']." AND caseid = ".$_POST['case_id']."";
$resultQuery = mysqli_query($docketDataSubscribe,$queryRestoreArchiveCase)or trigger_error("Query Failed! SQL: $queryRestoreArchiveCase - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);

$case_id = $_POST['case_id'];
$capi = new GoogleCalendarApi();
$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);

if($case_id)
	{
		$getAllImportDocketId="SELECT import_docket_id,calendar_id,access_token FROM import_docket_calculator WHERE case_id = ".$case_id."";
		$AllImportIdData = mysqli_query($docketDataSubscribe,$getAllImportDocketId);
		while($rowImportIdData = mysqli_fetch_assoc($AllImportIdData))
		{
			$calendar_id = $rowImportIdData['calendar_id'];
			$importDocketId = $rowImportIdData['import_docket_id'];
			
				$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
			$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
			while($row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo))
			{
				$reminder_minutes = $row_authOptionInfo['reminder_minutes'];
				$eventColor = $row_authOptionInfo['eventColor'];
			}

			$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id  from  import_events WHERE import_docket_id = ".$rowImportIdData['import_docket_id']."";
			$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
			while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
			{
				$queryGetDataofEventId = "SELECT * FROM case_events WHERE case_event_id='".$rowEventsIdForImportDocketIdData['import_event_id']."'";
				$resultQueryGetDataofEventId = mysqli_query($docketDataSubscribe,$queryGetDataofEventId);
				while($rowDataQueryGetDataofEventId = mysqli_fetch_assoc($resultQueryGetDataofEventId))
				{
					$summary = $rowDataQueryGetDataofEventId['short_name'];
					$description = $rowDataQueryGetDataofEventId['description'];
					$location = $rowDataQueryGetDataofEventId['location'];
					$status= $rowDataQueryGetDataofEventId['status'];
					$eventDate = $rowDataQueryGetDataofEventId['event_date'];
				}
	$all_day=1;
			$attendee="";
			$expTime = explode(" ", $eventDate);
			$eventTime = $expTime[0];				
			$date_array = array("start_time" => $eventTime, "end_time" => $eventTime, "event_date" =>$eventTime);
				$eventValue = $capi->CreateCalendarEvent(''.$calendar_id.'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendee,$description,$reminder_minutes,$eventColor,$location,$status);

				$queryUpdateEventsData="UPDATE import_events SET event_id ='".$eventValue."' WHERE import_event_id = '".$rowEventsIdForImportDocketIdData['case_event_id']."'";
							$resultUpdateEventsData = mysqli_query($docketDataSubscribe,$queryUpdateEventsData);				
			} 
		
			
			
		}
			
	}else
	{
		$result_html['html'] = "No Case DATA";	
	}
 $result_html['html'] = "Case Restored";
 echo json_encode($result_html);
	
      ?>
     