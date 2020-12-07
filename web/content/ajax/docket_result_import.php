<?php 

require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/settings.php');
ini_set('max_execution_time', 1200);
ini_set('memory_limit', '520M');
set_time_limit(1200);
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
session_start();
 $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
$docketUserDataSubscribe = $GLOBALS['docketUserDataSubscribe'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$database_docketData = $GLOBALS['database_docketData'];

if(isset($_POST))
{

    $userId = $_POST['userid'];
    $cmbJurisdictions = mysqli_real_escape_string($docketDataSubscribe,$_POST['cmbJurisdictions']);
    $cmbTriggers = mysqli_real_escape_string($docketDataSubscribe,$_POST['cmbTriggers']);
    $txtTriggerDate = mysqli_real_escape_string($docketDataSubscribe,$_POST['txtTriggerDate']);
    $txtTime = mysqli_real_escape_string($docketDataSubscribe,$_POST['txtTime']);
    $cmbServiceTypes = mysqli_real_escape_string($docketDataSubscribe,$_POST['cmbServiceTypes']);
    $isServed = mysqli_real_escape_string($docketDataSubscribe,$_POST['isServed']);
    $isTimeRequired = mysqli_real_escape_string($docketDataSubscribe,$_POST['isTimeRequired']);
    $cmbMatter = mysqli_real_escape_string($docketDataSubscribe,$_POST['cmbMatter']);
    $location = mysqli_real_escape_string($docketDataSubscribe,$_POST['location']);
    $custom_text = mysqli_real_escape_string($docketDataSubscribe,$_POST['custom_text']);
    $authToken = mysqli_real_escape_string($docketDataSubscribe,$_POST['auth_token']);

    $TriggerItem = mysqli_real_escape_string($docketDataSubscribe,$_POST['hidden_trigger_item']);
    $ServiceType = mysqli_real_escape_string($docketDataSubscribe,$_POST['hidden_service_type']);

  
    $_SESSION['JurisdictionData'] = $_POST['cmbJurisdictions'];
    $_SESSION['caseId'] = $_POST['cmbMatter'];
	
	if (isset($_POST['hidden_appointment_length'])) {
	$hidden_appointment_length = $_POST['hidden_appointment_length'];
	$_SESSION['hiddenAppointmentLength'] = $hidden_appointment_length;
	}
    $sort_date = $_POST['sort_date'];
    if(isset($_POST['events']))
    {
        $serialize_events = serialize($_POST['events']);
    } else {
        $serialize_events = '';
    }

    if($authToken == "")
    {
        $url = $login_url;
        $status = 0;
        header("Location: ".$url."");
        exit();
    } else {
        $url = "/import-calendar";
        $status = 1;
    }
    $meridiem = "";
    if($txtTime != '')
    {
      $exp_time = explode(" ",$txtTime);
      $txtTime = $exp_time[0].":00";
      $meridiem = trim($exp_time[1]);
    }

    $case_id = $cmbMatter;

	$updateIntoDocketCasesText="UPDATE docket_customtext SET case_customtext='".$_POST["custom_text"]."' WHERE case_id=".$case_id." AND user_id = ".$_SESSION['userid']."";
	mysqli_query($docketDataSubscribe,$updateIntoDocketCasesText);
	
	
    $insertSQL = "INSERT INTO import_docket_calculator(user_id, access_token, case_id, jurisdiction, trigger_item, triggerItem, location, serviceType, sort_date, events, trigger_date, trigger_time, meridiem, service_type, isServed, isTimeRequired, status, custom_text)
  VALUES (".$userId.",'".$authToken."', '".$case_id."', '".$cmbJurisdictions."','".$cmbTriggers."', '".$TriggerItem."', '".$location."', '".$ServiceType."', '".$sort_date."', '".$serialize_events."', '".$txtTriggerDate."','".$txtTime."', '".$meridiem."',".$cmbServiceTypes.",'".$isServed."','".$isTimeRequired."','".$status."','".$custom_text."')";
    $result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);

    $_SESSION['docket_search_id'] = mysqli_insert_id($docketDataSubscribe);

    if($authToken == "")
    {
        header("Location: ".$url."");
    } else {
        header("Location: ".$url."");
    }
    exit();
}  else {
    header("Location: /docket-calculator");
    exit();
}

