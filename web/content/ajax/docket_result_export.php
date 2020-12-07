<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1); 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/settings.php');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '128M');
set_time_limit(300);
$login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
session_set_cookie_params(3600,"/");
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


//echo "<pre>"; print_r($_POST); exit();
//print_r($_SESSION);
$colname_userInfo = "-1";
if (isset($_SESSION['userid'])) {
	$colname_userInfo = $_SESSION['userid'];
}

$query_userInfo = "SELECT * FROM attorneys WHERE user_id = ".$colname_userInfo."";
$userInfo = mysqli_query($docketDataSubscribe,$query_userInfo);
$row_userInfo = mysqli_fetch_assoc($userInfo);
$totalRows_userInfo = mysqli_num_rows($userInfo);

$newURL = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

$selectJurisdiction = 0;
$selectTriggerItem = 0;
$selectServiceType = 0;

if (isset($_POST['hidden_cmbJurisdictions_val'])) {
	$masterCourt = $_POST['hidden_cmbJurisdictions_val'];
	setcookie("masterCookie", $_POST['hidden_cmbJurisdictions_val'], time() + (60 * 60 * 24), "/");
} else {
	$masterCourt = $_COOKIE['masterCookie'];
}

if (isset($_SESSION['userid'])) {
    $command = "/users/" . $_SESSION['username'] . "?";
    $parameters = "password=" . $_SESSION['password'] . "&soapREST=REST";
    $file = $newURL . $command . $parameters;

    $content = file_get_contents($newURL . $command . $parameters, false, $context);
    $xml = $content;
    $array = xml2array($xml);

    $loginToken = $array['string'];

}

if (isset($_POST['hidden_cmbJurisdictions_val'])) {
	$selectJurisdiction = $_POST['hidden_cmbJurisdictions_val'];
}
if (isset($_POST['hidden_cmbTriggers_val'])) {
	$selectTriggerItem = $_POST['hidden_cmbTriggers_val'];
}
if (isset($_POST['hidden_isTimeRequired_val'])) {
	$isTimeRequired = $_POST['hidden_isTimeRequired_val'];
}
if (isset($_POST['hidden_selectServiceType_val'])) {
	$selectServiceType = $_POST['hidden_selectServiceType_val'];
}
if (isset($_POST['hidden_isServed_val'])) {
	$isServed = $_POST['hidden_isServed_val'];
}
 $events_array = $_POST['hidden_eventarray_val'];
 $events_array = (explode(",",$events_array));

//echo "<pre>";print_r($_POST);exit();
if ($selectJurisdiction != 0) {
//    echo "juris good";
	if ($selectTriggerItem != 0) {
		//    echo "trigger good";
		if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
			//    echo "service good";
			if (($isTimeRequired == "Y" && $_POST['hidden_txtTime_val'] != "") || $isTimeRequired != "Y") { 
				$formDate = $_POST['hidden_txtTriggerDate_val'];
				$triggerDate = substr($formDate, 6, 4) . "-" . substr($formDate, 0, 2) . "-" . substr($formDate, 3, 2);

				$xml = '<CalculationParameters xmlns="http://schemas.datacontract.org/2004/07/CRC.WCFService.Objects">
                    <Associations/>
                    <EventSystemID>0</EventSystemID>
                    <Events/>
                    <JurisdictionSystemID>' . $selectJurisdiction . '</JurisdictionSystemID>
                    <ServiceTypeSystemID>0</ServiceTypeSystemID>
                    <TriggerDate>' . $triggerDate . 'T' . $_POST['hidden_txtTime_val'] . '</TriggerDate>
                    <TriggerItemSystemID>' . $selectTriggerItem . '</TriggerItemSystemID>
                    <Twins/>
                    </CalculationParameters>';
					
						

				//echo $xml;
				//exit();
				$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
                $url = $uri . "compute/dates?loginToken=" . $loginToken;
                //$url=$uri."compute/dates";
				//echo   $url;
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
				 //echo "<pre>"; print_r($response); exit();
				$debuginfo = array('url' => $url, 'xml' => $xml, 'response' => $response);
				
				// EXPORT TO EXCEL
				//if (!empty($_POST['hidden_cmbJurisdictions_val'])) {
				if($_POST['hidden_excelData'] == "Excel")
				{
						$JURISDICTIONTEXT = strip_tags(trim($_POST['hidden_JurisdictionsText_val']));
						$TRIGGERTEXT = strip_tags(trim($_POST['hidden_TriggersText_val']));
						$CASETEXT = strip_tags(trim($_POST['hidden_cmbMatterText_val']));
						$SERVICETEXT = strip_tags(trim($_POST['hidden_selectServiceText_val']));
						$TIGGERTIME  = $_POST['hidden_txtTriggerDate_val'].' '.$_POST['hidden_txtTime_val'];
						$CaseInfo ="Jurisdiction:\t $JURISDICTIONTEXT\t";
						$CaseInfo=$CaseInfo."\n";
						$CaseInfo =$CaseInfo."Trigger:\t $TRIGGERTEXT\t";
						$CaseInfo=$CaseInfo."\n";
						$CaseInfo =$CaseInfo."Trigger(Date&Time):\t $TIGGERTIME  \t";
						$CaseInfo=$CaseInfo."\n";
						$CaseInfo =$CaseInfo."Case:\t $CASETEXT\t";
						$CaseInfo=$CaseInfo."\n";
						$CaseInfo =$CaseInfo."Service:\t $SERVICETEXT \t";
						$CaseInfo=$CaseInfo."\n\n";
						
						$xlsfile = "Event_Date\t Event_Type\t Shortname\t Date_Rule\t Court_Rule\t \n";
						
						$headerinfo = "";
						$ruleinfo = "";
						$dateruleinfo = "";

						$a = 0;

						$filename = "GoogleDocketData.xls";

						if (isset($response['Action'])) {
							
							$headerinfo = str_replace('T', '_', $response['CalendarRuleEvent']['EventDate']) . "\t";
							$headerinfo =$headerinfo.$headeEventDate;
							$headerinfo = $headerinfo.$response['CalendarRuleEvent']['EventType']['Description']. "\t";
							//
							//$headerinfo = $headerinfo . $_POST['hidden_cmbMatter_val'] . "\t";
							$headerinfo = $headerinfo.$response['CalendarRuleEvent']['ShortName'] . "\t";
							
							if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
								$dateRuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText']);
								$dateRuleText = str_replace("\r", " ", $dateRuleText);

								$dateruleinfo = $dateRuleText;

							} else {
								$dateruleinfo = "";
								foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
									$dateRuleText = str_replace("\n", " ", $Rule['RuleText']);
									$dateRuleText = str_replace("\r", " ", $dateRuleText);

									$dateruleinfo = $dateruleinfo . $dateRuleText . ";";
								}
								$dateruleinfo = substr($dateruleinfo, 0, strlen($dateruleinfo1 - 1));
							}
							
							$headerinfo = $headerinfo . $dateruleinfo . "\t";
							
							if (isset($response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
								$RuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText']);
								$RuleText = str_replace("\r", " ", $RuleText);

								$ruleinfo = $RuleText;

							} else {
								$ruleinfo = "";
								foreach ($response['CalendarRuleEvent']['CourtRules']['Rule'] as $Rule) {
									$RuleText = str_replace("\n", " ", $Rule['RuleText']);
									$RuleText = str_replace("\r", " ", $RuleText);

									$ruleinfo = $ruleinfo . $RuleText . ";";
								}
								$ruleinfo = substr($ruleinfo, 0, strlen($ruleinfo) - 1);
							}
							$xlsfile = $CaseInfo . $xlsfile . $headerinfo . $ruleinfo . "\r";
							
						} else {
						// MULTIPLE EVENTS

                        $icalevents = "";
                        $eve = 1;
                        $systemID = '';

						foreach ($response as $Event) {
	
                            $parentID = $Event[CalendarRuleEvent][ParentSystemID];
                            $sysID = $Event[CalendarRuleEvent][SystemID];

                         if(in_array($sysID,$events_array))
                         {
                            if($parentID == 0)
                            {
                              $systemID = $Event[CalendarRuleEvent][SystemID];
                            } else {
                              $systemID = $Event[CalendarRuleEvent][ParentSystemID];
                            }

							$headerinfo = str_replace('T', ' ', $Event[CalendarRuleEvent][EventDate]) . "\t";
							$headerinfo = $headerinfo.$Event[CalendarRuleEvent][EventType][Description]. "\t";
							$headerinfo = $headerinfo . $Event[CalendarRuleEvent][ShortName] . "\t";

							if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
								$dateRuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][DateRules][Rule][RuleText]);
								$dateRuleText = str_replace("\r", " ", $dateRuleText);

								$dateruleinfo = $dateRuleText;
								

							} else {
								$dateruleinfo = "";
								foreach ($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
									$dateRuleText = str_replace("\n", " ", $Rule[RuleText]);
									$dateRuleText = str_replace("\r", " ", $dateRuleText);

									$dateruleinfo = $dateruleinfo . $dateRuleText . ";";
								}
								$dateruleinfo = substr($dateruleinfo, 0, strlen($dateinfo) - 1);
							}
							$headerinfo = $headerinfo . $dateruleinfo . "\t";

							if (isset($Event[CalendarRuleEvent][CourtRules][Rule][RuleText])) {
								$RuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][CourtRules][Rule][RuleText]);
								$RuleText = str_replace("\r", " ", $RuleText);

								$ruleinfo = $RuleText;

							} else {
								$ruleinfo = "";
								foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
									$RuleText = str_replace("\n", " ", $Rule[RuleText]);
									$RuleText = str_replace("\r", " ", $RuleText);

									$ruleinfo = $ruleinfo . $RuleText . ";";
								}
								$ruleinfo = substr($ruleinfo, 0, strlen($ruleinfo) - 1);
							}
							$xlsfile = $xlsfile . $headerinfo . $ruleinfo . "\r";
							$eve++;
                         }
                          
						}
						$xlsfile = $CaseInfo . $xlsfile;
					}

						header('Content-type: application/vnd.ms-excel');
						header('Content-Disposition: attachment; filename=' . $filename);
						echo $xlsfile;
						exit();
					
				}	
				if($_POST['hidden_csvData'] == "CSV")
				{
					
						$filename = "GoogleDocketData.csv"; 
						header("Content-Description: File Transfer"); 
						header("Content-Disposition: attachment; filename=$filename"); 
						header("Content-Type: application/csv; ");					
						$file = fopen('php://output','w');
						$JURISDICTIONTEXT = strip_tags(trim($_POST['hidden_JurisdictionsText_val']));
						$JURIData = array("Jurisdiction : ",$JURISDICTIONTEXT); 
						fputcsv($file, $JURIData);
						$TRIGGERTEXT = strip_tags(trim($_POST['hidden_TriggersText_val']));
						$TRIGGERData = array("Trigger : ",$TRIGGERTEXT); 
						fputcsv($file, $TRIGGERData);
						$TIGGERTIME  = $_POST['hidden_txtTriggerDate_val'].' '.$_POST['hidden_txtTime_val'];
						$TRIGGERTIMEData = array("Trigger (DATE & TIME): ",$TIGGERTIME); 
						fputcsv($file, $TRIGGERTIMEData);
						$CASETEXT = strip_tags(trim($_POST['hidden_cmbMatterText_val']));
						$CASEData = array("Case : ",$CASETEXT); 
						fputcsv($file, $CASEData);
						$SERVICETEXT = strip_tags(trim($_POST['hidden_selectServiceText_val']));
						$SERVICEData = array("Service : ",$SERVICETEXT); 
						fputcsv($file, $SERVICEData);
						$header=array("Event_Date","Event_Type","Shortname","Date_Rule","Court_Rule");
						fputcsv($file, $header);
						$headerinfo = "";
						$ruleinfo = "";
						$dateruleinfo = "";
						

						$a = 0;
						if (isset($response['Action'])) {
							
							$header1 = str_replace('T', '_', $response['CalendarRuleEvent']['EventDate']) . "\t";
							$headerinfo =$headerinfo.$headeEventDate;
							$header2 = $headerinfo.$response['CalendarRuleEvent']['EventType']['Description']. "\t";
							//
							//$headerinfo = $headerinfo . $_POST['hidden_cmbMatter_val'] . "\t";
							$header3 = $headerinfo.$response['CalendarRuleEvent']['ShortName'] . "\t";
							
							if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
								$dateRuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText']);
								$dateRuleText = str_replace("\r", " ", $dateRuleText);

								$header4 = $dateRuleText;

							} else {
								$dateruleinfo = "";
								foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
									$dateRuleText = str_replace("\n", " ", $Rule['RuleText']);
									$dateRuleText = str_replace("\r", " ", $dateRuleText);

									$dateruleinfo = $dateruleinfo . $dateRuleText . ";";
								}
								$header4 = substr($dateruleinfo, 0, strlen($dateruleinfo1 - 1));
							}
							
							$headerinfo = $headerinfo . $dateruleinfo . "\t";
							
							if (isset($response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
								$RuleText = str_replace("\n", " ", $response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText']);
								$RuleText = str_replace("\r", " ", $RuleText);

								$header5 = $RuleText;

							} else {
								$ruleinfo = "";
								foreach ($response['CalendarRuleEvent']['CourtRules']['Rule'] as $Rule) {
									$RuleText = str_replace("\n", " ", $Rule['RuleText']);
									$RuleText = str_replace("\r", " ", $RuleText);

									$ruleinfo = $ruleinfo . $RuleText . ";";
								}
								$header5 = substr($ruleinfo, 0, strlen($ruleinfo) - 1);
							}
							$xlsfile = $CaseInfo . $xlsfile . $headerinfo . $ruleinfo . "\r";
							$data=array($header1,$header2,$header3,$header4,$header5);
							fputcsv($file, $data);
						} else {
						// MULTIPLE EVENTS

                        $icalevents = "";
                        $eve = 1;
                        $systemID = '';

						foreach ($response as $Event) {
	
                            $parentID = $Event[CalendarRuleEvent][ParentSystemID];
                            $sysID = $Event[CalendarRuleEvent][SystemID];

                         if(in_array($sysID,$events_array))
                         {
                            if($parentID == 0)
                            {
                              $systemID = $Event[CalendarRuleEvent][SystemID];
                            } else {
                              $systemID = $Event[CalendarRuleEvent][ParentSystemID];
                            }

							$header1 = str_replace('T', ' ', $Event[CalendarRuleEvent][EventDate]) . "\t";
							$header2 = $headerinfo.$Event[CalendarRuleEvent][EventType][Description]. "\t";
							$header3 = $headerinfo . $Event[CalendarRuleEvent][ShortName] . "\t";

							if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
								$dateRuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][DateRules][Rule][RuleText]);
								$dateRuleText = str_replace("\r", " ", $dateRuleText);

								$header4 = $dateRuleText;
								

							} else {
								$dateruleinfo = "";
								foreach ($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
									$dateRuleText = str_replace("\n", " ", $Rule[RuleText]);
									$dateRuleText = str_replace("\r", " ", $dateRuleText);

									$dateruleinfo = $dateruleinfo . $dateRuleText . ";";
								}
								$header4 = substr($dateruleinfo, 0, strlen($dateinfo) - 1);
							}
							$headerinfo = $headerinfo . $dateruleinfo . "\t";

							if (isset($Event[CalendarRuleEvent][CourtRules][Rule][RuleText])) {
								$RuleText = str_replace("\n", " ", $Event[CalendarRuleEvent][CourtRules][Rule][RuleText]);
								$RuleText = str_replace("\r", " ", $RuleText);

								$header5 = $RuleText;

							} else {
								$ruleinfo = "";
								foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
									$RuleText = str_replace("\n", " ", $Rule[RuleText]);
									$RuleText = str_replace("\r", " ", $RuleText);

									$ruleinfo = $ruleinfo . $RuleText . ";";
								}
								$header5 = substr($ruleinfo, 0, strlen($ruleinfo) - 1);
							}
							$xlsfile = $xlsfile . $headerinfo . $ruleinfo . "\r";
							$eve++;
                         }
							$data=array($header1,$header2,$header3,$header4,$header5);
							fputcsv($file, $data);
						}
						$xlsfile = $CaseInfo . $xlsfile;
						
					}
						fclose($file); 
						exit; 
					
				}
			
				if($_POST['hidden_iCalData'] == "iCal")
				{
					
					
					$icaltop = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
					$icalend = "\nEND:VCALENDAR";
					$filename = "GoogleDocketCalcDate.ics";
					$ruleinfo = "";
					$dateruleinfo = "";

					$a = 0;
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
									$icalevents = "BEGIN:VEVENT
								UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
								DTSTAMP:" . $date . "
								DTSTART;VALUE=DATE:" . $date . "
								DTEND;VALUE=DATE:" . date('Ymd', strtotime($date) + 86400) . 'T000000' . "
								CLASS:PUBLIC
								SUMMARY:" . $summary . "
								DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
								END:VEVENT";
														} else {
															$icalevents = "BEGIN:VEVENT
								UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
								DTSTAMP:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
								DTSTART:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
								CLASS:PUBLIC
								SUMMARY:" . $summary . "
								DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
								END:VEVENT";
														}
							
						} else {
						$icalevents = "";

                        $eve = 1;
                        $systemID = '';
						foreach ($response as $Event) {

                            $parentID = $Event[CalendarRuleEvent][ParentSystemID];
                            $sysID = $Event[CalendarRuleEvent][SystemID];

                           if(in_array($sysID,$events_array) || ($systemID == $parentID))
                         {
                            if($parentID == 0)
                            {
                              $systemID = $Event[CalendarRuleEvent][SystemID];
                            } else {
                              $systemID = $Event[CalendarRuleEvent][ParentSystemID];
                            }

							$date = str_replace("-", "", $Event[CalendarRuleEvent][EventDate]);
							$expTime = explode("T", $date);
							$date = str_replace(":", "", $date);

							$eventTime = $expTime[1];
							$eventExplodeTime = str_replace(":", "", $eventTime);

							$summary = $matterString . $headerinfo . $Event[CalendarRuleEvent][ShortName];

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
								// multiple court rules
							} else {
								$courtRuleAll = "";
								foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
									$courtRuleText = str_replace("\n", " ", $Rule[RuleText]);
									$courtRuleText = str_replace("\r", " ", $courtRuleText);

									$courtRuleAll = $courtRuleAll . $courtRuleText . ";";
								}
								$courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
							}
							if ($eventTime == "00:00:00") {
								$icalevents = $icalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . $date . "
DTSTART;VALUE=DATE:" . $date . "
DTEND;VALUE=DATE:" . date('Ymd', strtotime($date) + 86400) . 'T000000' . "
CLASS:PUBLIC
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
							} else {
								$icalevents = $icalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
DTSTART:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
CLASS:PUBLIC
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
							}

                           }
						}

					}
					$icalfile = $icaltop . $icalevents . $icalend;
					header('Content-type: text/calendar; charset=utf-8');
					header('Content-Disposition: inline; filename=' . $filename);
					echo $icalfile;
					exit();
				}
				if($_POST['hidden_outllookData'] == "outlook")
				{
$vcaltop = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN";
$vcalend = "\nEND:VCALENDAR";
					$ruleinfo = "";
					$dateruleinfo = "";

					$a = 0;

					$filename = "GoogleDocketCalcDate.vcs";

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
                            $vcalevents = $vcalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . $date . "
DTSTART:" . $date . "
DTEND:" . date('Ymd', strtotime($date) + 86400).'T00000'. "
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
                } else {
                   $s_time = date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime;
                   $e_time = strtotime($s_time.'+1 hour');
                   $vcalevents = $vcalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
DTSTART:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
DTEND:" . date('Ymd', strtotime($date)) . 'T' . date('His',$e_time). "
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
                }
						// multiple events
					} else {
						$vcalevents = "";
                        $eve = 1;
                        $systemID = '';

						foreach ($response as $Event) {
                          $parentID = $Event[CalendarRuleEvent][ParentSystemID];
                           $sysID = $Event[CalendarRuleEvent][SystemID];

                           if(in_array($sysID,$events_array) || ($systemID == $parentID))
                         {
                            if($parentID == 0)
                            {
                              $systemID = $Event[CalendarRuleEvent][SystemID];
                            } else {
                              $systemID = $Event[CalendarRuleEvent][ParentSystemID];
                            }
							$date = str_replace("-", "", $Event[CalendarRuleEvent][EventDate]);
                            $expTime = explode("T", $date);
							$date = str_replace(":", "", $date);

                            $eventTime = $expTime[1];
                            $eventExplodeTime = str_replace(":", "", $eventTime);

							$summary = $matterString . $headerinfo . $Event[CalendarRuleEvent][ShortName];

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
								// multiple court rules
							} else {
								$courtRuleAll = "";
								foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $Rule) {
									$courtRuleText = str_replace("\n", " ", $Rule[RuleText]);
									$courtRuleText = str_replace("\r", " ", $courtRuleText);

									$courtRuleAll = $courtRuleAll . $courtRuleText . ";";
								}
								$courtRuleText = substr($courtRuleAll, 0, strlen($courtRuleAll) - 1);
							}
                if ($eventTime == "00:00:00") {
							$vcalevents = $vcalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . $date . "
DTSTART:" . $date . "
DTEND:" . date('Ymd', strtotime($date) + 86400).'T00000'. "
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
                } else {
                   $s_time = date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime;
                   $e_time = strtotime($s_time.'+1 hour');
                   $vcalevents = $vcalevents . "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@calendarrules.com
DTSTAMP:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
DTSTART:" . date('Ymd', strtotime($date)) . 'T' . $eventExplodeTime . "
DTEND:" . date('Ymd', strtotime($date)) . 'T' . date('His',$e_time). "
SUMMARY:" . $summary . "
DESCRIPTION:" . $dateRuleText . " " . $courtRuleText . "
END:VEVENT";
                }

                        }
					} // for loop

					}

					$vcalfile = $vcaltop . $vcalevents . $vcalend;

					header('Content-type: text/calendar; charset=utf-8');
					header('Content-Disposition: inline; filename=' . $filename);
					echo $vcalfile;
					exit();
				}
				//}

			} else {
				$error = "service";
			}
		}
	}
}

function sort_by_date($a, $b) {
	return strcmp($a['CalendarRuleEvent']['EventDate'], $b['CalendarRuleEvent']['EventDate']);
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