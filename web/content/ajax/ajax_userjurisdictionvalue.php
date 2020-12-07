<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
session_start();
//global $docketDataSubscribe;
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

	if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
	{
			$queryGetCaseDetials = "SELECT * from docket_cases as dc
			INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
			WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id ";
			$resultGetCaseDetials = mysqli_query($docketDataSubscribe,$queryGetCaseDetials);
			$totalRowsCaseDetials = mysqli_num_rows($resultGetCaseDetials);

	}
	while ($rowCaseData = mysqli_fetch_assoc($resultGetCaseDetials)) 
	{
		$CaseId[] = $rowCaseData['case_id'];
	}	
	$inArrforCaseId = implode(",",$CaseId);
	$queryGetImportDocketId = "SELECT import_docket_id FROM import_docket_calculator WHERE case_id IN (".$inArrforCaseId.")";
	$resultGetImportDocketId= mysqli_query($docketDataSubscribe,$queryGetImportDocketId);
	while($rowresultGetImportDocketId = mysqli_fetch_assoc($resultGetImportDocketId))
	{
		$arrImportDocketId[]= $rowresultGetImportDocketId['import_docket_id']; 
	}
	
	$ImportIdInArrayQuery = implode(',',$arrImportDocketId);
	$queryGetCaseJuri = "SELECT distinct(jurisdiction) FROM import_docket_calculator WHERE import_docket_id IN (".$ImportIdInArrayQuery.")";
	$resultGetJuri= mysqli_query($docketDataSubscribe,$queryGetCaseJuri);
	while($rowresultGetJuridiction = mysqli_fetch_assoc($resultGetJuri))
	{
		$resultData[] = $rowresultGetJuridiction['jurisdiction'];
	}
	
	$colname_userInfo = "-1";
	if (isset($_SESSION['userid']))
	{
	  $colname_userInfo = $_SESSION['userid'];
	}
	
	$query_userInfo = sprintf("SELECT * FROM attorneys WHERE user_id = %s", GetSQLValueString($docketData,$colname_userInfo, "int"));
	$userInfo = mysqli_query($docketData,$query_userInfo);
	$row_userInfo = mysqli_fetch_assoc($userInfo);
	$totalRows_userInfo = mysqli_num_rows($userInfo);

	$newURL="http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

	$selectJurisdiction=0;
	$selectTriggerItem=0;
	$selectServiceType=0;
	$masterCourt=0;
	$result_html = array();


	if (isset($_SESSION['userid']))
	{
		$command="/users/".$_SESSION['username']."?";
		$parameters="password=".$_SESSION['password']."&soapREST=REST";
		$file= $newURL.$command.$parameters;
		$content=file_get_contents($newURL.$command.$parameters,false,$context);
		$xml=$content;
		$array=xml2array($xml);
		$loginToken=$array['string'];
		$newURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
		$command="/jurisdictions/my?";
		$parameters="loginToken=$loginToken";
		$file= $newURL.$command.$parameters;
		$content=file_get_contents($file,false,$context);
		$xml=$content;
		$array=xml2array($xml);
		$theTotalJurisdictions = $array['ArrayOfJurisdiction']['Jurisdiction'];
		$numJuris=sizeof($theTotalJurisdictions['Description']);
		if (isset($theTotalJurisdictions['Code'])) {
				$default_court_id =  $theTotalJurisdictions['SystemID'];
			} else {
				foreach($theTotalJurisdictions as $juris)
				{
				  $defaultCourt[] = $juris['SystemID'];
				}
			$default_court_id = $defaultCourt[0];
			}

		$query_authInfo = "SELECT default_jurisdication,option_id FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."'  AND user_id = '".$_SESSION['userid']."'";
		$authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
		$totalRows_authInfo = mysqli_num_rows($authInfo);

		$default_court_system_id = 0;
		if(isset($_SESSION['JurisdictionData']))
		{
		  $default_court_system_id = $_SESSION['JurisdictionData'];
		} else if($totalRows_authInfo > 0)
		{
		  $row_authInfo = mysqli_fetch_assoc($authInfo);
		  $default_court_system_id = $row_authInfo['default_jurisdication'];
		}
		?>	
		<select name="case_jurisdiction" id="case_jurisdiction"  style="width:450px;" >
        <option value="0">---Select Court---</option>
		<?php 
			foreach($theTotalJurisdictions as $juris)
            {
				if(in_array($juris['SystemID'],$resultData))
				{
        ?>
               <option value="<?php echo $juris['SystemID']; ?>">
                <?php echo $juris['Description']; ?></option>
			<?php
				}
             }
			?>  
		</select>
	<?php
	}

	
	
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
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

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();

        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;

                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;


            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }

    return($xml_array);
}
