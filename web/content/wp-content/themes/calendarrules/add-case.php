<?php 
/*
Template Name: Add Case
 */
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
session_start();
function custom_loop() {
	require_once('Connections/docketDataSubscribe.php');
	require('globals/global_tools.php');
    require('globals/global_courts.php');
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
	
	$query_authInfo = "SELECT assignees,googlecontactprefrence,reminder_minutes,reminder_minutes_popup,eventColor,default_calendar FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' and user_id=".$_SESSION['userid']." ";
	$authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
	$authInfo2 = mysqli_query($docketDataSubscribe,$query_authInfo);
	$totalRows_authInfo = mysqli_num_rows($authInfo);
	$assignees = array();
	if($totalRows_authInfo > 0)
	{
		while ($row = mysqli_fetch_array($authInfo)) {
			if($row['assignees']!=NULL)
			{
				$assignees = explode(",",$row['assignees']);
			}
			
		}
	}
	$googleContactlist=array();
	$row_authInfo = mysqli_fetch_assoc($authInfo2);
	if($row_authInfo['googlecontactprefrence'] == "Yes")
	{
		function cmp($a, $b){
            if ($a == $b)
            return 0;
            return ($a['name'] < $b['name']) ? -1 : 1;
            }
            $googleContactlist = $_SESSION['google_contacts']; usort($contact_list, "cmp");
	}
		
	$query_case_customContact = "SELECT userContactName as name,userContactEmail as email FROM userContactUpdate  WHERE  userid = ".$_SESSION['userid']."";
    $customContact = mysqli_query($docketDataSubscribe,$query_case_customContact);
    $totalRows_customContact = mysqli_num_rows($customContact);
	
	if($totalRows_customContact > 0)
	{
		while($row_customContact = mysqli_fetch_assoc($customContact))
       {
		 $customCustomerarray[]= array('name'=>$row_customContact['name'], 'email'=>$row_customContact['email']);
       }
	   $contact_list = array_merge($googleContactlist, $customCustomerarray);
	}else{
		$contact_list =$googleContactlist;
	}
	
	?>
	<!-- JAVASCRIPT -->
<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
	<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
	<!-- CSS -->
	<link rel = "stylesheet" type = "text/css"    href = "https://tools.docketcalendar.com/jquery/css/standalone.css">
	<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/fastselect.css">
<style>
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
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
.required:before {
    color: red;
    content: '*';
}

</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
    <div style="width: 80%;">
        <div style="float: left;width: 70%;"><h2>Add Case</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="docket-cases">Docket Cases</a></div>
    </div>
	<span id="ajax_result"></span>
    <div id="AddCaseid" class="widget FntCls">
        <form id="add_case" name="add_case" method="post" action="procs/add_case.php" >
        <table class="table table-striped" width="100%" cellpadding="10" cellspacing="10">
            <tr>
               <td style="width:25%"><strong><label class="required">Matter / Case Name</label>:</strong></td>
               <td><input type="text" id="case_matter" name="case_matter" style="width:410px;height: 35px;" value="">
			    <span style="color:red;" id="messageSpan"></span>
			   </td>
			   
            </tr>
			<tr>
               <td style="width:25%">Show/Hide Case Name:</td>
               <td>
			   <select name="casedisplay" id="casedisplay">
				<option value="Show">Show</option>
				<option value="Hide">Hide</option>
			   </select>
			   </td>
			   
            </tr>
            <tr>
		
                <td>Assign users to case:</td>
                <td>
                    <?php if(isset($_SESSION['google_contacts'])) { ?>
                    <select multiple="multiple" id="users" class="multipleSelect" name="users[]">
                    <?php 
							foreach($contact_list as $contact) 
							{ 
					 
								if($_SESSION['author_id'] == $contact['email'])
								{
									 unset($contact['email']); 
									 unset($contact['name']); 
								}
					?>
					<option  value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$assignees)) {   echo "selected"; } ?>><?php echo $contact['name'];?></option>
					<?php				
							}  		
					?>
                    </select>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td>Add attendees to case:</td>
                <td>
                    <?php if(isset($_SESSION['google_contacts'])) { ?>
                    <select multiple="multiple" id="attendee" class="multipleSelect" name="attendee[]" style="width:410px">
                     <?php foreach($contact_list as $contact) {

						if($_SESSION['author_id'] == $contact['email'])
						{
							 unset($contact['email']); 
							 unset($contact['name']); 
						} 					 
					?>
                        <option value="<?php echo $contact['email'];?>"><?php echo $contact['name'];?></option>
                     <?php } ?>
                    </select>
                    <?php } ?>
                </td>
				
            </tr>
            <tr>
                <td>Add Calendar:</td>
                <td>
                <?php
                    if(isset($calendarData))
                    {   ?>
                    <select type="select" name="calendar_id" id="calendar_id" style="width:410px;height: 35px;">
                    <?php
                        $dbCalendarId = $row_authInfo['default_calendar'];
                       foreach($calendarData as $calendar_list){
                         $calendar_id = $calendar_list['id'];
                          $calendar_summary = $calendar_list['summary'];
                       

                            if($calendar_summary == $calendar_id)
                            {
                             $calendarID = "primary";
                             $calendarSummary = "Primary Calendar";
                                 if(empty($dbCalendarId))
                                {
                                   $selected = 'selected="selected"';
                                }
                                else
                                {
                                      if($dbCalendarId == $calendarID)
                                     {
                                        $selected = 'selected="selected"';
                                     }else
                                     {
                                        $selected = "";
                                     }
                                }
                             
                            } 
                            else
                            {
                                $calendarID = $calendar_id;
                                $calendarSummary = $calendar_summary;
                                $selected = "";
                              if(empty($dbCalendarId))
                              {
                                $selected = "";
                              }
                              else
                              {
                                   if($dbCalendarId == $calendarID)
                                   {
                                      $selected = 'selected="selected"';
                                   }else
                                   {
                                      $selected = "";
                                   }
                              }
                              
                            }

                          ?>
                        <option value="<?php echo $calendarID;?>" <?php echo $selected;?>><?php echo $calendarSummary;?></option>
                       <?php

                       } ?>
                      </select>
                   <?php } ?>
                </td>
            </tr>
			<tr>
				<td>Jurisdiction:</td>
				<td>
					<span id="span_case_jurisdiction">
					
                    </span>
					
				</td>
			</tr>
			<tr>
				<td>Location:</td>
				<td><input type="text" id="case_location" name="case_location" style="width:410px;height: 35px;" value=""></td>
			</tr>
			<tr>
				<td>Custom Text:</td>
				<td>
				<textarea id="case_customtext" name="case_customtext" style="width:410px;height: 95px;"></textarea></td>
			</tr>
			<tr>
				<td style="width:25%">Reminder for Email:</td>
				<td>
				   <select name="reminder_minutes" id="reminder_minutes"style="width:410px;height: 35px;">
				   <option value="0" <?php if($row_authInfo['reminder_minutes'] == "0") { ?> selected="selected" <?php } ?>>---Select---</option>
				   <option value="5" <?php if($row_authInfo['reminder_minutes'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_authInfo['reminder_minutes'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_authInfo['reminder_minutes'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_authInfo['reminder_minutes'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_authInfo['reminder_minutes'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_authInfo['reminder_minutes'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_authInfo['reminder_minutes'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_authInfo['reminder_minutes'] == 240) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="300" <?php if($row_authInfo['reminder_minutes'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_authInfo['reminder_minutes'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_authInfo['reminder_minutes'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_authInfo['reminder_minutes'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_authInfo['reminder_minutes'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_authInfo['reminder_minutes'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_authInfo['reminder_minutes'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_authInfo['reminder_minutes'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_authInfo['reminder_minutes'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_authInfo['reminder_minutes'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_authInfo['reminder_minutes'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_authInfo['reminder_minutes'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_authInfo['reminder_minutes'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_authInfo['reminder_minutes'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_authInfo['reminder_minutes'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
				</td>
			</tr>
				<tr>
				<td style="width:25%">Reminder for PopUp:</td>
				<td>
				    <select name="reminder_minutes_popup" id="reminder_minutes_popup" style="width:410px;height: 35px;">
				   <option value="5" <?php if($row_authInfo['reminder_minutes_popup'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_authInfo['reminder_minutes_popup'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_authInfo['reminder_minutes_popup'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_authInfo['reminder_minutes_popup'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_authInfo['reminder_minutes_popup'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_authInfo['reminder_minutes_popup'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_authInfo['reminder_minutes_popup'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_authInfo['reminder_minutes_popup'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_authInfo['reminder_minutes_popup'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_authInfo['reminder_minutes_popup'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_authInfo['reminder_minutes_popup'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_authInfo['reminder_minutes_popup'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_authInfo['reminder_minutes_popup'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_authInfo['reminder_minutes_popup'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_authInfo['reminder_minutes_popup'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_authInfo['reminder_minutes_popup'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_authInfo['reminder_minutes_popup'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_authInfo['reminder_minutes_popup'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_authInfo['reminder_minutes_popup'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_authInfo['reminder_minutes_popup'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_authInfo['reminder_minutes_popup'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_authInfo['reminder_minutes_popup'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
				</td>
			</tr>
			<tr>
				   <td style="width:25%">Event Color:</td>
				   <td>
				  <select name="caseLeveleventColor" id="caseLeveleventColor" style="width:410px;height: 35px;">
				  <option value="0" <?php if($row_authInfo['eventColor'] == "0") { ?> selected="selected" <?php } ?>>No Color</option>
				   <option value="11" <?php if($row_authInfo['eventColor'] == "11") { ?> selected="selected" <?php } ?>>Tomato</option>
				   <option value="6" <?php if($row_authInfo['eventColor'] == "6") { ?> selected="selected" <?php } ?>>Tangerine</option>
				   <option value="2" <?php if($row_authInfo['eventColor'] == "2") { ?> selected="selected" <?php } ?>>Sage</option>
				   <option value="7" <?php if($row_authInfo['eventColor'] == "7") { ?> selected="selected" <?php } ?>>Peacock</option>
				   <option value="1" <?php if($row_authInfo['eventColor'] == "1") { ?> selected="selected" <?php } ?>>Lavender</option>
				   <option value="8" <?php if($row_authInfo['eventColor'] == "8") { ?> selected="selected" <?php } ?>>Graphite</option>
				   <option value="4" <?php if($row_authInfo['eventColor'] == "4") { ?> selected="selected" <?php } ?>>Flamingo</option>
				   <option value="5" <?php if($row_authInfo['eventColor'] == "5") { ?> selected="selected" <?php } ?>>Banana</option>
				   <option value="10" <?php if($row_authInfo['eventColor'] == "10") { ?> selected="selected" <?php } ?>>Basil</option>
				   <option value="9" <?php if($row_authInfo['eventColor'] == "9") { ?> selected="selected" <?php } ?>>Blueberry</option>
				   <option value="3" <?php if($row_authInfo['eventColor'] == "3") { ?> selected="selected" <?php } ?>>Grape</option>
				   </select>&nbsp;&nbsp;<div id="colorIdentifier" style="width:20px;height:10px;"></div>
				   </td>
			</tr>
            <tr><td colspan="2"><span id="show_msg" style="color:red;padding-left: 250px;"></span></td></tr>
            <tr>
                <td>&nbsp;<span id="insert_ajax_result"></td><td><input type="button" id="add" name="add" value="Add Case" onclick="validateCase();"></td>
            </tr>
        </table>
        </form>
    </div>
<script type="text/javascript">
jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
   jQuery('.multipleSelect').fastselect();
			jQuery("#ajax_result").show();
			jQuery("#AddCaseid").hide();
			
			
			jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_casejurisdiction.php",
            type: "post",
            success: function (response) {
			
              jQuery("#span_case_jurisdiction").html(response);
			  jQuery("#ajax_result").hide(200);
			  jQuery("#AddCaseid").show(200);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
			     jQuery("#ajax_result").hide(200);
            }
          });
		   function validateCase()
		   {
			    jQuery(".overlay").show();
				jQuery("#add").prop("disabled", true);
				var caseMatter = document.getElementById("case_matter").value;
				jQuery("#insert_ajax_result").show();
				
			 
				if(caseMatter.trim() == "")
				{
				  jQuery("#insert_ajax_result").hide(500);
				 
				  document.getElementById("case_matter").focus();
				  jQuery(".overlay").hide();
				  jQuery("#add").prop("disabled", false);
				   $.notify("Please enter case name", {
				  type:"danger",
				  align:"center", 
				  verticalAlign:"middle",
				  animation:true,
				  animationType:"scale",
				  icon:"bell",
				  delay:3000,
				  blur: 0.8,
				 close: true,
				  buttonAlign: "center",
				});
				  return false;
				} else if (caseMatter)
				{
					jQuery("#messageSpan").text('Checking...').fadeIn("slow");
					jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/api/api_checkCaseExists.php",
					type: "post",
					data:{"case_name":caseMatter,"createdby":'<?php echo $_SESSION['author_id'];?>'},
					success: function (response) {
						
					if(response=='no') //if case name not avaiable
						{
							jQuery("#insert_ajax_result").hide(500);
							document.getElementById("case_matter").focus();
							jQuery(".overlay").hide();
							jQuery("#add").prop("disabled", false);							
							 $.notify("Please enter case name", {
							  type:"danger",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"bell",
							  delay:3000,
							  blur: 0.8,
							 close: true,
							  buttonAlign: "center",
							});
							
							return false;
							 
						}
						else if(response=='yes'){
							 jQuery("#insert_ajax_result").hide(500);
							 document.getElementById("case_matter").value = caseMatter.trim();
							 document.getElementById("show_msg").innerHTML = "";
							 jQuery("#add_case").submit();
							 jQuery("#add").prop("disabled", false );
						}
					},
						error: function(jqXHR, textStatus, errorThrown) {
						   console.log(textStatus, errorThrown);
						   jQuery(".overlay").hide();
						   jQuery("#insert_ajax_result").hide();
						}
					});
					
				}else
				{
					  jQuery(".overlay").hide();
					  document.getElementById("case_matter").value = caseMatter.trim();
					  document.getElementById("show_msg").innerHTML = "";
					  //jQuery("#AddCaseid").submit();
					
				}	
				
			
		   }
		  
		 
</script>
<?php
}
genesis();
?>