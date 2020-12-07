<?php
/*
Template Name: Get a Quote Court List
 */

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	require_once 'Connections/docketData_courtlist.php';
	include 'globals/global_courts.php';
	$docketData = $GLOBALS['docketData'];
	$database_docketData = $GLOBALS['database_docketData'];

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

	mysqli_select_db($docketData,$database_docketData);

	$query_States = "SELECT * FROM court_pricing where State <> 'Other' ORDER BY State ASC";
	$States = mysqli_query($docketData,$query_States, $docketData) or die(mysqli_error($docketData));
	$row_States = mysqli_fetch_assoc($States);
	$totalRows_States = mysqli_num_rows($States);

	$query_Courts = "SELECT * FROM courts WHERE courtSystem_Description = 'United States' ORDER BY type_SystemID DESC";
	$Courts = mysqli_query($docketData,$query_Courts, $docketData) or die(mysqli_error($docketData));
	$row_Courts = mysqli_fetch_assoc($Courts);
	$totalRows_Courts = mysqli_num_rows($Courts);

	?>
	<?php if (@$_GET['msg'] == 1) {?>
		<div class="alert alert-success">
		Thank You for Your <b>Quote Request!</b> We have received your request successfully. Our team will be in touch shortly.
		</div>
	<?php }?>
	<?php if (@$_GET['err'] == 1) {?>
		<div class="alert alert-danger">
		Please select atleast one court.
		</div>
	<?php }?>
<h1>Get a Quote</h1>
<p>CalendarRules builds and maintains court rules from jurisdictions across the United States, including a mix of Federal, State, Appellate, Judges, Bankruptcy courts and agencies. Rules are available in all states, plus Puerto Rico and the Virgin Islands.  We build new rule sets for free, once we have a subscriber. <em>(click arrows to expand)</em></p>
<form action="get-quote-form" id="get_a_quote" name="get_a_quote" method="POST">
<div class="divider"></div>
<div><input type="submit" name="next" id="next" value="Next" style="margin-bottom: 25px;"></div>
<?php
echo '<ul class="triggersnav">';

	do {

		$query_Courts_2 = "SELECT * FROM `court_pricing` WHERE `State` = '" . $row_States['State'] . "'";
		$Courts_2 = mysqli_query($docketData,$query_Courts_2, $docketData) or die(mysqli_error($docketData));
		$row_Courts_2 = mysqli_fetch_assoc($Courts_2);
		$totalRows_Courts_2 = mysqli_num_rows($Courts_2);
		if ($row_Courts_2['Description'] == "") {

			$thedesc = "We currently do not offer rule-sets in this state, however we are constantly adding new jurisdictions. Please contact us if you would like to see this state prioritized as we release rule-sets based on customer demand.";

		} else {
			$thedesc = $row_Courts_2['Description'];
		}

		echo '<li><a href="javascript:void(0);"><b> ' . $row_States['State'] . '</b></a><ul><li>
		<input type="checkbox" class="parentCheckBox" id="' . $row_States['systemID'] . '" value="' . $row_States['systemID'] . '">
		&nbsp;Select All <b>' . $row_States['State'] . '</b> Courts</i></li></ul>';

		$query_Courts = sprintf("SELECT * FROM courts WHERE courtSystem_Description = %s ORDER BY type_Description, Description ASC", GetSQLValueString($row_States['State'], "text"));
		$Courts = mysqli_query($docketData,$query_Courts, $docketData) or die(mysqli_error($docketData));
		$row_Courts = mysqli_fetch_assoc($Courts);
		$totalRows_Courts = mysqli_num_rows($Courts);

		echo "<ul class='second'>";
		$lastcourttype = "";

		do {
			if ($lastcourttype != $row_Courts['type_Description']) {
				echo '<li><h4> ' . $row_Courts['type_Description'] . '</h4><input type="checkbox" class="state_courts_' . $row_Courts['courtSystem_SystemID'] . ' childCheckBox" value="' . $row_Courts['type_SystemID'] . '" id="' . $row_Courts['courtSystem_SystemID'] . '_' . $row_Courts['systemID'] . '">&nbsp;Select All <b>' . $row_Courts['type_Description'] . '</b> Courts<br>';
				$lastcourttype = $row_Courts['type_Description'];
			}

			echo '<li class="third"><input type="checkbox" class="state_child_' . $row_Courts['type_SystemID'] . ' child_child_' . $row_Courts['courtSystem_SystemID'] . '" value="' . $row_Courts['courtid'] . '"
			id="' . $row_Courts['systemID'] . '_' . $row_Courts['type_SystemID'] . '" name="court_list[]"> ' . $row_Courts['description'];
		} while ($row_Courts = mysqli_fetch_assoc($Courts));

		echo '</li></ul>';

	} while ($row_States = mysqli_fetch_assoc($States));

	echo '</li></ul>';

	echo '<div><input type="submit" name="next" id="next" value="Next" style="margin-top: 35px;"></div></form>';
	?>
<script type="text/javascript">
jQuery(function ($) {
    //clicking the parent checkbox should check or uncheck all child checkboxes
    $(".parentCheckBox").click(function () {
    	console.log($(this).val());
        $(this).closest('div').find('.state_courts_'+$(this).val()).prop('checked', this.checked);
		$(this).closest('div').find('.child_child_'+$(this).val()).prop('checked', this.checked);

    });

    $(".childCheckBox").click(function () {
    	console.log($(this).val());
        $(this).closest('ul').find('.state_child_'+$(this).val()).prop('checked', this.checked);
    });

    //clicking the last unchecked or checked checkbox should check or uncheck the parent checkbox
    /*$('.childCheckBox').click(function () {
        var $fs = $(this).closest('ul');
        $fs.find('.parentCheckBox').prop('checked', !$fs.find('.childCheckBox').is(':not(:checked)'))
    });*/
});

</script>

<?php }

genesis(); // <- everything important: make sure to include this.

?>