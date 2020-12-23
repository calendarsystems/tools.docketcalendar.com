<?php require_once('googleCalender/settings.php');
session_start();

?>
<link href="https://tools.docketcalendar.com/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="https://tools.docketcalendar.com/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
<style>
.jsgrid-grid-body {
  height:auto !important;
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
<?php
  $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';

?>
<form name="form1" method="POST" action="procs/process_login.php">
<h3>If you are a current user, please login!</h3>
  <table width="100%" border="0">
    <tr>
      <td width="20%" valign="top">Rules Login: 
<span id="sprytextfield1">    
      <input type="text" name="usernamelog" id="usernamelog" value="<?php if($_COOKIE['rememberme'] == 'on') { echo @$_COOKIE['username']; }?>"> <br /><span class="textfieldRequiredMsg"  id="userMsg">A value is required.</span></span>
</td>
      <td width="20%" valign="top">Password:
<span id="sprytextfield2">    
      <input type="password" name="passwordlog" id="passwordlog" value="<?php if($_COOKIE['rememberme'] == 'on') { echo @$_COOKIE['password']; }?>"><br /><span class="textfieldRequiredMsg" id="passwordMsg">A value is required.</span></span></td>


      <td width="60%" valign="top"><br /><input type="submit" name="signin" id="signin" value="Sign In">  <input type="checkbox" name="rememberme" id="rememberme" <?php if(isset($_COOKIE['rememberme'])) {
		echo 'checked="checked"';
	}
	else {
		echo '';
	}
	?>>
        Remember Me</td>
    </tr>
	
    <tr><td colspan="3"><span id="showWarning" style="color:red;"></span></td></tr>
	
	</p>
<?php    

if (!isset($returnto)) {
		$returnto=c2c_reveal_template(false);
		 $returnto=substr($returnto,0,strlen($returnto)-4);
}
 ?>
    <input type="hidden" name="returnto" value="<?php echo  $returnto; ?>" />

  </table>
  <p>&nbsp;</p>
   <p  style="font-size:13px !important;">
				<input type="checkbox" name="agreement" id="agreement" value="yes" <?php if($_COOKIE['agreementValue'] == 'yes'){?>checked <?php } else {}?> >&nbsp;I have read and agree to the <a href="https://tools.docketcalendar.com/eula.pdf" target="_blank">End User License Agreement</a>
</form>
<script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {validateOn:["submit"]});
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {validateOn:["submit"]});
/* Get from elements values */
var values = jQuery(this).serialize();

jQuery(document).on("submit", "form", function(event)
{
    event.preventDefault();
	 if (!jQuery("#agreement").is(":checked")) {
     alert("Please read and agree to the End User License Agreement");
     return false;
    }
    var user =  jQuery("#usernamelog").val();
    var pwd  =  jQuery("#usernamelog").val();

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

    jQuery("#showWarning").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

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
            jQuery("#showWarning").html(data);
          } else {

            var data = '<div style="padding:20px;">It will redirect to Google Authentication, Please login Google Account and access the Docket Tool.</div>';
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
