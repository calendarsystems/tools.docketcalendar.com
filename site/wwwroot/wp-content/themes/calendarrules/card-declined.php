<?php
/* 
Template Name: card declined
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
require_once('Connections/docketData.php'); ?>
<?php

include('globals/global_courts.php');

?>

<h1 class="entry-title">Checkout</h1>
      	<h2>Card failed. Please try again.</h2>

              <form action="procs/process_payment.php" method="post">
              <h3>Order Summary</h3>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">             
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
      </tr>
      	         <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php do { ?> 
                <tr>
                  <td><a href="proc/remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_attornys_cart['name']; ?></td>
      	          <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"></a></td>
    	          </tr>
      	        <tr>
                <?php } while ($row_attornys_cart = mysql_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
      	            <tr>
      	              <td>&nbsp;</td>
      	              <td><div class="divider"></div><strong>
      	              States  (<?php echo $totalRows_cartState; ?>)</strong></td>
      	          <td width="14">&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cartState > 0) { // Show if recordset not empty ?>
      	        <?php
						$statePrice = 0;
						 do { ?>
      	        <tr>
      	          <td><a href="proc/remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <?php if ($statePrice < $row_cartState['Price']){
							$statePrice = $row_cartState['Price'];
						}
						?>
      	          <td><?php echo $row_cartState['courtSystem_Description']; ?>
                  <?php $stateIDs = $stateIDs .  $row_cartState['systemid']. ','; ?>
                  </td>
      	          <td><a href="proc/remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cartState = mysql_fetch_assoc($cartState)); ?>
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
      	          <td><div class="divider"></div><strong>
      	            Courts (<?php echo $totalRows_cart; ?>)</strong><div class="divider"></div></td>
      	          <td>&nbsp;</td>
      </tr>
      	        <?php if ($totalRows_cart > 0) { // Show if recordset not empty ?>
      	        <?php do { ?>
      	        <tr>
      	          <td><a href="proc/remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_cart['description']; ?>
                  <?php $courtIDs = $courtIDs . $row_cart['systemid']. ','; ?>
                  </td>
      	          <td><a href="proc/remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cart = mysql_fetch_assoc($cart)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        
      	        <?php include('include/inc_pricing.php'); ?>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal"><br clear="all">Recurring Total: <strong>$<?php echo number_format($new_mo_cost, 2);?>/mo</strong></td>
      	          <td align="right" class="cartTotal"><span class="smallText">
      	            <input name="CurrentChargeAmount" type="hidden" value="<?php echo number_format($new_mo_cost, 2);?>" />
      	          </span></td>
   	            </tr>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">Amount Charged Today:
      	            <strong><?php
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
							$current_charge = $new_mo_cost;
						}
						?></strong></td>
      	          <td class="smallText"><input name="chrgAmount" type="hidden" value="<?php echo number_format($current_charge, 2); ?>" /></td>
   	            </tr>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center" class="smallText"><br clear="all">
                  Your charge today will be prorated to the number of days remaining in this month. Beginning next month, your card will be charged the &quot;Recurring Total&quot; amount on the 1st of each month.</td>
      	          <td align="right">&nbsp;</td>
      </tr>
   	          </table></div>

                <h3>Create Login</h3><div class="widget"><table width="100%" align="center" class="widget-wrap">                   <tr>
                  <td width="30%"><p><strong>Username:</strong></p></td>
                  <td width="70%"><label for="firstname"></label>
                    <span id="sprytextfield1">
                    <input name="username" type="text" id="username" autocomplete="off" value="<?php echo $_GET['username']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span>                    <div id="msgbox"></div>
                  </td>
                  </tr>
                <tr>
                  <td><p><strong>Password:</strong></p></td>
                  <td><label for="lastname"></label>
                    <span id="sprytextfield2">
                    <input name="password" type="text" id="password" value="<?php echo $_GET['password']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td><p><strong>Email:</strong></p></td>
                  <td><label for="billingaddress"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" value="<?php echo $_GET['email']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Firm/Practice Name:</strong></p></td>
                  <td><label for="cvv"></label>
                    <input name="firm" type="text" id="firm" value="<?php echo $_GET['firm']; ?>" size="50" /></td>
                </tr>
              </table></div>
 <h3>Terms and Conditions</h3>
 <div class="terms">
    <?php include('include/inc_terms.php'); ?>
                </div>
                <span id="sprycheckbox1">
                <input type="checkbox" name="Agree" id="Agree" />
				<label for="Agree">Accept</label><br />
                <span class="checkboxRequiredMsg">Please agree to the terms &amp; conditions.</span></span>
<h3>Payment Information </h3>
 <div class="widget"><table width="100%" align="center" class="widget-wrap">                   <tr>
                  <td width="30%"><p><strong>First Name</strong></p></td>
                  <td width="70%"><label for="firstname"></label>
                    <span id="sprytextfield4">
                    <input name="firstname" type="text" id="firstname" value="<?php echo $_GET['firstname']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                <tr>
                  <td><p><strong>Last Name</strong></p></td>
                  <td><label for="lastname"></label>
                    <span id="sprytextfield5">
                    <input name="lastname" type="text" id="lastname" value="<?php echo $_GET['lastname']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td><p><strong>Billing Address</strong></p></td>
                  <td><label for="billingaddress"></label>
                    <span id="sprytextfield6">
                    <input name="billingaddress" type="text" id="billingaddress" value="<?php echo $_GET['billingaddress']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Billing City</strong></p></td>
                  <td><span id="sprytextfield7">
                    <input name="city" type="text" id="city" value="<?php echo $_GET['city']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Billing State</strong></p></td>
                  <td><span id="sprytextfield8">
                    <input name="state" type="text" id="state" value="<?php echo $_GET['state']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Billing Zip</strong></p></td>
                  <td><span id="sprytextfield9">
                    <input name="zip" type="text" id="zip" value="<?php echo $_GET['zip']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Card Number</strong></p></td>
                  <td><span id="sprytextfield10">
                  <input name="cardnumber" type="text" id="cardnumber" size="50" />
                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Expiration Date</strong></p></td>
                  <td><label for="month"></label>
                    <select name="month" id="month">
                      <option>Month</option>
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
                      <option>Year</option>
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
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>CVV</strong></p></td>
                  <td><input name="cvv" type="text" id="cvv" size="8" /></td>
                </tr>
                <tr>
                  <td nowrap="nowrap">&nbsp;</td>
                  <td align="right"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
              </table></div>
              </form>



<?php
mysql_free_result($States);

mysql_free_result($Courts);

mysql_free_result($SelectedState);

mysql_free_result($cart);

mysql_free_result($stateComments);

}

genesis();
?>
