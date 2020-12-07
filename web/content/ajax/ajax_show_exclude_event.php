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
		if (isset($_POST['cmbJurisdictions'])) {
			$selectJurisdiction = $_POST['cmbJurisdictions'];
		}
		if (isset($_POST['cmbTriggers'])) {
			$selectTriggerItem = $_POST['cmbTriggers'];
		}
		
		$query_userInfo = sprintf("SELECT * FROM attorneys WHERE user_id = %s", GetSQLValueString($docketData,$colname_userInfo, "int"));
		$userInfo = mysqli_query($docketData,$query_userInfo);
		$row_userInfo = mysqli_fetch_assoc($userInfo);
		$totalRows_userInfo = mysqli_num_rows($userInfo);
		$sort_date = '';
		$events = '';
		$userName = $_SESSION['username'];
		
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
		$url="http://www.crcrules.com/CalendarRulesService.svc/rest/events/donotcreatelist?loginToken=".$loginToken."&jurisdictionSystemID=".$selectJurisdiction."&triggerItemSystemID=".$selectTriggerItem;
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_HEADER, 0);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		$response = curl_exec($session);
		curl_close($session);
		$start = strpos($response, "<ArrayOfCalendarRulesEvent");
		$end = strlen($response);
		$length = $end - $strart;
		$xmlblock = substr($response, $start, $length);
		$response = xml2array($xmlblock);
		$response = $response['ArrayOfCalendarRulesEvent']['CalendarRulesEvent'];
		if(!empty($response))
		{
			if(empty($response[0]))
			{
				$eventsCount = 1;
				
			}
			$result = '';
			$result.='<div><p>Events on DNC List</p></div>';	
			$result.='<div id="list_wrapper">';
			$result.='<ul class="multiple_columns">';
			if($eventsCount == 1)
			{
				$result.='<li>'.$response['ShortName'].'</li><br>';
			}else{
				
				foreach($response as $resultData)
				{
				
					$result.='<li>'.$resultData['ShortName'].'</li><br>';
				}
			}
			$result.='</ul>';
			$result.='</div>';	
		}
		else
		{
			$url="http://www.crcrules.com/CalendarRulesService.svc/rest/events/donotcreatelist?loginToken=".$loginToken."";
			$session = curl_init($url);
			curl_setopt($session, CURLOPT_HEADER, 0);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml',"&jurisdictionSystemID=".$selectJurisdiction."&triggerItemSystemID=".$selectTriggerItem));
			$response = curl_exec($session);
			curl_close($session);
			$start = strpos($response, "<ArrayOfCalendarRulesEvent");
			$end = strlen($response);
			$length = $end - $strart;
			$xmlblock = substr($response, $start, $length);
			$response = xml2array($xmlblock);
			$response = $response['ArrayOfCalendarRulesEvent']['CalendarRulesEvent'];
			
			
			
			/*Code to get Events as per JURI and Trigger */
			$uridata = "http://www.crcrules.com/CalendarRulesService.svc/rest/";
			$urlexeve = $uridata . "compute/preview?loginToken=" . $loginToken."&jurisdictionSystemID=".$selectJurisdiction."&triggerItemSystemID=".$selectTriggerItem;
			$sessionexevt = curl_init($urlexeve);
			curl_setopt($sessionexevt, CURLOPT_HEADER, true);
			curl_setopt($sessionexevt, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($sessionexevt, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
			$responseexevet = curl_exec($sessionexevt);
			curl_close($sessionexevt);
			$startexevt = strpos($responseexevet, "<PreviewResult");		
			$endexevt = strlen($responseexevet);
			$lengthexevt = $endexevt - $startexevt;
			$xmlblock = substr($responseexevet, $startexevt, $lengthexevt);
			$responseexevet = xml2array($xmlblock);
			$responseexevet = $responseexevet['PreviewResult']['CalendarRuleEventPreviews']['CalendarRulesEventPreview'];
			
			$getEventArray = array();
			if(empty($responseexevet[0]))
			{
				$getEventArray[] = $responseexevet['SystemID'];
			}else
			{
				foreach ($responseexevet as $Event) 
				{
					$getEventArray[] = $Event['SystemID'];
				}
			}
			/*END Code to get Events as per JURI and Trigger */
			if(empty($response[0]))
			{
				$eventsCount = 1;
				
			}
			$result = '';
			$result.='<div><p>Events on DNC List</p></div>';	
			$result.='<div id="list_wrapper">';
			$result.='<ul class="multiple_columns">';
			if($eventsCount == 1)
			{	$sysID = $response['SystemID'];
				if(in_array($sysID,$getEventArray))
					{
						$result.='<li>'.$response['ShortName'].'</li><br>';
					}
				
			}else{
				
				foreach($response as $resultData)
				{
					$sysID = $resultData['SystemID'];
					if(in_array($sysID,$getEventArray))
					{
						$result.='<li>'.$resultData['ShortName'].'</li><br>';
					}
					
				}
			}
			$result.='</ul>';
			$result.='</div>';
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


