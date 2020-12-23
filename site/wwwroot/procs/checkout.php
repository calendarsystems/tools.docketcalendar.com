<?php require_once('Connections/docketData.php'); 
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

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



//add to cart
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "select")) {
  $insertSQL = sprintf("INSERT INTO cart (sessionid, systemid, courttype) VALUES (%s, %s, %s)",
                       GetSQLValueString(session_id(), "text"),
                       GetSQLValueString($_POST['court'], "int"),
                       GetSQLValueString($_POST['courttype'], "int"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}
// add state to cart
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "state")) {
   // remove any individual courts first for this state.
  $deleteSQL = sprintf("DELETE FROM cart WHERE courttype=%s AND sessionid=%s",
                       GetSQLValueString($_POST['court'], "text"), 
					   GetSQLValueString(session_id(), "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$deleteSQL) or die(mysqli_error($docketData));
  
  $insertSQL = sprintf("INSERT INTO cart (sessionid, systemid, courttype) VALUES (%s, %s, %s)",
                       GetSQLValueString(session_id(), "text"),
                       GetSQLValueString($_POST['court'], "int"),
                       GetSQLValueString('state', "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}

mysqli_select_db($docketData,$database_docketData);
$query_States = "SELECT * FROM court_pricing ORDER BY State ASC";
$States =mysqli_query($docketData,$query_States) or die(mysqli_error($docketData));
$row_States = mysqli_fetch_assoc($States);
$totalRows_States = mysqli_num_rows($States);

$colname_Courts = "-1";
if (isset($_GET['state'])) {
  $colname_Courts = $_GET['state'];
} else {
$colname_Courts = $_SESSION['state'];	
}
mysqli_select_db($docketData,$database_docketData);
$query_Courts = sprintf("SELECT * FROM courts WHERE courtSystem_Description = %s", GetSQLValueString($colname_Courts, "text"));
$Courts =mysqli_query($docketData,$query_Courts) or die(mysqli_error($docketData));
$row_Courts = mysqli_fetch_assoc($Courts);
$totalRows_Courts = mysqli_num_rows($Courts);

$colname_SelectedState = "-1";
if (isset($_GET['state'])) {
  $colname_SelectedState = $_GET['state'];
} else {
  $colname_SelectedState = $_SESSION['state'];
}
mysqli_select_db($docketData,$database_docketData);
$query_SelectedState = sprintf("SELECT
courts.courtid,
courts.code,
courts.courtSystem_Description,
courts.courtSystem_SystemID,
courts.courtSystem_Code,
courts.description,
courts.price,
courts.systemID,
courts.type_Description,
courts.type_SystemID,
court_pricing.Price AS RecurringPrice,
court_pricing.Description AS StateText
FROM
courts
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE courtSystem_Description = %s AND courts.price <> '0.00'
ORDER BY
courts.type_Description ASC
", GetSQLValueString($colname_SelectedState, "text"));
$SelectedState =mysqli_query($docketData,$query_SelectedState) or die(mysqli_error($docketData));
$row_SelectedState = mysqli_fetch_assoc($SelectedState);
$totalRows_SelectedState = mysqli_num_rows($SelectedState);


$colname_stateComments = $colname_Courts;

mysqli_select_db($docketData,$database_docketData);
$query_stateComments = sprintf("SELECT * FROM court_pricing WHERE `State` = %s", GetSQLValueString($colname_stateComments, "text"));
$stateComments =mysqli_query($docketData,$query_stateComments) or die(mysqli_error($docketData));
$row_stateComments = mysqli_fetch_assoc($stateComments);
$totalRows_stateComments = mysqli_num_rows($stateComments);


mysqli_select_db($docketData,$database_docketData);
$query_cartState = sprintf("SELECT DISTINCT
cart.id,
cart.sessionid,
cart.systemid,
cart.courttype,
courts.courtSystem_Description,
court_pricing.Price
FROM
cart
Inner Join courts ON cart.systemid = courts.courtSystem_SystemID
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE cart.subscribed = '0' AND cart.courttype = 'state' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
$cartState =mysqli_query($docketData,$query_cartState) or die(mysqli_error($docketData));
$row_cartState = mysqli_fetch_assoc($cartState);
$totalRows_cartState = mysqli_num_rows($cartState);

mysqli_select_db($docketData,$database_docketData);
$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.subscribed = '0' AND cart.courttype <> 'state' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
$cart =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
$cart2 =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
$row_cart = mysqli_fetch_assoc($cart);
$row_cart2 = mysqli_fetch_assoc($cart2);
$totalRows_cart = mysqli_num_rows($cart);
$totalRows_cart2 = mysqli_num_rows($cart2);

$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s and isActive is null or sessionid = '". session_id() ."' and isActive is null ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);

function checkCourt($systemID){
			global $database_docketData;
			mysqli_select_db($docketData,$database_docketData);
			$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.courttype <> 'state' AND cart.systemid = '". $systemID ."' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
			$cart =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
			$row_cart = mysqli_fetch_assoc($cart);
    if ($row_cart['id'] != ''){
	return 'yes';
	}else{
	return 'no';
}
}

?>
<?php include('inc_top.php'); ?>

      <div class="middle_blank"> 
      	<div>
      	<h1>Checkout

      	  </h1><table width="100%" border="0" cellpadding="18">
      	    <tr>
      	      <td align="center" valign="top">
              <div class="orderForm">
              <form action="process_payment.php" method="post">
              <p class="formTitles">Order Summary</p>
              <table width="500" border="0" align="center" cellpadding="3">
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	         <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php do { ?> 
                <tr>
                  <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_attornys_cart['name']; ?></td>
      	          <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"></a></td>
    	          </tr>
      	        <tr>
                <?php } while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
      	            <tr>
      	              <td>&nbsp;</td>
      	              <td><strong><br />
      	              States  (<?php echo $totalRows_cartState; ?>)</strong></td>
      	          <td width="14">&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cartState > 0) { // Show if recordset not empty ?>
      	        <?php
						$statePrice = 0;
						 do { ?>
      	        <tr>
      	          <td><a href="remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <?php if ($statePrice < $row_cartState['Price']){
							$statePrice = $row_cartState['Price'];
						}
						?>
      	          <td><?php echo $row_cartState['courtSystem_Description']; ?>
                  <?php $stateIDs = $stateIDs .  $row_cartState['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cartState = mysqli_fetch_assoc($cartState)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        <?php 
					   $state_courts = $totalRows_cartState;
					   if ($state_courts == 1){
							$state_court_cost = $statePrice;   
					   }elseif ($state_courts == 0){
						   $state_court_cost = 0;
					   }else{
							$state_court_cost = ($state_courts - 1) * 19.95 + $statePrice;   
					   }
					   ?>
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong><br />
      	            Courts (<?php echo $totalRows_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cart > 0) { // Show if recordset not empty ?>
      	        <?php do { ?>
      	        <tr>
      	          <td><a href="remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_cart['description']; ?>
                  <?php $courtIDs = $courtIDs . $row_cart['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cart = mysqli_fetch_assoc($cart)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
   	            </tr>
      	        <?php include('inc_pricing.php'); ?>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">Recurring Total: $<?php echo number_format($new_mo_cost, 2);?>/mo</td>
      	          <td align="right" class="cartTotal"><span class="smallText">
      	            <input name="CurrentChargeAmount" type="hidden" value="<?php echo number_format($new_mo_cost, 2);?>" />
      	          </span></td>
   	            </tr>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">Amount Charged Today:
      	            <?php
						// calculate days remaining for month
						if (date('j') > 1){
						$daysused = date(d);
							//echo $daysused;
							$daysinmonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
							$daysleft = $daysinmonth - $daysused;
							$current_charge = ($new_mo_cost/$daysinmonth) * $daysleft;
							echo '$'.number_format($current_charge, 2);
						}else{
							echo '$'.number_format($new_mo_cost, 2);
						}
						?></td>
      	          <td class="smallText"><input name="chrgAmount" type="hidden" value="<?php echo number_format($current_charge, 2); ?>" /></td>
   	            </tr>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">Your charge today will be prorated to the number of days remaining in this month. Beginning next month, your card will be charged the &quot;Recurring Total&quot; amount on the 1st of each month.</td>
      	          <td align="right">&nbsp;</td>
      	          </tr>
   	          </table>
              
              <p><br />
                <img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Create Login</span></p>
              <table width="700" align="center" cellpadding="5">
                <tr>
                  <td colspan="2">Already have an account? <a href="login.php"> Login!</a></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td width="151" height="41"><strong>Username:</strong></td>
                  <td width="243"><label for="firstname"></label>
                    <span id="sprytextfield1">
                    <input name="username" id="username" autocomplete="off" type="text" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span><div id="msgbox"></div>
                    </td>
                  <td width="266">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Password:</strong></td>
                  <td><label for="password"></label>
                    <span id="sprytextfield2">
                    <input name="password" type="text" id="password" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td align="center">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Email:</strong></td>
                  <td><label for="email"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
                    <div id="msgbox2"></div>
                    </td>
                  <td align="center">&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Firm/Practice Name:</strong></td>
                  <td><label for="cvv"></label>
                    <input name="firm" type="text" id="firm" size="40" /></td>
                  <td align="center">&nbsp;</td>
                </tr>
              </table>
              <p><img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Terms and Conditions</span></p><div class="terms">
               <?php include('inc_terms.php'); ?>
                </div></p>
                <span id="sprycheckbox1">
                <input style="margin-left: 600px; " type="checkbox" name="Agree" id="Agree" />
				<label for="Agree">Accept</label><br />
                <span class="checkboxRequiredMsg" style="text-align:right;">Please agree to the terms & conditions.</span></span>
                
              <p><img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Payment Information </span></p>
              <table width="543" align="center" cellpadding="5">
                <tr>
                  <td width="171"><strong>First Name</strong></td>
                  <td width="265"><label for="firstname"></label>
                    <span id="sprytextfield4">
                    <input name="firstname" type="text" id="firstname" value="" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td width="59">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Last Name</strong></td>
                  <td><label for="lastname"></label>
                    <span id="sprytextfield5">
                    <input name="lastname" type="text" id="lastname" value="" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Billing Address</strong></td>
                  <td><label for="billingaddress"></label>
                    <span id="sprytextfield6">
                    <input name="billingaddress" type="text" id="billingaddress" value="" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Billing City</strong></td>
                  <td><span id="sprytextfield7">
                    <input name="city" type="text" id="city" value="" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Billing State</strong></td>
                  <td><span id="spryselect1">
                    <select name="state" size="1">
                      <option value=""selected="selected">Select</option>
                      <option value="AL">Alabama</option>
                      <option value="AK">Alaska</option>
                      <option value="AZ">Arizona</option>
                      <option value="AR">Arkansas</option>
                      <option value="CA">California</option>
                      <option value="CO">Colorado</option>
                      <option value="CT">Connecticut</option>
                      <option value="DE">Delaware</option>
                      <option value="DC">Dist of Columbia</option>
                      <option value="FL" >Florida</option>
                      <option value="GA">Georgia</option>
                      <option value="HI">Hawaii</option>
                      <option value="ID">Idaho</option>
                      <option value="IL">Illinois</option>
                      <option value="IN">Indiana</option>
                      <option value="IA">Iowa</option>
                      <option value="KS">Kansas</option>
                      <option value="KY">Kentucky</option>
                      <option value="LA">Louisiana</option>
                      <option value="ME">Maine</option>
                      <option value="MD">Maryland</option>
                      <option value="MA">Massachusetts</option>
                      <option value="MI">Michigan</option>
                      <option value="MN">Minnesota</option>
                      <option value="MS">Mississippi</option>
                      <option value="MO">Missouri</option>
                      <option value="MT">Montana</option>
                      <option value="NE">Nebraska</option>
                      <option value="NV">Nevada</option>
                      <option value="NH">New Hampshire</option>
                      <option value="NJ">New Jersey</option>
                      <option value="NM">New Mexico</option>
                      <option value="NY">New York</option>
                      <option value="NC">North Carolina</option>
                      <option value="ND">North Dakota</option>
                      <option value="OH">Ohio</option>
                      <option value="OK">Oklahoma</option>
                      <option value="OR">Oregon</option>
                      <option value="PA">Pennsylvania</option>
                      <option value="RI">Rhode Island</option>
                      <option value="SC">South Carolina</option>
                      <option value="SD">South Dakota</option>
                      <option value="TN">Tennessee</option>
                      <option value="TX">Texas</option>
                      <option value="UT">Utah</option>
                      <option value="VT">Vermont</option>
                      <option value="VA">Virginia</option>
                      <option value="WA">Washington</option>
                      <option value="WV">West Virginia</option>
                      <option value="WI">Wisconsin</option>
                      <option value="WY">Wyoming</option>
                    </select>
                    <span class="selectRequiredMsg">Please select an item.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Billing Zip</strong></td>
                  <td><span id="sprytextfield9">
                    <input name="zip" type="text" id="zip" value="" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Card Number</strong></td>
                  <td><span id="sprytextfield10">
                  <input name="cardnumber" type="text" id="cardnumber" value="" size="50" />
                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Expiration Date</strong></td>
                  <td><label for="month"></label>
                    <select name="month" id="month">
                      <option selected="selected">Month</option>
                      <option value="01">1</option>
                      <option value="02">2</option>
                      <option value="03">3</option>
                      <option value="04">4</option>
                      <option value="05">5</option>
                      <option value="06">6</option>
                      <option value="07">7</option>
                      <option value="08">8</option>
                      <option value="09">9</option>
                      <option value="10">10</option>
                      <option value="11">11</option>
                      <option value="12">12</option>
                    </select> <label for="year"></label>
                    <select name="year" id="year">
                      <option selected="selected">Year</option>
                      <option value="2011">2011</option>
                      <option value="2012">2012</option>
                      <option value="2013">2013</option>
                      <option value="2014">2014</option>
                      <option value="2015">2015</option>
                      <option value="2016">2016</option>
                      <option value="2017">2017</option>
                      <option value="2018">2018</option>
                      <option value="2019">2019</option>
                      <option value="2020">2020</option>
                    </select></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>CVV</strong></td>
                  <td><input name="cvv" type="text" id="cvv" value="" size="8" /></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap">&nbsp;</td>
                  <td colspan="2" align="right"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
              </table>
              <input name="stateIDs" type="hidden" value="<?php echo $stateIDs; ?>" />
              <input name="courtIDs" type="hidden" value="<?php echo $courtIDs; ?>" />
              </form>
              </div></td>
   	        </tr>
   	      </table>
    	  
<p>&nbsp;</p>

      	<div style="clear:both;"></div>
      	</div>

      </div><!-- end middle -->
   <?php include('inc_footer.php'); ?>

<?php
mysqli_free_result($States);

mysqli_free_result($Courts);

mysqli_free_result($SelectedState);

mysqli_free_result($cart);

mysqli_free_result($stateComments);
?>
