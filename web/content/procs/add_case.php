<?php require_once('../Connections/docketDataSubscribe.php'); 
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($dataValue,$theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	  
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($dataValue,$theValue) : mysqli_escape_string($dataValue,$theValue);

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
 if(isset($_POST) && $_POST['case_matter'] != "")
 {

    $insertSQL = sprintf("INSERT INTO docket_cases (user_id, case_matter,created_by,calendar_id,case_jurisdiction,case_location,case_customtext,caseReminderTime,reminder_minutes_popup,casedisplay,caseEventColor) VALUES (%s, %s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
    GetSQLValueString($docketDataSubscribe,$_SESSION['userid'], "text"),
    GetSQLValueString($docketDataSubscribe,$_POST['case_matter'], "text"),
    GetSQLValueString($docketDataSubscribe,$_SESSION['author_id'], "text"),
    GetSQLValueString($docketDataSubscribe,$_POST['calendar_id'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['case_jurisdiction'], "int"),
	GetSQLValueString($docketDataSubscribe,$_POST['case_location'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['case_customtext'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['reminder_minutes'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['reminder_minutes_popup'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['casedisplay'], "text"),
	GetSQLValueString($docketDataSubscribe,$_POST['caseLeveleventColor'], "text"));
    mysqli_query($docketDataSubscribe,$insertSQL);
    $case_id = mysqli_insert_id($docketDataSubscribe);
    $users = $_POST['users'];
	
	$insertIntoDocketCasesText="INSERT INTO docket_customtext (user_id,case_id,case_customtext,case_customtextlevel) VALUES(".$_SESSION['userid'].",'".$case_id."','".$_POST['case_customtext']."',1)";
	mysqli_query($docketDataSubscribe,$insertIntoDocketCasesText);
	
	$insertIntoDocketCasesModifiedBy="INSERT INTO docket_case_triggerevent_mod (user_id,case_id) VALUES(".$_SESSION['userid'].",'".$case_id."')";
	mysqli_query($docketDataSubscribe,$insertIntoDocketCasesModifiedBy);
	
	$queryInsertArchiveCase = "INSERT INTO docket_cases_archive (userid,caseid,case_delete) VALUES (".$_SESSION['userid'].",".$case_id.",1)";
	$resultQuery = mysqli_query($docketDataSubscribe,$queryInsertArchiveCase)or trigger_error("Query Failed! SQL: $queryInsertArchiveCase - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);
	
    $attendee = $_POST['attendee'];
	
    if(count($users) > 0)
    {
        array_push($users,$_SESSION['author_id']);
        array_unique($users);
    } else {
       $users[] = $_SESSION['author_id'];
    }
	
     if(count($attendee) > 0)
    {
        //array_push($attendee,$_SESSION['author_id']);
        array_unique($attendee);
    } 

    foreach($users as $user)
    {
         $insertSQL2 = sprintf("INSERT INTO docket_cases_users (case_id, user) VALUES (%s, %s)",
        GetSQLValueString($docketDataSubscribe,$case_id, "text"),
        GetSQLValueString($docketDataSubscribe,$user, "text"));
        mysqli_query($docketDataSubscribe,$insertSQL2);
    }
	


    foreach($attendee as $attendeeVal)
    {
        $insertSQL3 = sprintf("INSERT INTO docket_cases_attendees (case_id, caselevel,attendee) VALUES (%s,%s,%s)",
        GetSQLValueString($docketDataSubscribe,$case_id, "text"),
		GetSQLValueString($docketDataSubscribe,1, "int"),
        GetSQLValueString($docketDataSubscribe,$attendeeVal, "text"));
        mysqli_query($docketDataSubscribe,$insertSQL3);
    }

    header("Location: /docket-cases");
    die();
 } else {
    header("Location: /add-case");
    die();
 }

?>