<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
@ini_set('post_max_size', '4000M');		
@ini_set('max_input_time', 3600);
@ini_set('upload_max_filesize', '2000M');
@ini_set('max_execution_time', 0);
@ini_set("memory_limit", "-1");
@ini_set("max_input_vars",2000);
set_time_limit(0);
 ?>

<?php
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

    if(isset($_POST['case_event_id']))
    {
        $case_event_id = $_POST['case_event_id'];
    }

    if(isset($_POST['event_date']))
    {
        $event_date = $_POST['event_date'];
    }
		
    $capi = new GoogleCalendarApi();
    $result_html = array();

$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
$totalRows_authoptionInfo = mysqli_num_rows($authOptionInfo);
$row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo);

$add_court_rule_body = $row_authOptionInfo['add_court_rule_body'];
$add_date_rule_body = $row_authOptionInfo['add_date_rule_body'];
$case_name_location = $row_authOptionInfo['case_name_location'];
$custom_text_location = $row_authOptionInfo['custom_text_location'];

$recalculated_events = $row_authOptionInfo['recalculated_events'];
$do_not_recalculate_events = $row_authOptionInfo['do_not_recalculate_events'];

    if( ($_POST['caselab'] == "eventdocket" || $_POST['caselab'] == "parent"))
    {
        echo $query_searchInfo = "SELECT idc.*, ie.system_id,idc.case_id, ie.import_event_id as import_event_id, ie.system_id as system_id, ie.event_id as event_id,ie.recalculate_flag FROM case_events as ce
        inner join import_events as ie ON ie.import_event_id = ce.import_event_id
        inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
        WHERE ce.case_event_id = ".$case_event_id."";
        $searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
        $row_searchInfo = mysqli_fetch_assoc($searchInfo);
        $totalRows_searchInfo = mysqli_num_rows($searchInfo);
		
        echo $case_id = $row_searchInfo['case_id'];
		echo $caseeventid= $row_searchInfo['event_id'];
        $case_system = array();
        $parent_system = array();
        $has_child = 0;
		
        if($_POST['caselab'] == "eventdocket")
        {

             $query_caseInfo = "SELECT ie.system_id as system_id, ie.event_id as event_id
             FROM case_events as ce
             inner join import_events as ie ON ie.import_event_id = ce.import_event_id
             inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
             WHERE idc.case_id = '".$case_id."' ";
             $caseInfo = mysqli_query($docketDataSubscribe,$query_caseInfo);

             $totalRows_caseInfo = mysqli_num_rows($caseInfo);

             if($totalRows_caseInfo > 0)
             {
                 while($row_casseInfo = mysqli_fetch_assoc($caseInfo))
                 {
                   $case_system[] = $row_casseInfo['system_id'];
                 }
             }
        }

        if($_POST['caselab'] == "parent")
        {
             echo $query_parentInfo = "SELECT ie.system_id as system_id, ie.event_id as event_id
             FROM case_events as ce
             inner join import_events as ie ON ie.import_event_id = ce.import_event_id
             inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
             WHERE ie.parent_system_id = '".$row_searchInfo['system_id']."' AND idc.case_id = '".$case_id."' ";
             $parentInfo = mysqli_query($docketDataSubscribe,$query_parentInfo);

             $totalRows_parentInfo = mysqli_num_rows($parentInfo);

             if($totalRows_parentInfo > 0)
             {
                 $has_child = 1;
                 while($row_parentInfo = mysqli_fetch_assoc($parentInfo))
                 {
                   $parent_system[] = $row_parentInfo['system_id'];
                 }
             }

        }
		
		$query_MaxCaseEventId = "SELECT max(case_event_id) as MaxId FROM case_events";
		$maxIdResult = mysqli_query($docketDataSubscribe,$query_MaxCaseEventId);
		$row_maxIdResult = mysqli_fetch_assoc($maxIdResult);
		$maxCaseEventId = $row_maxIdResult['MaxId'];
       // "<pre>"; print_r($parent_system);
       //exit();

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
		//echo "juris good";
			if ($selectTriggerItem != 0) {
					//echo "trigger good";
				if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
						//echo "service good";
					if (($isTimeRequired == "Y" && $row_searchInfo['trigger_time'] != "") || $isTimeRequired != "Y") {

		$explode_event_date = explode(" ",$event_date);
		$formDate = $explode_event_date[0];
		$trigTime = explode(":",$explode_event_date[1]);

		$triggerDate = $formDate;

		$xml = '<CalculationParameters xmlns="http://schemas.datacontract.org/2004/07/CRC.WCFService.Objects">
			<Associations/>
			<EventSystemID>0</EventSystemID>
			<Events/>
			<JurisdictionSystemID>' . $selectJurisdiction . '</JurisdictionSystemID>
			<ServiceTypeSystemID>' . $selectServiceType . '</ServiceTypeSystemID>
			<TriggerDate>' . $formDate . 'T' . $explode_event_date[1] . '</TriggerDate>
			<TriggerItemSystemID>' . $selectTriggerItem . '</TriggerItemSystemID>
			<Twins/>
			</CalculationParameters>';
		//echo $xml;exit();
		$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
		$url = $uri . "compute/dates?loginToken=" . $loginToken;
		$session = curl_init($url);

		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($session, CURLOPT_HEADER, true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		$response = curl_exec($session);
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

		$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);

		if (isset($response[Action])) 
		{

			$date = str_replace("-", "", $response[CalendarRuleEvent][EventDate]);
			$expTime = explode("T", $date);
			$date = str_replace(":", "", $date);

			$eventTime = $expTime[1];
			$eventExplodeTime = str_replace(":", "", $eventTime);

			$summary = $matterString . $response[CalendarRuleEvent][ShortName];
			$eventName=$response[CalendarRuleEvent][ShortName];
			//single date rules
			if (isset($response[CalendarRuleEvent][DateRules][Rule][RuleText])) {
				$dateRuleText = str_replace("\n", " ", $response[CalendarRuleEvent][DateRules][Rule][RuleText]);
				$dateRuleText = str_replace("\r", " ", $dateRuleText);
				// mutliple date rules
			} else {
				$dateRuleAll = "";
				foreach ($response[CalendarRuleEvent][DateRules][Rule] as $Rule) {
					$dateRuleText = str_replace("\n", " ", $Rule[RuleText]);
					$dateRuleText = str_replace("\r", " ", $dateRuleText);

					$dateRuleAll = $dateRuleAll . $dateRuleText . ";";
				}
				$dateRuleText = substr($dateRuleAll, 0, strlen($dateRuleAll - 1));
			}
			if($response[CalendarRuleEvent][EventType][Description])
			{
				 $eventTypeDesc = $response[CalendarRuleEvent][EventType][Description];
			}
			// single court rule
			if (isset($response[CalendarRuleEvent][CourtRules][Rule][RuleText])) {
				$courtRuleText = str_replace("\n", " ", $response[CalendarRuleEvent][CourtRules][Rule][RuleText]);
				$courtRuleText = str_replace("\r", " ", $courtRuleText);
				$courtRuleId = $response[CalendarRuleEvent][CourtRules][Rule][RuleID];
				// multiple court rules
			} else {
				$courtRuleAll = "";
				foreach ($response[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
					$courtRuleText = str_replace("\n", " ", $Rule[RuleText]);
					$courtRuleText = str_replace("\r", " ", $courtRuleText);

					$courtRuleAll = $courtRuleAll . $courtRuleText . ";";
					$courtRuleId .= $Rule[RuleID] . " - ";
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
					  $attendees = $_POST['attendees'];
					} else {
					  $attendees = $row_searchInfo['attendees'];
					}

			$eventDocket = $response[CalendarRuleEvent][IsEventDocket];
			$eventRecalc = $response[CalendarRuleEvent][DoNotRecaltulateFlag];
			$eventParentSystemID = $response[CalendarRuleEvent][ParentSystemID];
			$eventSystemID = $response[CalendarRuleEvent][SystemID];


			if($add_court_rule_body == "Rule Text")
			{
			  $court_rule = $courtRuleText;
			} else if($add_court_rule_body == "Rule ID only")
			{
			  $court_rule = $courtRuleId;
			} else if($add_court_rule_body == "Don't add")
			{
			  $court_rule = '';
			}

			if($add_date_rule_body == "yes")
			{
			  $date_rule = $dateRuleText;
			} else if($add_date_rule_body == "no")
			{
			  $date_rule = '';
			}

			$case_name_append_before = '';
			$case_name_append_after = '';
			$case_name_summary_before = '';
			$case_name_summary_after = '';

			if($case_name_location == "prepend to subject")
			{
			  $case_name_summary_before = $row_caseInfo['case_matter'];
			} else if($case_name_location == "append to subject")
			{
			  $case_name_summary_after = $row_caseInfo['case_matter'];
			}
			else if($case_name_location == "prepand to body")
			{
			  $case_name_append_before = $row_caseInfo['case_matter'];
			} else if($case_name_location == "append to body")
			{
			  $case_name_append_after = $row_caseInfo['case_matter'];
			} else if($case_name_location == "don't add")
			{
				$case_name_append_before = '';
				$case_name_append_after = '';
				$case_name_summary_before = '';
				$case_name_summary_after = '';
			}

			$custom_text_append_before = '';
			$custom_text_append_after = '';
			$custom_text_summary_before = '';
			$custom_text_summary_after = '';

			if($custom_text_location == "prepend to subject")
			{
			  $custom_text_summary_before = $row_searchInfo['custom_text'];
			} else if($custom_text_location == "append to subject")
			{
			  $custom_text_summary_after = $row_searchInfo['custom_text'];
			}
			else if($custom_text_location == "prepand to body")
			{
			  $custom_text_append_before = $row_searchInfo['custom_text'];
			} else if($custom_text_location == "append to body")
			{
			  $custom_text_append_after = $row_searchInfo['custom_text'];
			} else if($custom_text_location == "don't add")
			{
				$custom_text_append_before = '';
				$custom_text_append_after = '';
				$custom_text_summary_before = '';
				$custom_text_summary_after = '';
			}
			if($row_authOptionInfo['calendar_rules_events_tag'] == 'Yes')
			{
			  $calendar_rules_events_tag = "CalendarRulesEvent";
			} else {
			  $calendar_rules_events_tag = '';
			}
			if($row_authOptionInfo['show_trigger'] == 'yes')
			{
			  $triggerItemCalc = $row_searchInfo['triggerItem'];
			} else {
			  $triggerItemCalc = '';
			}
			$JuriURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
			$juricommand="/jurisdictions/my?";
			$juriparameters="loginToken=$loginToken";
			$jurifile= $JuriURL.$juricommand.$juriparameters;
			$juricontent=file_get_contents($jurifile,false,$context);
			$jurixml=$juricontent;
			$juriarray=xml2array($jurixml);
			$TotalJurisdictionsList = $juriarray['ArrayOfJurisdiction']['Jurisdiction'];
			
			$query_MaxImportDocketId = "SELECT max(import_docket_id) as MaxImportId FROM  import_docket_calculator";
			$maxImportIdResult = mysqli_query($docketDataSubscribe,$query_MaxImportDocketId);
			$row_maxImportIdResult = mysqli_fetch_assoc($maxImportIdResult);
			$maxImportDocketId = $row_maxImportIdResult['MaxImportId'];
			
			$selectImportDokcetData = "SELECT * from import_docket_calculator where import_docket_id = ".$maxImportDocketId."";
			$ResultImportIdResult = mysqli_query($docketDataSubscribe,$selectImportDokcetData);
			$row_importDocketId = mysqli_fetch_assoc($ResultImportIdResult);
			$originalDate = $row_importDocketId['trigger_date'];
			$newDate = date("d-m-Y", strtotime($originalDate));
			$TriggerData = '<br>';
			foreach($TotalJurisdictionsList as $jurisData)
			{
					if($jurisData['SystemID']==$row_importDocketId['jurisdiction'])
					{
						$TriggerData.='<b>Jurisdiction: '.$jurisData['Description'].'</b><br>';
					}
			}	
			$TriggerData.='<b>Trigger: '.$row_importDocketId['triggerItem'].'</b><br>';
			$TriggerData.='<b>Trigger Date and Time: '.date('d-m-Y',strtotime($newDate)).' '.date('h:i:s',strtotime($row_importDocketId['trigger_time'])).' '.$row_importDocketId['meridiem'].'</b><br>';	
			$TriggerData.='<b>Event Type: '.$eventTypeDesc.'</b><br>';
			$LinkToEditCaseEvent = "<br>http://googledocket.com/view-calendar-event?id=";
			$maxCaseEventId = $maxCaseEventId + 1;
			$EditCaseEventLink = $LinkToEditCaseEvent.$maxCaseEventId.'<br>';
		
			$description = $TriggerData.' '.$EditCaseEventLink.' '.$case_name_append_before.' '.$custom_text_append_before.' '.$court_rule. ' ' .$date_rule.' '.$triggerItemCalc.' '.$case_name_append_after.' '.$custom_text_append_after."<br/>"."<br/>".$calendar_rules_events_tag;
			
			$summary = $case_name_summary_before .' '.$custom_text_summary_before.' '.$summary.' '.$case_name_summary_after.' '.$custom_text_summary_after;
			$location = $row_searchInfo['location'];

			if($all_day == 1)
			{
			  $status = $row_authOptionInfo['all_day_appointments'];
			} else {
			  $status = $row_authOptionInfo['appointments_status'];
			}
			$caseLevelreminder_minutes = $row_caseInfo['caseReminderTime'];
			if($caseLevelreminder_minutes == NULL || $caseLevelreminder_minutes == 0)
			{
				$reminder_minutes = $row_authOptionInfo['reminder_minutes'];
			}else{
				$reminder_minutes = $row_caseInfo['caseReminderTime'];
			}
						
						
			$caseLeveleventColor = $row_caseInfo['caseEventColor'];
			if($caseLeveleventColor == NULL || $caseLeveleventColor == 0)
			{
				$eventColor = $row_authOptionInfo['eventColor'];
			}else{
				$eventColor =$row_caseInfo['caseEventColor'];
			}

            if($eventSystemID == $row_searchInfo['system_id'])
            {
			$data = $capi->DeleteCalendarEvent($row_searchInfo['event_id'],$row_searchInfo['calendar_id'],$row_searchInfo['access_token']);
			$query_deleteInfo = "DELETE FROM import_events WHERE import_event_id = '".$row_searchInfo['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo);

			$query_deleteInfo1 = "DELETE FROM case_events WHERE import_event_id = '".$row_searchInfo['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo1);	
			
			/* INSERT INTO CALENDAR */	   
			$event_id = $capi->CreateCalendarEvent(''.$row_searchInfo['calendar_id'].'', $summary,$all_day, $date_array, $user_timezone, $_SESSION['access_token'], $attendees, $description,$reminder_minutes,$eventColor,$location,$status);
			/* INSERT INTO DB */
			$summary = mysqli_real_escape_string($docketDataSubscribe,$summary);
			$description = mysqli_real_escape_string($docketDataSubscribe,$description);
			$location = mysqli_real_escape_string($docketDataSubscribe,$location);
			$eventName = mysqli_real_escape_string($docketDataSubscribe,$eventName);
			$dateRule = mysqli_real_escape_string($docketDataSubscribe,$dateRuleText);
			$courtRule = mysqli_real_escape_string($docketDataSubscribe,$courtRuleText);
			
			$insertSQL = "INSERT INTO import_events(authenticator,import_docket_id, event_id, event_docket, recalculate_flag, parent_system_id, system_id)  VALUES ('".$_SESSION['author_id']."',".$row_searchInfo['import_docket_id'].",'".$event_id."','".$eventDocket."','".$eventRecalc."', '".$eventParentSystemID."','".$eventSystemID."')";
			$result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
			$import_event_id = mysqli_insert_id($docketDataSubscribe);

			$shortName = mysqli_real_escape_string($docketDataSubscribe,$response[CalendarRuleEvent][ShortName]);
			
			$insertSQLCases = "INSERT INTO case_events(import_event_id, event_date, short_name, description,remainder,location,status,eventtype,eventName,courtRule,dateRule) VALUES ('".$import_event_id."','".$date."','".$summary."','".$description."','".$reminder_minutes."','".$location."','".$status."','".$eventTypeDesc."','".$eventName."','".$courtRule."','".$dateRule."')";
			$resultCases = mysqli_query($docketDataSubscribe,$insertSQLCases) or trigger_error("Query Failed! SQL: $insertSQLCases - Error: ".mysqli_error(), E_USER_ERROR);
			}
                                // multiple events
		} else {
		   $icalevents = "";
		   $eve = 1;

		foreach ($response as $Event) {

		$date = str_replace("-", "", $Event[CalendarRuleEvent][EventDate]);
		$expTime = explode("T", $date);
		$date = str_replace(":", "", $date);

		$eventTime = $expTime[1];
		$eventExplodeTime = str_replace(":", "", $eventTime);

		$summary = $matterString . $headerinfo . $Event[CalendarRuleEvent][ShortName];
		$eventName = $Event[CalendarRuleEvent][ShortName];
		//single date rules
		if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
			$dateRuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][DateRules][Rule][RuleText]);
			$dateRuleText = str_replace("\r", " ", $dateRuleText);
			// mutliple date rules
		} else {
			$dateRuleAll = "";
			foreach ($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
				$dateRuleText = str_replace("\n", " ", $Rule[RuleText]);
				$dateRuleText = str_replace("\r", " ", $dateRuleText);

				$dateRuleAll = $dateRuleAll . $dateRuleText . ";";
			}
			$dateRuleText = substr($dateRuleAll, 0, strlen($dateRuleAll - 1));
		}

		// single court rule
		if (isset($Event[CalendarRuleEvent][CourtRules][Rule][RuleText])) {
					$courtRuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][CourtRules][Rule][RuleText]);
					$courtRuleText = str_replace("\r", " ", $courtRuleText);
					$courtRuleId = $Event[CourtRuleID];
					// multiple court rules
		} else {
					$courtRuleAll = "";
					foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
						$courtRuleText = str_replace("\n", " ", $Rule[RuleText]);
						$courtRuleText = str_replace("\r", " ", $courtRuleText);

						$courtRuleAll = $courtRuleAll . $courtRuleText . ";";
						$courtRuleId .= $Rule[RuleID]. " - ";
            }
			$courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
		}
		if($Event[CalendarRuleEvent][EventType][Description])
		{
			 $eventTypeDesc = $Event[CalendarRuleEvent][EventType][Description];
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
		  $attendees = $_POST['attendees'];
		} else {
		  $attendees = $row_searchInfo['attendees'];
		}

		$eventDocket = $Event[CalendarRuleEvent][IsEventDocket];
		$eventRecalc = $Event[CalendarRuleEvent][DoNotRecaltulateFlag];
		$eventParentSystemID = $Event[CalendarRuleEvent][ParentSystemID];
		$eventSystemID = $Event[CalendarRuleEvent][SystemID];
		//echo "<br>";
		 if($_POST['caselab'] == "parent" && $eventSystemID == $row_searchInfo['system_id'])
		 {
		   $has_child = 1;
		 } else {
		   $has_child = 0;
		 }

        if($add_court_rule_body == "Rule Text")
        {
          $court_rule = $courtRuleText;
        } else if($add_court_rule_body == "Rule ID only")
        {
          $court_rule = $courtRuleId;
        } else if($add_court_rule_body == "Don't add")
        {
          $court_rule = '';
        }

        if($add_date_rule_body == "yes")
        {
          $date_rule = $dateRuleText;
        } else if($add_date_rule_body == "no")
        {
          $date_rule = '';
        }

        $case_name_append_before = '';
        $case_name_append_after = '';
        $case_name_summary_before = '';
        $case_name_summary_after = '';

        if($case_name_location == "prepend to subject")
        {
          $case_name_summary_before = $row_caseInfo['case_matter'];
        } else if($case_name_location == "append to subject")
        {
          $case_name_summary_after = $row_caseInfo['case_matter'];
        }
        else if($case_name_location == "prepand to body")
        {
          $case_name_append_before = $row_caseInfo['case_matter'];
        } else if($case_name_location == "append to body")
        {
          $case_name_append_after = $row_caseInfo['case_matter'];
        } else if($case_name_location == "don't add")
        {
            $case_name_append_before = '';
            $case_name_append_after = '';
            $case_name_summary_before = '';
            $case_name_summary_after = '';
        }

        $custom_text_append_before = '';
        $custom_text_append_after = '';
        $custom_text_summary_before = '';
        $custom_text_summary_after = '';

        if($custom_text_location == "prepend to subject")
        {
          $custom_text_summary_before = $row_searchInfo['custom_text'];
        } else if($custom_text_location == "append to subject")
        {
          $custom_text_summary_after = $row_searchInfo['custom_text'];
        }
        else if($custom_text_location == "prepand to body")
        {
          $custom_text_append_before = $row_searchInfo['custom_text'];
        } else if($custom_text_location == "append to body")
        {
          $custom_text_append_after = $row_searchInfo['custom_text'];
        } else if($custom_text_location == "don't add")
        {
            $custom_text_append_before = '';
            $custom_text_append_after = '';
            $custom_text_summary_before = '';
            $custom_text_summary_after = '';
        }
		if($row_authOptionInfo['calendar_rules_events_tag'] == 'Yes')
		{
		  $calendar_rules_events_tag = "CalendarRulesEvent";
		} else {
		  $calendar_rules_events_tag = '';
		}
        if($row_authOptionInfo['show_trigger'] == 'yes')
        {
          $triggerItemCalc = $row_searchInfo['triggerItem'];
        } else {
          $triggerItemCalc = '';
        }
		$JuriURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
		$juricommand="/jurisdictions/my?";
		$juriparameters="loginToken=$loginToken";
		$jurifile= $JuriURL.$juricommand.$juriparameters;
		$juricontent=file_get_contents($jurifile,false,$context);
		$jurixml=$juricontent;
		$juriarray=xml2array($jurixml);
		$TotalJurisdictionsList = $juriarray['ArrayOfJurisdiction']['Jurisdiction'];
			
		$query_MaxImportDocketId = "SELECT max(import_docket_id) as MaxImportId FROM  import_docket_calculator";
		$maxImportIdResult = mysqli_query($docketDataSubscribe,$query_MaxImportDocketId);
		$row_maxImportIdResult = mysqli_fetch_assoc($maxImportIdResult);
		$maxImportDocketId = $row_maxImportIdResult['MaxImportId'];
		
		$selectImportDokcetData = "SELECT * from import_docket_calculator where import_docket_id = ".$maxImportDocketId."";
		$ResultImportIdResult = mysqli_query($docketDataSubscribe,$selectImportDokcetData);
		$row_importDocketId = mysqli_fetch_assoc($ResultImportIdResult);
		$originalDate = $row_importDocketId['trigger_date'];
		$newDate = date("d-m-Y", strtotime($originalDate));
		$TriggerData = '<br>';
		foreach($TotalJurisdictionsList as $jurisData)
        {
				if($jurisData['SystemID']==$row_importDocketId['jurisdiction'])
				{
					$TriggerData.='<b>Jurisdiction: '.$jurisData['Description'].'</b><br>';
				}
		}	
		$TriggerData.='<b>Trigger: '.$row_importDocketId['triggerItem'].'</b><br>';
		$TriggerData.='<b>Trigger Date and Time: '.date('d-m-Y',strtotime($newDate)).' '.date('h:i:s',strtotime($row_importDocketId['trigger_time'])).' '.$row_importDocketId['meridiem'].'</b><br>';		
		$TriggerData.='<b>Event Type: '.$eventTypeDesc.'</b><br>';
		$eventIncrement = 1;
		$LinkToEditCaseEvent = "<br>http://googledocket.com/view-calendar-event?id=";
		$caseLevelreminder_minutes = $row_caseInfo['caseReminderTime'];
		if($caseLevelreminder_minutes == NULL || $caseLevelreminder_minutes == 0)
		{
			$reminder_minutes = $row_authOptionInfo['reminder_minutes'];
		}else{
			$reminder_minutes = $row_caseInfo['caseReminderTime'];
		}				
		$caseLeveleventColor = $row_caseInfo['caseEventColor'];
		if($caseLeveleventColor == NULL || $caseLeveleventColor == 0)
		{
			$eventColor = $row_authOptionInfo['eventColor'];
		}else{
			$eventColor =$row_caseInfo['caseEventColor'];
		}
		$maxCaseEventId = $maxCaseEventId + $eventIncrement;
		$EditCaseEventLink = $LinkToEditCaseEvent.$maxCaseEventId.'<br>';
        $description =  $TriggerData.' '.$EditCaseEventLink.' '.$case_name_append_before.' '.$custom_text_append_before.' '.$court_rule. ' ' .$date_rule.' '.$triggerItemCalc.' '.$case_name_append_after.' '.$custom_text_append_after."<br/>"."<br/>".$calendar_rules_events_tag;
		
		
        $summary = $case_name_summary_before .' '.$custom_text_summary_before.' '.$summary.' '.$case_name_summary_after.' '.$custom_text_summary_after;
        $location = $row_searchInfo['location'];

        if($all_day == 1)
        {
          $status = $row_authOptionInfo['all_day_appointments'];
        } else {
          $status = $row_authOptionInfo['appointments_status'];
        }

		$dont_flag = 0;

		if($row_searchInfo['recalculate_flag'] == 'true')
		{
			if($do_not_recalculate_events == "use original date")
			{
			  $dont_flag = 1;
			} else if($do_not_recalculate_events == "use new date")
			{
			  $dont_flag = 0;
			}
		}

        //$recalculated_events == "replace events";

		if($eventSystemID == $row_searchInfo['system_id']  || ($_POST['caselab'] == "eventdocket" && in_array($eventSystemID,$case_system)) || (in_array($eventSystemID,$parent_system) && $_POST['caselab'] == "parent" && $dont_flag == 0))
       {
        $event_id = $capi->CreateCalendarEvent(''.$row_searchInfo['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees, $description,$reminder_minutes,$eventColor,$location,$status);

		/* INSERT INTO DB */
		$summary = mysqli_real_escape_string($docketDataSubscribe,$summary);
		$description = mysqli_real_escape_string($docketDataSubscribe,$description);
		$location = mysqli_real_escape_string($docketDataSubscribe,$location);
		$eventName = mysqli_real_escape_string($docketDataSubscribe,$eventName);
		$dateRule = mysqli_real_escape_string($docketDataSubscribe,$dateRuleText);
		$courtRule = mysqli_real_escape_string($docketDataSubscribe,$courtRuleText);
		
        $insertSQL = "INSERT INTO import_events(authenticator,import_docket_id, event_id, event_docket, recalculate_flag, parent_system_id, system_id, has_child)  VALUES ('".$_SESSION['author_id']."',".$row_searchInfo['import_docket_id'].",'".$event_id."','".$eventDocket."','".$eventRecalc."','".$eventParentSystemID."','".$eventSystemID."','".$has_child."')";
        $result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
        $import_event_id = mysqli_insert_id($docketDataSubscribe);

        $shortName = mysqli_real_escape_string($docketDataSubscribe,$Event[CalendarRuleEvent][ShortName]);
        
        $insertSQLCases = "INSERT INTO case_events(import_event_id, event_date, short_name, description,remainder,location,status,eventtype) VALUES ('".$import_event_id."','".$date."','".$summary."','".$description."','".$reminder_minutes."','".$location."','".$status."','".$eventTypeDesc."','".$eventName."','".$courtRule."','".$dateRule."')";
        $resultCases = mysqli_query($docketDataSubscribe,$insertSQLCases) or trigger_error("Query Failed! SQL: $insertSQLCases - Error: ".mysqli_error(), E_USER_ERROR);

			if( ($_POST['caselab'] == "parent" && in_array($eventSystemID,$parent_system) && $dont_flag == 0) || ($_POST['caselab'] == "eventdocket" && in_array($eventSystemID,$case_system) && $eventSystemID != $row_searchInfo['system_id']))
			{
			$query_searchInfo1 = "SELECT idc.*, ie.system_id, ie.import_event_id as import_event_id, ie.system_id as system_id, ie.event_id as event_id FROM case_events as ce
			inner join import_events as ie ON ie.import_event_id = ce.import_event_id
			inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
			WHERE ie.system_id = '".$eventSystemID."' AND idc.case_id = '".$row_searchInfo['case_id']."' ";
			$searchInfo1 = mysqli_query($docketDataSubscribe,$query_searchInfo1);
			$row_searchInfo1 = mysqli_fetch_assoc($searchInfo1);
			$totalRows_searchInfo1 = mysqli_num_rows($searchInfo1);

			$data = $capi->DeleteCalendarEvent($row_searchInfo1['event_id'],$row_searchInfo1['calendar_id'],$row_searchInfo1['access_token']);
			$query_deleteInfo3 = "DELETE FROM import_events WHERE import_event_id = '".$row_searchInfo1['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo3);

			$query_deleteInfo4 = "DELETE FROM case_events WHERE import_event_id = '".$row_searchInfo1['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo4);

			}
		}
		$eventIncrement++;
		$eve++;
		} //for loop

			$data = $capi->DeleteCalendarEvent($row_searchInfo['event_id'],$row_searchInfo['calendar_id'],$row_searchInfo['access_token']);
			$query_deleteInfo = "DELETE FROM import_events WHERE import_event_id = '".$row_searchInfo['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo);

			$query_deleteInfo1 = "DELETE FROM case_events WHERE import_event_id = '".$row_searchInfo['import_event_id']."' ";
			mysqli_query($docketDataSubscribe,$query_deleteInfo1);

		}
							
					$selectCaseAttendees =  "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id." and eventlevel = 1 and eventid =".$case_event_id." ";
					$caseAttendee = mysqli_query($docketDataSubscribe,$selectCaseAttendees);
					$totalRows_attendees = mysqli_num_rows($caseAttendee);
				
					if($totalRows_attendees > 0)
					{
					  $delete_case_attendees = "DELETE FROM docket_cases_attendees WHERE case_id = ".$case_id." and eventlevel = 1 and eventid =".$case_event_id." ";
					  mysqli_query($docketDataSubscribe,$delete_case_attendees);
					}
			if($_POST['attendees'] != '')
			{
				$attendee = $_POST['attendees'];
				if(count($attendee) > 0)
				{
					array_push($attendee);
					array_unique($attendee);
				} 
				foreach($attendee as $attendeeVal)
				{
					
					$insertAttendees = sprintf("INSERT INTO docket_cases_attendees (case_id, attendee,eventlevel,eventid) VALUES (%s, %s, %s, %s)",
					GetSQLValueString($docketDataSubscribe,$case_id, "text"),
					GetSQLValueString($docketDataSubscribe,$attendeeVal, "text"),
					GetSQLValueString($docketDataSubscribe,1, "int"),
					GetSQLValueString($docketDataSubscribe,$case_event_id, "int"));
					mysqli_query($docketDataSubscribe,$insertAttendees);
				}
			}
			$responseHtml = "<span style='color:green;'>Successfully updated to your Google Calendar.</span>";

            } else {
                        $responseHtml = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
                    }
                }
            }
        }
		  $result_html['html'] = $responseHtml;
		  echo json_encode($result_html);

	}  else 
	{
		
	/* LOOP For NORMAL Events Updatation */
	//NORMAL
		$result_html = array();
		$query_importEvents = "SELECT e.event_id,i.calendar_id,i.access_token,i.attendees,i.case_id,c.event_date,c.short_name,e.import_event_id,c.description,c.remainder,c.location,c.status FROM docket_cases dc
		INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
		INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
		INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
		WHERE c.case_event_id = ".$case_event_id." ";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);
	
			if($totalRows_importEvents > 0)
			{
			$row_events = mysqli_fetch_assoc($ImportEvents);
			$case_id = $row_events['case_id'];
			

		require_once('../googleCalender/google-calendar-api.php');
		$capi = new GoogleCalendarApi();

		//$data = $capi->DeleteCalendarEvent($row_events['event_id'],$row_events['calendar_id'],$_SESSION['access_token']);

		$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);


			$expTime = explode(" ", $event_date);
			$eventTime = $expTime[1];

			if($expTime[2] == "am") {
				if($expTime[1] == "12:00:00") {
				  $eventTime = "00:00:00";
				}
			}
			if ($eventTime == "00:00:00") {
					$start_time = "";
					$end_time =  "";
					$event_date = $expTime[0];
					$update_event_date = $expTime[0];
					$all_day = 1;
			} else {
					$eventExplodeTime = str_replace(":", "", $eventTime);
					$start_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) );
					$end_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
					$event_date = "";
					$update_event_date = $expTime[0].' '.date('H:i:s', strtotime($eventExplodeTime) );
					$all_day = 0;
			}

			if($_POST['attendees'] != '')
			{
			  $attendees = $_POST['attendees'];
			} else {
			  $attendees = $row_events['attendees'];
			}
			$date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);
			//echo "<pre>"; print_r($date_array); 
			
			$summary = mysqli_real_escape_string($docketDataSubscribe,$row_events['short_name']);
			$description = mysqli_real_escape_string($docketDataSubscribe,$row_events['description']);
			 $remainder = mysqli_real_escape_string($docketDataSubscribe,$row_events['remainder']);
			$location = mysqli_real_escape_string($docketDataSubscribe,$row_events['location']);
			 $status = mysqli_real_escape_string($docketDataSubscribe,$row_events['status']);
			$event_id = $capi->UpdateCalendarEvent($row_events['event_id'],''.$row_events['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees,$description,$remainder,$location,$status);

			$updateSQL = "UPDATE case_events SET event_date = '".$update_event_date."'   WHERE case_event_id  = ".$case_event_id." ";
			$result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);
		
		//$updateSQL2 = "UPDATE import_events SET event_id = '".$event_id."' WHERE import_event_id  = ".$row_events['import_event_id']." ";
		//$result2 = mysqli_query($docketDataSubscribe,$updateSQL2) or trigger_error("Query Failed! SQL: $updateSQL2 - Error: ".mysqli_error(), E_USER_ERROR);

			$selectCaseAttendees =  "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id." and eventlevel = 1 and eventid =".$case_event_id." ";
			$caseAttendee = mysqli_query($docketDataSubscribe,$selectCaseAttendees);
			$totalRows_attendees = mysqli_num_rows($caseAttendee);
		
			if($totalRows_attendees > 0)
			{
			  $delete_case_attendees = "DELETE FROM docket_cases_attendees WHERE case_id = ".$case_id." and eventlevel = 1 and eventid =".$case_event_id." ";
			  mysqli_query($docketDataSubscribe,$delete_case_attendees);
			}
			if($_POST['attendees'] != '')
           {
				$attendee = $_POST['attendees'];
						if(count($attendee) > 0)
						{
							array_push($attendee);
							array_unique($attendee);
						} 
							foreach($attendee as $attendeeVal)
							{
								
								$insertAttendees = sprintf("INSERT INTO docket_cases_attendees (case_id, attendee,eventlevel,eventid) VALUES (%s, %s, %s, %s)",
								GetSQLValueString($docketDataSubscribe,$case_id, "text"),
								GetSQLValueString($docketDataSubscribe,$attendeeVal, "text"),
								GetSQLValueString($docketDataSubscribe,1, "int"),
								GetSQLValueString($docketDataSubscribe,$case_event_id, "int"));
								mysqli_query($docketDataSubscribe,$insertAttendees);
							}
                    }
					

                $responseHtml = "<span style='color:green;'>Successfully updated to your Google Calendar.</span>";
                $result_html['html'] = $responseHtml;
                echo json_encode($result_html);
		} else {
             $result_html['html'] = "<span style='color:red;'>Error Occured. Please Try Again.</span>";
             echo json_encode($result_html);
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
