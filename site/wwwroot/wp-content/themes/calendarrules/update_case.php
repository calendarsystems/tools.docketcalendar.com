<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Update Case
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
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
	<!-- CSS -->
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/fastselect.css">
<link rel = "stylesheet" type = "text/css"    href = "https://tools.docketcalendar.com/jquery/css/standalone.css">
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">

    <?php
    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    if($_GET['case_id'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    $case_id = $_GET['case_id'];

     $query_importEvents = "SELECT * FROM docket_cases dc WHERE  dc.case_id = ".$case_id."";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);
    $row_events = mysqli_fetch_assoc($ImportEvents);

    $query_case_users = "SELECT * FROM docket_cases_users dcu WHERE  dcu.case_id = ".$case_id."";
    $caseUsers = mysqli_query($docketDataSubscribe,$query_case_users);
    $totalRows_users = mysqli_num_rows($caseUsers);

    $query_case_attendees = "SELECT * FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id." and caselevel = 1";
    $caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
    $totalRows_attendees = mysqli_num_rows($caseAttendee);

    $user = array();
    $attendee = array();
    $dbCalendarId  = $row_events['calendar_id'];
    if($totalRows_users > 0)
    {
       while($row_users = mysqli_fetch_assoc($caseUsers))
       {
		   if($row_users['user']!=NULL)
			{
				 $user[] = $row_users['user'];
			}
       
       }
    }
    if($totalRows_attendees > 0)
    {
       while($row_attendees = mysqli_fetch_assoc($caseAttendee))
       {
		   
         $attendee[] = $row_attendees['attendee'];
       }
    }
	
	$query_authInfo = "SELECT googlecontactprefrence FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' and user_id=".$_SESSION['userid']." ";
	$authInfo2 = mysqli_query($docketDataSubscribe,$query_authInfo);
	$totalRows_authInfo = mysqli_num_rows($authInfo);
	
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
	
	$sqlSelectCaseCustomText = "SELECT case_customtext FROM docket_customtext WHERE case_id =".$case_id." AND case_customtextlevel=1";
					$ResultSelectCaseCustomText = mysqli_query($docketDataSubscribe,$sqlSelectCaseCustomText);
					$totalRowsSelectCaseCustomText = mysqli_num_rows($ResultSelectCaseCustomText);
					if($totalRowsSelectCaseCustomText > 0)
					{
						while ($rowData = mysqli_fetch_assoc($ResultSelectCaseCustomText))
						{
							$textValue = $rowData ['case_customtext'];
						}	
					}
					else{
						$textValue ="";
					}
					
	$tempArr = array_unique(array_column($contact_list, 'email'));
	$contact_list = (array_intersect_key($contact_list, $tempArr)); 
	
    ?>
<style type="text/css">
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
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

#dialogbox
{
	top: 1053.5px !important;
}

.popup-overlay{
  /*Hides pop-up when there is no "active" class*/
  visibility:hidden;
  position:absolute;
  background:#FFF;
  border:3px solid #666666;
  width:268px;
  height:95px%;
  left:36%; 
  top:105%;
  z-index: 15;
}
.popup-overlay.active{
  /*displays pop-up when "active" class is present*/
  visibility:visible;
  text-align:left;
}

.popup-content {
  /*Hides pop-up content when there is no "active" class */
 visibility:hidden;
}

.popup-content.active {
  /*Shows pop-up content when "active" class is present */
  visibility:visible;
}

</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
    <div style="width: 80%;" class="FntCls">
        <div style="float: left;width: 70%;"><h2>Update Case</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="https://tools.docketcalendar.com/docket-cases">Docket Cases</a></div>
    </div>

   
    <div class="widget FntCls">
        <form id="add_case" name="add_case" method="post">
        <table class="table table-striped" width="100%" cellpadding="10" cellspacing="10">
            <tr>
               <td style="width:25%">Matter / Case Name:</td>
               <td><input type="text" id="case_matter" name="case_matter" style="width:410px;height: 35px;" value="<?php echo $row_events['case_matter'];?>"></td>
            </tr>
			<tr>
               <td style="width:25%">Show/Hide Case Name:</td>
               <td>
			   <select name="casedisplay" id="casedisplay">
				<option value="Show" <?php if($row_events['casedisplay'] == 'Show'){echo "selected";}?>>Show</option>
				<option value="Hide"  <?php if($row_events['casedisplay'] == 'Hide'){echo "selected";}?>>Hide</option>
			   </select>
			   </td>
			   
            </tr>
            <tr>
            <td>Assign users to case:</td>
            <td>
                <?php if(isset($_SESSION['google_contacts'])) { ?>
                <select multiple="multiple" id="users" name="users[]" class="multipleSelect" style="width:410px;height: 35px;">
                 <?php foreach($contact_list as $contact) { 
                  if($_SESSION['author_id'] == $contact['email'])
                    {
                          unset($contact['email']); 
						  unset($contact['name']); 
                    } 
                    ?>
                    <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$user)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>
                 <?php } ?>
                </select>
                <?php } ?>
            </td>
            </tr>
            <tr>
            <td>Add attendees to case:</td>
            <td>
                <?php if(isset($_SESSION['google_contacts'])) { ?>
                <select multiple="multiple" id="attendee" name="attendee[]" class="multipleSelect" style="width:410px;height: 35px;">
                 <?php foreach($contact_list as $contact) { 
                  if($_SESSION['author_id'] == $contact['email'])
                    {
                         unset($contact['email']); 
						 unset($contact['name']); 
                    } 
                    ?>
                    <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>
                 <?php } ?>
                </select>
                <?php } ?>
            </td>
			<td>
				<input type="button" id="updateAttendees" name="updateAttendees" value="Update Attendees">
			</td>
            </tr>
                   <tr>
                <td>Add Calendar:</td>
                <td>
                
                <?php
                    if(isset($calendarData))
                    { 
                    ?>
                    <select type="select" name="calendar_id" id="calendar_id" style="width:410px;height: 35px;">
                    <?php
                  
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
				<td><input type="text" id="case_location" name="case_location" style="width:410px;height: 35px;" value="<?php echo $row_events['case_location'];?>"></td>
			</tr>
			<tr>
				<td>Custom Text:</td>
				<td><textarea id="case_customtext" name="case_customtext" style="width:410px;height: 95px;"><?php echo $textValue; ?></textarea></td>
				<td>
				<input type="button" id="updateCustomText" name="updateCustomText" value="Update Custom Text">
				</td>
			</tr>
			<tr>
				   <td style="width:25%">Reminder for Email:</td>
				   <td>
				   <select name="reminder_minutes" id="reminder_minutes"style="width:410px;height: 35px;">
				   <option value="0" <?php if($row_events['caseReminderTime'] == "0") { ?> selected="selected" <?php } ?>>---Select---</option>
				   <option value="5" <?php if($row_events['caseReminderTime'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_events['caseReminderTime'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_events['caseReminderTime'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_events['caseReminderTime'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_events['caseReminderTime'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_events['caseReminderTime'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_events['caseReminderTime'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_events['caseReminderTime'] == 240) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="300" <?php if($row_events['caseReminderTime'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_events['caseReminderTime'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_events['caseReminderTime'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_events['caseReminderTime'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_events['caseReminderTime'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_events['caseReminderTime'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_events['caseReminderTime'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_events['caseReminderTime'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_events['caseReminderTime'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_events['caseReminderTime'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_events['caseReminderTime'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_events['caseReminderTime'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_events['caseReminderTime'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_events['caseReminderTime'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_events['caseReminderTime'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
					</td>
			</tr>
			<tr>
				   <td style="width:25%">Reminder for PopUp:</td>
				   <td>
				   <select name="reminder_minutes_popup" id="reminder_minutes_popup"style="width:410px;height: 35px;">
				   <option value="0" <?php if($row_events['reminder_minutes_popup'] == "0") { ?> selected="selected" <?php } ?>>---Select---</option>
				   <option value="5" <?php if($row_events['reminder_minutes_popup'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_events['reminder_minutes_popup'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_events['reminder_minutes_popup'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_events['reminder_minutes_popup'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_events['reminder_minutes_popup'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_events['reminder_minutes_popup'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_events['reminder_minutes_popup'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_events['reminder_minutes_popup'] == 240) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="300" <?php if($row_events['reminder_minutes_popup'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_events['reminder_minutes_popup'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_events['reminder_minutes_popup'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_events['reminder_minutes_popup'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_events['reminder_minutes_popup'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_events['reminder_minutes_popup'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_events['reminder_minutes_popup'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_events['reminder_minutes_popup'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_events['reminder_minutes_popup'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_events['reminder_minutes_popup'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_events['reminder_minutes_popup'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_events['reminder_minutes_popup'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_events['reminder_minutes_popup'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_events['reminder_minutes_popup'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_events['reminder_minutes_popup'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
					</td>
			</tr>
				<tr>
				   <td style="width:25%">Event Color:</td>
				   <td>
				  <select name="eventColor" id="eventColor" style="width:410px;height: 35px;">
				   <option value="0" <?php if($row_events['caseEventColor'] == "0") { ?> selected="selected" <?php } ?>>No Color</option>
				   <option value="11" <?php if($row_events['caseEventColor'] == "11") { ?> selected="selected" <?php } ?>>Tomato</option>
				   <option value="6" <?php if($row_events['caseEventColor'] == "6") { ?> selected="selected" <?php } ?>>Tangerine</option>
				   <option value="2" <?php if($row_events['caseEventColor'] == "2") { ?> selected="selected" <?php } ?>>Sage</option>
				   <option value="7" <?php if($row_events['caseEventColor'] == "7") { ?> selected="selected" <?php } ?>>Peacock</option>
				   <option value="1" <?php if($row_events['caseEventColor'] == "1") { ?> selected="selected" <?php } ?>>Lavender</option>
				   <option value="8" <?php if($row_events['caseEventColor'] == "8") { ?> selected="selected" <?php } ?>>Graphite</option>
				   <option value="4" <?php if($row_events['caseEventColor'] == "4") { ?> selected="selected" <?php } ?>>Flamingo</option>
				   <option value="5" <?php if($row_events['caseEventColor'] == "5") { ?> selected="selected" <?php } ?>>Banana</option>
				   <option value="10" <?php if($row_events['caseEventColor'] == "10") { ?> selected="selected" <?php } ?>>Basil</option>
				   <option value="9" <?php if($row_events['caseEventColor'] == "9") { ?> selected="selected" <?php } ?>>Blueberry</option>
				   <option value="3" <?php if($row_events['caseEventColor'] == "3") { ?> selected="selected" <?php } ?>>Grape</option>
				   </select>&nbsp;&nbsp;<div id="colorIdentifier" style="width:20px;height:10px;"></div>
				   </td>
				   
				</tr>
            <tr><td colspan="2"><span id="show_msg" style="color:red;padding-left: 250px;"></span><input type="hidden" id="case_id" name="case_id" value="<?php echo $row_events['case_id'];?>"></td></tr>
            <tr>
                <td>&nbsp;<span style="color:green;"id="case_ajax_result"></span></td><td><input type="button" id="updatecase" name="updatecase" value="Update Case"></td>
				<td><input style="background:#ff1a1a !important; background-color:#ff1a1a;" type="button" id="deletecase" name="deletecase" value="Archive Case" onclick='javascript:archive_case(<?php echo $row_events['case_id']; ?>);'></td>
				
            </tr>
        </table>
        </form>
    </div>
	<div id="ex3" class="modal"></div>
	<div class="popup-overlay">
	<div class="popup-content">
     <input type="radio" name="eventCustomtext" value="allevent">Update All event<br>
	 <input type="radio" name="eventCustomtext" value="customText">Update Custom text<br>
	 <button style="margin:5px 5px 5px 5px;"id="cancelPopUp" >Cancel</button>
	<button style="margin:5px 5px 5px 5px;"id="okPopUp" >Ok</button>	 
	</div>
</div>
<script type="text/javascript">
jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
   jQuery('.multipleSelect').fastselect();
			jQuery("#ajax_result").show();
			jQuery("#updateCaseid").hide();
			jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_casejurisdiction.php",
            type: "post",
			data: { "caseid":<?php echo $case_id; ?>},
            success: function (response) {
				jQuery("#span_case_jurisdiction").html(response);
				jQuery("#ajax_result").hide(200);
				jQuery("#updateCaseid").show(200);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
			    jQuery(".overlay").hide();
            }
			});
			
			jQuery("#updatecase").click(function(){
				jQuery(".overlay").show();
				var caseMatter = document.getElementById("case_matter").value;
				var calendar_id = document.getElementById("calendar_id").value;
				var case_jurisdiction = document.getElementById("case_jurisdiction").value;
				var case_location = document.getElementById("case_location").value;
				var case_customtext = document.getElementById("case_customtext").value;
				var users = jQuery('#users').val();
				var attendee = jQuery('#attendee').val();
				var reminder_minutes = jQuery('#reminder_minutes').val();
				var reminder_minutes_popup = jQuery('#reminder_minutes_popup').val();
				var eventColor = jQuery('#eventColor').val();
				var casedisplay = jQuery('#casedisplay').val();
				var updateCaseValue = "CaseData";
				if(caseMatter.trim() == "")
				{
				
				  document.getElementById("case_matter").focus();
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
				
				} else {
				document.getElementById("case_matter").value = caseMatter.trim();
				document.getElementById("show_msg").innerHTML = "";
				
					jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatecase.php",
					type: "post",
					data: { "case_id":<?php echo $case_id; ?>,"case_matter":caseMatter,"calendar_id":calendar_id,"case_jurisdiction":case_jurisdiction,"case_location":case_location,"case_customtext":case_customtext,"users":users,"attendee":attendee,"reminder_minutes":reminder_minutes,"reminder_minutes_popup":reminder_minutes_popup,"eventColor":eventColor,"casedisplay":casedisplay,"updateCaseValue":updateCaseValue},
					success: function (response) {
								jQuery(".overlay").hide();	
								 jQuery("#case_ajax_result").hide();
								  $.notify("Case sucessfully updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
								 setTimeout(function() {
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 6000);
						
						
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   if(errorThrown)
					 {
						 //This code is just a bugfix for 500 error
						jQuery(".overlay").hide();
							  $.notify("Case sucessfully updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
						   setTimeout(function() {
								 jQuery('#ajax_result').hide(1000);
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 5000);
						    
					 }
					    
					}
					});
				}
			});
			
			     function archive_case(case_id)
        {
            <?php if(!isset($_SESSION['access_token'])) { ?>
                alert('Please login into Google Authentication to access delete.');
                window.location.href='https://tools.docketcalendar.com/google-login/?docket_case='+case_id;
            <?php } else { ?>
            jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Archiving a case will also archive all triggers and events associated with this case. Are you sure you want to proceed?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
               
				jQuery(".overlay").show();	
                jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_archive_case.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_id":case_id },
                    success: function (response) {
                       console.log(response);
					   jQuery(".overlay").hide();	
                      // jQuery("#ajax_result").show();
                       //jQuery("#ajax_result").html(response.html);
					   $.notify("Case archived", {
							  type:"danger",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"bell",
							  delay:3000,
							  blur: 0.8,
							  close: true,
							  background: "#D44950",
							  color: "#FDFEFE",
							  buttonAlign: "center",
							});
                       setTimeout(function() {
                            window.location.href = 'https://tools.docketcalendar.com/docket-cases';
                       }, 1000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                      if(errorThrown)
					  {
						 setTimeout(function() {
                            window.location.href = 'https://tools.docketcalendar.com/docket-cases';
                       }, 1000); 
					  }
                    }
                });
                jQuery.dialogbox.close();
            },
            function(){
                jQuery.dialogbox.close();
            }
            ]
        });

            <?php } ?>
        }
		
		
			jQuery("#updateCustomText").click(function(){
				jQuery(".overlay").show();
				var case_customtext = document.getElementById("case_customtext").value;
				if(case_customtext == "")
				{
					jQuery(".overlay").hide();
					$.notify("Please provide Custom Text", {
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
				
				jQuery(".popup-overlay, .popup-content").addClass("active");
				jQuery(".overlay").hide();
				
				
			});
			
			
		jQuery("#cancelPopUp").on("click", function(){
				jQuery(".popup-overlay, .popup-content").removeClass("active");
			});
		 jQuery("#okPopUp").on("click", function(){
			
				 jQuery(".overlay").show();
				jQuery(".popup-overlay, .popup-content").removeClass("active");
				var ValueOfCustomTextButton = jQuery("input[name='eventCustomtext']:checked"). val();
				var case_customtext = document.getElementById("case_customtext").value;
				
				if(ValueOfCustomTextButton == "customText")
				{
					jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatecustomtextcase.php",
					type: "post",
					data: {"case_id":<?php echo $case_id; ?>,"case_customtext":case_customtext },
					success: function (response) {
						jQuery(".overlay").hide();
						  	$.notify("Custom text for Case Updated", {
							  type:"success",
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
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   
					}
					});
				}
				if(ValueOfCustomTextButton == "allevent")
				{
					var caseMatter = document.getElementById("case_matter").value;
					var calendar_id = document.getElementById("calendar_id").value;
					var case_jurisdiction = document.getElementById("case_jurisdiction").value;
					var case_location = document.getElementById("case_location").value;
					var case_customtext = document.getElementById("case_customtext").value;
					
					var users = jQuery('#users').val();
					var updateCaseValue = "CaseData";
					var callUpdateFunctionality = "valueUpdate";
					var reminder_minutes = jQuery('#reminder_minutes').val();
					var reminder_minutes_popup = jQuery('#reminder_minutes_popup').val();
					var eventColor = jQuery('#eventColor').val();
					var casedisplay = jQuery('#casedisplay').val();
				
					jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatecase.php",
					type: "post",
					data: { "case_id":<?php echo $case_id; ?>,"case_matter":caseMatter,"calendar_id":calendar_id,"case_jurisdiction":case_jurisdiction,"case_location":case_location,"case_customtext":case_customtext,"users":users,"reminder_minutes":reminder_minutes,"eventColor":eventColor,"casedisplay":casedisplay,"updateCaseValue":updateCaseValue,"callUpdateFunctionality":callUpdateFunctionality,"reminder_minutes_popup":reminder_minutes_popup},
					success: function (response) {
						  setTimeout(function() {
								jQuery(".overlay").hide();	
								 jQuery("#case_ajax_result").hide();
								  $.notify("Custom text for all Case Events Updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 6000);
						
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					    if(errorThrown)
					 {
						 //This code is just a bugfix for 500 error
							jQuery(".overlay").hide();
							  $.notify("Custom text for all Case Events Updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
						  setTimeout(function() {
								 jQuery('#ajax_result').hide(1000);
								// jQuery("#case_ajax_result").html(response);
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 5000);
						    
					 }
					}
					});
				}
			}); 
		

		jQuery("#updateAttendees").click(function(){
				jQuery(".overlay").show();
				var caseMatter = document.getElementById("case_matter").value;
				var calendar_id = document.getElementById("calendar_id").value;
				var case_jurisdiction = document.getElementById("case_jurisdiction").value;
				var case_location = document.getElementById("case_location").value;
				var case_customtext = document.getElementById("case_customtext").value;
				var users = jQuery('#users').val();
				var attendee = jQuery('#attendee').val();
				var reminder_minutes = jQuery('#reminder_minutes').val();
				var eventColor = jQuery('#eventColor').val();
				var casedisplay = jQuery('#casedisplay').val();
				var updateCaseValue = "AttendeeData";
				if(caseMatter.trim() == "")
				{
				  document.getElementById("show_msg").innerHTML = "Please enter case name.";
				  document.getElementById("case_matter").focus();
				  return false;
				} else {
				document.getElementById("case_matter").value = caseMatter.trim();
				document.getElementById("show_msg").innerHTML = "";
				
					jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatecase.php",
					type: "post",
					data: { "case_id":<?php echo $case_id; ?>,"case_matter":caseMatter,"calendar_id":calendar_id,"case_jurisdiction":case_jurisdiction,"case_location":case_location,"case_customtext":case_customtext,"users":users,"attendee":attendee,"reminder_minutes":reminder_minutes,"eventColor":eventColor,"casedisplay":casedisplay,"updateCaseValue":updateCaseValue},
					success: function (response) {
						console.log(response);
						jQuery(".overlay").hide();
							  $.notify("Attendees sucessfully updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
						  setTimeout(function() {
								 jQuery('#ajax_result').hide(1000);
								// jQuery("#case_ajax_result").html(response);
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 5000);
						
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
				    if(errorThrown)
					 {
						 jQuery(".overlay").hide();
							  $.notify("Attendees sucessfully updated", {
								  type:"success",
								  align:"center", 
								  verticalAlign:"middle",
								  animation:true,
								  animationType:"scale",
								  icon:"check",
								  delay:3000,
								  blur: 0.8,
								 close: true,
								  buttonAlign: "center",
								});
						  setTimeout(function() {
								 jQuery('#ajax_result').hide(1000);
								// jQuery("#case_ajax_result").html(response);
								 window.location.href = "<?php echo get_home_url(); ?>/docket-cases";
							}, 5000);
					 }
					}
					});
				}
			});			
</script>
<?php
}

genesis();
?>