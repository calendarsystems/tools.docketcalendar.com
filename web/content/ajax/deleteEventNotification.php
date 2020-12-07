<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
require_once('../googleCalender/google-calendar-api.php');
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

		$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$_POST['caseid']."";
		$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
		$totalRows_caseQuery = mysqli_num_rows($caseQuery);
			while($row_caseevents = mysqli_fetch_assoc($caseQuery))
			{	
				$getTriggerdata = "SELECT triggerItem,calendar_id from import_docket_calculator WHERE case_id = ".$_POST['caseid']." AND user_id=".$row_caseevents['user_id'] ." AND trigger_item = ".$_POST['trigger_item']."";
				$caseTriggerQuery = mysqli_query($docketDataSubscribe,$getTriggerdata);
				$row_trigger = mysqli_fetch_assoc($caseTriggerQuery);
				
				$getEventData = "SELECT short_name FROM case_events WHERE case_event_id = ".$_POST['case_event_id'] ."";
				$caseEventQuery = mysqli_query($docketDataSubscribe,$getEventData);
				$row_event = mysqli_fetch_assoc($caseEventQuery);
				
				 $to = $row_caseevents['created_by'];
				 
				 $subject = "Notification to Delete Event";
				 $message = "
				<html>
				<head>
				<title>Notification for Delete Event</title>
				</head>
				<body>
				<p>This is an confirmation email to delete the Event: ".$row_event['short_name']." </p> <p>From the aligned Case: ".$row_caseevents['case_matter']." and Trigger: ".$row_trigger['triggerItem']." </p>
				<p><b>Please click the link below to delete the Event</b></p><br>
				<a href='http://googledocket.com/ajax/delete_import_event.php?case_id=".$_POST['caseid']."&eventId=".$_POST['case_event_id']."&triggerId=".$_POST['trigger_item']."'><p>LINK TO DELETE EVENT</p></a><br>
				</body>
				</html>
				";
         $header = "From:donotreply@calendarrules.com \r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-type: text/html\r\n";
         $retval = mail ($to,$subject,$message,$header);
         if( $retval == true ) {
             $result_html['html'] = "Mail Send Successfully.";
         }else {
            $result_html['html'] = "Message could not be sent...";
         }
		}
		echo json_encode($result_html);
      ?>
     