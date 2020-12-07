<?php 
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/Connections/docketData.php');
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/Connections/docketDataSubscribe.php');
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/googleCalender/google-calendar-api.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$capi = new GoogleCalendarApi();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
/*
Description: This script is written to Import events in calendar
Duration : This CRON Script runs every 30 sec's
*/
$msg = 'UPDATE CRON JOB TIMESTAMP: ';
		$queryUpdateCronJOB = "UPDATE `cronjobrun` set jobrunat = now() where id = 1";
		$userUpdateCronJOB = mysqli_query($docketDataSubscribe,$queryUpdateCronJOB);

$querySelectCronJOB = "SELECT isRun,id FROM cronjobrun";
$userSelectCronJOB = mysqli_query($docketDataSubscribe,$querySelectCronJOB); 

	while($rowAllSelectCronJOB=mysqli_fetch_assoc($userSelectCronJOB))
	{
		if($rowAllSelectCronJOB['isRun'] == 1)
		{
			
			switch($rowAllSelectCronJOB['id'])
			{
				CASE 1:
						shell_exec('php /mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/CRON/CRON_add_calendar_event.php');
						break;
				CASE 2:
						shell_exec('php /mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/CRON/CRON_update_case_events_calendar.php');
						break;
				CASE 3:
						shell_exec('php /mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/CRON/CRON_archive_case.php');
						break;
				CASE 4:	
						shell_exec('php /mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/CRON/CRON_archive_case_restore.php');
						break;
			}
				
		}
	}
	$fileName = "/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/logs/sample-log.txt";
				
	if (file_exists($fileName))  
	{ 
		echo "The file $fileName exists"; 
	} 
	else 
	{ 
	$msgNew="OPEN FILE";

	$fh = fopen($fileName, "w") or die("can't open file");
	fwrite($fh, "Output".$msgNew. PHP_EOL);
	fclose($fh);
		try
		{
			
			
			/* Get All calendar events which are not added in event */

			$queryGetAllUserDetailsNeededToSendCalendarEvents = "SELECT * FROM events WHERE send_status = 1";
			$userDataForUserDetailsNeededToSendCalendarEvents = mysqli_query($docketDataSubscribe,$queryGetAllUserDetailsNeededToSendCalendarEvents);
			$totalRows_userInfo = mysqli_num_rows($userDataForUserDetailsNeededToSendCalendarEvents);
				if($totalRows_userInfo > 0)
				{
				
					$msg.='CALENDAR EVENTS UPDATE JOB:';
				
						while($rowUserData = mysqli_fetch_assoc($userDataForUserDetailsNeededToSendCalendarEvents))
					{
						$msg.= 'EVENTS UPDATE ID: '.$rowUserData['id'];
						
						$summary = mysqli_real_escape_string($docketDataSubscribe,$rowUserData['title']);
						$description = mysqli_real_escape_string($docketDataSubscribe,$rowUserData['description']);
						  $user_timezone = $capi->GetUserCalendarTimezone($rowUserData['session']);
						/* OLD CODE ONLY FOR REFRENCE */
						/*
							$event_id = $capi->CreateCalendarEvent(''.$_POST['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'], $attendees,$textValue.$description,$reminder_minutes,$eventColor,$location,$status);
						*/
						$arrAttendees = explode (",", $rowUserData['attendee']); 
						$date_array = array("start_time" => $rowUserData['start_time'], "end_time" =>$rowUserData['end_time'], "event_date" => $rowUserData['event_date']);
					
						$event_id = $capi->CreateCalendarEvent(''.$rowUserData['calendar'].'', $summary, $rowUserData['allday'], $date_array, $rowUserData['usertimezone'], $rowUserData['session'], $arrAttendees ,$rowUserData['description'],$rowUserData['remindermin'],$rowUserData['eventColor'],$rowUserData['location'],$rowUserData['status']);
						$queryUpdateEventsTableForSendStatus = "UPDATE events set send_status = 2 WHERE id =".$rowUserData['id']."";
						$userUpdateEventsTableForSendStatus = mysqli_query($docketDataSubscribe,$queryUpdateEventsTableForSendStatus);
						$queryUpdateImportEvents = "UPDATE import_events set event_id = '".$event_id."' WHERE import_event_id =".$rowUserData['eventIdvalue']."";
						$userUpdateImportEvents = mysqli_query($docketDataSubscribe,$queryUpdateImportEvents);
					
						
						$fh = fopen("/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/logs/cron-update-log.txt", "w+") or die("can't open file");
						fwrite($fh, "Output Response <br>". $msg . PHP_EOL);
						fclose($fh);
											
					}
				}else{
					$queryUpdateCronJOBIsRun = "UPDATE `cronjobrun` set isRun = 2 where id = 1";
					$userUpdateCronJOB = mysqli_query($docketDataSubscribe,$queryUpdateCronJOBIsRun);
				}
			
		}
			catch (Exception $e) {
			$msg = 'Error Message: ' .$e->getMessage();
			$fh = fopen("/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/logs/cron-update-log.txt", "w+") or die("can't open file");
				fwrite($fh, "Output Response " . $msg . PHP_EOL);
				fclose($fh);
		}
		
		unlink($fileName);
	} 
