<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
require_once('../googleCalender/settings.php');
require('../globals/global_tools.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@ini_set('post_max_size', '2000M');		
@ini_set('max_input_time', 1800);
@ini_set('upload_max_filesize', '1000M');
@ini_set('max_execution_time', 0);
@ini_set("memory_limit", "-1");
set_time_limit(0);
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

if($_POST['case_matter'] != "")
{
	if($_POST['updateCaseValue'] == "CaseData")
	{		
			$caseId = $_POST['case_id'];
			$updateSQL = "UPDATE docket_cases SET case_matter = '".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_matter'])."',calendar_id = '".mysqli_real_escape_string($docketDataSubscribe,$_POST['calendar_id'])."',case_jurisdiction ='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_jurisdiction'])."',case_location='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_location'])."',case_customtext='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."',caseReminderTime='".mysqli_real_escape_string($docketDataSubscribe,$_POST['reminder_minutes'])."',caseEventColor='".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventColor'])."',casedisplay='".mysqli_real_escape_string($docketDataSubscribe,$_POST['casedisplay'])."',modified_by='".mysqli_real_escape_string($docketDataSubscribe,$_SESSION['author_id'])."'
			WHERE case_id = ".$_POST['case_id']." ";

			mysqli_query($docketDataSubscribe,$updateSQL);
			
			$updateDocketCaseTextSQL = "UPDATE docket_customtext SET case_customtext='".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."' WHERE case_id = ".$_POST['case_id']." ";
			mysqli_query($docketDataSubscribe,$updateDocketCaseTextSQL);
			
			
			$query_case_users = "SELECT * FROM docket_cases_users dcu WHERE  dcu.case_id = ".$_POST['case_id']."";
			$caseUsers = mysqli_query($docketDataSubscribe,$query_case_users);
			$totalRows_users = mysqli_num_rows($caseUsers);
			
			$query_case_attendees = "SELECT * FROM docket_cases_attendees dca WHERE  dca.case_id = ".$_POST['case_id']."";
			$caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
			$totalRows_attendees = mysqli_num_rows($caseAttendee);
			
			
		$updateEventColorSQL = "UPDATE docket_cases SET caseEventColor='".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventColor'])."' WHERE case_id = ".$_POST['case_id']." ";
			mysqli_query($docketDataSubscribe,$updateEventColorSQL);
			
		   
			$users = array();
			 if(!empty($_POST['users']))
			{
				if($totalRows_users > 0)
				{
					$delete_case_users = "DELETE FROM docket_cases_users WHERE case_id = ".$_POST['case_id']." ";
				 mysqli_query($docketDataSubscribe,$delete_case_users);
				}
				foreach($_POST['users'] as $userval) {
				 $users[] = $userval;
				}
				array_push($users,$_SESSION['author_id']);
				array_unique($users);
			} else {
					$users[] = $_SESSION['author_id'];
			}
			
			  foreach($users as $user)
			{
				$insertSQL2 = "INSERT INTO docket_cases_users (case_id, user) VALUES (".$_POST['case_id'].",'".$user."')";
				mysqli_query($docketDataSubscribe,$insertSQL2);
			}
			
			if (!empty($_POST['attendee'])) {
			foreach($_POST['attendee'] as $val) {
			 $attendee[] = $val;
				}
				array_unique($attendee);	
			}
			$caseId = $_POST['case_id'];
			$eventColor =$_POST['eventColor'];
			$callToUpdateCasedetails = UpdateCaseDetails($caseId,$capi,$attendee,$eventColor);
			
		$responseHtml = "Successfully updated case";
		echo $responseHtml;
	}
		
	if($_POST['updateCaseValue'] == "AttendeeData")
	{
		$delete_case_attendees = "DELETE FROM docket_cases_attendees WHERE case_id = ".$_POST['case_id']." ";
		mysqli_query($docketDataSubscribe,$delete_case_attendees);
			$attendee = array();
			if (!empty($_POST['attendee'])) {
				foreach($_POST['attendee'] as $val) {
				 $attendee[] = $val;
				}
				array_unique($attendee);	
				
				
				 
					foreach($attendee as $attendeeVal)
					{
						$val = 1;
						 $insertSQL3 ="INSERT INTO docket_cases_attendees (case_id,caselevel,attendee) VALUES (".$_POST['case_id'].", ".$val.",'".$attendeeVal."')";
						mysqli_query($docketDataSubscribe,$insertSQL3);
					}
					$caseId = $_POST['case_id'];
					
					$eventColor =$_POST['eventColor'];
					

					$callToUpdateCasedetails = UpdateCaseDetails($caseId,$capi,$attendee,$eventColor);
			}
			
		$responseHtml = "Successfully Attendees updated";
		echo $responseHtml;	
	}	
			
				
	
	
	
}
 
	function cust_sort_multiple($a, $b) {
								return strtolower($a['CalendarRuleEvent']['EventDate']) < strtolower($b['CalendarRuleEvent']['EventDate']);
							}
	function cust_sort($a, $b) {
								return strtolower($a['CalendarRuleEvent']['EventDate']) > strtolower($b['CalendarRuleEvent']['EventDate']);
							}
							
	function UpdateCaseDetails($caseId,$capi,$attendee,$eventColor)
	{
		
		$docketData = $GLOBALS['docketData'];
		$database_docketData = $GLOBALS['database_docketData'];
		$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);
		
		$getAllImportDocketIdForCase = "SELECT import_docket_id,calendar_id FROM import_docket_calculator WHERE case_id = ".$caseId."";
		$dataAllImportDocketIdForCase = mysqli_query($docketDataSubscribe,$getAllImportDocketIdForCase);
		
		$countAllImportDocketIdForCase = mysqli_num_rows($dataAllImportDocketIdForCase);
		if($countAllImportDocketIdForCase > 0)
		{
			while($rowAllImportDocketIdForCase=mysqli_fetch_array($dataAllImportDocketIdForCase))
			{
				$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id  from  import_events WHERE import_docket_id = ".$rowAllImportDocketIdForCase['import_docket_id']."";
				$AllEventsIdForImportDocketIdData = mysqli_query($docketDataSubscribe,$getAllEventsIdForImportDocketId);
				while($rowEventsIdForImportDocketIdData = mysqli_fetch_assoc($AllEventsIdForImportDocketIdData))
				{
					 $getCaseEventsTableData = "SELECT short_name,remainder,location,status,event_date,description FROM case_events WHERE case_event_id = ".$rowEventsIdForImportDocketIdData['import_event_id']."";
					$getCaseEventsTableDataData = mysqli_query($docketDataSubscribe,$getCaseEventsTableData);
					$rowgetCaseEventsTableDataData = mysqli_fetch_assoc($getCaseEventsTableDataData);
					
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
					/*
					$data = $capi->UpdateCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],''.$rowAllImportDocketIdForCase['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendee,"Case Custom Comments : ".mysqli_real_escape_string($docketDataSubscribe,$_POST['case_customtext'])."<br/>".$description,$remainder,$eventColor,$location,$status);
					*/
					$queryUpdateEventsTableForSendStatus = "UPDATE events set update_case_status = 2 WHERE eventIdvalue =".$rowEventsIdForImportDocketIdData['event_id']."";
					$userUpdateEventsTableForSendStatus = mysqli_query($docketDataSubscribe,$queryUpdateEventsTableForSendStatus);
					
				}
				
			}
		}
		
		
	}	
 
 function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
    if (!$contents) {
        return array();
    }

    if (!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if (!$xml_values) {
        return;
    }
//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
    foreach ($xml_values as $data) {
        unset($attributes, $value); //Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data); //We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();

        if (isset($value)) {
            if ($priority == 'tag') {
                $result = $value;
            } else {
                $result['value'] = $value;
            }
            //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if (isset($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag') {
                    $attributes_data[$attr] = $val;
                } else {
                    $result['attr'][$attr] = $val;
                }
                //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if ($type == "open") {
//The starting of the tag '<tag>'
            $parent[$level - 1] = &$current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                //Insert New tag
                $current[$tag] = $result;
                if ($attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }

                $repeated_tag_index[$tag . '_' . $level] = 1;

                $current = &$current[$tag];

            } else {
                //There was another element with the same tag name

                if (isset($current[$tag][0])) {
//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag . '_' . $level] = 2;

                    if (isset($current[$tag . '_attr'])) {
                        //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset($current[$tag . '_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif ($type == "complete") {
            //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if (!isset($current[$tag])) {
                //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data) {
                    $current[$tag . '_attr'] = $attributes_data;
                }

            } else {
                //If taken, put all things inside a list(array)
                if (isset($current[$tag][0]) and is_array($current[$tag])) {
                    //If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;

                } else {
                    //If it is not an array...
                    $current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset($current[$tag . '_attr'])) {
                            //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }

                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }

        } elseif ($type == 'close') {
            //End of tag '</tag>'
            $current = &$parent[$level - 1];
        }
    }

    return ($xml_array);
}
?>



