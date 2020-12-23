<?php
require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
//global $docketDataSubscribe;

$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$deleteSQL = "DELETE FROM userContactUpdate  WHERE id = ".$_POST['id']."" ;
$result = mysqli_query($docketDataSubscribe,$deleteSQL) or trigger_error("Query Failed! SQL: $deleteSQL - Error: ".mysqli_error($docketDataSubscribe), E_USER_ERROR); 
echo "Deleted";
?>



