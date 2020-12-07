<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');

session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

require('../globals/global_tools.php');

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


$result_html = array();


$colname_userInfo = "-1";
if (isset($_SESSION['userid']))
{
  $colname_userInfo = $_SESSION['userid'];
}

if (isset($_SESSION['userid'])) {
$query_searchInfo = "SELECT * FROM import_docket_calculator WHERE import_docket_id = '".$_POST['docket_search_id']."' ";
$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
$row_searchInfo = mysqli_fetch_assoc($searchInfo);
$totalRows_searchInfo = mysqli_num_rows($searchInfo);

$query_userInfo = sprintf("SELECT * FROM attorneys WHERE user_id = %s", GetSQLValueString($docketData,$colname_userInfo, "int"));
$userInfo = mysqli_query($docketData,$query_userInfo);
$row_userInfo = mysqli_fetch_assoc($userInfo);
$totalRows_userInfo = mysqli_num_rows($userInfo);

$newURL = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

$selectJurisdiction = 0;
$selectTriggerItem = 0;
$selectServiceType = 0;

$masterCourt = $row_searchInfo['jurisdiction'];


if (isset($_SESSION['userid'])) {
    $command = "/users/" . $_SESSION['username'] . "?";
    $parameters = "password=" . $_SESSION['password'] . "&soapREST=REST";
    $file = $newURL . $command . $parameters;

    $content = file_get_contents($newURL . $command . $parameters, false, $context);
    $xml = $content;
    $array = xml2array($xml);

    $loginToken = $array['string'];

}

if (isset($row_searchInfo['jurisdiction'])) {
    $selectJurisdiction = $row_searchInfo['jurisdiction'];
}
if (isset($row_searchInfo['trigger_item'])) {
    $selectTriggerItem = $row_searchInfo['trigger_item'];
}
if (isset($row_searchInfo['isTimeRequired'])) {
    $isTimeRequired = $row_searchInfo['isTimeRequired'];
}
if (isset($row_searchInfo['service_type'])) {
    $selectServiceType = $row_searchInfo['service_type'];
}
if (isset($row_searchInfo['isServed'])) {
    $isServed = $row_searchInfo['isServed'];
}
$events_array = '';
$sort = $row_searchInfo['sort_date'];
if($row_searchInfo['events'] != '')
{
  $events_array = unserialize($row_searchInfo['events']);
}
$result_html = array();
$responseHtml = "";
if ($selectJurisdiction != 0) {
//    echo "juris good";
    if ($selectTriggerItem != 0) {
        //    echo "trigger good";
        if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
            //    echo "service good";
            if (($isTimeRequired == "Y" && $row_searchInfo['trigger_time'] != "") || $isTimeRequired != "Y") {

                        if($totalRows_searchInfo > 0)
                        {
                            if($row_searchInfo['status'] == 2)
                            {
                                $query_importInfo = "SELECT * FROM import_events WHERE import_docket_id = '".$_POST['docket_search_id']."' ";
                                $importInfo = mysqli_query($docketDataSubscribe,$query_importInfo);
                                $totalRows_importInfo = mysqli_num_rows($importInfo);

                                if($totalRows_importInfo > 0)
                                {
                                    while($row_importInfo = mysqli_fetch_array($importInfo))
                                    {
                                        $data = $capi->DeleteCalendarEvent($row_importInfo['event_id'],$row_searchInfo['calendar_id'],$row_searchInfo['access_token']);
                                    }
                                    $query_deleteInfo = "DELETE FROM import_events WHERE import_docket_id = '".$_POST['docket_search_id']."' ";
                                    mysqli_query($docketDataSubscribe,$query_deleteInfo);
                                }
                            }

                       }
                 
                $formDate = $row_searchInfo['trigger_date'];
                $triggerDate = substr($formDate, 6, 4) . "-" . substr($formDate, 0, 2) . "-" . substr($formDate, 3, 2);

                $xml = '<CalculationParameters xmlns="http://schemas.datacontract.org/2004/07/CRC.WCFService.Objects">
                    <Associations/>
                    <EventSystemID>0</EventSystemID>
                    <Events/>
                    <JurisdictionSystemID>' . $selectJurisdiction . '</JurisdictionSystemID>
                    <ServiceTypeSystemID>' . $selectServiceType . '</ServiceTypeSystemID>
                    <TriggerDate>' . $triggerDate . 'T' . $row_searchInfo['trigger_time'] . '</TriggerDate>
                    <TriggerItemSystemID>' . $selectTriggerItem . '</TriggerItemSystemID>
                    <Twins/>
                    </CalculationParameters>';

                //echo $xml;

                $uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
                $url = $uri . "compute/dates?loginToken=" . $loginToken;
                //$url=$uri."compute/dates";
                $session = curl_init($url);

                curl_setopt($session, CURLOPT_POST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
                curl_setopt($session, CURLOPT_HEADER, true);
                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
                //    curl_setopt ($session, CURLOPT_HTTPHEADER, array('Expect:'));

                $response = curl_exec($session);
                //echo "<pre>";
                //print_r($response);
                curl_close($session);

                $start = strpos($response, "<CalculationResult");
                $end = strlen($response);
                $length = $end - $strart;
                $xmlblock = substr($response, $start, $length);

                $response = xml2array($xmlblock);
                $response = $response['CalculationResult']['CompoundEvents']['CompoundEvent'];

                if (!isset($response['Action'])) {
                    $z = usort($response, 'sort_by_date');
                }

                $debuginfo = array('url' => $url, 'xml' => $xml, 'response' => $response);

                if ($row_searchInfo['matter'] != "") {
                    $matterString = "(" . $row_searchInfo['matter'] . ") ";
                } else {
                    $matterString = "";
                }


                // Get user calendar timezone
                $user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);


                // single event
         if (isset($response['Action'])) {

                $date = str_replace("-", "", $response['CalendarRuleEvent']['EventDate']);
                $expTime = explode("T", $date);
                $date = str_replace(":", "", $date);

                $eventTime = $expTime[1];
                $eventExplodeTime = str_replace(":", "", $eventTime);

                $summary = $matterString . $response['CalendarRuleEvent']['ShortName'];

                //single date rules
                if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
                    $dateRuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText']);
                    $dateRuleText = str_replace("\r", " ", $dateRuleText);
                    // mutliple date rules
                } else {
                    $dateRuleAll = "";
                    foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                        $dateRuleText = str_replace("\n", " ", $Rule['RuleText']);
                        $dateRuleText = str_replace("\r", " ", $dateRuleText);

                        $dateRuleAll = $dateRuleAll . $dateRuleText . ";";
                    }
                    $dateRuleText = substr($dateRuleAll, 0, strlen($dateRuleAll - 1));
                }
				if($response['CalendarRuleEvent']['EventType']['Description'])
				{
					 $eventTypeDesc = $response['CalendarRuleEvent']['EventType']['Description'];
				}
                // single court rule
                if (isset($response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
                    $courtRuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText']);
                    $courtRuleText = str_replace("\r", " ", $courtRuleText);
                    // multiple court rules
                } else {
                    $courtRuleAll = "";
                    foreach ($response['CalendarRuleEvent']['CourtRules']['Rule'] as $Rule) {
                        $courtRuleText = str_replace("\n", " ", $Rule['RuleText']);
                        $courtRuleText = str_replace("\r", " ", $courtRuleText);

                        $courtRuleAll = $courtRuleAll . $courtRuleText . ";";
                    }
                    $courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
                }

                        if ($eventTime == "00:00:00") {
                            $start_time = "";
                            $end_time =  "";
                            $event_date = date('Y-m-d', strtotime($date) );
                            $all_day = 1;
                        } else {

                            $start_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) );
                            $end_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
                            $event_date = "";
                            $all_day = 0;
                        }
                        $date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);

                        if($_POST['attendees'] != '')
                        {
                          //$attendees = explode(",",$_POST['attendees']);
                          $attendees = $_POST['attendees'];
                        } else {
                          $attendees = "";
                        }

                        $eventDocket = $response['CalendarRuleEvent']['IsEventDocket'];
                        $eventParentSystemID = $response['CalendarRuleEvent']['ParentSystemID'];
                        $eventSystemID = $response['CalendarRuleEvent']['SystemID'];

                        $event_id = $capi->CreateCalendarEvent(''.$_POST['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'], $attendees);

                        $insertSQL = "INSERT INTO import_events(import_docket_id, event_id, event_docket, parent_system_id, system_id)  VALUES (".$_SESSION['docket_search_id'].",'".$event_id."','".$eventDocket."','".$eventParentSystemID."','".$eventSystemID."')";
                        $result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
                        $import_event_id = mysqli_insert_id($docketDataSubscribe);

                        $shortName = mysqli_real_escape_string($docketDataSubscribe,$response'CalendarRuleEvent']['ShortName']);

            $insertSQLCases = "INSERT INTO case_events(import_event_id, event_date, short_name,eventtype) VALUES ('".$import_event_id."','".$date."','".$shortName."','".$eventTypeDesc."')";
            $resultCases = mysqli_query($docketDataSubscribe,$insertSQLCases) or trigger_error("Query Failed! SQL: $insertSQLCases - Error: ".mysqli_error(), E_USER_ERROR);
                        // multiple events
                    } else {
                       $icalevents = "";
                       $eve = 1;
                        foreach ($response as $Event) {
                          if(in_array($eve,$events_array))
                          {
                            $date = str_replace("-", "", $Event['CalendarRuleEvent']['EventDate']);
                            $expTime = explode("T", $date);
                            $date = str_replace(":", "", $date);

                            $eventTime = $expTime[1];
                            $eventExplodeTime = str_replace(":", "", $eventTime);

                            $summary = $matterString . $headerinfo . $Event['CalendarRuleEvent']['ShortName'];

                            //single date rules
                            if (isset($Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
                                $dateRuleText = str_replace("\n", " ", $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText']);
                                $dateRuleText = str_replace("\r", " ", $dateRuleText);
                                // mutliple date rules
                            } else {
                                $dateRuleAll = "";
                                foreach ($Event['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                                    $dateRuleText = str_replace("\n", " ", $Rule['RuleText']);
                                    $dateRuleText = str_replace("\r", " ", $dateRuleText);

                                    $dateRuleAll = $dateRuleAll . $dateRuleText . ";";
                                }
                                $dateRuleText = substr($dateRuleAll, 0, strlen($dateRuleAll - 1));
                            }
								if($Event['CalendarRuleEvent']['EventType']['Description'])
								{
								 $EventType = $Event['CalendarRuleEvent']['EventType']['Description'];
								}
                            // single court rule
                            if (isset($Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
                                $courtRuleText = str_replace("\n", " ", $Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleText']);
                                $courtRuleText = str_replace("\r", " ", $courtRuleText);
                                // multiple court rules
                            } else {
                                $courtRuleAll = "";
                                foreach ($Event['CalendarRuleEvent']['CourtRules']['Rule'] as $Rule) {
                                    $courtRuleText = str_replace("\n", " ", $Rule['RuleText']);
                                    $courtRuleText = str_replace("\r", " ", $courtRuleText);

                                    $courtRuleAll = $courtRuleAll . $courtRuleText . ";";
                                }
                                $courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
                            }

                           if ($eventTime == "00:00:00") {

                            $start_time = "";
                            $end_time =  "";
                            $event_date = date('Y-m-d', strtotime($date) );
                            $all_day = 1;

                           } else {

                            $start_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) );
                            $end_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
                            $event_date = "";
                            $all_day = 0;
                        }

                        $date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);
                        if($_POST['attendees'] != '')
                        {
                          //$attendees = explode(",",$_POST['attendees']);
                          $attendees = $_POST['attendees'];
                        } else {
                          $attendees = "";
                        }
                            $eventDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
                            $eventParentSystemID = $Event['CalendarRuleEvent']['ParentSystemID'];
                            $eventSystemID = $Event['CalendarRuleEvent']['SystemID'];

                            $event_id = $capi->CreateCalendarEvent(''.$_POST['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees);

                            $insertSQL = "INSERT INTO import_events(import_docket_id, event_id, event_docket, parent_system_id, system_id)  VALUES (".$_SESSION['docket_search_id'].",'".$event_id."','".$eventDocket."','".$eventParentSystemID."','".$eventSystemID."')";
                            $result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
                            $import_event_id = mysqli_insert_id($docketDataSubscribe);

                            $shortName = mysqli_real_escape_string($docketDataSubscribe,$Event['CalendarRuleEvent']['ShortName']);

            $insertSQLCases = "INSERT INTO case_events(import_event_id, event_date, short_name,eventtype) VALUES ('".$import_event_id."','".$date."','".$shortName."','".$EventType."')";
            $resultCases = mysqli_query($docketDataSubscribe,$insertSQLCases) or trigger_error("Query Failed! SQL: $insertSQLCases - Error: ".mysqli_error(), E_USER_ERROR);
                        }
                             $eve++;
                        } //for loop
                    }
                $responseHtml = "<span style='color:green;'>Successfully imported to your Google Calendar.</span>";
                if(isset($_SESSION['docket_search_id'])) {
                    $updateSQL = "UPDATE import_docket_calculator SET calendar_id = '".$_POST['calendar_id']."', attendees = '".implode(",",$_POST['attendees'])."', status = 2
                       WHERE import_docket_id  = ".$_SESSION['docket_search_id']." ";
                       $result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
                }
            } else {
                $responseHtml = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
            }
        }
    }
}

$result_html['html'] = $responseHtml;
echo json_encode($result_html);

} else {
 $result_html['html'] = "<span style='color:red;'>Error Occured. Please Login Once Again.</a>";
 echo json_encode($result_html);
}
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

