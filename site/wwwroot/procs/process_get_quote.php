<?php
session_id();
session_start();
require_once '../Connections/docketData.php';
ini_set('display_errors', 1);
ob_start();
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

//echo "<pre>";
//print_r($_POST);

//echo "<pre>";
$courts_array = explode(",", $_POST['court_list']);

echo "insert user<br>";
$insertSQL = sprintf("INSERT INTO quote_user (name, email, phone, firm_name, firm_address, current_calendar, comments) VALUES (%s, %s, %s, %s, %s, %s, %s)",
	GetSQLValueString($_POST['name'], "text"),
	GetSQLValueString($_POST['email'], "text"),
	GetSQLValueString($_POST['phone'], "text"),
	GetSQLValueString($_POST['firm_name'], "text"),
	GetSQLValueString($_POST['firm_address'], "text"),
	GetSQLValueString($_POST['current_calendar'], "text"),
	GetSQLValueString($_POST['comments'], "text"));
mysqli_select_db($docketData,$database_docketData);
$Result1 = mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
$quote_user_id = mysqli_insert_id($docketData);

global $docketData1;
$hostname_docketData1 = "mariadb-066.wc1.dfw3.stabletransit.com";
$database_docketData1 = "375786_dlsub";
$username_docketData1 = "375786_dlsub";
$password_docketData1 = "D0cketLaw123";
$docketData1 = mysqli_connect($hostname_docketData1, $username_docketData1, $password_docketData1) or trigger_error(mysqli_error($docketData1), E_USER_ERROR);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
mysqli_select_db($docketData1,$database_docketData1);

if (count($courts_array) > 0) {

	$today_date = date("mdYhis");
	$header[0] = "State";
	$header[1] = "Court";
	$header[2] = "Court Type";

	$filename = "get-a-quote-" . $today_date . ".csv";
	$fp = fopen("../logs/" . $filename, "w");
	fputcsv($fp, $header);

	foreach ($courts_array as $court_id) {
		$query_Courts = "SELECT * FROM courts WHERE courtid = " . $court_id . "";
		$Courts = mysqli_query($docketData1,$query_Courts) or die(mysqli_error($docketData1));
		$row_Courts = mysqli_fetch_assoc($Courts);
		$totalRows_Courts = mysqli_num_rows($Courts);

		if ($totalRows_Courts > 0) {
			$systemID = $row_Courts['systemID'];
			//echo "<br>";
			$type_SystemID = $row_Courts['courtSystem_SystemID'];

			$result_array['state'] = $row_Courts['courtSystem_Description'];
			$result_array['court'] = $row_Courts['type_Description'];
			$result_array['courttype'] = $row_Courts['description'];

			fputcsv($fp, $result_array);

			$insertSQLCart = sprintf("INSERT INTO quote_courts (quote_user_id, systemid, courttype) VALUES (%s, %s, %s)",
				GetSQLValueString($quote_user_id, "text"),
				GetSQLValueString($systemID, "text"),
				GetSQLValueString($type_SystemID, "text"));
			mysqli_select_db($docketData,$database_docketData);
			$Result2 = mysqli_query($docketData,$insertSQLCart) or die(mysqli_error($docketData));
		}
	}

	echo $update_csv = "UPDATE quote_user SET quote_csv='" . $filename . "' WHERE quote_user_id = " . $quote_user_id . " ";
	$result = mysqli_query($docketData,$update_csv) or die(mysqli_error($docketData));

	$_SESSION['proc'] = 'get_a_quote';
	$_SESSION['from1'] = $_POST['email'];
	$_SESSION['to1'] = "stacy@calendarrules.com";
	$_SESSION['subject1'] = "Get a Quote Request - " . date("d-m-Y h:i:s", mktime()) . " ";
	$_SESSION['courts_csv'] = $filename;
	$_SESSION['html1'] = "Hi<br><br><b>Get a Quote - Request</b><br><br>
				 Name: " . $_POST['name'] . "<BR><BR>
				 Email: " . $_POST['email'] . "<BR><BR>
				 Phone: " . $_POST['phone'] . "<BR><BR>
				 Firm Name: " . $_POST['firm_name'] . "<BR><BR>
				 Firm Address: " . $_POST['firm_address'] . "<BR><BR>
				 Current Calendaring or Case Management System, if any: " . $_POST['current_calendar'] . "<BR><BR>
				 Comments or questions: " . $_POST['comments'] . "<BR><BR>
				 Date Time: " . date("d-m-Y h:i:s", mktime()) . "<BR>";

	header('Location: mailgun_emailer.php');
	exit();
}
?>