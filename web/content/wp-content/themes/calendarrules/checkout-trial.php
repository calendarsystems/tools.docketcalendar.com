<?php
/* 
Template Name: checkout trial
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	include('globals/global_courts.php');
?>
<script src="SpryAssets/SpryValidationCheckbox.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationCheckbox.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />


      	<h1 class="entry-title">Checkout</h1>
              <form action="procs/process_trial.php" method="post">
              <h3>Order Summary</h3>
              <div class="widget"><table width="100%" align="center" class="widget-wrap">             
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
      </tr>
      	         <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php 
					 $attcount=1;
					 
					 do { ?> 
                <tr>
                  <td><a href="procs/remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_attornys_cart['name']; ?></td>
      	          <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"></a></td>
    	          </tr>
      	        <tr>
                <?php 
				if ($attcount==1) {
					$mastername=$row_attornys_cart['name'];
					$masteremail=$row_attornys_cart['email'];	
					$masterusername=$row_attornys_cart['username'];	
					$masterpassword=$row_attornys_cart['password'];					}
				
					 
				$attcount=$attcount+1;
				
				
				
				} while ($row_attornys_cart = mysql_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
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
      	          <td><a href="procs/remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <?php if ($statePrice < $row_cartState['Price']){
							$statePrice = $row_cartState['Price'];
						}
						?>
      	          <td><?php echo $row_cartState['courtSystem_Description']; ?>
                  <?php $stateIDs = $stateIDs .  $row_cartState['systemid']. ','; ?>
                  </td>
      	          <td><a href="procs/remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
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
      	          <td><a href="procs/remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_cart['description']; ?>
                  <?php $courtIDs = $courtIDs . $row_cart['systemid']. ','; ?>
                  </td>
      	          <td><a href="procs/remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
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
              <h3>Create Login</h3>
<div class="widget"><table width="100%" align="center" class="widget-wrap">                <tr>
                  <td colspan="2"><p>Already have an account? <a href="login"> Login!</a></p></td>
                  </tr>
                <tr>
                  <td><p><strong>Username:</strong></p></td>
                  <td>
                   <?php if ($totalRows_attornys_cart > 0) { ?>
 						<?php echo $masterusername; ?>
                  <input type="hidden" name="username" id="username" value="<?php echo $masterusername; ?>" /> <?php } else { ?>
                  <p>
                    <label for="firstname"></label>
                    <span id="sprytextfield1">
                    <input name="username" id="username" autocomplete="off" type="text" size="40" />
                    <input type="hidden" value="Y" name="addattorneyfirst" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></p>                    <div id="msgbox"></div> <?php } ?>
                    
                    </td>
                </tr>
                <tr>
                  <td><p><strong>Password:</strong></p></td>
                  <td>     <?php if ($totalRows_attornys_cart > 0) { ?>
 						<?php echo $masterpassword; ?>
                  <input type="hidden" name="password" id="password" value="<?php echo $masterpassword; ?>" /> <?php } else { ?><p>
                    <label for="password"></label>
                    <span id="sprytextfield2">
                    <input name="password" type="text" id="password" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></p></td><?php } ?>
                </tr>
                <tr>
                  <td><p><strong>Email:</strong></p></td>
                  <td><?php if ($totalRows_attornys_cart > 0) { ?>
 						<?php echo $masteremail; ?>
                  <input type="hidden" name="email" id="email" value="<?php echo $masteremail; ?>" /> <?php } else { ?><p>
                    <label for="email"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
                  </p>
                    <div id="msgbox2"></div>
                    </td><?php } ?>
                </tr>
                <tr>
                  <td nowrap="nowrap"><p><strong>Firm/Practice Name:</strong></p></td>
                  <td><p>
                    <label for="cvv"></label><span id="sprytextfield4">
                    <input name="firm" type="text" id="firm" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>                    
                  </p></td>
                </tr>
              </table></div>

 <h3>Terms and Conditions</h3>
 <div class="terms scrolldiv" style="height: 150px;">
   <?php include('include/inc_terms.php'); ?>
 </div>
 <p><span id="sprycheckbox1">
   <input type="checkbox" name="Agree" id="Agree" />
   <label for="Agree">Accept</label>
 </span></p>
 <p><span class="checkboxRequiredMsg">Please agree to the terms &amp; condition</span></p>
 <h3>Payment Information </h3>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">
                <tr>
                  <td width="30%"><p><strong>First Name</strong></p></td>
                  <td><label for="firstname"></label>
                    <span id="sprytextfield4">
                    <input name="firstname" type="text" id="firstname" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                 </tr>
                <tr>
                  <td><p><strong>Last Name</strong></p></td>
                  <td><label for="lastname"></label>
                    <span id="sprytextfield5">
                    <input name="lastname" type="text" id="lastname" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                 </tr>
                <tr>
                  <td nowrap="nowrap">&nbsp;</td>
                  <td align="right"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
              </table></div>
              <input name="stateIDs" type="hidden" value="<?php echo $stateIDs; ?>" />
              <input name="courtIDs" type="hidden" value="<?php echo $courtIDs; ?>" />
              </form>
              

<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {validateOn:["blur"]});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {validateOn:["blur"]});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email", {validateOn:["blur"]});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {validateOn:["blur"]});
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {validateOn:["blur"]});
var sprycheckbox1 = new Spry.Widget.ValidationCheckbox("sprycheckbox1", {validateOn:["blur"]});
</script>
<?php
mysql_free_result($States);

mysql_free_result($Courts);

mysql_free_result($SelectedState);

mysql_free_result($cart);

mysql_free_result($stateComments);

}


genesis();
?>
