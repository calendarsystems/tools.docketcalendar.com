<?php
/* 
Template Name: update profile
*/

//custom hooks below here...



remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

include('inc_top.php'); ?>
	<?php if ($_GET['cardfailed'] == 1){ ?>
        <h1 class="entry-title">Card Failed, please update your billing information</h1>
        <?php }else{ ?>
        <h1 class="entry-title">Update Profile</h1>
        <?php } ?>
              <form action="process_profile_update.php" method="post">
                <h3>Update Password</h3>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">             
                <tr>
                  <td width="30%"><p><strong>New Password:</strong></p></td>
                  <td><label for="lastname"></label>
                    
                      <input name="password" type="text" id="password" value="<?php echo $row_userinfo['userpassword']; ?>" size="40" />
                      <input name="email" type="hidden" id="email" value="<?php echo $row_userinfo['email']; ?>" size="40" />
                      <input name="firm" type="hidden" id="firm" value="<?php echo $row_userinfo['firm']; ?>" size="40" /></td>
                 </tr>
              </table></div>
              <p>&nbsp;</p>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">             
                <tr>
                  <td width="265"><label for="firstname"></label>
                    <span id="sprytextfield4">
                      <input name="firstname" type="hidden" id="firstname" value="<?php echo $row_userinfo['firstname']; ?>" size="50" />
</span>
                    <input name="lastname" type="hidden" id="lastname" value="<?php echo $row_userinfo['lastname']; ?>" size="50" />
                    <input name="billingaddress" type="hidden" id="billingaddress" value="<?php echo $row_userinfo['billingaddress']; ?>" size="50" />
                    <input name="city" type="hidden" id="city" value="<?php echo $row_userinfo['billingcity']; ?>" size="50" />
                    <input name="state" type="hidden" id="state" value="<?php echo $row_userinfo['billingstate']; ?>" />
                    <input name="zip" type="hidden" id="zip" value="<?php echo $row_userinfo['billingzip']; ?>" size="50" />
                    <input name="cardnumber" type="hidden" id="cardnumber" value="XXXX-XXXX-XXXX-<?php echo $row_userinfo['CardLastFour']; ?>" size="50" />
                    <input name="month" type="hidden" value="<?php echo $row_userinfo['Month']; ?>" />
                    <label for="year"></label>
                    <input name="year" type="hidden" value="<?php echo $row_userinfo['Year']; ?>" />
                    <input name="cvv" type="hidden" id="cvv" size="8" /></td>
                 </tr>
                <tr>
                  <td align="right">                  <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
          </table></div>
              </form>
<?php
mysqli_free_result($userinfo);


}
genesis();

?>
