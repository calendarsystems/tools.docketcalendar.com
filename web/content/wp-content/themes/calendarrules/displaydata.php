<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Displaydata
 */
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
session_start();
function custom_loop() {
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
    require('globals/global_tools.php');
    require('globals/global_courts.php');
?>
	<style>
	#header{ display: none; }  
	#nav { display: none; }  
	#footer-widgets { display: none; }  
	body{
		background: white; !important;
	}
	#footer .creds { display: none; }  
	</style>

<?php
$id = $_GET['id'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$sqlEvents = "SELECT description FROM  events WHERE id = ".$id."";
$resultset = mysqli_query($docketDataSubscribe, $sqlEvents) or die("database error:". mysqli_error($docketDataSubscribe));
$dataset = mysqli_fetch_assoc($resultset);
echo $dataset['description'];
}
genesis();
?>