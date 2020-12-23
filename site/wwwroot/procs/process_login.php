<?php 
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
//require_once('../Connections/docketDataForUserSubscribe.php');

require_once('../googleCalender/settings.php');
session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
ob_start();
//ini_set('display_errors',1);
//error_reporting(E_ALL);
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


$colname_rs = "-1";
if (isset($_POST['usernamelog'])) {
  $colname_rs = $_POST['usernamelog'];
}

$colname2_rs = "-1";
if (isset($_POST['passwordlog'])) {
  $colname2_rs = $_POST['passwordlog'];
}



$query_rs="SELECT * FROM attorneys WHERE BINARY username='$colname_rs' and BINARY password='$colname2_rs'";
//echo $query_rs;
$rs = mysqli_query($docketDataSubscribe,$query_rs) or die(mysqli_error($docketDataSubscribe));
$row_rs = mysqli_fetch_assoc($rs);
$totalRows_rs = mysqli_num_rows($rs);

if ($totalRows_rs == 1) {
	$newURL="http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
	$command="/users/".$_POST['usernamelog']."?";
	$parameters="password=".$_POST['passwordlog']."&soapREST=REST";
	$file= $newURL.$command.$parameters;

		
	$content=file_get_contents($file);
	$xml=$content;
	$array=xml2array($xml);
	//print_r($array);
	if ($array['string'] != "") {
		// login found in DM
		$loginToken=$array['string'];
		
		$content_2 = file_get_contents($newURL."/user?loginToken=".$loginToken."");
		$xml2=$content_2;
		$array2=xml2array($xml2);
		//print_r($array2);exit();
		//Changes done on 17Oct2018 to enable user to login from any site
		//if ($array2['User']['Vendor'] == "GOO") {	
		if (!empty($array2)) {
		//echo "found in DM<br>";
		

				$_SESSION['sessionID']=$_POST['usernamelog'];
				$_SESSION['parent'] = "Y";
				//$_SESSION['userid'] = $_POST['usernamelog'];
				$_SESSION['username']  = $_POST['usernamelog'];
				$_SESSION['password']  = $_POST['passwordlog'];
		
				if($_POST['rememberme'] == 'on')
				{
					setcookie("username", $_POST['usernamelog'], time() + (86400 * 30), "/");
					setcookie("password", $_POST['passwordlog'], time() + (86400 * 30), "/");
				}
				
				$query_userinfo = "SELECT * FROM users WHERE username = '".$_POST['usernamelog']."'";
				$userinfo = mysqli_query($docketDataSubscribe,$query_userinfo);
				$row_rs = mysqli_fetch_assoc($userinfo);
				$totalRows_userinfo = mysqli_num_rows($userinfo);
			
				if($totalRows_userinfo > 0)
				{
					$TrialAccount = $row_rs['TrialAccount'];
					$TrialActive = $row_rs['TrialActive'];
					$CardLastFour = $row_rs['CardLastFour'];
				
					$_SESSION['userid'] = $row_rs['id'];


					if($TrialAccount == 1 && $TrialActive == 1)
					{
					 $_SESSION['trial'] = 'Y';
					 $url = 'update-trial';
					} else {
					 $_SESSION['trial'] = 'N';
					 $url = 'update-card';
					}

					$_SESSION['account_exist'] = 'Y';

					$characters = 'bcdfghjklmnpqrstvwxyz0123456789';
					$string = '';

					$max = strlen($characters) - 1;
					for ($i = 0; $i < 25; $i++) {
						$string .= $characters[mt_rand(0, $max)];
					}
					$action=$string;



					$query_rs = "SELECT * FROM external WHERE (username = '".$row_rs['username']."' AND password = '".$row_rs['userpassword']."') AND (template = 'update-trial' OR template = 'update-card')";
					$user_rs = mysqli_query($docketDataSubscribe,$query_rs);
					$totalRows_user_rs = mysqli_num_rows($user_rs);
					if($totalRows_user_rs == 0)
					{

						$insertSQL = "INSERT INTO `external`(`action`, `template`, `username`, `password`) VALUES ('".$string."','".$url."','".$row_rs['username']."','".$row_rs['userpassword']."')";
						$insert_Result=mysqli_query($docketDataSubscribe,$insertSQL);
						$_SESSION['action'] = $action;
					} else
					{
						$row_rs_external = mysqli_fetch_assoc($user_rs);
						$_SESSION['action'] = $row_rs_external['action'];
						$updateSQL = sprintf("UPDATE external SET template=%s WHERE action=%s",
						GetSQLValueString($url, "text"),
						GetSQLValueString($row_rs_external['action'], "text"));
						$Result1 = mysqli_query($docketDataSubscribe,$updateSQL);
					}
				} else {
				   $_SESSION['account_exist'] = 'N';
				}

				if(count($array2['User']['Vendor']) > 0)
				{
				 $_SESSION['usersite'] = $array2['User']['Vendor'];
				}
				if(count($array2['User']['Name']) > 0)
				{
				 $_SESSION['fullname'] = $array2['User']['Name'];
				}
				if(count($array2['User']['Login']) > 0)
				{
				 $_SESSION['firstname'] = $array2['User']['Login'];
				}
				if(count($array2['User']['EMail']) > 0)
				{
				 $_SESSION['email'] = $array2['User']['EMail'];
				 /*Programing PATCH on 22 May 2020*/
				 $_SESSION['author_id'] = $array2['User']['EMail'];
				}
				/* CODE to check for Gmail Access to be given or not 16/12/2019 */
				
					$response = "success";

			} else {
			   $response = "Invalid Account. Please contact sdavis@calendarrules.com";
			}

		} else {
		  $response = "Failed, Please Try Again.";
		}
			
}
else{
	$response = "Invalid Account. Please contact sdavis@calendarrules.com";
}	

		mysqli_free_result($rs);
		 if($_POST['spoofparam'] == "SPOOF")
			{
				
				$_SESSION['author_id'] = $_POST['userSpoofEmail'];
				$_SESSION['spoofsess'] = "SPOOF";
				 $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
				
				header("Location: $login_url");
				exit;
			}else{
				 echo json_encode($response);
				 exit();
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





?>
