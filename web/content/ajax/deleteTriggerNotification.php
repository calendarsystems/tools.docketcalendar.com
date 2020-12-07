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

		$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$_POST['case_id']."";
		$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
		$totalRows_caseQuery = mysqli_num_rows($caseQuery);
		
		
			while($row_caseevents = mysqli_fetch_assoc($caseQuery))
			{	
				$getTriggerdata = "SELECT triggerItem from import_docket_calculator WHERE case_id = ".$_POST['case_id']." AND user_id=".$row_caseevents['user_id']." AND trigger_item = ".$_POST['triggerId']."";
				$caseTriggerQuery = mysqli_query($docketDataSubscribe,$getTriggerdata);
				$row_trigger = mysqli_fetch_assoc($caseTriggerQuery);
				
				 //$to = $row_caseevents['created_by'];
				 $to ="manoj.mahamunkar@clariontechnologies.co.in";
				 $subject = "Notification to Delete Trigger";
				 $message = "
				<html>
				<head>
				<title>Notification for Delete Trigger</title>
				</head>
				<body>
				<p>This is an confirmation email to delete the Trigger: ".$row_trigger['triggerItem']." </p> <p>From the aligned Case: ".$row_caseevents['case_matter']."</p>
				<p><b>Please click the link below to delete the Trigger</b></p><br>
				<a href='http://googledocket.com/ajax/delete_import_trigger.php?case_id=".$_POST['case_id']."&triggerId=".$_POST['triggerId']."'><p>LINK TO DELETE TRIGGER</p></a><br>
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
     