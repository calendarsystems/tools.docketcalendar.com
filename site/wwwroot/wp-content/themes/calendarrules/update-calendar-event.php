<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Update Calendar Event
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

  $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

    require('globals/global_tools.php');
    require('globals/global_courts.php');

    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    if($_GET['id'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-cases';</script>";
    }
    if($_SESSION['access_token'] == '')
    {
      echo "<script>alert('Please login into Google Authentication.');window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/google-login/?update_event=".$_GET['id']."';</script>";
    }
    unset($_SESSION['update_event']);
    $event_id = $_GET['id'];
	$arrayForCurrentUserIdForCase = array();
	$arrayForUserIdForAssignCase = array();
	$getAllUserIdForAssignCase="SELECT dc.user_id from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id != '".$_SESSION['userid']."'  AND dc.created_by != '".$_SESSION['author_id']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
	$dataUserIdForAssignCase = mysqli_query($docketDataSubscribe,$getAllUserIdForAssignCase);
	while($rowUserIdForAssignCase = mysqli_fetch_assoc($dataUserIdForAssignCase))
		{
			$arrayForUserIdForAssignCase[] = $rowUserIdForAssignCase["user_id"];
		}
	$arrayForCurrentUserIdForCase[]=$_SESSION['userid'];
	
	$output = array_merge($arrayForCurrentUserIdForCase,$arrayForUserIdForAssignCase);
	$output = array_unique($output);
	
	$inClause = implode(",",$output);
	
$query_importEvents = "SELECT c.event_date,c.short_name,i.jurisdiction,dc.case_matter,c.case_event_id,c.eventtype,i.import_docket_id,i.case_id,dc.case_matter,i.triggerItem,i.trigger_item,i.trigger_date,i.trigger_time,e.event_docket,e.has_child,i.attendees FROM docket_cases dc
INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
WHERE dc.user_id IN (".$inClause.") AND c.import_event_id = ".$event_id."";
$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
$totalRows_importEvents = mysqli_num_rows($ImportEvents);

if ($totalRows_importEvents == 0) {
    echo "No Events found";
} else {
$row_events = mysqli_fetch_assoc($ImportEvents);

    $short_name = $row_events['short_name'];
    $event_date = $row_events['event_date'];
	$eventType = $row_events['eventtype'];
    $case_event_id = $row_events['case_event_id'];
    $case_id = $row_events['case_id'];
	$triggerDate  = $row_events['trigger_date']; 
    $triggerTime  = $row_events['trigger_time']; 
    $triggerMeridiem  = $row_events['meridiem'];
	$importDocketId  = $row_events['import_docket_id'];
	$trigger_item  = $row_events['trigger_item'];

    if($row_events['event_docket'] == 'true')
    {
       $case = 'eventdocket';
    } else if($row_events['has_child'] == 1)
    {
       $case = 'parent';
    } else {
       $case = 'normal';
    }
}


	$query_case_attendees = "SELECT distinct(dca.attendee) FROM docket_cases_attendees dca WHERE dca.case_id =".$case_id." and (dca.caselevel = 1 OR dca.triggerlevel = 1 OR dca.eventid=".$event_id.")";
    $caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
	
    $totalRows_attendees = mysqli_num_rows($caseAttendee);
    if($totalRows_attendees > 0)
    {
       while($row_attendees = mysqli_fetch_assoc($caseAttendee))
       {
        $attendee[] = $row_attendees['attendee'];
       }
    }
	
	/*
   $event_attendees = $row_events['attendees'];
   if($event_attendees != '')
   {
    array_push($attendee,$event_attendees);
    array_unique($attendee);
   }
	*/
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
       $textValue = getEventCustomTextValue($row_events['case_event_id'],$row_events['trigger_item'],$row_events['jurisdiction'],$case_id,$_SESSION['userid']);	   
       ?>
<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="jquery/css/notify.css"> 
<script type="text/javascript" src="jquery/js/notify.js"></script>
<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
<link rel="stylesheet" href="jquery/css/fastselect.css">
<link rel = "stylesheet" type = "text/css"    href = "jquery/css/standalone.css">
<link href="jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
<!-- JS-->
<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
<script src="jquery/js/jquery.datetimepicker.full.min.js"></script>
<style>
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
    }
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
</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div style="width: 80%;">
    <div style="float: left;width: 70%;"><h2>Update Event</h2></div>
    <div style="float: right;">Case:<a href="casetriggers?case_id=<?php echo $case_id;?>"><?php echo $row_events['case_matter'];?></a>&nbsp;|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></div>
</div>
  <div class="widget FntCls">
        <table class="table table-striped" width="100%">
            <tr>
               <td style="width:15%">Event Date:</td><td><input type="text" id="event_date" name="event_date" value="<?php echo $event_date;?>"></td>
            </tr>
            <tr>
                <td>Event Name:</td><td><?php echo $short_name;?></td>
            </tr>
			<tr>
                <td>Event Type:</td><td><?php echo $eventType;?></td>
            </tr>
            <tr>
                <td>Case:</td><td><?php echo $row_events['case_matter'];?></td>
            </tr>
            <tr>
                <td>Trigger:</td><td><?php echo $row_events['triggerItem'];?></td>
            </tr>
            <tr>
                <td>Trigger Date & Time:</td><td><?php  echo date('m/d/Y', strtotime($triggerDate)) .' '.date("h:i A",strtotime($triggerTime)).' '.$triggerMeridiem;?>&nbsp;</td>
            </tr>
             <tr>
                <td>Event Attendees:</td><td><select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]">
                    <?php foreach($contact_list as $contact) { 
           if($_SESSION['author_id'] == $contact['email'])
                    {
                         unset($contact['email']); 
						 unset($contact['name']); 
                    } 
          ?>
          <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>
          <?php } ?>
              </select></td>
            </tr>
			<tr>
					<td>Event Custom Text:</td>
					<td><textarea id="event_customtext" name="event_customtext" style="width:374px;height: 95px;"><?php echo $textValue; ?></textarea></td>
			</tr>
            <tr>
                <td align="left"><input type="button" value="Update Event" onclick="javascript:update_event(<?php echo $importDocketId;?>,<?php echo $case_event_id;?>,<?php echo $case_id;?>,'<?php echo $case; ?>');">
                &nbsp;</td>
				<td align="right"><input type="button" style="background:#ff1a1a !important; background-color:#ff1a1a;" value="Archive Event" onclick="javascript:delete_calendar_events(<?php echo $row_events['case_event_id']; ?>,<?php echo $case_id;?>,<?php echo $row_events['trigger_item']; ?>,<?php echo $importDocketId; ?>);">
                &nbsp;</td>
            </tr>
        </table>
  </div>
    <script type="text/javascript">
	jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
        jQuery('.multipleSelect').fastselect();
        function update_event(importDocketId,case_event_id,case_id,caselab)
        {
			
            var attendees =  jQuery("#attendees").val();
			var eventCustomText =jQuery("#event_customtext").val();
			
            if(caselab == "eventdocket")
            {
            
               jQuery.dialogbox({
                type:'msg',
                title:'',
                content:'Do you wish to re-calculate all other events dates?',
                closeBtn:true,
                btn:['Confirm','Cancel'],
                call:[
                function(){
                   jQuery(".overlay").show();
                    var event_date = jQuery("#event_date").val();
                    console.log(event_date);
                    jQuery.ajax({
                        url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
                        type: "post",
                        dataType: "json",
                        data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date, "caselab":caselab,"eventCustomText":eventCustomText },
                        success: function (response) {
                         
                           console.log(response);
                          // jQuery("#ajax_result").show(4500);
                          // jQuery("#ajax_result").html(response.html);
						  jQuery(".overlay").hide();
						 $.notify("Event sucessfully updated", {
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
                               window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
                           }, 500);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           console.log(textStatus, errorThrown);
						   if(errorThrown)
						   {
									 $.notify("Event sucessfully updated", {
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
							   /*
                           setTimeout(function() {
                                window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
                           }, 500);
						   */
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
            }

            if(caselab == "parent")
            {
				
               jQuery.dialogbox({
                type:'msg',
                title:'',
                content:'Updating this event will change dates for child events also, do you wish to proceed?',
                closeBtn:true,
                btn:['Confirm','Cancel'],
                call:[
                function(){
                   // jQuery("#ajax_result").show();
                   // jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				    jQuery(".overlay").show();
                    var event_date = jQuery("#event_date").val();
                    console.log(event_date);
					
							//jQuery("#ajax_result").show(500);
                           setTimeout(function() {
                                window.location.href = '<?php echo get_home_url(); ?>/viewchildevents?event_date='+event_date+'&case_event_id='+case_event_id+'&attendees='+attendees+'&caselab='+caselab+'&eventCustomText='+eventCustomText;
                           }, 500);
                    jQuery.dialogbox.close();
                },
                function(){
                    jQuery.dialogbox.close();
                }
                ]
              });
            }

            if(caselab == "normal")
            {
              
               // jQuery("#ajax_result").show();
                //jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				 jQuery(".overlay").show();
                var event_date = jQuery("#event_date").val();
                //console.log(event_date);
                jQuery.ajax({
                        url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
                        type: "post",
                        dataType: "json",
                        data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date,"eventCustomText":eventCustomText},
                        success: function (response) {
							
                           //jQuery("#ajax_result").show(4500);
                           //jQuery("#ajax_result").html(response.html);
						    jQuery(".overlay").hide();
							 $.notify("Event sucessfully updated", {
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
							/*
                           setTimeout(function() {
                                window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
                           }, 500);
						   */
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           console.log(textStatus, errorThrown);
                             if(errorThrown)
						   {
									 $.notify("Event sucessfully updated", {
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
								   window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
							   }, 500); 
						   }
                        }
                 });
            }
        }
		function delete_calendar_events(case_event_id,case_id,trigger_item,import_docket_id)
    {
		
		var caseid = <?php echo $case_id;?>
		
        <?php if(!isset($_SESSION['access_token'])) { ?>
        var data = '<div style="padding:20px;">Please login into Google Authentication to access delete.</div>';
        jQuery.dialogbox({
            type:'msg',
            title:'',
            content:data,
            btn:['Login'],
            call:[
              function(){
                jQuery.dialogbox.prompt({
                    content:'Redirect to Google Authentication Login',
                    time:2000
                });
                window.location.href='<?php echo get_home_url(); ?>/docket-case';
             }
            ],
            closeCallback:function(){
                jQuery.dialogbox.prompt({
                    content:'You have closed the message',
                    time:2000
                });
                return false;
            }
        });
        //alert('Please login into Google Authentication to access delete.');

        <?php } else { ?>
        jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Are you sure you want to archived events?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
                //jQuery("#ajax_result").show();
                //jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery(".overlay").show();
                jQuery.ajax({
                    //url: "<?php echo get_home_url(); ?>/ajax/delete_import_calendar.php",
					url: "<?php echo get_home_url(); ?>/ajax/ajax_archive_event.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_event_id":case_event_id,"trigger_item":trigger_item,"caseid":caseid },
                    success: function (response) {
                       console.log(response);
					   jQuery(".overlay").hide();
                      // jQuery("#ajax_result").show();
                      // jQuery("#ajax_result").html(response.html);
					     $.notify("Event archived", {
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
						    jQuery(".overlay").show();
                            window.location.href = '<?php echo get_home_url(); ?>/calendar-events?case_id='+case_id+'&importDocketId='+import_docket_id;
                       }, 2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                          if(errorThrown)
						   {
									 $.notify("Event archived", {
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
								   window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
							   }, 500); 
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

    </script>
    <script>
    jQuery('#event_date').datetimepicker({
            datepicker:true,
            format:'Y-m-d H:i:s a',
            value:'<?php echo $event_date;?>',
            formatTime: 'g:i a',
            step:30,
            ampm: true
    });

    </script>
<?php
}

function getEventCustomTextValue($caseEventId,$triggerId,$juriId,$caseId,$userId)
{
	$database_docketData = $GLOBALS['database_docketData'];
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	mysqli_select_db($docketDataSubscribe,$database_docketData);
	$sqlSelectEventCustomText="SELECT event_custometext FROM docket_customtext WHERE event_eventid='".$caseEventId."' AND trigger_trigid='".$triggerId."'  AND case_id=".$caseId." AND user_id=".$userId." AND event_custometext IS NOT NULL  order by id desc limit 1";
	$ResultSelectEventCustomText = mysqli_query($docketDataSubscribe,$sqlSelectEventCustomText);
	$totalRowsResultSelectEventCustomText = mysqli_num_rows($ResultSelectEventCustomText);
	if($totalRowsResultSelectEventCustomText > 0)
	{
		while ($rowData = mysqli_fetch_assoc($ResultSelectEventCustomText))
				{
					
					return $textValue = $rowData ['event_custometext'];
				}
	}
	else{
		
		$sqlSelectTriggerCustomText="SELECT trigger_customtext FROM docket_customtext WHERE   trigger_trigid='".$triggerId."' AND trigger_juri='".$juriId."' AND case_id=".$caseId." AND user_id=".$userId." AND trigger_customtext IS NOT NULL  order by id desc limit 1";
	    $ResultSelectTriggerCustomText = mysqli_query($docketDataSubscribe,$sqlSelectTriggerCustomText);
		$totalRowsResultSelectTriggerCustomText = mysqli_num_rows($ResultSelectTriggerCustomText);
		if($totalRowsResultSelectTriggerCustomText > 0)
		{
			while ($rowTriggerData = mysqli_fetch_assoc($ResultSelectTriggerCustomText))
							{
								
								return $textValue = $rowTriggerData ['trigger_customtext'];
							}
		}
		else{
			$sqlSelectCaseCustomText="SELECT case_customtext FROM docket_customtext WHERE   case_id=".$caseId." AND user_id=".$userId." AND case_customtext IS NOT NULL  order by id desc limit 1";
			$ResultSelectCaseCustomText = mysqli_query($docketDataSubscribe,$sqlSelectCaseCustomText);
			$totalRowsResultSelectCaseCustomText = mysqli_num_rows($ResultSelectCaseCustomText);
			if($totalRowsResultSelectCaseCustomText > 0)
			{
				while ($rowCaseData = mysqli_fetch_assoc($ResultSelectCaseCustomText))
				{
					
					return $textValue = $rowCaseData ['case_customtext'];
				}
			}
			else{
				$textValue="";
				return $textValue;
			}
		}
	}
	
	
}
genesis();
?>