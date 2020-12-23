<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Custom Contact
 */
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
function custom_loop() {
set_time_limit(0);
ini_set('memory_limit', '3000M');
ini_set('post_max_size', '2000M');		
ini_set('max_execution_time', 0);
ini_set('max_input_time', 1800);
ini_set('upload_max_filesize', '1000M');
	session_start();
    $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
    require('globals/global_tools.php');
    require('globals/global_courts.php');
    ?>
	<!-- JAVASCRIPT -->
	<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="jquery/css/notify.css"> 
<script type="text/javascript" src="jquery/js/notify.js"></script>
<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
	<!-- CSS -->
<link rel="stylesheet" href="jquery/css/fastselect.css">
<link rel = "stylesheet" type = "text/css"    href = "jquery/css/standalone.css">
<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">

    
<style type="text/css">
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
	#loading-img {
    background: url(assets/images/ajax-loader.gif) center center no-repeat;
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

#dialogbox
{
	top: 1053.5px !important;
}

label.switch-toggle {
    background: url('images/switch.png') repeat-y;
    display: block !important;
    height: 12px;
    padding-left: 26px;
    cursor: pointer;
    display: none;
}
label.switch-toggle.on {
    background-position: 0 12px;
}
label.switch-toggle.off {
    background-position: 0 0;
}
label.switch-toggle.hidden {
    display: none;
}


</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
    <div style="width: 80%;" class="FntCls">
        <div style="float: left;width: 70%;"><h2>Custom Contacts</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="docket-cases">Docket Cases</a></div>
    </div>

    <div class="widget FntCls">
       <table class="table table-striped" width="100%" cellpadding="10" cellspacing="10">
            <tr>
               <td style="width:25%">Use [Google Contacts]</td>
				<td>
					<select id="contactGoogle" name="contactGoogle[]"  style="width:410px;height: 35px;">
                    <option value="Yes">Yes</option>
					<option value="No">No</option>
					</select>
				</td>
            </tr>
			
			<tr>
               <td style="width:25%">Use [Custom Contacts]</td>
               <td>
					<select id="contactCustom" name="contactCustom[]"  style="width:410px;height: 35px;">
                    <option value="Yes">Yes</option>
					<option value="No">No</option>
					</select>
				</td>
            </tr>
			
			<tr id="customContactAdd">
               <td style="width:25%">Click to Add Custom Contacts</td>
                <td>
					<a href="add-custom-contacts" >Add Contacts</a> 
				</td>
				
            </tr>
			<tr id="googleContactAdd">
               <td style="width:25%">Click to Update Google Contacts</td>
                <td>
					<input type="button" id="getContacts" name="getContacts" value="Update Contacts" onclick="getGmailContacts();">
				</td>
				
            </tr>
			
		</table>
		
    </div>
<script type="text/javascript">
$( document ).ready(function() {
   var contactGoogleoptionSelected = jQuery( "#contactGoogle" ).val();
   if(contactGoogleoptionSelected == "Yes")
		{
			jQuery( "#contactCustom" ).val("No");
			jQuery("#googleContactAdd").show();
			jQuery("#customContactAdd").hide();
			
			
		}
		if(contactGoogleoptionSelected == "No")
		{
			jQuery( "#contactCustom" ).val("Yes");
			jQuery("#googleContactAdd").hide();
			jQuery("#customContactAdd").show();
		}
});
		

    jQuery("#contactGoogle").change(function(){
		var contactGoogleoptionSelected = jQuery( "#contactGoogle" ).val()
		if(contactGoogleoptionSelected == "Yes")
		{
			jQuery( "#contactCustom" ).val("No");
			jQuery("#googleContactAdd").show();
			jQuery("#customContactAdd").hide();
			ajaxCallForContactUpdateStatus(contactGoogleoptionSelected);
		}
		if(contactGoogleoptionSelected == "No")
		{
			jQuery( "#contactCustom" ).val("Yes");
			jQuery("#googleContactAdd").hide();
			jQuery("#customContactAdd").show();
			ajaxCallForContactUpdateStatus(contactGoogleoptionSelected);
		}
		
    });
	jQuery("#contactCustom").change(function(){
		var contactCustomoptionSelected = jQuery( "#contactCustom" ).val()
		if(contactCustomoptionSelected == "Yes")
		{
			jQuery( "#contactGoogle" ).val("No");
			jQuery("#googleContactAdd").hide();
			jQuery("#customContactAdd").show();
			ajaxCallForContactUpdateStatus(contactCustomoptionSelected);
		}
		if(contactCustomoptionSelected == "No")
		{
			jQuery( "#contactGoogle" ).val("Yes");
			jQuery("#googleContactAdd").show();
			jQuery("#customContactAdd").hide();
			ajaxCallForContactUpdateStatus(contactCustomoptionSelected);
		}
    });
	
	function getGmailContacts()
		{
			jQuery(".overlay").show();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_importGoogleContacts.php",
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                       console.log(response);
                      jQuery(".overlay").hide();
                       $.notify("Contact Imported", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"check",
							  delay:3500,
							  blur: 0.8,
							  close: true,
							  buttonAlign: "center",
							});
                       setTimeout(function() {
                            window.location.href = '<?php echo get_home_url(); ?>/custom-contacts';
                       }, 500);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       
                    }
                });
			
		}
		
		function ajaxCallForContactUpdateStatus(valContact)
		{
			jQuery(".overlay").show();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updateContactList.php",
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                       console.log(response);
                      jQuery(".overlay").hide();
                       $.notify("Update Contact List", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"check",
							  delay:3500,
							  blur: 0.8,
							  close: true,
							  buttonAlign: "center",
							});
                       setTimeout(function() {
                            window.location.href = '<?php echo get_home_url(); ?>/custom-contacts';
                       }, 500);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       
                    }
                });
		}
</script>
<?php
}
genesis();
?>