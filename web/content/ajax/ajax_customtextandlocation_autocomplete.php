<?php require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
$context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
require '../globals/global_tools.php';
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

			if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
			{
				$queryGetCaseDetials = "SELECT * from docket_cases as dc
				INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
				WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id ";
				$resultGetCaseDetials = mysqli_query($docketDataSubscribe,$queryGetCaseDetials);
				$totalRowsCaseDetials = mysqli_num_rows($resultGetCaseDetials);

			}
		while ($rowCaseData = mysqli_fetch_assoc($resultGetCaseDetials)) 
		{
			$CaseId[] = $rowCaseData['case_id'];
        }			
		global $docketDataSubscribe;
		$inArrforCaseId = implode(",",$CaseId);
		$arrImportDocketId = array();
		$arrImportEventsId = array();
		$arrCaseEventsd = array();
		
		$queryGetImportDocketId = "SELECT import_docket_id FROM import_docket_calculator WHERE case_id IN (".$inArrforCaseId.")";
		$resultGetImportDocketId= mysqli_query($docketDataSubscribe,$queryGetImportDocketId);
		while($rowresultGetImportDocketId = mysqli_fetch_assoc($resultGetImportDocketId))
		{
			$arrImportDocketId[]= $rowresultGetImportDocketId['import_docket_id']; 
		}
		$ImportIdInArrayQuery = implode(',',$arrImportDocketId);
		
		
		if($_POST['location'])
		{
			$keyword = strval($_POST['location']);
			$search_param = "{$keyword}%";
			$queryGetCaseLocation = "SELECT distinct(location) FROM import_docket_calculator WHERE import_docket_id IN (".$ImportIdInArrayQuery.") and location LIKE '%".$search_param."'";
			$resultGetCaseLocation= mysqli_query($docketDataSubscribe,$queryGetCaseLocation);
			while($rowresultGetCaseLocation = mysqli_fetch_assoc($resultGetCaseLocation))
			{
				$resultData[] = $rowresultGetCaseLocation['location'];
			}	
		}
		elseif($_POST['customtext'])
		{
			$keyword = strval($_POST['customtext']);
			$search_param = "{$keyword}%";
			$queryGetCaseCustomText = "SELECT distinct(case_customtext) FROM docket_customtext WHERE user_id = '".$_SESSION['userid']."' and case_customtext LIKE '%".$search_param."' limit 5";
			$resultGetCaseCustomtext= mysqli_query($docketDataSubscribe,$queryGetCaseCustomText);
			while($rowresultGetCaseCustomText = mysqli_fetch_assoc($resultGetCaseCustomtext))
			{
				$resultData[] = $rowresultGetCaseCustomText['case_customtext'];
			}	
		}
		
		if (empty($resultData)) {
			$resultData[]= "No Specific data";
			echo json_encode($resultData);
		}
		else{
			echo json_encode($resultData);
		}
?>




