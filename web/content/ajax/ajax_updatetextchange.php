<?php
require_once('../Connections/docketData.php');
require_once('../Connections/docketDataSubscribe.php');
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
@ini_set('post_max_size', '2000M');		
@ini_set('max_input_time', 1800);
@ini_set('upload_max_filesize', '1000M');
@ini_set('max_execution_time', 0);
@ini_set("memory_limit", "-1");
set_time_limit(0);
session_start();
//global $docketDataSubscribe;
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
	$eventdesc =$_POST["changeTextValue"];
	$importdocketid=$_POST["docket_search_id"];
	$caseid= $_POST["caseid"];
	if (!empty($_POST["systemId"]))
	{
		$systemId=$_POST["systemId"];
	}
	else
	{
		$systemId=0;
	}
	
	$tableName = "updateeventsdesc";
	$tbl_Events = "events";
	$insertupdateeventsdesc = array(
		            'eventdesc' => $eventdesc,
		            'importdocketid' => $importdocketid,
		            'caseid' => $caseid,
		            'eventid' => $systemId,
		        );
				
				
	$insertCondition="";
	$insertValues="";
	if (!empty($_POST["singleEventColorVal"]))
	{
		$EventColorVal= $_POST["singleEventColorVal"];
		$insertCondition.=",eventColor";
		$insertValues.=",".$EventColorVal;
		//$insertupdateeventsdesc= array_merge($insertupdateeventsdesc,array("eventColor"=>$EventColorVal));
		
	}
	if (!empty($_POST["multipleEventColorVal"]))
	{
		$EventColorVal= $_POST["multipleEventColorVal"];
		$insertCondition.=",eventColor";
		$insertValues.=",".$EventColorVal;
		//$insertupdateeventsdesc= array_merge($insertupdateeventsdesc, array("eventColor"=>$EventColorVal));
	}
	if (!empty($_POST["singleReminderValue"]))
	{
		$singleReminderValue= $_POST["singleReminderValue"];
		$insertCondition.=",eventreminderval";
		$insertValues.=",".$singleReminderValue;
		//$insertupdateeventsdesc= array_merge($insertupdateeventsdesc, array("eventreminderval"=>$singleReminderValue));
	}
	if (!empty($_POST["popupReminderDropDown"]))
	{
		$popupReminderDropDown= $_POST["popupReminderDropDown"];
		$insertCondition.=",eventpopupreminderval";
		$insertValues.=",".$popupReminderDropDown;
		//$insertupdateeventsdesc= array_merge($insertupdateeventsdesc, array("eventpopupreminderval"=>$singleReminderValue));
	}
	

	
				
	
	$checkForInsertOrUpdate="SELECT descid FROM updateeventsdesc WHERE importdocketid=".$importdocketid." AND caseid = ".$caseid." AND eventid = '".$systemId."'";
	$resultForCheck = mysqli_query($docketDataSubscribe,$checkForInsertOrUpdate);
	$rowcount=mysqli_num_rows($resultForCheck);
	
	$updateEventsTableData=array(
	'eventColor' => $EventColorVal
	);
	$updateEventsTableDataCondition = array(
	'importdocketid' => $importdocketid,
	'caseid' => $caseid,
	'eventIdvalue' => $systemId,
	);
	
	if($rowcount > 0)
	{
		
			$eventdesc = mysqli_real_escape_string($docketDataSubscribe,$eventdesc);
			$updateSQL="UPDATE updateeventsdesc SET eventdesc = '".$eventdesc."',eventColor=".$EventColorVal.",eventreminderval=".$singleReminderValue." ,eventpopupreminderval = ".$popupReminderDropDown." WHERE importdocketid=".$importdocketid." AND caseid = ".$caseid." AND eventid = '".$systemId."'";
			$result1 = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);
			
					$Updateupdateeventsdesc = array(
		            'eventdesc' => $eventdesc,
		            'eventColor' => $EventColorVal
		        );
			
			$updateCondition = array(
		            'importdocketid' => $importdocketid,
		            'caseid' => $caseid,
		            'eventid' => $systemId
		        );
				
			//$updateUpdateeventsdescSQL=Db::getInstance()->db_update_from_array($Updateupdateeventsdesc,$updateCondition,$tableName);
			
			$updateEventsTableSQL="UPDATE events SET eventColor = ".$EventColorVal."  WHERE importdocketid=".$importdocketid." AND caseid = ".$caseid." AND eventIdvalue = '".$systemId."'";
			$resultData = mysqli_query($docketDataSubscribe,$updateEventsTableSQL) or trigger_error("Query Failed! SQL: $updateEventsTableSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);
			
			//$updateEventColorInEventsSQL=Db::getInstance()->db_update_from_array($updateEventsTableData,$updateEventsTableDataCondition,$tbl_Events);
	}else{
		
			
			$insertSQL = "INSERT INTO updateeventsdesc (eventdesc,importdocketid,caseid,eventid".$insertCondition.") VALUES ('".$eventdesc."',".$importdocketid.",".$caseid.",'".$systemId."'".$insertValues.")";
			$result2 = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);
			
				
		//$insertUpdateeventsdescSQL=Db::getInstance()->db_insert_from_array($insertupdateeventsdesc,$tableName);
			//$updateEventColorInEventsSQL=Db::getInstance()->db_update_from_array($updateEventsTableData,$updateEventsTableDataCondition,$tbl_Events);
		
			$updateEventsTableSQL="UPDATE events SET eventColor = ".$EventColorVal."  WHERE importdocketid=".$importdocketid." AND caseid = ".$caseid." AND eventIdvalue = '".$systemId."'";
			$resultData = mysqli_query($docketDataSubscribe,$updateEventsTableSQL) or trigger_error("Query Failed! SQL: $updateEventsTableSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR);
			
	}

	$responseHtml = "Success";
	$result_html['html'] = $responseHtml;
	echo json_encode($result_html);
?>



