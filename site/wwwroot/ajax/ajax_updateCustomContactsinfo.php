<?php
require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
//global $docketDataSubscribe;
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$userContactName = $_POST['contactNameId'];
$userContactEmail = $_POST['contactEmailId'];
$userContactPhone = $_POST['contactNumberId'];
$updateSQL = "UPDATE userContactUpdate SET userContactName = '".$userContactName."',userContactEmail='".$userContactEmail."',userContactPhone='".$userContactPhone."' WHERE id = ".$_POST['id']."" ;
$result = mysqli_query($docketDataSubscribe,$updateSQL) or trigger_error("Query Failed! SQL: $updateSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR); 
echo $responseHtml = "Successfully Updated";

?>



