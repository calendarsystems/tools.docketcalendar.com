<?php require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');

session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

require('../globals/global_tools.php');

$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

 $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';

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
			
			
			  if(isset($_SESSION['google_code'])) {
    $auth_code = $_SESSION['google_code'];
    $max_results = 500;
	$accesstoken = $_SESSION['access_token'];
    $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&alt=json&v=3.0&oauth_token='.$accesstoken;
    $xmlresponse =  curl($url);
    $contacts = json_decode($xmlresponse,true);
    //echo "<pre>"; print_r($contacts);  exit();
    $return = array();
    if (!empty($contacts['feed']['id'])) {
          $_SESSION['author_id'] =  $contacts['feed']['id']['$t'];
    }

    if (!empty($contacts['feed']['entry'])) {
        foreach($contacts['feed']['entry'] as $contact) {
            if(@$contact['gd$email'][0]['address'] != '' && @$contact['title']['$t'] != '')
            {
           //retrieve Name and email address
            $return[] = array (
                'name'=> $contact['title']['$t'],
                'email' => $contact['gd$email'][0]['address'],
            );
            }
        }
    }
    $_SESSION['google_contacts'] =  $return;
  }
			
		
			   function curl($url, $post = "") {
                $curl = curl_init();
                $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
                curl_setopt($curl, CURLOPT_URL, $url);
                //The URL to fetch. This can also be set when initializing a session with curl_init().
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                //TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                //The number of seconds to wait while trying to connect.
                if ($post != "") {
                curl_setopt($curl, CURLOPT_POST, 5);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                }
                curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
                //The contents of the "User-Agent: " header to be used in a HTTP request.
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
                //To follow any "Location: " header that the server sends as part of the HTTP header.
                curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
                //To automatically set the Referer: field in requests where it follows a Location: redirect.
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                //The maximum number of seconds to allow cURL functions to execute.
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                //To stop cURL from verifying the peer's certificate.
                $contents = curl_exec($curl);
                curl_close($curl);
                return $contents;
            }

	$result_html['html'] ="Contacts Imported Sucessfully";
	echo json_encode($result_html);