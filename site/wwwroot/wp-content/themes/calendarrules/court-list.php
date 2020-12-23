<?php
/*
Template Name: Court List
 */

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	require_once 'Connections/docketDataSubscribe.php';
	include 'globals/global_courts.php';
	$docketData = $GLOBALS['docketData'];
	$database_docketData = $GLOBALS['database_docketData'];
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

	if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	  $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketDataSubscribe,$theValue) : mysqli_escape_string($docketDataSubscribe,$theValue);

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


    $query_States = "SELECT * FROM court_pricing where State <> 'Other' ORDER BY State ASC";
    $States = mysqli_query($docketDataSubscribe,$query_States);
    if (!$States) {
        die("Database query failed: " . mysqli_connect_error($docketDataSubscribe));
    }
    $row_States = mysqli_fetch_assoc($States);
    $totalRows_States = mysqli_num_rows($States);

    $query_Courts = "SELECT * FROM courts WHERE courtSystem_Description = 'United States' ORDER BY type_SystemID DESC";
    $Courts = mysqli_query($docketDataSubscribe,$query_Courts);
    $row_Courts = mysqli_fetch_assoc($Courts);
    //echo "<br>";
    $totalRows_Courts = mysqli_num_rows($Courts);

	?>
<h1>Available Court Rules</h1>
<p>CalendarRules builds and maintains court rules from jurisdictions across the United States, including a mix of Federal, State, Appellate, Judges, Bankruptcy courts and agencies. Rules are available in all states, plus Puerto Rico and the Virgin Islands.  We build new rule sets for free, once we have a subscriber. <em>(click arrows to expand)</em></p>
<div class="divider"></div>
<?php
//echo $hostname_docketData;
	echo '<ul class="triggersnav">';
	do {

		$query_Courts_2 = "SELECT * FROM `court_pricing` WHERE `State` = '" . $row_States['State'] . "'";
		$Courts_2 = mysqli_query($docketDataSubscribe,$query_Courts_2);
		$row_Courts_2 = mysqli_fetch_assoc($Courts_2);
		$totalRows_Courts_2 = mysqli_num_rows($Courts_2);
		if ($row_Courts_2['Description'] == "") {

			$thedesc = "We currently do not offer rule-sets in this state, however we are constantly adding new jurisdictions. Please contact us if you would like to see this state prioritized as we release rule-sets based on customer demand.";

		} else {
			$thedesc = $row_Courts_2['Description'];
		}

		echo '<li><a href="javascript:void"><b> ' . $row_States['State'] . '</b></a><ul><li>' . $thedesc . '</i></li></ul>';

		$query_Courts = sprintf("SELECT * FROM courts WHERE courtSystem_Description = %s ORDER BY type_Description, Description ASC", GetSQLValueString($docketDataSubscribe,$row_States['State'], "text"));
		$Courts = mysqli_query($docketDataSubscribe,$query_Courts);
		$row_Courts = mysqli_fetch_assoc($Courts);
		$totalRows_Courts = mysqli_num_rows($Courts);

		echo "<ul id='second'>";
		$lastcourttype = "";

		do {
			if ($lastcourttype != $row_Courts['type_Description']) {
				echo '<li><h4> ' . $row_Courts['type_Description'] . '</h4>';
				$lastcourttype = $row_Courts['type_Description'];  
			}

			echo '<li> ' . $row_Courts['description'];
		} while ($row_Courts = mysqli_fetch_assoc($Courts));

		echo '</li></ul>';

	} while ($row_States = mysqli_fetch_assoc($States));

	echo '</li></ul>';

}

genesis(); // <- everything important: make sure to include this.

?>