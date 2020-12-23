<?php
/* 
Template Name: edit password
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

              <form action="" method="post">
                <h3>Update Password</h3>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">             
                <tr>
                  <td colspan="2">
                  <?php if ($pass == 'no'){
					 echo 'Password does not match. Please try again';
				  }else{
					  
				  }?></td>
                  </tr>
                <tr>
                  <td width="30%"><strong>Current Password:</strong></td>
                  <td><label for="currentPassword"></label>
                    <span id="sprytextfield3">
                      <input name="currentPassword" type="password" id="currentPassword" size="40" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="right"><input name="imageField" type="image" id="imageField" src="assets/images/btn_submit.png" /></td>
                  </tr>
              </table></div>
              
              </form>
              

<?php
mysqli_free_result($userinfo);
}

genesis();

?>
