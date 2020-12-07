<?php require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
require_once('Connections/docketDataSubscribe.php');
session_start();
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


    if(isset($_GET['update_event'])) {
     $_SESSION['update_event'] = $_GET['update_event'];
     unset($_SESSION['delete_event']);
     unset($_SESSION['docket_case']);
     unset($_SESSION['view_event']);
     unset($_SESSION['docket_calculator']);
    }
    if(isset($_GET['view_event'])) {
     $_SESSION['view_event'] = $_GET['view_event'];
     unset($_SESSION['delete_event']);
     unset($_SESSION['docket_case']);
     unset($_SESSION['update_event']);
     unset($_SESSION['docket_calculator']);
    }
    if(isset($_GET['delete_event'])) {
     $_SESSION['delete_event'] = $_GET['delete_event'];
     unset($_SESSION['update_event']);
     unset($_SESSION['docket_case']);
     unset($_SESSION['view_event']);
     unset($_SESSION['docket_calculator']);
    }
    if(isset($_GET['docket_case'])) {
     $_SESSION['docket_case'] = $_GET['docket_case'];
     unset($_SESSION['update_event']);
     unset($_SESSION['delete_event']);
     unset($_SESSION['view_event']);
     unset($_SESSION['docket_calculator']);
    }


 // Google passes a parameter 'code' in the Redirect Url
    if(isset($_GET['code'])) {
        try {

            $capi = new GoogleCalendarApi();
            $_SESSION['google_code'] = $_GET['code'];
            // Get the access token
            $data = $capi->GetAccessToken(CLIENT_ID, CLIENT_REDIRECT_URL, CLIENT_SECRET, $_GET['code']);
            $_SESSION['access_token'] = $accesstoken = $data['access_token'];
            //echo "<pre>"; print_r($data);  exit();

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


  if(isset($_SESSION['google_code'])) {
    $auth_code = $_SESSION['google_code'];
    $max_results = 500;
    $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results='.$max_results.'&alt=json&v=3.0&oauth_token='.$accesstoken;
    $xmlresponse =  curl($url);
    $contacts = json_decode($xmlresponse,true);
    //echo "<pre>"; print_r($contacts);  exit();
    $return = array();
    if (!empty($contacts['feed']['id'])) {
          $_SESSION['author_id'] =  $contacts['feed']['id']['$t'];
    }
	//print_r($_SESSION);exit();

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

    if($_SESSION['author_id'] != '')
    {
        $query_authInfo = "SELECT option_id FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' AND user_id = '".$_SESSION['userid']."' ";
        $authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
        $totalRows_authInfo = mysqli_num_rows($authInfo);

        if($totalRows_authInfo == 0)
        {
           $query_insert = "INSERT INTO users_tool_option(user_id,authenticator,default_jurisdication,add_court_rule_body,add_date_rule_body,add_activity_body,save_case,preload_events,recalculated_events,do_not_recalculate_events,check_updates,show_excluded_events,show_trigger,request_response,reminder_minutes,all_day_appointments,appointments_status,appointment_length,case_name_location,custom_text_location,status,eventColor,calendar_rules_events_tag,googlecontactprefrence) VALUES('".$_SESSION['userid']."','".$_SESSION['author_id']."','0','Rule Text','yes','yes','yes','no','update event dates only','use new date','yes','no','yes','yes','30','free','busy',3600,'prepend to subject','prepand to body',1,0,'Yes','Yes')";
           mysqli_query($docketDataSubscribe,$query_insert);
        }
    }

    $_SESSION['google_contacts'] =  $return;
    $calendarData = $capi->GetCalendarsList($_SESSION['access_token']);

    if(isset($_SESSION['update_event'])) {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/update-calendar-event?id=".$_SESSION['update_event']."';</script>";
    } else if(isset($_SESSION['view_event'])) {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/view-calendar-event?id=".$_SESSION['view_event']."';</script>";
    } else if(isset($_SESSION['delete_event'])) {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/calendar-events?case_id=".$_SESSION['delete_event']."';</script>";
    } else if(isset($_SESSION['docket_case'])) {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/docket-cases';</script>";
    } else if(isset($_SESSION['docket_calculator'])) {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/docket-calculator';</script>";
    } else {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/docket-calculator';</script>";
    }
  }
        }
        catch(Exception $e) {
            echo $e->getMessage();
            exit();
        }

    }

?>
