<?php require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
$context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));

require '../globals/global_tools.php';

$result .='<style>
.tree,
.tree ul {
  margin:0 0 0 1em; /* indentation */
  padding:0;
  list-style:none;
  position:relative;
}

.tree ul {margin-left:.5em;left: auto !important;} /* (indentation/2) */

.tree:before,
.tree ul:before {
  content:"";
  display:block;
  width:0;
  position:absolute;
  top:0;
  bottom:0;
  left:0;
  border-left:1px solid;
}

.tree li {
  margin:0;
  padding:0 1.5em;
  line-height:2em;
  position:relative;
}

.tree li:before {
  content:"";
  display:block;
  width:10px; /* same with indentation */
  height:0;
  border-top:1px solid;
  margin-top:-1px; /* border top width */
  position:absolute;
  top:1em; /* (line-height/2) */
  left:0;
}

.tree li:last-child:before {
  background:white; /* same with body background */
  height:auto;
  top:1em; /* (line-height/2) */
  bottom:0;
}

</style>';

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

$sort_date = '';
$events = '';

if(@$_POST['import_docket_id'] != '')
{
    $query_importEvents = "SELECT i.* FROM import_docket_calculator as i
    INNER JOIN courts as c ON c.systemID = i.jurisdiction
    WHERE i.import_docket_id = ".$_POST['import_docket_id']." ";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);

    if($totalRows_importEvents > 0)
    {
      $fetch_importEvents = mysqli_fetch_assoc($ImportEvents);

      $sort_date = $fetch_importEvents['sort_date'];
      $events = $fetch_importEvents['events'];
    }
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
                    <TriggerDate>' . $triggerDate . 'T' . $_POST['txtTime'] . '</TriggerDate>
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

				if (!isset($response[Action])) {
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
//echo '<pre>'; print_r($response); exit();
$result_html['count'] = $numresults;


if ($_COOKIE['sortDateCookie'] != '') {$sort_date = $_COOKIE['sortDateCookie'];} else { $sort_date = 2;}



if ($numresults > 0) {
	if ($numresults > 1) {
        $result .= '<div><input type="checkbox" onchange="checkAll(this)"/>Select All</div>&nbsp;&nbsp;&nbsp;<div id="asc_link" style="margin-top: -45px;padding-left: 132px;"><a href="javascript:void(0);" onclick="javascript:parent.sort_date(1);">Sort by Date <img src="wp-content/themes/calendarrules/images/arrow-up.gif"></a></div>
        <div id="desc_link" style="margin-top: -45px;padding-left: 132px;display:none;"><a href="javascript:void(0);" onclick="javascript:parent.sort_date(2);">Sort by Date <img src="wp-content/themes/calendarrules/images/arrow-down.gif"></a></div>&nbsp;&nbsp;&nbsp;<div style="margin-top: -45px;padding-left: 270px;"><a href="javascript:void(0);" onclick="javascript:parent.PrintResult();">Print this result</a>
        </div>';
        $result .= '<div style="margin-top: -19px;padding-left: 410px;"><h4>RESULTS (' . $numresults . ')</h4></div>';
        $result .= '<div style="margin-top: -32px;padding-left: 520px;"><a href="javascript:void(0);" onclick="javascript:parent.normal_view();">show normal view</a></div>';
	} else {
        $result .= '<div><h4>RESULTS (' . $numresults . ')</h4></div>';
		$result .= '&nbsp;&nbsp;<div style="margin-top: -52px;padding-left: 180px;margin-bottom: 20px;"><a href="javascript:void(0);" onclick="javascript:parent.PrintResult();">Print this result</a></div>';
	}



	$result .= '<div id="show_results_list"><span id="show_search_term" style="display:none;">Jurisdiction:<span id="show_jurisdiction_print" style="font-weight: bold;"></span><br>Trigger Item: <span id="show_trigger_print" style="font-weight: bold;"></span><br>Trigger Date <b>: ' . $triggerDate . ' ' . $_POST['txtTime'] . '</b><br><span id="show_service_print"></span><br>';
	if ($matterString != '') {
		$result .= 'Matter: ' . $matterString . '';
	}
	$result .= '<br><br></span>
  <table class="triggers">';
    $eventResultsArray = array();


		// IF THERE ARE MULTIPLE EVENTS

		$result .= '<tr><td valign="top"><div  style="padding-top: 25px;">';
        $eve = 1;
        $selected_child = '';

		foreach ($response as $Event) {

            if(@$_POST['import_docket_id'] != '' && $events != '') {
                $events_array = unserialize($events);
                if(in_array($eve,$events_array)) {
                  $selected_child = " checked='checked' ";
                } else {
                  $selected_child = "";
                }
            }

			$justdate = substr($Event[CalendarRuleEvent][EventDate], 0, 10);
			$mo = substr($justdate, 5, 2);

            $justdate = substr($Event[CalendarRuleEvent][EventDate], 0, 10);
            $mo = substr($justdate, 5, 2);

            $explode_event_date = explode("T",$Event[CalendarRuleEvent][EventDate]);
            $event_specific_time = '';
            if($explode_event_date[1] != "00:00:00")
            {
               $event_specific_time =  $explode_event_date[1];
            }

            $sysID = $Event[CalendarRuleEvent][SystemID];
            $eventDocket = $Event[CalendarRuleEvent][IsEventDocket];
            $eventParentSystemID = $Event[CalendarRuleEvent][ParentSystemID];
            $eventSystemID = $Event[CalendarRuleEvent][SystemID];
            $eventResultsArray[] = $eventSystemID;
			$eventIsOnDNCList = $Event[CalendarRuleEvent][IsOnDNCList];


            $tree_array[$eventSystemID]['SystemID'] =  $eventSystemID;
            $tree_array[$eventSystemID]['ParentSystemID'] =  $eventParentSystemID;
			$tree_array[$eventSystemID]['IsOnDNCList'] =  $eventIsOnDNCList;
            $tree_array[$eventSystemID]['ShortName'] =  $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - '. $Event[CalendarRuleEvent][ShortName];


			//$result .= '<ul class="triggersnav" style="padding: 0 0 0 15px !important;"><li><input type="checkbox" class="evenetClass" name="events[]" '.$selected_child.' value="'.$sysID.'" style="float: left;margin: 5px -17px;"/><a  href="javascript:void(0);">' . $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) . ' - ';

			                    if ($_POST['cmbMatter'] != "") {
				                    //$result .= '(' . $_POST['cmbMatter'] . ') ';
			                    }

			                    //$result .= $Event[CalendarRuleEvent][ShortName] . ' - ';

			                    if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
				                    //$result .= $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
                                    $tree_array[$eventSystemID]['DateRules_RuleText'] =  $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
			                    } else {
				                    foreach ($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
					                    $result_date .= $Rule[RuleText];
				                    }
                                    $tree_array[$eventSystemID]['DateRules_RuleText'] = $result_date;
			                    }

                                if (isset($Event[CalendarRuleEvent][CourtRules][Rule][RuleText])) {
                                // IF THERE IS ONE RULE
                                $tree_array[$eventSystemID]['Court_RuleText'] = '<ul class="triggersnav"><li><a href="javascript:void(0);"><span class="court_rule">Court Rule:</span> ' . $Event[CalendarRuleEvent][CourtRules][Rule][RuleText] . '</li></a></ul>';

                                ?>  <?php
                                } else {

                                    // IF THERE ARE MANY RULES
                                    foreach ($Event[CalendarRuleEvent][CourtRules][Rule] as $rule) {
                                        $result_court .= '<ul class="triggersnav">
                                        <li>
                                        <a href="javascript:void(0);"><span class="court_rule">Court Rule:</span> ' . $rule[RuleText] . '</li></a></ul>';
                                        ?>  <?php
                                    }

                                    $tree_array[$eventSystemID]['Court_RuleText'] = $result_court;
                                }

            $eve++;
		}

        function HTMLMenu($menu, $parentid = 0)
        {
            foreach ($menu as $item) if ($item["ParentSystemID"] == $parentid)
            {
				if($item["IsOnDNCList"] == "true" )
				{
					$selected  = "";
				}
				else{
					$selected  = "checked";
				}
                $result1 .= "<li class='container'><input type='checkbox' onclick='checkResult(".$item["SystemID"].",this)' ".$selected." class='evenetClass' id='".$item["SystemID"].'_'.$item["ParentSystemID"]."' data='".$item["ParentSystemID"]."' name='events[]'  value='".$item["SystemID"]."' />" . $item["ShortName"]  . " " .
                HTMLMenu($menu, $item["SystemID"]) . "</li>\n";
            }
            return $result1 ?  "\n<ul class='tree'>\n$result1</ul>\n" : null;
        }

        //echo "<pre>"; print_r($tree_array);

        $res = HTMLMenu($tree_array);

        $result .= $res;
		$result .= '</div></td></tr>';




	$result .= '</table></div>
    <script>
   function checkAll(ele) {
     var checkboxes = document.getElementsByTagName("input");
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
 function checkResult(val, parentElem)
 {
    var queryString = "input[data=\'"+val+"\']";
    var parent = document.querySelectorAll(queryString);
    parent.forEach(function(inputElem){
        inputElem.checked = parentElem.checked
    });
 }
</script>';

   if($_POST['clear_checkbox'] == 'clear')
   {
     $result .='<script>
     var checkboxes = document.getElementsByTagName("input");
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = false;
             }
         }
      </script>';
   }
}

$result_html['html'] = $result;

echo json_encode($result_html);

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


