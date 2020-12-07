<?php
require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
//global $docketDataSubscribe;
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$updateGooglePrefrencesSQL = "UPDATE users_tool_option SET googlecontactprefrence = '".$_POST['googlePrefrence']."' WHERE user_id = '".$_POST['userID']."' AND authenticator = '".$_POST['autheticatorEmailID']."'";
$result = mysqli_query($docketDataSubscribe,$updateGooglePrefrencesSQL) or trigger_error("Query Failed! SQL: $updateGooglePrefrencesSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR); 
echo $responseHtml = "Successfully Update";

?>



