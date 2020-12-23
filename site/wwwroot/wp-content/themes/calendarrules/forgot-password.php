<?php
/* 
Template Name: forgot password
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
?>
	      
<h1 class="entry-title">Forgot Username/Password </h1>
        <?php if ($_GET['e']=='77'){ ?>
        That email was not found in our system. Please try again.
        <?php } ?>
      	  <form id="form1" name="form1" method="post" action="/procs/process_forgot_password.php">
      	    <p><strong>Email</strong><br />
      <label for="Email"></label>
      <span id="sprytextfield1">
      <input name="Email" type="text" id="Email" size="30" maxlength="80" /><br />

      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">
      Invalid format.</span></span>
    </p>
    <p>
      <input name="imageField" type="image" id="imageField" src="assets/images/submit.jpg" />
    </p>
  </form>

    <a href="courts.php"></a>
    
<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "email");
</script>
<?php }

genesis();
?>