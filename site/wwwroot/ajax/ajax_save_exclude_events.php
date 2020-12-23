<?php 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
ini_set('max_execution_time', 300);
set_time_limit(300);

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

    if(isset($_POST['cmbJurisdictions']))
    {
        $cmbJurisdictions = $_POST['cmbJurisdictions'];
    }

    if(isset($_POST['cmbTriggers']))
    {
        $cmbTriggers = $_POST['cmbTriggers'];
    }
	
	if(isset($_POST['events']))
	{
		$serialize_events = serialize($_POST['events']);
	} else {
		$serialize_events = '';
	}
	if(isset($_POST['uncheckevents']))
	{
		$uncheckevents = serialize($_POST['uncheckevents']);
	} else {
		$uncheckevents = '';
	}
	if(isset($_POST['applyjuri']))
    {
        $applyjuri = $_POST['applyjuri'];
    }

	$userName = $_SESSION['username'];
	if($serialize_events != '')
	{
		$selectSQL = "SELECT count(*) as duplicateCount,id from exclude_events WHERE
		jurisdiction = ".$cmbJurisdictions." and trigger_item = ".$cmbTriggers." and createdBy = '".$userName."'";
		$countResult = mysqli_query($docketDataSubscribe,$selectSQL);
		$resultData = mysqli_fetch_array($countResult);
		;
		if($resultData['duplicateCount'] == 0)
		{
			
			$insertSQL = "INSERT INTO exclude_events(jurisdiction,trigger_item,events,juriapply,createdBy)  VALUES ('".$cmbJurisdictions."','".$cmbTriggers ."','".$serialize_events."','".$applyjuri."','".$userName."')";
			$result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
		}
		else
		{
			
			$updateSQL = "UPDATE exclude_events SET events = '".$serialize_events."' WHERE id = ".$resultData['id']."";
			$Updateresult = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error(), E_USER_ERROR);
		}
		
	}
		
		$responseHtml = "<span style='color:green;'>Update Excluded Events Successful.</span>";
		$result_html['html'] = $responseHtml;
		echo json_encode($result_html);	
		
		$newURL = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
		if (isset($_SESSION['userid'])) {
			$command = "/users/" . $_SESSION['username'] . "?";
			$parameters = "password=" . $_SESSION['password'] . "&soapREST=REST";
			$file = $newURL . $command . $parameters;

			$content = file_get_contents($newURL . $command . $parameters, false, $context);
			$xml = $content;
			$array = xml2array($xml);

			$loginToken = $array['string'];

		}
			//FOR SINGLE JURISDICTION
			if($applyjuri == 1)
			{
				
				if($serialize_events != '')
				{
					$eventsSystemIds=unserialize($serialize_events);
					$xml = '<ArrayOflong xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays">';
					foreach($eventsSystemIds as $eval)
					{
						$xml.= '<long>' .$eval. '</long>';	
					}
					$xml.='</ArrayOflong>';
					
					$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
					$url = $uri . "events/donotcreatelist/add?loginToken=" . $loginToken."&jurisdictionSystemID=".$cmbJurisdictions;
					$session = curl_init($url);

					curl_setopt($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
					curl_setopt($session, CURLOPT_HEADER, true);
					curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
					$response = curl_exec($session);
					curl_close($session);
					
				}
				
				if($uncheckevents!='')
				{
					$uncheckeventsSystemIds=unserialize($uncheckevents);
					$ValtoRemovearray = array();
					foreach($uncheckeventsSystemIds as $uncheckeval)
					{
						
						array_push($ValtoRemovearray,$uncheckeval);
					}
					unset($ValtoRemovearray[0]);
					$removexml = '<ArrayOflong xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays">';
					foreach($ValtoRemovearray as $reval)
					{
				
						$removexml.= '<long>' .$reval. '</long>';
					}
					$removexml.='</ArrayOflong>';
					
					$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
					$url = $uri . "events/donotcreatelist/remove?loginToken=" . $loginToken."&jurisdictionSystemID=".$cmbJurisdictions;
					$session = curl_init($url);

					curl_setopt($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_POSTFIELDS, $removexml);
					curl_setopt($session, CURLOPT_HEADER, true);
					curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
					$response = curl_exec($session);
					curl_close($session);	
				
				}
			}	

				//FOR ALL JURISDICTION
			if($applyjuri == 2)
			{
				
				$newJuris = 0;
				if($serialize_events != '')
				{
					$eventsSystemIds=unserialize($serialize_events);
					$xml = '<ArrayOflong xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays">';
					foreach($eventsSystemIds as $eval)
					{
						$xml.= '<long>' .$eval. '</long>';	
					}
					$xml.='</ArrayOflong>';
					
					$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
					$url = $uri . "events/donotcreatelist/add?loginToken=" . $loginToken."&jurisdictionSystemID=".$newJuris;
					$session = curl_init($url);

					curl_setopt($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
					curl_setopt($session, CURLOPT_HEADER, true);
					curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
					$response = curl_exec($session);
					curl_close($session);
					
				}
				
				if($uncheckevents!='')
				{
					$uncheckeventsSystemIds=unserialize($uncheckevents);
					$ValtoRemovearray = array();
					foreach($uncheckeventsSystemIds as $uncheckeval)
					{
						
						array_push($ValtoRemovearray,$uncheckeval);
					}
					unset($ValtoRemovearray[0]);
					$removexml = '<ArrayOflong xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays">';
					foreach($ValtoRemovearray as $reval)
					{
				
						$removexml.= '<long>' .$reval. '</long>';
					}
					$removexml.='</ArrayOflong>';
					
					$uri = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
					$url = $uri . "events/donotcreatelist/remove?loginToken=" . $loginToken."&jurisdictionSystemID=".$newJuris;
					$session = curl_init($url);

					curl_setopt($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_POSTFIELDS, $removexml);
					curl_setopt($session, CURLOPT_HEADER, true);
					curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
					$response = curl_exec($session);
					curl_close($session);	
				
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