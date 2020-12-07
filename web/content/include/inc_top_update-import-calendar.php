<?php require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
require_once('Connections/docketDataSubscribe.php');
global $docketDataSubscribe;
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

//ini_set('display_errors',1);
//error_reporting(E_ALL);

    if(!isset($_SESSION['access_token']))
    {
      echo "<script>window.location.href='/docket-calculator';</script>";
    }

    if(isset($_SESSION['access_token'])) {

        try {
            global $calendarData;
            $capi = new GoogleCalendarApi();

            // Get the access token
            $calendarData = $capi->GetCalendarsList($_SESSION['access_token']);
        }
        catch(Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    if(isset($_SESSION['docket_search_id'])) {

       $updateSQL = "UPDATE import_docket_calculator SET status = 2 WHERE import_docket_id = ".$_SESSION['docket_search_id']." ";
       $result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error(), E_USER_ERROR);
    }
?>