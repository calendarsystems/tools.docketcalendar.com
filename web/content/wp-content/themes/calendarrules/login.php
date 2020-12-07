<?php require_once('googleCalender/settings.php');
/* 
Template Name: login
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
session_start();
function custom_loop() {

 if($_SESSION['access_token'] == '')
 {
	 if($_SESSION['CheckAccess']!="NoGmail")
		{
			session_destroy();
		}
    
 }
 $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';

 ?>
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
<style>
.jsgrid-grid-body {
  height:auto !important;
}
.logincontainer
{
  padding: 15px;
}
.left-div
{
  display: inline-block;
  max-width: 372px;
  text-align: left;
  border-radius: 1px;
  margin: 15px;
  vertical-align: top;
}
.right-div
{
  display: inline-block;
  max-width: 375px;
  text-align: left;
  padding: 30px;
  border-radius: 1px;
  margin: 15px;
  position:absolute;
}
.left-text, .right-text
{
  color: #000;
}
@media screen and (max-width: 600px)
{
  .left-div, .right-div
    {
       max-width: 100%;
    }
}

#loading-img {
    background: url(https://tools.docketcalendar.com/assets/images/ajax-loader.gif) center center no-repeat;
    height: 100%;
    z-index: 20;
	width: 100%;
}

.overlay {
	display: none;
	width: 100%;
    height: 100%;
	background: #e9e9e9;
    position: fixed;
    top: 0;
    left: 0;
	opacity: 0.5;
    z-index: 100; /* Just to keep it at the very top */
}
</style>
<script src="https://tools.docketcalendar.com/jquery/js/jquery-1.8.3.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.core.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-indicator.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.sort-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.field.js"></script>
<script src="https://tools.docketcalendar.com/src/fields/jsgrid.field.text.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
<?php //include('/include/inc_top.php'); ?>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div class="logincontainer">
<h1 class="emtry-title">Login to Access your Account</h1>
        <?php if (@$_GET['e']=='99'){ ?>
        	<span class="loginFailed">Login failed</span><BR />
        	<a href="forgot-password">Forgot Username/Password? </a>
        <?php } ?>

		<?php if (@$_GET['e']=='66'){ ?>
        	<span class="loginFailed">Sorry, this ID is not a valid login for this site.</span><BR />
        	<a href="forgot-password">Forgot Username/Password? </a>
        <?php } ?>
        


        <?php if (@$_GET['e']=='88'){ ?>
        Your login information has been sent to <?php echo $_GET['to']; ?>.
        <?php } ?>

        <div class="left-div left-text">
      	  <form id="form1" name="form1" method="post" action="procs/process_login.php">
					<p><strong>Rules Login:</strong><br />
				<label for="usernamelog"></label>
				<span id="sprytextfielda">
				<input name="usernamelog" type="text" id="usernamelog" size="30" maxlength="50" value="<?php echo @$_COOKIE['username'];?>"/><br />
				<span class="textfieldRequiredMsg" id="userMsg">A value is required.</span></span><br />
				</p>
				<p><strong>Password:</strong><br />
				  <label for="passwordlog"></label>
				  <span id="sprytextfieldb">
				  <input name="passwordlog" type="password" id="passwordlog" autocomplete="off" size="30" maxlength="50" value="<?php echo @$_COOKIE['password'];?>" /><br />
				  <span class="textfieldRequiredMsg" id="passwordMsg">A value is required.</span></span><br />
				</p>
                <p  style="font-size:13px !important;">
				<input type="checkbox" name="agreement" id="agreement" value="yes" <?php if($_COOKIE['agreementValue'] == 'yes'){?>checked <?php } else {}?> >&nbsp;I have read and agree to the <a href=https://tools.docketcalendar.com/eula.pdf target="_blank">End User License Agreement</a></p>
                <p><input type="submit" name="signin" id="signin" value="Sign In"> <input type="checkbox" name="rememberme" id="rememberme" <?php if(isset($_COOKIE['rememberme'])) {
		echo 'checked="checked"';
	}
	else {
		echo '';
	}
	?>>
        Remember Me
        <p>&nbsp;</p>
        <p>
		<span id="showWarning" style="color:red;"></span>
		</p>
</form></div>
  <div class="right-div right-text">After successful login, the browser will redirect to Google Authentication Login to access Docket Tools. Please login your Google Account. After successful authorization, the browser redirects the back to the Google Docket page.</div>
		  <?php //print_r($_SESSION);?>
		  <br />
			<br /><?php //print_r($_COOKIE); ?></a><br />
</div>
<script type="text/javascript">

var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfielda", "none", {validateOn:["blur"]});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfieldb", "none", {validateOn:["blur"]});
/* Get from elements values */
var values = jQuery(this).serialize();

jQuery(document).on("submit", "form", function(event)
{
    event.preventDefault();
	jQuery(".overlay").show();
	

    var user =  jQuery("#usernamelog").val();
    var pwd  =  jQuery("#passwordlog").val();

    if (!jQuery("#agreement").is(":checked")) {
     alert("Please read and agree to the End User License Agreement");
	 jQuery(".overlay").hide();
     return false;
    }

    if(user.trim() == '')
    {
      jQuery("#userMsg").show();
      return false;
    }
    if(pwd.trim() == '')
    {
      jQuery("#passwordMsg").show();
      return false;
    }

   // jQuery("#showWarning").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

    var url=jQuery(this).attr("action");
    jQuery.ajax({
        url: url,
        type: 'POST',
        dataType: "JSON",
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function (data, status)
        {
          if(data != "success")
          {
			if(data == "NoGmail"){
			 setTimeout(function() { 
			 jQuery(".overlay").hide();
			 window.location.href='<?php echo get_home_url(); ?>/docket-calculator'; }, 5000); 
			}	else{
				jQuery(".overlay").hide();
				jQuery("#showWarning").html(data);
			}  
          } 
		  else {
			jQuery(".overlay").hide();
            jQuery("#showWarning").html("");
            var data = '<div style="padding:20px;">Log in to your Google Account to access your Google Calendar with Calendar Rules for Google.<img src="assets/images/ajax-loader.gif" style="height:30px;"></div>';
            jQuery.dialogbox({
            type:'msg',
            title:'',
            content:data,
            call:[],
            closeCallback:function(){
                jQuery.dialogbox.prompt({
                    content:'You have closed the message',
                    time:2000
                });
                return false;
            }
            });

            setTimeout(function() { window.location.href='<?php echo $login_url;?>'; }, 5000);
			
			
          }
          console.log(data);
        },
        error: function (xhr, desc, err)
        {
            console.log("error");
        }
    });

});
</script>

<?php

}



genesis();
?>
