<?php 
require_once('googleCalender/settings.php');
?>
<!--
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
-->
<style>
.jsgrid-grid-body {
  height:auto !important;
}


/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    margin-left: 255px;
	margin-top: 155px;
    padding: 30px;
    border: 7px solid #888;
    width: 756px;
	border-radius:8px;
	font-size:28px;
	position: relative;
}

/* The Close Button */
.x {
	position: relative;
	width: 23px;
	height: 23px;
	border: 2px solid #eef5df;
	background-color: red;
	border-radius: 50%;
	float:right;
	top: -28px;
	left:29px;
	}
.x::before, .x::after {
	position: absolute;
	top: 10px;
	left: 5px;
	width: 13px;
	height: 3px;
	content: "";
	background-color: white;
	display: block;
	float:right;
	}
.x::before {
	-ms-transform: rotate(-45deg);
	-webkit-transform: rotate(-45deg);
	transform: rotate(-45deg);
	}
.x::after {
	-ms-transform: rotate(45deg);
	-webkit-transform: rotate(45deg);
	transform: rotate(45deg);
	}

</style>
<!--
<script src="src/jsgrid.core.js"></script>
<script src="src/jsgrid.load-indicator.js"></script>
<script src="src/jsgrid.load-strategies.js"></script>
<script src="src/jsgrid.sort-strategies.js"></script>
<script src="src/jsgrid.field.js"></script>
<script src="src/fields/jsgrid.field.text.js"></script>
<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
-->
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
				
				
<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <div class="x"> </div>
    <p>Log in to your Google Account to access your Google Calendar with Calendar Rules for Google.<img src="https://tools.docketcalendar.com/assets/images/ajax-loader.gif" style="height:25px;"></p>
  </div>

</div>
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

    jQuery("#showWarning").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");

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
			 window.location.href='https://tools.docketcalendar.com/docket-calculator'; }, 5000); 
			}	else{
				jQuery(".overlay").hide();
				jQuery("#showWarning").html(data);
			} 
          } else {
			jQuery("#showWarning").html("");
			// Get the modal
			var modal = document.getElementById('myModal');

			// Get the <span> element that closes the modal
			var span = document.getElementsByClassName("x")[0];
						// When the user clicks on <span> (x), close the modal
						
			modal.style.display = "block";
			
			span.onclick = function() {
				modal.style.display = "none";
			}
						// When the user clicks anywhere outside of the modal, close it
			window.onclick = function(event) {
				if (event.target == modal) {
					modal.style.display = "none";
				}
			}
			   setTimeout(function() {
                          modal.style.display = "none";
						  window.location.href='<?php echo $login_url;?>';
                          }, 2000);
			
			/*
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
			*/
           // setTimeout(function() { }, 5000);
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
