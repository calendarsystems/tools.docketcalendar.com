<?php require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
$context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));

require '../globals/global_tools.php';
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


$colname_userInfo = "-1";
if (isset($_SESSION['userid'])) {
	$colname_userInfo = $_SESSION['userid'];
}

//echo "<pre>"; print_r($_SESSION);
$query_userInfo = sprintf("SELECT * FROM attorneys WHERE user_id = %s", GetSQLValueString($docketData,$colname_userInfo, "int"));
$userInfo = mysqli_query($docketData,$query_userInfo);
$row_userInfo = mysqli_fetch_assoc($userInfo);
$totalRows_userInfo = mysqli_num_rows($userInfo);

$query_authoptionInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
$authOptionInfo = mysqli_query($docketDataSubscribe,$query_authoptionInfo);
$totalRows_authoptionInfo = mysqli_num_rows($authOptionInfo);
$row_authOptionInfo = mysqli_fetch_assoc($authOptionInfo);
$do_not_recalculate_events_val = $row_authOptionInfo['do_not_recalculate_events'];


$sort_date = '';
$events = '';


    $query_importEvents = "SELECT events from exclude_events where jurisdiction = ".$_POST['cmbJurisdictions']." and trigger_item = ".$_POST['cmbTriggers']."";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);

    if($totalRows_importEvents > 0)
    {
      $fetch_importEvents = mysqli_fetch_assoc($ImportEvents);
      $events = $fetch_importEvents['events'];
    }


$newURL = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

$selectJurisdiction = 0;
$selectTriggerItem = 0;
$selectServiceType = 0;

if (isset($_POST['cmbJurisdictions'])) {
	$masterCourt = $_POST['cmbJurisdictions'];
	setcookie("masterCookie", $_POST['cmbJurisdictions'], time() + (60 * 60 * 24), "/");
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

if (isset($_POST['cmbJurisdictions'])) {
	$selectJurisdiction = $_POST['cmbJurisdictions'];
}
if (isset($_POST['cmbTriggers'])) {
	$selectTriggerItem = $_POST['cmbTriggers'];
}
if (isset($_POST['isTimeRequired'])) {
	$isTimeRequired = $_POST['isTimeRequired'];
}
if (isset($_POST['selectServiceType'])) {
	$selectServiceType = $_POST['selectServiceType'];
}
if (isset($_POST['isServed'])) {
	$isServed = $_POST['isServed'];
}
if (isset($_POST['sort'])) {
	$sort = $_POST['sort'];
	setcookie("sortDateCookie", $_POST['sort'], time() + (60 * 60 * 24 * 3), "/");
} else if($sort_date != ''){
    $sort = $sort_date;
    setcookie("sortDateCookie", $sort_date, time() + (60 * 60 * 24 * 3), "/");
} else {
	$sort = $_COOKIE['sortDateCookie'];
}

if ($selectJurisdiction != 0) {
//    echo "juris good";
	if ($selectTriggerItem != 0) {
		//    echo "trigger good";
		if (($isServed == "Y" && $selectServiceType) || $isServed != "Y") {
			//    echo "service good";
			if (($isTimeRequired == "Y" && $_POST['txtTime'] != "") || $isTimeRequired != "Y") {

				$formDate = $_POST['txtTriggerDate'];
				$triggerDate = substr($formDate, 6, 4) . "-" . substr($formDate, 0, 2) . "-" . substr($formDate, 3, 2);

				$xml = '<CalculationParameters xmlns="http://schemas.datacontract.org/2004/07/CRC.WCFService.Objects">
                    <Associations/>
                    <EventSystemID>0</EventSystemID>
                    <Events/>
                    <JurisdictionSystemID>' . $selectJurisdiction . '</JurisdictionSystemID>
                    <ServiceTypeSystemID>' . $selectServiceType . '</ServiceTypeSystemID>
                    <TriggerDate>' . $triggerDate . 'T' . '12:00:00' . '</TriggerDate>
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

				if ($_POST['cmbMatter'] != "") {
					//$matterString = "(" . $_POST['cmbMatter'] . ") ";
				} else {
					$matterString = "";
				}

			} else {
				$error = "service";
			}
		}
	}
}
 //echo "<pre>";
 //print_r($response);exit();
?>


    <?php
$result_html = array();

$montharray = array(
	"01" => "January",
	"02" => "February",
	"03" => "March",
	"04" => "April",
	"05" => "May",
	"06" => "June",
	"07" => "July",
	"08" => "August",
	"09" => "September",
	"10" => "October",
	"11" => "November",
	"12" => "December",
);

$x = 0;
$single = $response;

//echo '<pre>'; print_r($single); echo '</pre>';
	if (isset($single['Action'])) {
			    $numresults = 1;
			} else {
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
			}
			$result_html['count'] = $numresults;
		if ($numresults > 0) {
				$resultOutput = "";
				$resultOutput.="<table class='reviewdata'>";
				$resultOutput.="<tr><td class='reviewheader'>Events</td></tr>";
    $eventResultsArray = array();
    $alreadyExistMessage = 0;

    if (isset($single['Action'])) {
        $selected = '';
        $justdate = substr($response['CalendarRuleEvent']['EventDate'], 0, 10);
        $mo = substr($justdate, 5, 2);

        $sysID = $response['CalendarRuleEvent']['SystemID'];
        $alreadyExist = 0;
        $explode_event_date = explode("T",$response['CalendarRuleEvent']['EventDate']);
        $event_specific_time = '';
        if($explode_event_date[1] != "00:00:00")
        {
           $event_specific_time =  $explode_event_date[1];
        }
        if (array_key_exists($sysID,$existEvents))
        {
          $eveDate = substr($justdate, 0, 4).'-'.substr($justdate, 5, 2).'-'.substr($justdate, 8, 2);
          if($existEvents[$sysID] == $eveDate)
          {
            $alreadyExist = 1;
            $alreadyExistMessage = 1;
          }
        }

        if(@$_POST['import_docket_id'] != '')
        {
           $selected = " checked='checked' ";
        }
        $style = '';
        if($alreadyExist == 1) { $style = "color:red;"; }
		$resultOutput.= '<tr><td>';
        /*$result .= '
        <tr><td>
            '. $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';*/

        $resultOutput .= $response['CalendarRuleEvent']['ShortName'];
		$resultOutput .='<br> Date Rule:';
        if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
            $resultOutput .= $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
        } else {
            foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                $resultOutput .= $Rule['RuleText'];
            }
        }
		$resultOutput .='</br>';
        $resultOutput .= '</td></tr>';
        $eventParentSystemID = $response['CalendarRuleEvent']['ParentSystemID'];
        $eventSystemID = $response['CalendarRuleEvent']['SystemID'];
        $eventResultsArray[] = $eventSystemID;
    } else {
        // IF THERE ARE MULTIPLE EVENTS
        $eve = 1;
        $selected_child = '';
		$events_array = unserialize($events); 
        $selectedEvents = 0;
        foreach ($response as $Event) {
		
          $sysID = $Event['CalendarRuleEvent']['SystemID'];
          $alreadyExist = 0;
           if(in_array($sysID,$events_array))
          {
            $justdate = substr($Event['CalendarRuleEvent']['EventDate'], 0, 10);
            $mo = substr($justdate, 5, 2);
              $explode_event_date = explode("T",$Event['CalendarRuleEvent']['EventDate']);
            $event_specific_time = '';
            if($explode_event_date[1] != "00:00:00")
            {
               $event_specific_time =  $explode_event_date[1];
            }
            if (array_key_exists($sysID,$existEvents))
           {
              $eveDate = substr($justdate, 0, 4).'-'.substr($justdate, 5, 2).'-'.substr($justdate, 8, 2);
              if($existEvents[$sysID] == $eveDate)
              {
                 $alreadyExist = 1;
                 $alreadyExistMessage = 1;
              }
           }

            if(@$_POST['import_docket_id'] != '' && $events != '') {
                $events_array = unserialize($events);
                if(in_array($eve,$events_array)) {
                  $selected_child = " checked='checked' ";
                } else {
                  $selected_child = "";
                }
            }
            $sysID = $Event['CalendarRuleEvent']['SystemID'];
            $parentID = $Event['CalendarRuleEvent']['ParentSystemID'];
            $style = '';
			$resultOutput .= '<tr><td>';
            /*$result .= '<tr><td>' . $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';*/

                                $resultOutput .= $Event['CalendarRuleEvent']['ShortName'];
								$resultOutput .='<br> Date Rule:';
                                if (isset($Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
                                    $resultOutput .= $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
                                } else {
                                    foreach ($Event['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                                        $resultOutput .= $Rule['RuleText'];
                                    }
                                }
								$resultOutput .='</br>';
            $resultOutput .= '</td></tr>';
            $eventDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
            $eventParentSystemID = $Event['CalendarRuleEvent']['ParentSystemID'];
            $eventSystemID = $Event['CalendarRuleEvent']['SystemID'];
            $eventResultsArray[] = $eventSystemID;
            $eve++;
            $selectedEvents++;
          }
        }    
    }
    $resultOutput.= '</table>';
}
    echo $resultOutput;

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


