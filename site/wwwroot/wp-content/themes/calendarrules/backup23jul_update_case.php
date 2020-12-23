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
    global $docketDataSubscribe;
    global $calendarData;
    require('globals/global_tools.php');
    require('globals/global_courts.php');
    ?>
	<!-- JAVASCRIPT -->
	<script src="jquery/js/jquery-1.8.3.js"></script>
	<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
	<!-- CSS -->
	<link rel="stylesheet" href="jquery/css/fastselect.css">
	<link rel = "stylesheet" type = "text/css"    href = "jquery/css/standalone.css">
    <?php
    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
    }
    if($_GET['case_id'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
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
         $user[] = $row_users['user'];
       }
    }
    if($totalRows_attendees > 0)
    {
       while($row_attendees = mysqli_fetch_assoc($caseAttendee))
       {
         $attendee[] = $row_attendees['attendee'];
       }
    }
    ?>
<style type="text/css">
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
</style>
    <div style="width: 80%;" class="FntCls">
        <div style="float: left;width: 70%;"><h2>Update Case</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="docket-cases">Docket Cases</a></div>
    </div>

    <div class="widget FntCls">
        <form id="add_case" name="add_case" method="post" action="procs/update_case.php" onsubmit="return validateCase();">
        <table class="table table-striped" width="100%" cellpadding="10" cellspacing="10">
            <tr>
               <td style="width:25%">Matter case:</td>
               <td><input type="text" id="case_matter" name="case_matter" style="width:370px;height: 35px;" value="<?php echo $row_events['case_matter'];?>"></td>
            </tr>
            <?php
        function cmp($a, $b){
            if ($a == $b)
            return 0;
            return ($a['name'] < $b['name']) ? -1 : 1;
        }
       $contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp");
       ?>
            <tr>
            <td>Assign users to case:</td>
            <td>
                <?php if(isset($_SESSION['google_contacts'])) { ?>
                <select multiple="multiple" id="users" name="users[]" class="multipleSelect">
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
                <select multiple="multiple" id="attendee" name="attendee[]" class="multipleSelect" style="width:380px;">
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
            </tr>
                   <tr>
                <td>Add Calendar:</td>
                <td>
                
                <?php
                    if(isset($calendarData))
                    { 
                    ?>
                    <select type="select" name="calendar_id" id="calendar_id" style="width:380px;">
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
				<td><input type="text" id="case_location" name="case_location" style="width:375px;height: 35px;" value="<?php echo $row_events['case_location'];?>"></td>
			</tr>
			<tr>
				<td>Custom Text:</td>
				<td><textarea id="case_customtext" name="case_customtext" style="width:380px;"><?php echo $row_events['case_customtext'];?></textarea></td>
			</tr>
            <tr><td colspan="2"><span id="show_msg" style="color:red;padding-left: 250px;"></span><input type="hidden" id="case_id" name="case_id" value="<?php echo $row_events['case_id'];?>"></td></tr>
            <tr>
                <td>&nbsp;</td><td><input type="submit" id="update" name="update" value="Update Case"></td>
            </tr>
        </table>
        </form>
    </div>
	<div id="ex3" class="modal">
                            </div>
<script type="text/javascript">
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
            }
			});
		      function validateCase()
		   {
			   var caseMatter = document.getElementById("case_matter").value;
			  
			 
				 if(caseMatter.trim() == "")
				 {
				  document.getElementById("show_msg").innerHTML = "Please enter case name.";
				  document.getElementById("case_matter").focus();
				  return false;
				 } else {
				  document.getElementById("case_matter").value = caseMatter.trim();
				  document.getElementById("show_msg").innerHTML = "";
				
				 }
				 
			 
		   }
</script>
<?php
}

genesis();
?>