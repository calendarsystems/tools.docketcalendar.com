<?php 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
require_once('../googleCalender/settings.php');
require('../globals/global_tools.php');
ini_set('max_execution_time', 1200);
ini_set('memory_limit', '520M');
set_time_limit(1200);
session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
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

			    $importDocketId = $_POST['import_docket_id'];
				$caseId = $_POST['case_id'];
				$importEventId=array();
				$caseEventId = array();
				$capi = new GoogleCalendarApi();
				if($importDocketId)
				{
					$query_importEvents = "SELECT i.access_token,e.authenticator,c.event_date,i.import_docket_id,c.short_name,dc.case_matter,c.case_event_id,e.event_docket,e.has_child,i.triggerItem,i.trigger_item FROM docket_cases dc
					INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
					INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
					INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
					WHERE dc.user_id = ".$_SESSION['userid']." AND dc.case_id = ".$caseId." and e.import_docket_id  = ".$importDocketId." ORDER BY c.import_event_id desc";
					$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
					$totalRows_importEvents = mysqli_num_rows($ImportEvents);
					while($rowCaseEventId = mysqli_fetch_assoc($ImportEvents))
					{
							$case_event_id = $rowCaseEventId['case_event_id'];
							$trigger_item = $rowCaseEventId['trigger_item'];
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
					
					$query_searchInfo = "SELECT status,calendar_id FROM import_docket_calculator WHERE import_docket_id = '".$importDocketId."' ";
					$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
					$rowStatusCalendarId = mysqli_fetch_array($searchInfo);
					for($i=0;$i < sizeof($caseEventId);$i++)
					{
						
						 $queryDeleteCaseEvents = "DELETE FROM case_events WHERE case_event_id = '".$caseEventId[$i] ."'";
						 mysqli_query($docketDataSubscribe,$queryDeleteCaseEvents);
						
					}
					for($j=0;$j < sizeof($importEventId);$j++)
					{
						 $queryDeleteImportEvents = "DELETE FROM import_events WHERE event_id = '".$importEventId[$j]."' ";
					     mysqli_query($docketDataSubscribe,$queryDeleteImportEvents);	
					}	

					$deleteSQL = "DELETE from import_docket_calculator WHERE import_docket_id  = ".$importDocketId."";
					$result2 = mysqli_query($docketDataSubscribe,$deleteSQL) or trigger_error("Query Failed! SQL: $deleteSQL - Error: ".mysqli_error(), E_USER_ERROR);	

					$deleteEventsTable = "DELETE from events WHERE triggerid  = ".$trigger_item." AND caseid = ".$caseId."";
					$result2 = mysqli_query($docketDataSubscribe,$deleteEventsTable) or trigger_error("Query Failed! SQL: $deleteEventsTable - Error: ".mysqli_error(), E_USER_ERROR);						
							
					$responseHtml = "<span style='color:green;'>Successfully deleted trigger.</span>";
					$result_html['html'] = $responseHtml;
					echo json_encode($result_html);
				} else {
				 $result_html['html'] = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
				 echo json_encode($result_html);
				}
    
?>

