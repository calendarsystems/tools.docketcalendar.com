<?php require_once('../Connections/docketData.php');
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

$query_importEvents = "SELECT e.event_id,i.calendar_id
FROM docket_cases dc
INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
WHERE c.case_event_id = ".$_POST['case_event_id']." ";

$searchInfo = mysqli_query($docketDataSubscribe,$query_importEvents);
$row_searchInfo = mysqli_fetch_array($searchInfo);
$totalRows_searchInfo = mysqli_num_rows($searchInfo);

$capi = new GoogleCalendarApi();


$result_html = array();

if($totalRows_searchInfo > 0)
{

    $data = $capi->DeleteCalendarEvent($row_searchInfo['event_id'],$row_searchInfo['calendar_id'],$_SESSION['access_token']);

    
    $query_deleteInfo = "DELETE FROM case_events WHERE case_event_id = '".$_POST['case_event_id']."' ";
   mysqli_query($docketDataSubscribe,$query_deleteInfo);

   $query_deleteInfo1 = "DELETE FROM import_events WHERE event_id = '".$row_searchInfo['event_id']."' ";
    mysqli_query($docketDataSubscribe,$query_deleteInfo1);

    if(isset($_POST['import_docket_id']))
    {
       $query_importDocket = "SELECT import_event_id FROM import_events WHERE import_docket_id = '".$_POST['import_docket_id']."' ";
        $get_docketImport = mysqli_query($docketDataSubscribe,$query_importDocket);
        $totalRows_docketImport = mysqli_num_rows($get_docketImport);

        if($totalRows_docketImport == 0)
        {
           $query_deleteDocket = "DELETE FROM import_docket_calculator WHERE import_docket_id = '".$_POST['import_docket_id']."'";
           mysqli_query($docketDataSubscribe,$query_deleteDocket);
        } 
    }

    $result_html['html'] = "Deleted Successfully.";

} else {
  $result_html['html'] = "Error Occured.";
}

echo json_encode($result_html);

?>
