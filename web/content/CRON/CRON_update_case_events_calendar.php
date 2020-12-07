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
Description: This script is written to Update the Case Information
Duration : This script is been called from CRON add calendar events
*/

$msg = 'UPDATE CASE CRON JOB TIMESTAMP: ';
		$queryUpdateCronJOB = "UPDATE `cronjobrun` set jobrunat = now() where id = 2";
		$userUpdateCronJOB = mysqli_query($docketDataSubscribe,$queryUpdateCronJOB);
	
	$fileName = "/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/logs/caseupdate-log.txt";
				
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

			$queryGetAllUserDetailsNeededToSendCalendarEvents = "SELECT usertimezone,session,caseid,eventIdvalue,calendar FROM events WHERE update_case_status = 2";
			$userDataForUserDetailsNeededToSendCalendarEvents = mysqli_query($docketDataSubscribe,$queryGetAllUserDetailsNeededToSendCalendarEvents);
			$totalRows_userInfo = mysqli_num_rows($userDataForUserDetailsNeededToSendCalendarEvents);
				if($totalRows_userInfo > 0)
				{
					while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($userDataForUserDetailsNeededToSendCalendarEvents))
					{
						$session = $rowEventsIdForImportDocketIdData['session'];
						$caseid = $rowEventsIdForImportDocketIdData['caseid'];
						$eventIdvalue = $rowEventsIdForImportDocketIdData['eventIdvalue'];
						$calendarValue = $rowEventsIdForImportDocketIdData['calendar'];
						
						$getCaseEventsTableData = "SELECT short_name,remainder,location,status,event_date,description FROM case_events WHERE case_event_id = ".$eventIdvalue."";
						$getCaseEventsTableDataData = mysqli_query($docketDataSubscribe,$getCaseEventsTableData);
						$rowgetCaseEventsTableDataData = mysqli_fetch_assoc($getCaseEventsTableDataData);
						
						$selectEventsId = "SELECT event_id,import_event_id  from  import_events WHERE import_event_id".$eventIdvalue."";
						$getEventsId = mysqli_query($docketDataSubscribe,$selectEventsId);
						while ($rowEventsId = mysqli_fetch_assoc($getEventsId))
						{
							$eventId = $rowEventsId['event_id'];
						}
						
						$summary  = mysqli_real_escape_string($docketDataSubscribe,$rowgetCaseEventsTableDataData['short_name']);
						$remainder = mysqli_real_escape_string($docketDataSubscribe,$rowgetCaseEventsTableDataData['remainder']);
						$location = mysqli_real_escape_string($docketDataSubscribe,$rowgetCaseEventsTableDataData['location']);
						$status = mysqli_real_escape_string($docketDataSubscribe,$rowgetCaseEventsTableDataData['status']);
						$description = mysqli_real_escape_string($docketDataSubscribe,$rowgetCaseEventsTableDataData['description']);
						
						$expTime = explode(" ", $rowgetCaseEventsTableDataData['event_date']);
						$eventTime = $expTime[1];
					
						if ($eventTime == "00:00:00") {
								$start_time = "";
								$end_time =  "";
								$event_date = date('Y-m-d', strtotime($expTime[0]) );;
								$startDate = $event_date;
								$endDate = $event_date;
								$all_day = 1;
						} else {
								$eventExplodeTime = str_replace(":", "", $eventTime);
								$start_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) );
								$end_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
								$event_date = "";
								$update_event_date = $expTime[0].' '.date('H:i:s', strtotime($eventExplodeTime) );
								$all_day = 0;
						}
								
						$date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);
						$TIMEZONE = $rowEventsIdForImportDocketIdData['usertimezone'];
						
						$selectCaseAttendees = "SELECT DISTINCT(attendee) FROM docket_cases_attendees WHERE case_id =".$caseid."";
						$getSelectCaseAttendees =mysqli_query($docketDataSubscribe,$selectCaseAttendees);
						
						while($rowCaseAttendees = mysqli_fetch_assoc($getSelectCaseAttendees))
							{
								$attendee[] = $rowCaseAttendees['attendee'];
							}
						
						$selectCaseEventColor = "SELECT caseEventColor FROM docket_cases WHERE case_id =".$caseid."";
						$getCaseEventColor =mysqli_query($docketDataSubscribe,$selectCaseEventColor);
						
						while($rowCaseEventColor = mysqli_fetch_assoc($getCaseEventColor))
							{
								$caseEventColor = $rowCaseEventColor['caseEventColor'];
							}
						
						$selectCaseCustomText = "SELECT DISTINCT(case_customtext) FROM docket_customtext WHERE case_id =".$caseid."";
						$getCaseCustomText =mysqli_query($docketDataSubscribe,$selectCaseCustomText);
						
						while($rowgetCaseCustomText = mysqli_fetch_assoc($getCaseCustomText))
							{
								$case_customtext= $rowCaseAttendees['case_customtext'];
							}	
						
					}
					/*
					event_id is to be taken from DB
						in While Loop Got ID
						in While Loop case ID I can get calendar id
						from importEventId I can get - summary
						                             - all day
													 - dateArray
													 - TIMEZONE(Capi)
					    from abov in While Loop  SESSION						
						attendee need to be fetched from docket_case_attendees -> CaseID
						Custom Text Value from CaseID
						description Value from CaseID
						remainder Value from CaseID
						eventColor Value from CaseID
						location Value from CaseID
						status Value from CaseID
					*/
						
					$data = $capi->UpdateCalendarEvent($eventId,''.$calendarValue.'', $summary, $all_day, $date_array, $TIMEZONE, $session,$attendee,"Case Custom Comments : ".$case_customtext."<br/>".$description,$remainder,$caseEventColor,$location,$status);
				}
				else{
					$queryUpdateCronJOBIsRun = "UPDATE `cronjobrun` set isRun = 2 where id = 2";
					$userUpdateCronJOB = mysqli_query($docketDataSubscribe,$queryUpdateCronJOBIsRun);
				}
		}catch (Exception $e) {
			$msg = 'Error Message: ' .$e->getMessage();
			$fh = fopen("/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/logs/croncaseupdatelog.txt", "w+") or die("can't open file");
				fwrite($fh, "Output Response " . $msg . PHP_EOL);
				fclose($fh);
		}
		
	}
	
	
	

