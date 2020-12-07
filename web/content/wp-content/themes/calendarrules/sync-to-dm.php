<?php
/* 
Template Name: sync to dm
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	
	
include('Connections/docketData.php');
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];


mysqli_select_db($docketData,$database_docketData);
$query_userInfo = sprintf("SELECT * FROM users ORDER BY id");
$userInfo = mysqli_query($docketData,$query_userInfo, $docketData) or die(mysqli_error($docketData));
$userInfoArray = array();

while ($row = mysqli_fetch_assoc($userInfo)) {
	$userInfoArray[] = $row;
}


?>
<table><tr><td>
<h1>Sync to Data Manager</h1> 
 </td></tr></table>
<table>
<?php foreach ($userInfoArray as $user) { 
	mysqli_select_db($docketData,$database_docketData);
	$query_attyInfo = sprintf("SELECT * FROM attorneys WHERE isActive = 1 AND user_id = ".$user['id']);
	$attyInfo = mysqli_query($docketData,$query_attyInfo, $docketData) or die(mysqli_error($docketData));
	$attyInfoArray = array();

	while ($row = mysqli_fetch_assoc($attyInfo)) {
		$attyInfoArray[] = $row;
	}

	mysqli_select_db($docketData,$database_docketData);
	$query_stateInfo = sprintf("SELECT * FROM cart WHERE courttype = 'state' AND subscribed = ".$user['id']);
	$stateInfo = mysqli_query($docketData,$query_stateInfo, $docketData) or die(mysqli_error($docketData));
	$stateInfoArray = array();

	while ($row = mysqli_fetch_assoc($stateInfo)) {
		$stateInfoArray[] = $row;
	}

	mysqli_select_db($docketData,$database_docketData);
	$query_courtInfo = sprintf("SELECT * FROM cart WHERE courttype != 'state' AND subscribed = ".$user['id']);
	$courtInfo = mysqli_query($docketData,$query_courtInfo, $docketData) or die(mysqli_error($docketData));
	$courtInfoArray = array();

	while ($row = mysqli_fetch_assoc($courtInfo)) {
		$courtInfoArray[] = $row;
	}


?>

  <tr>
	<td>
    <?php echo "FIRM: <br>".$user['id']." - ".$user['firm']." - ".$user['sessionID']."<br>USERS:"; 
		foreach ($attyInfoArray as $atty) {
			echo "<br>".$atty['name']." - ".$atty['username'];
		}

		echo " <br>STATES:";
		foreach ($stateInfoArray as $state) {
			$query_stateDesc = sprintf("SELECT * FROM courts WHERE courtSystem_SystemID = '".$state[systemid]."'");
			$stateDesc = mysqli_query($docketData,$query_stateDesc, $docketData) or die(mysqli_error($docketData));
			$stateDescRow=mysqli_fetch_assoc($stateDesc);
	
			echo "<br>".$state['systemid']." - ".$stateDescRow['courtSystem_Description'];
		}

		echo " <br>COURTS:";		
		foreach ($courtInfoArray as $court) {
			$query_courtDesc = sprintf("SELECT * FROM courts WHERE systemID = '".$court[systemid]."'");
			$courtDesc = mysqli_query($docketData,$query_courtDesc, $docketData) or die(mysqli_error($docketData));
			$courtDescRow=mysqli_fetch_assoc($stateDesc);
	
			echo "<br>".$court['systemid']." - ".$courtDescRow['description'];
		}
		
		
		?>
    <br />= = = = = = = = = = = = =
    </td>
    
</tr>
<?php } ?>
</table>  



<?php
}

genesis();
?>