<?php 
require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
require_once('Connections/docketDataSubscribe.php');
session_start();
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$database_docketData = $GLOBALS['database_docketData'];

//ini_set('display_errors',1);
//error_reporting(E_ALL);
    if (!isset($_SESSION['userid'])) {
      echo "<script>alert('Your browser session has expired, please login into Site.');window.location.href='/login';</script>";
    }

    if(!isset($_SESSION['access_token']))
    {
      echo "<script>window.location.href='https://tools.docketcalendar.com/docket-calculator';</script>";
    }

    $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
    //echo "s=".$_SESSION['access_token'];
    if(isset($_SESSION['access_token'])) {

        try {
            global $calendarData;
            $capi = new GoogleCalendarApi();

            // Get the access token
            $calendarData = $capi->GetCalendarsList($_SESSION['access_token']);
			
        }
        catch(Exception $e) {
            unset($_SESSION['access_token']);
            echo "<script>alert('Your browser session has expired, please login into Google.');window.location.href='".$login_url."';</script>";
        }
    }

    if(isset($_SESSION['docket_search_id'])) {
      $updateSQL = "UPDATE import_docket_calculator SET status = 1, access_token = '".$_SESSION['access_token']."' WHERE import_docket_id = ".$_SESSION['docket_search_id']." ";
      $result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);

        global $attendee;
        $query_case_attendees = "SELECT da.attendee FROM import_docket_calculator ic INNER JOIN docket_cases dc  ON ic.case_id = dc.case_id  INNER JOIN docket_cases_attendees da  ON ic.case_id = da.case_id WHERE import_docket_id = ".$_SESSION['docket_search_id']."  AND dc.created_by = '".$_SESSION['author_id']."' and da.caselevel = 1";
        $caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
        $totalRows_attendees = mysqli_num_rows($caseAttendee);

        $attendee = array();
        if($totalRows_attendees > 0)
        {
             while($row_attendees = mysqli_fetch_assoc($caseAttendee))
            {
                 $attendeeArr[] = $row_attendees['attendee'];
            }
        }
        $attendee=$attendeeArr;

        $query_searchInfo = "SELECT * FROM import_docket_calculator WHERE import_docket_id = '".$_SESSION['docket_search_id']."' ";
        $searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
        $row_searchInfo = mysqli_fetch_assoc($searchInfo);
        $totalRows_searchInfo = mysqli_num_rows($searchInfo);
		
		$queryGetArchiveInfo = "SELECT * FROM docket_cases_archive WHERE caseid = '".$row_searchInfo['case_id']."' AND triggerid = '".$row_searchInfo['trigger_item']."' ";
        $searchInfoGetArchiveInfo = mysqli_query($docketDataSubscribe,$queryGetArchiveInfo);
        $row_searchInfoGetArchiveInfo = mysqli_fetch_assoc($searchInfoGetArchiveInfo);
        $totalRows_searchInfoGetArchiveInfo = mysqli_num_rows($searchInfoGetArchiveInfo);

        $query_caseInfo = "SELECT * FROM docket_cases WHERE case_id = '".$row_searchInfo['case_id']."' ";
        $caseInfo = mysqli_query($docketDataSubscribe,$query_caseInfo);
        $totalRows_caseInfo = mysqli_num_rows($caseInfo);
        $row_caseInfo = mysqli_fetch_assoc($caseInfo);

        $query_eventsInfo = "SELECT ce.event_date,ie.system_id FROM import_docket_calculator as idc
        INNER JOIN import_events as ie ON ie.import_docket_id = idc.import_docket_id
        INNER JOIN case_events as ce ON ce.import_event_id = ie.import_event_id
         WHERE idc.case_id = '".$row_searchInfo['case_id']."' ";
        $searchEventsInfo = mysqli_query($docketDataSubscribe,$query_eventsInfo);

        $totalRows_searchEventsInfo = mysqli_num_rows($searchEventsInfo);
        global $existEvents;
        $existEvents = array();
        if($totalRows_searchEventsInfo > 0)
        {
             while($row_searchEventsInfo = mysqli_fetch_assoc($searchEventsInfo))
             {
                $eventDate = date("Y-m-d",strtotime($row_searchEventsInfo['event_date']));
                $eventSystemID = $row_searchEventsInfo['system_id'];
				if( $totalRows_searchInfoGetArchiveInfo == 1)
				{
					$existEvents[$eventSystemID] = 0;
				}else{
					$existEvents[$eventSystemID] = $eventDate;
				}
                
             }
        }
        // echo "<pre>"; print_r($existEvents);
		global $case_name;
		global $case_id;
        global $dbCalendarId;
		global $triggerName;
		$case_id =$row_caseInfo['case_id'];
        $case_name = $row_caseInfo['case_matter'];
        $dbCalendarId = $row_caseInfo['calendar_id'];
		$triggerName= $row_searchInfo['triggerItem'];

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

        global $events_array;

        $events_array = '';
        $sort = $row_searchInfo['sort_date'];

        if($row_searchInfo['events'] != '')
        {
          $events_array = unserialize($row_searchInfo['events']);
        }

        $result_html = array();
        $responseHtml = "";

        global $response;

if ($selectJurisdiction != 0) {
//    echo "juris good";
    if ($selectTriggerItem != 0) {
        //    echo "trigger good";
        if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
            //    echo "service good";
            if (($isTimeRequired == "Y" && $row_searchInfo['trigger_time'] != "") || $isTimeRequired != "Y") {

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

                if (!isset($response[Action])) {
                    $z = usort($response, 'sort_by_date');
                }

                $debuginfo = array('url' => $url, 'xml' => $xml, 'response' => $response);

                if ($row_searchInfo['matter'] != "") {
                    $matterString = "(" . $row_searchInfo['matter'] . ") ";
                } else {
                    $matterString = "";
                }


            }
        }
    }
}

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
<script src="jquery/js/jquery-1.8.3.js"></script>
<script language="javascript">
jQuery(document).ready(function()
{
	
	if (jQuery("#caseLevelImporteventColor").val())
		{
		
			var colorValueId =  jQuery("#caseLevelImporteventColor").val();
			if(colorValueId != '0')
			{
				var PresentcolorValue = getColorCode(colorValueId);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
		}
		jQuery('#caseLevelImporteventColor').on('change', function() {
			var colorValue =  this.value ;
			if(colorValue != '0')
			{
				var PresentcolorValue = getColorCode(colorValue);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
			else
			{
				jQuery('#colorIdentifier').css('background-color','#ffffff');
			};
		});
}); function getColorCode(ID)
		{
			var colorCode="";
			switch (ID) {
				case '1':
					colorCode = "#7986cb";
					break;
				case '2':
					colorCode = "#33b679";
					break;
				case '3':
					colorCode = "#8e24aa";
					break;
				case '4':
					colorCode = "#e67c73";
					break;
				case '5':
					colorCode = "#f6bf26";
					break;
				case '6':
					colorCode = "#F4511E";
					break;
				case '7':
					colorCode = "#039be5";
					break;
				case '8':
					colorCode = "#616161";
					break;
				case '9':
					colorCode = "#3f51b5";
					break;
				case '10':
					colorCode = "#0b8043";
					break;	
                case '11':
					colorCode = "#D50000";
					break;
				default:
					  return "default";
					break;					
			}
			return colorCode;
			
		}
</script>