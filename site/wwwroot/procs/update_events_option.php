<?php require_once('../Connections/docketDataSubscribe.php'); 
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
 if(isset($_POST))
 {
	
    $query_update_option = 'UPDATE users_tool_option SET default_jurisdication = "'.$_POST['cmbJurisdictions'].'",
    add_court_rule_body = "'.$_POST['add_court_rule_body'].'",
    add_date_rule_body = "'.$_POST['add_date_rule_body'].'",
    case_name_location = "'.$_POST['case_name_location'].'",
    custom_text_location = "'.$_POST['custom_text_location'].'",
    reminder_minutes = "'.$_POST['reminder_minutes'].'",
	reminder_minutes_popup = "'.$_POST['reminder_minutes_popup'].'",
    show_trigger = "'.$_POST['show_trigger'].'",
    all_day_appointments = "'.$_POST['all_day_appointments'].'",
    appointments_status = "'.$_POST['appointments_status'].'",
    appointment_length = "'.$_POST['appointment_length'].'",
    request_response = "'.$_POST['request_response'].'",
    recalculated_events = "'.$_POST['recalculated_events'].'",
    do_not_recalculate_events = "'.$_POST['do_not_recalculate_events'].'",
	eventColor = "'.$_POST['eventColor'].'",
	calendar_rules_events_tag = "'.$_POST['calendar_rules_events_tag'].'",
	googlecontactprefrence  = "'.$_POST['hiddenGooglePrefrencesValue'].'",
	assignees  = "'.implode(",",$_POST['addassignee']).'"
    WHERE authenticator = "'.$_SESSION['author_id'].'" and user_id = "'.$_SESSION['userid'].'"';
	$defaultOptions = mysqli_query($docketDataSubscribe,$query_update_option);
		if(!empty($_POST['addassignee'])) 
		{
		  	$getAllCaseIdForUserEmail="SELECT distinct(dcu.case_id) from docket_cases as dc
			INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
			WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = ".$_SESSION['userid']." GROUP BY dc.case_id ORDER BY dc.case_id DESC";
			$dataCaseIdForUserEmail = mysqli_query($docketDataSubscribe,$getAllCaseIdForUserEmail);
			$totalCaseIdForUserEmail = mysqli_num_rows($dataCaseIdForUserEmail); 
			if($totalCaseIdForUserEmail > 0)
			{
				while($rowCaseIdForUserEmail = mysqli_fetch_assoc($dataCaseIdForUserEmail))
				{
					$arrayForCaseIdForUserEmail[] = $rowCaseIdForUserEmail["case_id"];
				}
			}
			
			foreach($arrayForCaseIdForUserEmail as $caseID)
			{
				foreach($_POST['addassignee'] as $user)
				{
				   $insertSQL2 = "INSERT INTO docket_cases_users (case_id, user) VALUES (".$caseID.",'".$user."')";
					mysqli_query($docketDataSubscribe,$insertSQL2);
				}
			}
		}
	
   

    header("Location: /event-option");
    die();
 } else {
    header("Location: /event-option");
    die();
 }

?>