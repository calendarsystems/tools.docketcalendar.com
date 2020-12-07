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
session_start();
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$database_docketData = $GLOBALS['database_docketData'];

$addReminderVal = 0;
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

require('../globals/global_tools.php');

if (!function_exists("GetSQLValueString")) 
{
	function GetSQLValueString($dataValue,$theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	{
		if (PHP_VERSION < 6) {
			$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		}

		$theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($dataValue,$theValue) : mysqli_escape_string($dataValue,$theValue);

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
	 if(isset($_POST['system_id']))
    {
        $system_id = $_POST['system_id'];
    }else{
		$system_id = 0;
	}
		
		

    if( ($_POST['caselab'] == "eventdocket" || $_POST['caselab'] == "parent"))
    {
		//PARENT
			require_once('../googleCalender/google-calendar-api.php');
			$capi = new GoogleCalendarApi();
			$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);
			$importEventId=array();
			$caseEventId = array();
			$query_searchInfo = "SELECT idc.*, ie.system_id,idc.case_id,idc.trigger_item, ie.import_event_id as import_event_id,ie.import_docket_id,ie.system_id as system_id, ie.event_id as event_id,ie.recalculate_flag,idc.calendar_id
			FROM case_events as ce INNER JOIN import_events as ie ON ie.import_event_id = ce.import_event_id INNER JOIN import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
			WHERE ce.case_event_id =".$case_event_id."";
			$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
			$row_searchInfo = mysqli_fetch_assoc($searchInfo);
				
			$case_id        = $row_searchInfo['case_id'];
			$caseeventid    = $row_searchInfo['event_id'];
			$importDocketId = $row_searchInfo['import_docket_id'];
			$calendar_id 	= $row_searchInfo['calendar_id'];
			$systemId 		= $row_searchInfo['system_id'];
	
			$updateEventModifiedBy =  "UPDATE  docket_case_triggerevent_mod SET event_modified_by = 
			'".$_SESSION['author_id']."',event_id='".$case_event_id."' WHERE case_id = ".$case_id." AND user_id=".
			$_SESSION['userid']." AND trigger_id='".$row_searchInfo['trigger_item']."'";
	
			$updateEventModifiedDetails = mysqli_query($docketDataSubscribe,$updateEventModifiedBy);
			
			 $insertEventCustomText =  "INSERT INTO docket_customtext  (user_id,case_id,	trigger_trigid,event_eventid,event_custometext,event_customtextlevel)VALUES (".$_SESSION['userid'].",".$case_id.",'".$row_searchInfo['trigger_item']."','".$case_event_id."','".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventCustomText'])."',1)";
			$ResultupdateEventCustomText = mysqli_query($docketDataSubscribe,$insertEventCustomText);
			$query_parentInfo = "SELECT ie.system_id as system_id  FROM case_events as ce INNER JOIN import_events as ie ON ie.import_event_id = ce.import_event_id INNER JOIN import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
			WHERE ie.parent_system_id = '".$systemId."' AND idc.case_id = '".$case_id."' ";
			$parentInfo = mysqli_query($docketDataSubscribe,$query_parentInfo);
			
			$querygetupdateeventsdescInfo = "SELECT * FROM updateeventsdesc WHERE importdocketid = '".$importDocketId."' and caseid = ".$case_id."";
				$querygetupdateeventsdescInfoData = mysqli_query($docketDataSubscribe,$querygetupdateeventsdescInfo);
				$totalRowsupdateeventsdescInfo = mysqli_num_rows($querygetupdateeventsdescInfoData);
				if($totalRowsupdateeventsdescInfo > 0)
				{
					$row_getupdateeventsdesc = mysqli_fetch_assoc($querygetupdateeventsdescInfoData);
				}
				

			$totalRows_parentInfo = mysqli_num_rows($parentInfo);

			if($totalRows_parentInfo > 0)
			{
				$has_child = 1;
				while($row_parentInfo = mysqli_fetch_assoc($parentInfo))
				{
				   $parent_system[] = $row_parentInfo['system_id'];
				}
			}
			$inArrayQuery = implode(',',$parent_system);
			
		/*Start Delete all Data */
			$query_importEvents = "SELECT import_event_id,event_id FROM import_events WHERE import_docket_id = ".$importDocketId." and `system_id` IN (".$inArrayQuery.")";
			$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
			while($rowCaseEventId = mysqli_fetch_assoc($ImportEvents))
			{
			$eventIdVal = $rowCaseEventId['import_event_id'];
			array_push($caseEventId , $eventIdVal);
			$importEventIdVal = $rowCaseEventId['event_id'];
			array_push($importEventId , $importEventIdVal);
		
			
			/* DELETE Calendar events  */			
			$data = $capi->DeleteCalendarEvent($importEventIdVal,$calendar_id,$_SESSION['access_token']);
											
			}
			for($i=0;$i < sizeof($caseEventId);$i++)
			{
				$query_deleteInfo = "DELETE FROM case_events WHERE case_event_id = '".$caseEventId[$i] ."'";
				mysqli_query($docketDataSubscribe,$query_deleteInfo);
				
				$querydeleteEvents = "DELETE FROM events WHERE eventIdvalue = '".$caseEventId[$i] ."'";
				mysqli_query($docketDataSubscribe,$querydeleteEvents);
				
			}
			
			for($j=0;$j < sizeof($importEventId);$j++)
			{
				$query_deleteInfo1 = "DELETE FROM import_events WHERE event_id = '".$importEventId[$j]."' ";
				mysqli_query($docketDataSubscribe,$query_deleteInfo1);	
				
			}
		/*END Delete all data*/		
			
			$colname_userInfo = "-1";
			if (isset($_SESSION['userid']))
			{
			  $colname_userInfo = $_SESSION['userid'];
			}
			$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
				$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
				$totalRows_authoptionInfo = mysqli_num_rows($authOptionInfo);
				$row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo);
			$query_caseInfo = "SELECT * FROM docket_cases WHERE case_id = '".$case_id."' ";
				$caseInfo = mysqli_query($docketDataSubscribe,$query_caseInfo);
				$totalRows_caseInfo = mysqli_num_rows($caseInfo);
				$row_caseInfo = mysqli_fetch_assoc($caseInfo);	
				
			
			
				$caseLeveleventColor = $row_caseInfo['caseEventColor'];
				if($caseLeveleventColor == NULL || $caseLeveleventColor == 0)
				{
					$eventColor = $row_authOptionInfo['eventColor'];
				}else{
					$eventColor =$row_caseInfo['caseEventColor'];
				}
	
			if (isset($_SESSION['userid'])) 
			{
				$query_searchInfo = "SELECT * FROM import_docket_calculator WHERE import_docket_id = ".$importDocketId."";
				$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
				$row_searchInfo = mysqli_fetch_assoc($searchInfo);
				$totalRows_searchInfo = mysqli_num_rows($searchInfo);
				
				$add_court_rule_body = $row_authOptionInfo['add_court_rule_body'];
				$add_date_rule_body = $row_authOptionInfo['add_date_rule_body'];
				$case_name_location = $row_authOptionInfo['case_name_location'];
				$custom_text_location = $row_authOptionInfo['custom_text_location'];
				$custom_appointment_length = $row_authOptionInfo['appointment_length'];
				
				
				$newURL = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

				$selectJurisdiction = 0;
				$selectTriggerItem = 0;
				$selectServiceType = 0;

				$masterCourt = $row_searchInfo['jurisdiction'];
				
				if (isset($_SESSION['userid'])) 
				{
				$command = "/users/" . $_SESSION['username'] . "?";
				$parameters = "password=" . $_SESSION['password'] . "&soapREST=REST";
				$file = $newURL . $command . $parameters;
				$content = file_get_contents($newURL . $command . $parameters, false, $context);
				$xml = $content;
				$array = xml2array($xml);
				$loginToken = $array['string'];
				}
				$events_array = $parent_system;
				
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
				$sort = $row_searchInfo['sort_date'];
				if ($selectJurisdiction != 0) {
					//    echo "juris good";
					if ($selectTriggerItem != 0) {
							//    echo "trigger good";
						if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
								//    echo "service good";
								if (($isTimeRequired == "Y" && $row_searchInfo['trigger_time'] != "") || $isTimeRequired != "Y") {

								$explode_event_date = explode(" ",$event_date);
								$formDate = $explode_event_date[0];
								$trigTime = explode(":",$explode_event_date[1]);

								$triggerDate = $formDate;

								$xml = '<CalculationParameters xmlns="http://schemas.datacontract.org/2004/07/CRC.WCFService.Objects">
								<Associations/>
								<EventSystemID>'.$system_id.'</EventSystemID>
								<Events/>
								<JurisdictionSystemID>' . $selectJurisdiction . '</JurisdictionSystemID>
								<ServiceTypeSystemID>' . $selectServiceType . '</ServiceTypeSystemID>
								<TriggerDate>' . $explode_event_date[0] . 'T' . $explode_event_date[1] . '</TriggerDate>
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
								// curl_setopt ($session, CURLOPT_HTTPHEADER, array('Expect:'));

								$response = curl_exec($session);
								//echo "<pre>";
								//print_r($response);exit();
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

							// single event
					if (isset($response['Action'])) {

										
					//SINGLE EVENTS				   
					} 
					else 
					{
						
						 // MULTIPLE EVENTS
						if ($sort == 2) {

							function cust_sort($a, $b) {
								return strtolower($a['CalendarRuleEvent']['EventDate']) < strtolower($b['CalendarRuleEvent']['EventDate']);
							}
							usort($response, 'cust_sort');
							$numresults = sizeof($response);

						} else if ($sort == 1) {

							function cust_sort($a, $b) {
								return strtolower($a['CalendarRuleEvent']['EventDate']) > strtolower($b['CalendarRuleEvent']['EventDate']);
							}
							usort($response, 'cust_sort');
							$numresults = sizeof($response);

						}
						if($events_array != '')
						{
							$icalevents = "";
							$eve = 1;
							$systemID = '';
							$tree_array = array();

							foreach ($response as $Event) {
								
							  $parentID = $Event['CalendarRuleEvent']['ParentSystemID'];
							  $systemID = $Event['CalendarRuleEvent']['SystemID'];
							  $eventDate = $Event['CalendarRuleEvent']['EventDate'];
							  $shortName = $Event['CalendarRuleEvent']['ShortName'];

							  $dateruleText = $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
							  $daterule = $Event['CalendarRuleEvent']['DateRules']['Rule'];

							  $courtRuleText = $Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'];
							  $courtRuleID = $Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleID'];
							  $courtrule = $Event['CalendarRuleEvent']['CourtRules']['Rule'];

							  $evenDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
							  $evenRecalc = $Event['CalendarRuleEvent']['DoNotRecaltulateFlag'];
								if($Event['CalendarRuleEvent']['EventType']['Description'])
								{
								 $EventType = $Event['CalendarRuleEvent']['EventType']['Description'];
								}
							  $tree_array[$systemID]['SystemID'] =  $systemID;
							  $tree_array[$systemID]['ParentSystemID'] =  $parentID;
							  $tree_array[$systemID]['EventDate'] =  $eventDate;
							  $tree_array[$systemID]['ShortName'] =  $shortName;
							  $tree_array[$systemID]['DateRuleText'] =  $dateruleText;
							  $tree_array[$systemID]['DateRule'] =  $daterule;
							  $tree_array[$systemID]['CourtRuleText'] =  $courtRuleText;
							  $tree_array[$systemID]['CourtRuleID'] =  $courtRuleID;
							  $tree_array[$systemID]['CourtRule'] =  $courtrule;
							  $tree_array[$systemID]['IsEventDocket'] =  $evenDocket;
							  $tree_array[$systemID]['DoNotRecaltulateFlag'] =  $evenRecalc;
							  $tree_array[$systemID]['EventType'] =  $EventType;
							}

						ksort($tree_array);
						$systemID = '';
						$i=1;
						if($_POST['attendees'] != '')
						{
						 
						  $attendees = $_POST['attendees'];
						} else {
						  $attendees = "";
						}	
						$JuriURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
						$juricommand="/jurisdictions/my?";
						$juriparameters="loginToken=$loginToken";
						$jurifile= $JuriURL.$juricommand.$juriparameters;
						$juricontent=file_get_contents($jurifile,false,$context);
						$jurixml=$juricontent;
						$juriarray=xml2array($jurixml);
						$TotalJurisdictionsList = $juriarray['ArrayOfJurisdiction']['Jurisdiction'];	
						foreach ($tree_array as $key => $Event) 
						{ 
							  $parentID = $Event['ParentSystemID'];
							  $sysID = $Event['SystemID'];
							  /*Changes done on 26 Sep 2020 for single */
			
			  $queryForMultipleUpdateEvents="SELECT * FROM updateeventsdesc WHERE importdocketid=".$importDocketId." AND caseid = ".$case_id." AND eventid = '".$sysID."'";
				$resultForMultiple = mysqli_query($docketDataSubscribe,$queryForMultipleUpdateEvents);
				$totalRowsupdateeventsdescMultipleInfo=mysqli_num_rows($resultForMultiple);
				if($totalRowsupdateeventsdescMultipleInfo > 0)
				{
					$row_ForMultiple = mysqli_fetch_assoc($resultForMultiple);
				}
					if(in_array($sysID,$events_array))
					{
								$date = str_replace("-", "", $Event['EventDate']);
								$expTime = explode("T", $date);
								$date = str_replace(":", "", $date);
								$eventTime = $expTime[1];
								$eventExplodeTime = str_replace(":", "", $eventTime);
					if($totalRowsupdateeventsdescMultipleInfo > 0)
					{
						if($sysID == $row_ForMultiple['eventid'])
						{
							$summary   = $row_ForMultiple['eventdesc'];
							$eventName = $row_ForMultiple['eventdesc'];
							if($row_ForMultiple['eventreminderval']!=NULL)
							{
								
								$addReminderFlag = $addReminderFlag + 1;
								$addReminderVal = $row_ForMultiple['eventreminderval'];
							}
							if($row_ForMultiple['eventpopupreminderval']!=NULL)
							{
								
								$addReminderFlag = $addReminderFlag + 1;
								$addPopUpReminderVal = $row_ForMultiple['eventpopupreminderval'];
							
							}
							if($row_ForMultiple['eventColor']!=NULL)
							{
								
								$eventColor = $row_ForMultiple['eventColor'];
							}
						}
						else{
							$summary = $matterString . $headerinfo . $Event['ShortName'];
							$eventName = $Event['ShortName'];
						}
					}
					else{
						$summary = $matterString . $headerinfo . $Event['ShortName'];
						$eventName = $Event['ShortName'];
						}
								//single date rules
								$dateRuleTextValue="";
								if (isset($Event['DateRuleText'])) {
									$dateRuleText = str_replace("\n", " ", $Event['DateRuleText']);
									$dateRuleText = str_replace("\r", " ", $dateRuleText);
									$dateRuleTextValue.=$dateRuleText;
									
								} else {
									// mutliple date rules
									$dateRuleAll = "";
									foreach ($Event['DateRule'] as $Rule) {
										$dateRuleText = str_replace("\n", " ", $Rule['RuleText']);
										$dateRuleText = str_replace("\r", " ", $dateRuleText);
										$dateRuleTextValue.=$dateRuleText;
										$dateRuleAll = $dateRuleAll . $dateRuleText . ";";
									}
									$dateRuleText = substr($dateRuleAll, 0, strlen($dateRuleAll - 1));
									
								}

								// single court rule
								$courtRuleTextValue="";
								if (isset($Event['CourtRuleText'])) {
									$courtRuleText = str_replace("\n", " ", $Event['CourtRuleText']);
									$courtRuleText = str_replace("\r", " ", $courtRuleText);
									$courtRuleTextValue.=$courtRuleText;
									$courtRuleId = $Event['CourtRuleID'];
									// multiple court rules
								} else {
									$courtRuleAll = "";
									foreach ($Event['CourtRule'] as $Rule) {
										$courtRuleText = str_replace("\n", " ", $Rule['RuleText']);
										$courtRuleText = str_replace("\r", " ", $courtRuleText);
										$courtRuleTextValue.=$courtRuleText;
										$courtRuleAll = $courtRuleAll . $courtRuleText . ";";

										$courtRuleId .= $Rule['RuleID']. " - ";
									}
									$courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
									
								}

							   if ($eventTime == "00:00:00") {   // all day event

								$start_time = "";
								$end_time =  "";
								$event_date = date('Y-m-d', strtotime($date) );
								$all_day = 1;
								$startDate = $event_date;
								$endDate = $event_date;

							   } else { //specific time event

								$start_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) );
								$end_time =  date('Y-m-d', strtotime($date) ).'T'.date('H:i:s', strtotime($eventExplodeTime) + $custom_appointment_length);
								$event_date = "";
								$all_day = 0;
								$startDate = $start_time;
								$endDate = $end_time;
							}

							$date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);
							

							$eventDocket = $Event['IsEventDocket'];
							$eventRecalc = $Event['DoNotRecaltulateFlag'];
							$eventParentSystemID = $Event['ParentSystemID'];
							$eventSystemID = $Event['SystemID'];
							$eventTypeDesc = $Event['EventType'];

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
								$location = $row_searchInfo['location'];
								$caseLevelreminder_minutes = $row_caseInfo['caseReminderTime'];
								if($caseLevelreminder_minutes == NULL || $caseLevelreminder_minutes == 0)
								{
									$reminder_minutes = $row_authOptionInfo['reminder_minutes'];
								}else{
									$reminder_minutes = $row_caseInfo['caseReminderTime'];
								}
								$caseLevelreminder_popminutes = $row_caseInfo['reminder_minutes_popup'];
								if($caseLevelreminder_popminutes == NULL || $caseLevelreminder_popminutes == 0)
								{
									$reminder_popupminutes = $row_authOptionInfo['reminder_minutes_popup'];
								}else{
									$reminder_popupminutes = $row_caseInfo['reminder_minutes_popup'];
								}
								
							

								if($all_day == 1)
								{
								  $status = $row_authOptionInfo['all_day_appointments'];
								} else {
								  $status = $row_authOptionInfo['appointments_status'];
								}
								
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
								$query_MaxCaseEventId = "SELECT max(case_event_id) as MaxId FROM case_events";
								$maxIdResult = mysqli_query($docketDataSubscribe,$query_MaxCaseEventId);
								$row_maxIdResult = mysqli_fetch_assoc($maxIdResult);
								$maxCaseEventId = $row_maxIdResult['MaxId'];
								
								$LinkToEditCaseEvent = "<br>https://tools.docketcalendar.com/update-calendar-event?id=";
								$maxCaseEventId = $maxCaseEventId + $eventIncrement;
								$EditCaseEventLink = $LinkToEditCaseEvent.$maxCaseEventId.'<br>';
								
								$description = $TriggerData.' '.$EditCaseEventLink.' '.$case_name_append_before.' '.$custom_text_append_before.' '.$court_rule. ' ' .$date_rule.' '.$triggerItemCalc.' '.$case_name_append_after.' '.$custom_text_append_after."<br/>"."<br/>".$calendar_rules_events_tag;
								$summary = $case_name_summary_before .' '.$custom_text_summary_before.' '.$summary.' '.$case_name_summary_after.' '.$custom_text_summary_after;
								
								/* EDIT LINK SEND EMAIL TO ATTENDEES AND ALSO CREATE EVENT IN CALENDAR */
								$event_id = $capi->CreateCalendarEvent(''.$calendar_id.'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees,$description,$reminder_minutes,$reminder_popupminutes,$eventColor,$location,$status,$addReminderFlag,$addReminderVal,$addPopUpReminderVal);
				
								
										  /* INSERT INTO DB */
							$summary = mysqli_real_escape_string($docketDataSubscribe,$summary);
							$description = mysqli_real_escape_string($docketDataSubscribe,$description);
							$location = mysqli_real_escape_string($docketDataSubscribe,$location);
							$eventName = mysqli_real_escape_string($docketDataSubscribe,$eventName);
							$dateRule = mysqli_real_escape_string($docketDataSubscribe,$dateRuleTextValue);
							$courtRule = mysqli_real_escape_string($docketDataSubscribe,$courtRuleTextValue);		  
							//echo "Event ID=".$event_id;
							$insertSQL = "INSERT INTO import_events(authenticator,import_docket_id, event_id, event_docket, recalculate_flag, parent_system_id, system_id)  VALUES ('".$_SESSION['author_id']."',".$importDocketId.",'".$event_id."','".$eventDocket."','".$eventRecalc."','".$eventParentSystemID."','".$eventSystemID."')";
							$result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
							$import_event_id = mysqli_insert_id($docketDataSubscribe);
										//$import_event_id = $import_event_id +1;
						   if($eventParentSystemID > 0)
						   {
							  $query_importEvents = "select * from import_events where system_id = '".$eventParentSystemID."' AND import_docket_id = '".$importDocketId."' ";
							  $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
							  $totalRows_importEvents = mysqli_num_rows($ImportEvents);

								if($totalRows_importEvents > 0)
								{
								$query_update_importEvents = "UPDATE import_events SET has_child = 1 WHERE system_id = '".$eventParentSystemID."' AND import_docket_id = '".$importDocketId."' ";
								 $UpdateImportEvents = mysqli_query($docketDataSubscribe,$query_update_importEvents);
								}
						   }  else {

							  $query_importEvents = "select * from import_events where parent_system_id = '".$eventSystemID."' AND import_docket_id = '".$importDocketId."' ";
							  $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
							  $totalRows_importEvents = mysqli_num_rows($ImportEvents);

							  if($totalRows_importEvents > 0)
							  {
								$query_update_importEvents = "UPDATE import_events SET has_child = 1 WHERE system_id = '".$eventSystemID."' AND import_docket_id = '".$importDocketId."' ";
								 $UpdateImportEvents = mysqli_query($docketDataSubscribe,$query_update_importEvents);
							  }

						   }
							$insertSQLCases = "INSERT INTO case_events(import_event_id, event_date, short_name, description,remainder,location,status,eventtype,eventName,courtRule,dateRule) VALUES ('".$import_event_id."','".$date."','".$summary."','".$description."','".$reminder_minutes."','".$location."','".$status."','".$eventTypeDesc."','".$eventName."','".$courtRule."','".$dateRule."')";
							$resultCases = mysqli_query($docketDataSubscribe,$insertSQLCases) or trigger_error("Query Failed! SQL: $insertSQLCases - Error: ".mysqli_error(), E_USER_ERROR);
							
							$insertEventsTableData = "INSERT INTO events(eventIdvalue,user_id, emailid, caseid, 	triggerid,eventid,title,description,start_date,end_date,status,eventColor) VALUES ('".$import_event_id."','".$_SESSION['userid']."','".$_SESSION['author_id']."',".$case_id.",".$selectTriggerItem.",".$import_event_id.",'".$summary."','".$description."','".$startDate."','".$endDate."',1,'".$eventColor."')";
		$resultCases = mysqli_query($docketDataSubscribe,$insertEventsTableData) or trigger_error("Query Failed! SQL: $insertEventsTableData - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);

					}
											 $eve++;
											 $i++;
							} //for loop

						}
					}
										

								$responseHtml = "<span style='color:green;'>Successfully imported to your Google Calendar.</span>";
							   
							} else {
								$responseHtml = "<span style='color:red;'>Error Occured. Please Try Again.</a>";
							}
						}
					}
				}
			
			
			}/* USER ID LOOP END */	
		
			

		$query_importEvents = "SELECT e.event_id,i.calendar_id,i.access_token,i.trigger_item,i.attendees,i.case_id,c.event_date,c.short_name,e.import_event_id,c.description,c.remainder,c.location,c.status FROM docket_cases dc
		INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
		INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
		INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
		WHERE c.case_event_id = ".$case_event_id." ";
		
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);
		
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
		if($totalRows_importEvents > 0)
		{
			$row_events = mysqli_fetch_assoc($ImportEvents);
			$case_id = $row_events['case_id'];
			$insertEventCustomText =  "INSERT INTO docket_customtext  (user_id,case_id,	trigger_trigid,event_eventid,event_custometext,event_customtextlevel)VALUES (".$_SESSION['userid'].",".$case_id.",'".$row_events['trigger_item']."','".$case_event_id."','".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventCustomText'])."',1)";
			$ResultupdateEventCustomText = mysqli_query($docketDataSubscribe,$insertEventCustomText);
			$expTime = explode(" ", $_POST['event_date']);
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
					$startDate = $event_date;
					$endDate = $event_date;
			} else {
					$eventExplodeTime = str_replace(":", "", $eventTime);
					$start_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) );
					$end_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
					$event_date = "";
					$update_event_date = $expTime[0].' '.date('H:i:s', strtotime($eventExplodeTime) );
					$startDate = $start_time;
					$endDate = $end_time;
					$all_day = 0;
			}

			if($_POST['attendees'] != '')
			{
			  $attendees = $_POST['attendees'];
			} else {
			  $attendees = $row_events['attendees'];
			}
			$date_array = array("start_time" => $start_time, "end_time" => $end_time, "event_date" => $event_date);
			
			
			$summary = mysqli_real_escape_string($docketDataSubscribe,$row_events['short_name']);
			$description = mysqli_real_escape_string($docketDataSubscribe,$row_events['description']);
			 $remainder = mysqli_real_escape_string($docketDataSubscribe,$row_events['remainder']);
			$location = mysqli_real_escape_string($docketDataSubscribe,$row_events['location']);
			 $status = mysqli_real_escape_string($docketDataSubscribe,$row_events['status']);
			$event_id = $capi->UpdateCalendarEvent($row_events['event_id'],''.$row_events['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees,"Event Custom Comments : ".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventCustomText'])."<br/>".$description,$remainder,$eventColor,$location,$status);

			$updateSQL = "UPDATE case_events SET event_date = '".$update_event_date."'   WHERE case_event_id  = ".$case_event_id." ";
			$result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);
			
			$updateEventsSQL = "UPDATE events SET start_date = '".$startDate."',end_date = '".$endDate."'   WHERE eventIdvalue  = ".$case_event_id." ";
			$resultEvents = mysqli_query($docketDataSubscribe,$updateEventsSQL) or trigger_error("Query Failed! SQL: $updateEventsSQL - Error: ".mysqli_error(), E_USER_ERROR);
			
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
		}
		else{
			$result_html['html'] = "<span style='color:red;'>Error Occured. Please Try Again.</span>";
            echo json_encode($result_html);
		}
	}
	
	else 
	{
		
	/* LOOP For NORMAL Events Updatation */
	//NORMAL
		$result_html = array();
		require_once('../googleCalender/google-calendar-api.php');
			$capi = new GoogleCalendarApi();
			$user_timezone = $capi->GetUserCalendarTimezone($_SESSION['access_token']);
			$expTime = explode(" ", $event_date);
			$eventTime = $expTime[1];

		$query_importEvents = "SELECT e.event_id,i.calendar_id,i.trigger_item,i.access_token,i.attendees,i.import_docket_id,i.case_id,c.event_date,c.short_name,e.import_event_id,dc.caseEventColor,c.description,c.remainder,c.location,c.status FROM docket_cases dc
		INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
		INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
		INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
		WHERE c.case_event_id =  ".$case_event_id." ";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);
		
		
				
		
		if($totalRows_importEvents > 0)
		{
			$row_events = mysqli_fetch_assoc($ImportEvents);
			$case_id = $row_events['case_id'];
			$importDocketId = $row_events['import_docket_id'];
			$caseEventColor = $row_events['caseEventColor'];
			$insertEventCustomText =  "INSERT INTO docket_customtext  (user_id,case_id,	trigger_trigid,event_eventid,event_custometext,event_customtextlevel)VALUES (".$_SESSION['userid'].",".$case_id.",'".$row_events['trigger_item']."','".$case_event_id."','".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventCustomText'])."',1)";
			$ResultupdateEventCustomText = mysqli_query($docketDataSubscribe,$insertEventCustomText);
			
			$querygetupdateeventsdescInfo = "SELECT * FROM updateeventsdesc WHERE importdocketid = '".$importDocketId."' and caseid = ".$case_id."";
				$querygetupdateeventsdescInfoData = mysqli_query($docketDataSubscribe,$querygetupdateeventsdescInfo);
				$totalRowsupdateeventsdescInfo = mysqli_num_rows($querygetupdateeventsdescInfoData);
				if($totalRowsupdateeventsdescInfo > 0)
				{
					$row_getupdateeventsdesc = mysqli_fetch_assoc($querygetupdateeventsdescInfoData);
				}
			$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
				$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
				$totalRows_authoptionInfo = mysqli_num_rows($authOptionInfo);
				$row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo);
				
			if($totalRowsupdateeventsdescInfo > 0)
			{
				if($row_getupdateeventsdesc['eventColor'] != NULL || $row_getupdateeventsdesc['eventColor'] != 0)
				{
					$eventColor = $row_getupdateeventsdesc['eventColor'];
				}
			}else
			{
				$caseLeveleventColor = $caseEventColor;
				if($caseLeveleventColor == NULL || $caseLeveleventColor == 0)
				{
					$eventColor = $row_authOptionInfo['eventColor'];
				}else{
					$eventColor =$caseEventColor;
				}
			}
			
			
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
					$startDate = $event_date;
					$endDate = $event_date;
					$all_day = 1;
			} else {
					$eventExplodeTime = str_replace(":", "", $eventTime);
					$start_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) );
					$end_time =  $expTime[0].'T'.date('H:i:s', strtotime($eventExplodeTime) + 86400);
					$event_date = "";
					$update_event_date = $expTime[0].' '.date('H:i:s', strtotime($eventExplodeTime) );
					$startDate = $start_time;
					$endDate = $end_time;
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
			$event_id = $capi->UpdateCalendarEvent($row_events['event_id'],''.$row_events['calendar_id'].'', $summary, $all_day, $date_array, $user_timezone, $_SESSION['access_token'],$attendees,"Event Custom Comments : ".mysqli_real_escape_string($docketDataSubscribe,$_POST['eventCustomText'])."<br/>".$description,$remainder,$eventColor,$location,$status);

			$updateSQL = "UPDATE case_events SET event_date = '".$update_event_date."'   WHERE case_event_id  = ".$case_event_id." ";
			$result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);
			
			$updateEventsSQL = "UPDATE events SET start_date = '".$startDate."',end_date = '".$endDate."'   WHERE eventIdvalue  = ".$case_event_id." ";
			$resultEvents = mysqli_query($docketDataSubscribe,$updateEventsSQL) or trigger_error("Query Failed! SQL: $updateEventsSQL - Error: ".mysqli_error(), E_USER_ERROR);
		
			//$updateSQL2 = "UPDATE import_events SET event_id = '".$event_id."' WHERE import_event_id  = ".$row_events['import_event_id']." ";
			//$result2 = mysqli_query($docketDataSubscribe,$updateSQL2) or trigger_error("Query Failed! SQL: $updateSQL2 - Error: ".mysqli_error(), E_USER_ERROR);

			$selectCaseAttendees =  "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id." and eventlevel = 1 and eventid =".$case_event_id." ";
			$caseAttendee = mysqli_query($docketDataSubscribe,$selectCaseAttendees);
			$totalRows_attendees = mysqli_num_rows($caseAttendee);
			
			$updateEventModifiedBy =  "UPDATE  docket_case_triggerevent_mod SET event_modified_by = 
			'".$_SESSION['author_id']."',event_id='".$case_event_id."' WHERE case_id = ".$caseId." AND user_id=".
			$_SESSION['userid']." AND trigger_id='".$row_events['trigger_item']."'";
	
			$updateEventModifiedDetails = mysqli_query($docketDataSubscribe,$updateEventModifiedBy);
			
			
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
