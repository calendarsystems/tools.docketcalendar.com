<?php 
require('../globals/global_tools.php');
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/settings.php');
require_once('../googleCalender/google-calendar-api.php');
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

		$caseId = $_POST['caseId'];
		$importDocketId = $_POST['importDocketId'];
		$result_html = array();
		$responseHtml = "";
		$updateDocketData =  "UPDATE import_docket_calculator SET modified_by = '".$_SESSION['author_id']."' where import_docket_id = ".$importDocketId."";
		$updateDocketResult = mysqli_query($docketDataSubscribe,$updateDocketData);
		
		
		
		$selectCaseImportId =  "SELECT jurisdiction,trigger_item FROM import_docket_calculator where import_docket_id = ".$importDocketId."";
		$caseImportData = mysqli_query($docketDataSubscribe,$selectCaseImportId);
		$rowcaseImportData = mysqli_fetch_array($caseImportData);
		$jurisid = $rowcaseImportData['jurisdiction'];
		$triggerid = $rowcaseImportData['trigger_item'];
		
		if($caseId)
		{
			
			$insertTriggerCustomText="INSERT INTO docket_customtext(user_id,case_id,trigger_customtext,trigger_juri,trigger_trigid,trigger_customtextlevel) VALUES(".$_SESSION['userid'].",".$caseId.",'".mysqli_real_escape_string($docketDataSubscribe,$_POST['triggerCustomText'])."','".$jurisid."','".$triggerid."',1)";
	
			$updateTriggerCustomTextResult = mysqli_query($docketDataSubscribe,$insertTriggerCustomText);	
			
			
			$updateTriggerModifiedBy =  "UPDATE  docket_case_triggerevent_mod SET trigger_modified_by = 
			'".$_SESSION['author_id']."',trigger_id='".
			$triggerid."' WHERE case_id = ".$caseId." AND user_id=".
			$_SESSION['userid']."";
	
			$updateTriggerModifiedDetails = mysqli_query($docketDataSubscribe,$updateTriggerModifiedBy);
	
			$selectCaseAttendees =  "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$caseId." and triggerlevel = 1 and jurisid = ".$jurisid." and triggerid=".$triggerid."";
			$caseAttendee = mysqli_query($docketDataSubscribe,$selectCaseAttendees);
			$totalRows_attendees = mysqli_num_rows($caseAttendee);
			
			if($totalRows_attendees > 0)
			{
			  $delete_case_attendees = "DELETE FROM docket_cases_attendees WHERE case_id = ".$caseId." and triggerlevel = 1 and jurisid = ".$jurisid." and triggerid=".$triggerid."";
			  mysqli_query($docketDataSubscribe,$delete_case_attendees);
			}
			if(!empty($_POST['attendees']))
			{
				$attendee = $_POST['attendees'];
				
				if(count($attendee) > 0)
				{
					array_push($attendee);
					array_unique($attendee);
				} 
				
				foreach($attendee as $attendeeVal)
				{
					
					$insertAttendees = sprintf("INSERT INTO docket_cases_attendees (case_id, attendee,jurisid,triggerid,triggerlevel) VALUES (%s, %s, %s,%s,%s)",
					GetSQLValueString($caseId, "text"),
					GetSQLValueString($attendeeVal, "text"),
					GetSQLValueString($jurisid, "int"),
					GetSQLValueString($triggerid, "int"),
					GetSQLValueString(1, "int"));
					mysqli_query($docketDataSubscribe,$insertAttendees);
					
				}
			}
			
					
					
			$capi = new GoogleCalendarApi();
			// Get user calendar timezone
			$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);
			
			$queryCaseInfo = "SELECT caseEventColor,user_id FROM docket_cases WHERE case_id = '".$caseId."' ";
			$caseInfo = mysqli_query($docketDataSubscribe,$queryCaseInfo);
			$totalRows_caseInfo = mysqli_num_rows($caseInfo);
			$rowCaseInfo = mysqli_fetch_assoc($caseInfo);
			if($totalRows_caseInfo > 0)
			{
				$eventColor =$rowCaseInfo['caseEventColor'];
			}else{
				$queryUserToolInfo="SELECT eventColor from users_tool_option WHERE user_id = ".$rowCaseInfo['user_id']."";
				$eventInfo = mysqli_query($docketDataSubscribe,$queryUserToolInfo);
				$rowEventInfo = mysqli_fetch_assoc($eventInfo);
				$eventColor =$rowEventInfo['eventColor'];
			}
	
			$getAllImportDocketIdForCase = "SELECT calendar_id FROM import_docket_calculator WHERE case_id = ".$caseId." AND import_docket_id = ".$importDocketId."";
			$dataAllImportDocketIdForCase = mysqli_query($docketDataSubscribe,$getAllImportDocketIdForCase);
			$countAllImportDocketIdForCase = mysqli_num_rows($dataAllImportDocketIdForCase);
			if($countAllImportDocketIdForCase > 0)
			{
				while($rowAllImportDocketIdForCase=mysqli_fetch_array($dataAllImportDocketIdForCase))
				{
					$getAllEventsIdForImportDocketId="SELECT event_id,import_event_id  from  import_events WHERE import_docket_id = ".$importDocketId."";
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
						$eventExplodeTime = str_replace(":", "", $eventTime);
						$start_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) );
						$end_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
						$event_date = "";
						$update_event_date = $expTime[0].' '.date('H:i:s', strtotime($eventExplodeTime) );
						$all_day = 0;
								
						$date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" =>$rowgetCaseEventsTableDataData['event_date']);
						
						$data = $capi->UpdateCalendarEvent($rowEventsIdForImportDocketIdData['event_id'],''.$rowAllImportDocketIdForCase['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendee,"Trigger Custom Comments : ".mysqli_real_escape_string($docketDataSubscribe,$_POST['triggerCustomText'])."<br/>".$description,$remainder,$eventColor,$location,$status);
						
					}
					
				}
			}
					
					$responseHtml = "<span style='color:green;'>Successfully Attendees updated.</span>";
					$result_html['html'] = $responseHtml;
			
		}
		else {
					 $result_html['html'] = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
					 
			}

			
				
				echo json_encode($result_html);
		/**/
			
		

		?>
		
		<?php

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

