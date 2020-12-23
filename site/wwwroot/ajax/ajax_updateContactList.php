<?php
require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
//global $docketDataSubscribe;
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$insertSQL = "INSERT INTO userContactUpdate (userid,authenticator,userContactName,	userContactEmail,userContactPhone) VALUES (".$_POST['userID'].",'".$_POST['autheticatorEmailID']."','".$_POST['userName']."','".$_POST['userEmail']."','".$_POST['userPhone']."')";
$result = mysqli_query($docketDataSubscribe,$insertSQL) or trigger_error("Query Failed! SQL: $insertSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR); 
echo $responseHtml = "Successfully Update";

?>



