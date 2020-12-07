<?php
/* 
Template Name: ruleslogin
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {


setcookie('oldone',session_id());
 ?>

<h1 class="entry-title">Login to Manage your Account</h1>

        <?php if ($_GET['e']=='99'){ ?>
        <span class="loginFailed">Login failed</span><BR />
        <a href="forgot_password.php">Forgot Password? </a>
        <?php } ?>
        <?php if ($_GET['e']=='88'){ ?>
        Your login information has been sent to the email we have on file.
        <?php } ?>
      	  <form id="form1" name="form1" method="post" action="dev/process_login.php">
					<p><strong>Username</strong><br />
				<label for="username"></label>
				<span id="sprytextfield1">
				<input name="username" type="text" id="username" size="30" maxlength="50" /><br />
				<span class="textfieldRequiredMsg">A value is required.</span></span><br />
				</p>
				<p><strong>Password</strong><br />
				  <label for="password"></label>
				  <span id="sprytextfield2">
				  <input name="password" type="password" id="password" autocomplete="off" size="30" maxlength="50" /><br />
				  <span class="textfieldRequiredMsg">A value is required.</span></span><br />
				</p>
				<p>
			  <input name="imageField" type="image" id="imageField" src="assets/images/login_button.png" />
			</p>
		  </form>
	

<?php }

genesis();
?>
