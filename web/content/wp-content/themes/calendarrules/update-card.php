<?php
/* 
Template Name: update card
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	require_once('Connections/docketData.php'); 
	include('globals/global_courts.php');
	$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
//print_r($row_userInfo);
?>

<table><tr><td></td></tr></table>
<?php if ($_GET['cardfailed'] == 1){ ?>
<h2>Card Failed, please update your billing information</h2>
<?php }else{ ?>
<h1>Update Profile</h1>
<?php } ?>

      	  <table width="100%">
      	    <tr>
      	      <td >
              <form action="procs/process_profile_update.php" method="post">
              <input type="hidden" name="attorneyid" value="<?php echo $row_attorneyInfo['attorneyID']; ?>">
                <h3>Update Login</h3>
<div class="widget"><table width="100%" align="center" class="widget-wrap"> 
                
              <!--  <tr>
                  <td width="20%"><p><strong>Password:</strong></p></td>
                  <td width="80%"><label for="lastname"></label>
                    
                      <input name="password" type="hidden" id="password" value="<?php echo $row_userInfo['userpassword']; ?>" />
                      <a href="update-password">Edit Password</a></td>
                </tr> -->
                <tr>
                  <td width="20%"><p><strong>Email:</strong></p></td>
                  <td width="80%"><label for="billingaddress"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" value="<?php echo $row_userInfo['email']; ?>" size="30" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Firm/Practice Name:</strong></p></td>
                  <td width="80%"><label for="cvv"></label>
                    <input name="firm" type="text" id="firm" value="<?php echo $row_userInfo['firm']; ?>" size="30"  /></td>
                </tr>
              </table></div>

              <h3>Update Payment Information</h3>
              <div class="widget"><table width="100%" align="center" class="widget-wrap">                   <tr>
                  <td width="20%"><p><strong>First Name</strong></p></td>
                  <td width="80%"><label for="firstname"></label>
                    <span id="sprytextfield4">
                    <input name="firstname" type="text" id="firstname" value="<?php echo $row_userInfo['firstname']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                <tr>
                  <td width="20%"><p><strong>Last Name</strong></p></td>
                  <td width="80%"><label for="lastname"></label>
                    <span id="sprytextfield5">
                    <input name="lastname" type="text" id="lastname" value="<?php echo $row_userInfo['lastname']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td width="20%"><p><strong>Billing Address</strong></p></td>
                  <td width="80%"><label for="billingaddress"></label>
                    <span id="sprytextfield6">
                    <input name="billingaddress" type="text" id="billingaddress" value="<?php echo $row_userInfo['billingaddress']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Billing City</strong></p></td>
                  <td width="80%"><span id="sprytextfield7">
                    <input name="city" type="text" id="city" value="<?php echo $row_userInfo['billingcity']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Billing State</strong></p></td>
                  <td width="80%"><span id="sprytextfield8">
                    <input name="state" type="text" id="state" value="<?php echo $row_userInfo['billingstate']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Billing Zip</strong></p></td>
                  <td width="80%"><span id="sprytextfield9">
                    <input name="zip" type="text" id="zip" value="<?php echo $row_userInfo['billingzip']; ?>" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr><td colspan="2"><hr /></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Card Number</strong></p></td>
                  <td width="80%"><span id="sprytextfield10">
                  <input name="cardnumber" type="text" id="cardnumber" size="50" value="<?php echo "XXXXXXXXXXXX-",$row_userInfo['CardLastFour']; ?>"/>
                  <input name="oldcardnumber" type ="hidden" id ="oldcardnumber" value="<?php echo "XXXXXXXXXXXX-",$row_userInfo['CardLastFour']; ?><br>
                  <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>Expiration Date</strong></p></td>
                  <td width="80%"><label for="month"></label>
                    <select name="month" id="month">
                      <option>Month</option>
                      <option value="01" <?php if ($row_userInfo['Month']=="01") { echo 'selected = "selected"';} ?>>1</option>
                      <option value="02" <?php if ($row_userInfo['Month']=="02") { echo 'selected = "selected"';} ?>>2</option>
                      <option value="03" <?php if ($row_userInfo['Month']=="03") { echo 'selected = "selected"';} ?>>3</option>
                      <option value="04" <?php if ($row_userInfo['Month']=="04") { echo 'selected = "selected"';} ?>>4</option>
                      <option value="05" <?php if ($row_userInfo['Month']=="05") { echo 'selected = "selected"';} ?>>5</option>
                      <option value="06" <?php if ($row_userInfo['Month']=="06") { echo 'selected = "selected"';} ?>>6</option>
                      <option value="07" <?php if ($row_userInfo['Month']=="07") { echo 'selected = "selected"';} ?>>7</option>
                      <option value="08" <?php if ($row_userInfo['Month']=="08") { echo 'selected = "selected"';} ?>>8</option>
                      <option value="09" <?php if ($row_userInfo['Month']=="09") { echo 'selected = "selected"';} ?>>9</option>
                      <option value="10" <?php if ($row_userInfo['Month']=="10") { echo 'selected = "selected"';} ?>>10</option>
                      <option value="11" <?php if ($row_userInfo['Month']=="11") { echo 'selected = "selected"';} ?>>11</option>
                      <option value="12" <?php if ($row_userInfo['Month']=="12") { echo 'selected = "selected"';} ?>>12</option>
                    </select>
                     <label for="year"></label>
                    <select name="year" id="year">
                      <option>Year</option>
                      <option value="2011" <?php if ($row_userInfo['Year']=="2011") { echo 'selected="selected"';} ; ?>>2011</option>
                      <option value="2012" <?php if ($row_userInfo['Year']=="2012") { echo 'selected="selected"';} ; ?>>2012</option>
                      <option value="2013" <?php if ($row_userInfo['Year']=="2013") { echo 'selected="selected"';} ; ?>>2013</option>
                      <option value="2014" <?php if ($row_userInfo['Year']=="2014") { echo 'selected="selected"';} ; ?>>2014</option>
                      <option value="2015" <?php if ($row_userInfo['Year']=="2015") { echo 'selected="selected"';} ; ?>>2015</option>
                      <option value="2016" <?php if ($row_userInfo['Year']=="2016") { echo 'selected="selected"';} ; ?>>2016</option>
                      <option value="2017" <?php if ($row_userInfo['Year']=="2017") { echo 'selected="selected"';} ; ?>>2017</option>
                      <option value="2018" <?php if ($row_userInfo['Year']=="2018") { echo 'selected="selected"';} ; ?>>2018</option>
                      <option value="2019" <?php if ($row_userInfo['Year']=="2019") { echo 'selected="selected"';} ; ?>>2019</option>
                      <option value="2020" <?php if ($row_userInfo['Year']=="2020") { echo 'selected="selected"';} ; ?>>2020</option>
                    </select></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap"><p><strong>CVV</strong></p></td>
                  <td width="80%"><input name="cvv" type="text" id="cvv" size="8" /></td>
                </tr>
                <tr>
                  <td width="20%" nowrap="nowrap">&nbsp;</td>
                  <td width="80%"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
              </table></div>
              </form>
              </td>
   	        </tr>
   	      </table>

<?php mysqli_free_result($userinfo);



}
genesis();
?>
