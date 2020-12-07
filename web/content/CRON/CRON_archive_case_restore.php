<?php 
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/Connections/docketData.php');
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/Connections/docketDataSubscribe.php');
require_once('/mnt/stor1-wc1-dfw1/375786/www.googledocket.com/web/content/googleCalender/google-calendar-api.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$capi = new GoogleCalendarApi();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
/*
Description: This script is written to Restore Archived Case and Sub events
Duration : This script is been called from CRON add calendar events
*/

$msg = 'UPDATE CASE RESTORE CRON JOB TIMESTAMP: ';
		$queryUpdateCronJOB = "UPDATE `cronjobrun` set jobrunat = now() where id = 4";
		$userUpdateCronJOB = mysqli_query($docketDataSubscribe,$queryUpdateCronJOB);