<?php
require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
//global $docketDataSubscribe;

$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

$viewSQL = "SELECT * FROM userContactUpdate  WHERE id = ".$_POST['id']."" ;
$result = mysqli_query($docketDataSubscribe,$viewSQL) or trigger_error("Query Failed! SQL: $viewSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR); 
$rowData = mysqli_fetch_array($result);
$responseHtml['userContactName'] = $rowData['userContactName'];
$responseHtml['userContactEmail'] = $rowData['userContactEmail'];
$responseHtml['userContactPhone'] = $rowData['userContactPhone'];
$result_html = $responseHtml;
echo json_encode($result_html);

?>



