<?php 
require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
require_once('googleCalender/google-calendar-api.php');
/*
Template Name: Update case triggers
*/

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

require('globals/global_tools.php');
require('globals/global_courts.php');
global $calendarData;
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
global $attendee;
global $response;
global $events_array;
global $case_name;
global $existEvents;
		
		session_start();
		$result_html = array();
		//    echo "<pre>"; print_r($existEvents);
		$montharray = array(
		"01" => "January",
		"02" => "February",
		"03" => "March",
		"04" => "April",
		"05" => "May",
		"06" => "June",
		"07" => "July",
		"08" => "August",
		"09" => "September",
		"10" => "October",
		"11" => "November",
		"12" => "December",
		);
		if($_SESSION['userid'] == '')
		{
		echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
		}

		if($_SESSION['access_token'] == '')
		{
			if($_SESSION['CheckAccess']!="NoGmail")
			{
				echo "<script>alert('Please login into Google Authentication.');window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/google-login/?update_event=".$_GET['id']."';</script>";
			}
			
		}
		$importDocketId  = $_REQUEST['importdocketid'];
		$flag = $_REQUEST['flag'];
		if($flag == 'updateFlag')
		{
		$checkFlag  = 'updateFlag';
		}
		else
		{
		$checkFlag  = 'viewData';
		}

		$query_importEvents = "SELECT i.import_docket_id,dc.case_id,dc.case_matter,i.triggerItem,i.serviceType,i.trigger_date,i.trigger_time,i.meridiem,i.trigger_item,i.jurisdiction,dc.created_by FROM docket_cases dc INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
		WHERE dcu.user = '".$_SESSION['author_id']."' and i.import_docket_id = ".$importDocketId."";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);

		if ($totalRows_importEvents == 0) {
		echo "No Events found";
		} else {
		$row_events = mysqli_fetch_assoc($ImportEvents);
		$importDocketId = $row_events['import_docket_id'];
		$serviceType = $row_events['serviceType'];
		$triggerName = $row_events['triggerItem'];
		$triggerDate  = $row_events['trigger_date']; 
		$triggerTime  = $row_events['trigger_time']; 
		$triggerMeridiem  = $row_events['meridiem'];
		$caseName = $row_events['case_matter'];
		$caseId = 	$row_events['case_id'];
		$triggerItem  = $row_events['trigger_item'];
		$triggerJurisdiction  = $row_events['jurisdiction'];
		}

		try {
			global $calendarData;
			$capi = new GoogleCalendarApi();

			// Get the access token
			$calendarData = $capi->GetCalendarsList($_SESSION['access_token']);
		}
		catch(Exception $e) {
			unset($_SESSION['access_token']);
			echo "<script>alert('Please login Google Authentication');window.location.href='/google-login';</script>";
		}
		
				$query_trigger_attendees = "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$caseId." and triggerlevel = 1 OR caselevel=1 and jurisid = '".$triggerJurisdiction." 'and triggerid='".$triggerItem."'";
				$triggerAttendee = mysqli_query($docketDataSubscribe,$query_trigger_attendees);
				$totalRows_attendees = mysqli_num_rows($triggerAttendee);
				$trriger_attendee =array();
				 if($totalRows_attendees > 0)
				{
				   while($row_attendees = mysqli_fetch_assoc($triggerAttendee))
					{
						$trriger_attendee[] = $row_attendees['attendee'];
					}	
				}
				$calTRIGARR = 0;
				if (!empty($trriger_attendee)) {
					$calTRIGARR = 1;
				}	
				$query_case_attendees = "SELECT attendee FROM docket_cases_attendees dca WHERE  dca.case_id = ".$caseId." and caselevel = 1";
				$caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
				while($row_case_attendees = mysqli_fetch_assoc($caseAttendee))
					{
						$case_attendees[] = $row_case_attendees['attendee'];
					}
				$calCASGARR = 0;
				if (!empty($case_attendees)) {
					$calCASGARR = 2;
				}
				$calRES = $calTRIGARR+$calCASGARR;
				$attendee = array();
				switch($calRES)
				{
					CASE 1:
							foreach($trriger_attendee as $keyVal){
							$attendee[]=$keyVal;
							}
							break;
					CASE 2:
							foreach($case_attendees as $keyVal){
							$attendee[]=$keyVal;
							}
							break;
					CASE 3:
							$attendArr =array_merge($trriger_attendee,$case_attendees);
							foreach($attendArr as $keyVal){
							$attendee[]=$keyVal;
							}	  
							break;
				}
      
	$query_authInfo = "SELECT do_not_recalculate_events,googlecontactprefrence FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' and user_id=".$_SESSION['userid']." ";
	$authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
	$totalRows_authInfo = mysqli_num_rows($authInfo);
	$row_authInfo = mysqli_fetch_assoc($authInfo);
	$googleContactlist=array();
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
	$textValue = getEventCustomTextValue($triggerItem ,$triggerJurisdiction,$caseId,$_SESSION['userid']);
	
	
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
<style type="text/css">
.FntCls {
		font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
		mainContent {
  display: none;
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
		<body>
		<div class="overlay">
			<div id="loading-img"></div>
		</div>
		<div id="ajax_result"></div>
		<div id="mainContent">
		<div style="width: 80%;">
			<div style="float: left;width: 70%;"><h2>Update Trigger</h2></div>
			<div style="float: right;"><a href="docket-cases">Docket Cases</a>&nbsp;|&nbsp;<a href='casetriggers?case_id=<?php echo $caseId; ?>'>Back</a></div>
		</div>
		<?php if($checkFlag == 'updateFlag'){?>
		<div class="widget FntCls">
		<table class="table table-striped" width="100%">
			<tr>
				<td class="reviewheader">Jurisdiction:</td>
				<td id="jurisdictionValtd"></td>
			</tr>	
			<tr>
				<td>Trigger:</td><td><?php echo $triggerName;?></td>
			</tr>
			<tr>
				<td>Case Name:</td><td><?php echo $caseName;?></td>
			</tr>
			<tr>
			<td class="reviewheader">Calendar:</td>
			<td> <?php 
			foreach ($calendarData as $calendar_list) {
				 $calendar_id = $calendar_list['id'];
				 $calendar_summary = $calendar_list['summary'];
				if($calendar_summary == $calendar_id){
						 $calendarID = "primary";
						 $calendarSummary = "Primary Calendar";
				 
					  } else {
						 $calendarID = $calendar_id;
						 $calendarSummary = $calendar_summary;          
					  }
			}
			echo $calendarSummary; ?>
			</td>
			</tr>
			<tr>
				<td>Trigger Date:</td>
				<td> <input type="text" id="datepicker" class="datepicker" name="trigger_date" value="<?php echo date('m/d/Y', strtotime($triggerDate));?>">&nbsp;&nbsp;Time:
				<input type="text" name="trigger_time" id="trigger_time" value="<?php echo date("H:i:s",strtotime($triggerTime)); ?>" style="width:65px;" />&nbsp;&nbsp;(<?php  echo date('dS M Y', strtotime($triggerDate)) .' '.date("H:i:s",strtotime($triggerTime)).' '.$triggerMeridiem;?>)
				<form id="triggerDatafrom" method="post" action="/recalculatetrigger">
					<input type="hidden" name="TriggerDate" id="TriggerDate"/>
					<input type="hidden" name="TriggerTime" id="TriggerTime"/>
					<input type="hidden" name="meridiem" id="meridiem"/>
					<input type="hidden" name="importDocketId" value="<?php echo $importDocketId; ?>"/>
					<input type="hidden" name="JurisdictionsValText" id="JurisdictionsValText"/>
					<input type="hidden" name="TriggersText" id="TriggersText"/>
					<input type="hidden" name="hidden_selectServiceText_val" id="hidden_selectServiceText_val"/>
					<input type="hidden" name="hidden_cmbMatterText_val" id="hidden_cmbMatterText_val"/>
				</form>
				</td>
			</tr>
		</table>
		<table style="margin-bottom:25px;">			
		<tr>
			<td style="padding-right:20px;"><input id="" onclick="recalculateTriggerDate(<?php echo $importDocketId; ?>)"
			type="button" value="Recalculate" />
			</td>
			
			<?php
			$queryShowRecalculateDropDown = "SELECT recalculate_flag  from import_events where import_docket_id = '".$importDocketId."'";
			$getdata = mysqli_query($docketDataSubscribe,$queryShowRecalculateDropDown);
			$checkRecalArr = array();
			while($rowdata=mysqli_fetch_assoc($getdata))
			{
				$checkRecalArr[] = $rowdata['recalculate_flag'];
			}
			if(in_array("true",$checkRecalArr))
			{	
			?>	
				<td style="padding-left:230px;">
				<span>Recalculated events:</span>
				<span>
				<select name="do_not_recalculate_events" id="do_not_recalculate_events">
					
					   <option value="use original date" <?php if($row_authInfo['do_not_recalculate_events'] == "use original date") { ?> selected="selected" <?php } ?>>use original date</option>
					  <option value="use new date" <?php if($row_authInfo['do_not_recalculate_events'] == "use new date") { ?> selected="selected" <?php  } ?>>use new date</option>
				</select>
				</span>	
				<span>
				<input  id="recalButtn" onclick="updateRecalculateFlag(<?php echo $importDocketId; ?>)" type="button" value="Change" />
				</span>
				</td>
			<?php	
			}
			?>
			
		</tr>
		</table>
		<table  cellspacing="10">
				<tr>
					<td>Trigger Custom Text:</td>
					<td><textarea id="trigger_customtext" name="trigger_customtext" style="width:374px;height: 95px;"><?php echo $textValue;?></textarea></td>
				</tr>
				<tr>
					<td>Trigger Attendees:</td>
					<td style="padding-left:2px;">
						<select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]">
							<?php foreach($contact_list as $contact) { 
							if($_SESSION['author_id'] == $contact['email'])
							{
								 unset($contact['email']); 
								 unset($contact['name']); 
							} 
							?>
							  <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?>
							  </option>
							<?php } ?>
					  </select>
					</td>
					
				</tr>
				<tr>
					<td style="padding-left:10px;">
						<input type="button" value="Update" onclick="javascript:updateTriggerAttendee(<?php echo $caseId ; ?>,<?php echo $importDocketId  ; ?>);">
					</td>

				</tr>
								
		</table>
		<div style="float:right;margin-bottom:5px;">
			<input style="background:#ff1a1a !important; background-color:#ff1a1a;" type="button" id="deletecase" name="deletecase" value="Archive Trigger" onclick='javascript:archive_calendar_trigger(<?php echo $caseId;?>,<?php echo $triggerItem; ?>);'>
		</div>
		<table class="table table-striped" style="margin-top:5px;">
		<tr>
				<td colspan="2"> <b>Event</b>(s) </td><td></td>
		<tr>
	<?php
		$queryShowEvents = "SELECT import_event_id  from import_events where import_docket_id = '".$importDocketId."'";
		$getimport_event_id = mysqli_query($docketDataSubscribe,$queryShowEvents);
		
		while($row1=mysqli_fetch_assoc($getimport_event_id))
		{
		
		$queryShowcaseevets = "SELECT event_date,short_name FROM case_events WHERE import_event_id = '".$row1['import_event_id']."'
		AND case_event_id NOT IN (select eventid FROM docket_cases_archive WHERE caseid = ".$caseId." AND userid = ".$_SESSION['userid']." AND event_delete = 2)";
		$getcase_event_id = mysqli_query($docketDataSubscribe,$queryShowcaseevets);
			while($row2=mysqli_fetch_assoc($getcase_event_id))
			{
				echo '<tr><td width="25%">'.date("m/d/Y, h:i A", strtotime($row2['event_date'])).'</td><td>'.$row2['short_name'].'</td></tr>';
			}
		
		}	
						
	?>
		</table>		
	</div>
	<?php 
			}
	?>
		<div id="output"></div>
	</div>	
</body>	
<script type="text/javascript">
		jQuery(window).load(function() {
		jQuery(".overlay").show();
		jQuery('.multipleSelect').fastselect();
		var jurisdicationVal = <?php echo $triggerJurisdiction;?>;
		var triggerItemVal = <?php echo $triggerItem;?>;

		jQuery.ajax({
			url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
			type: "post",
			data: {"jurisdicationVal":jurisdicationVal,"triggerVal":triggerItemVal},
			dataType: "json",
			success: function (response) {
			   jQuery('#ajax_result').hide(1000);
			   jQuery(".overlay").hide();
			   jQuery('#JurisdictionsText').val(response.jurisdictionResultVal);
			   jQuery("#jurisdictionValtd").html(response.jurisdictionResultVal);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			}
		  });
		});

		jQuery('#trigger_time').datetimepicker({
			 datepicker:false,
			 format:'h:i a',
			 formatTime: 'h:i a',
			 step:30,
			 ampm: true,
			 value:'<?php echo $triggerTime; ?>',      
		});
		jQuery('#trigger_time').click(function() {
		jQuery('#txtTime').datetimepicker({
			 value:'08:00', 
			 datepicker:false,
			 format:'h:i a',
			 formatTime: 'h:i a',
			 step:30,
			 ampm: true,     
		});
		}); 
		function updateTriggerAttendee(caseIdval,importDocketId)
		{
			jQuery(".overlay").show();
			var caseId = caseIdval;
			var attendees =jQuery("#attendees").val();
			var triggerCustomText =jQuery("#trigger_customtext").val();

			//jQuery("#ajax_result").show();
			//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			jQuery.ajax({
			url: "<?php echo get_home_url(); ?>/ajax/ajax_update_trigger_attendees.php",
			type: "post",
			dataType: "json",
			data: { "caseId":caseId,"importDocketId":importDocketId,"attendees":attendees,"triggerCustomText":triggerCustomText },
			success: function (response) {
			   //jQuery('#ajax_result').hide(1000);
			   //jQuery("#ajax_result").html(response.html);
			   jQuery(".overlay").hide();
			   $.notify("Trigger sucessfully updated", {
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
			  setTimeout(function(){ location.reload();}, 1000);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			   	   jQuery(".overlay").hide();
			   $.notify("Trigger sucessfully updated", {
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
			  setTimeout(function(){ location.reload();}, 1000);
			}
		  });
		}
	
		function recalculateTriggerDate(importDocketId){
		 <?php if(!isset($_SESSION['access_token'])) { ?>
				alert('Please login into Google Authentication to access delete.');
				window.location.href='<?php echo get_home_url(); ?>/google-login/?docket_case='+case_id;
			<?php } else { ?>
				//jQuery("#ajax_result").show();
				//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery(".overlay").show();
				 var txtTime = jQuery('#trigger_time').val();
				 var meridiemval;
			     if(txtTime != '') {
				 txtTime_arr = txtTime.split(':');
				 txtTime_arr_2 = txtTime_arr[1].split(' ');
				 meridiemval = txtTime_arr_2[1];
				 txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
				 }
				 var TriggerDateVal = jQuery('#datepicker').val();
		         var TriggerTimeVal = txtTime;
				 jQuery('#TriggerDate').val(TriggerDateVal);
				 jQuery('#TriggerTime').val(TriggerTimeVal);
				 jQuery('#meridiem').val(meridiemval);
				 jQuery('#hiddenattenddes').val(jQuery("#attendees").val());
				 //alert('case NAME : <?php echo $caseName; ?>');
				 jQuery('#JurisdictionsValText').val(jQuery("#jurisdictionValtd").text());
				 jQuery('#TriggersText').val("<?php echo $triggerName;?>");
				 jQuery('#hidden_selectServiceText_val').val("<?php echo $serviceType; ?>");
				 jQuery('#hidden_cmbMatterText_val').val("<?php echo $caseName; ?>");
				 
				setTimeout(function() {
				//jQuery("#ajax_result").hide();
				jQuery(".overlay").hide();
				jQuery("#triggerDatafrom").submit();
					   }, 2000);

			<?php } ?>
		}
		
		function updateRecalculateFlag(importDocketId)
		{
		
			var flagValue = jQuery('#do_not_recalculate_events').val();
			 <?php if(!isset($_SESSION['access_token'])) { ?>
				alert('Please login into Google Authentication to access delete.');
				window.location.href='<?php echo get_home_url(); ?>/google-login/?docket_case='+case_id;
			<?php } else { ?>
				jQuery("#ajax_result").show();
				jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_update_recalculate_flag.php",
					type: "post",
					dataType: "json",
					data: { "importDocketId":importDocketId,"flagValue":flagValue},
					success: function (response) {
					jQuery('#ajax_result').hide(500);
					jQuery("#ajax_result").html(response.html);
					},
					error: function(jqXHR, textStatus, errorThrown) {
					console.log(textStatus, errorThrown);
			   	
					}
				});

			<?php } ?>
			
		}
		
		  function archive_calendar_trigger(case_id,trigger_item)
    {
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
                window.location.href='https://www.google.calendarrules.com/docket-cases';
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
            content:'Archive Trigger also result in archive its events . Are you sure you want to proceed?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
                //jQuery("#ajax_result").show();
                //jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery(".overlay").show();
                jQuery.ajax({
                    //url: "<?php echo get_home_url(); ?>/ajax/ajax_delete_trigger.php",
					url: "<?php echo get_home_url(); ?>/ajax/ajax_archive_trigger.php",
                    type: "post",
                    dataType: "json",
                    data: {"case_id":case_id,"trigger_item":trigger_item},
                    success: function (response) {
                       console.log(response);
                      //jQuery("#ajax_result").show();
                      //jQuery("#ajax_result").html(response.html);
					  jQuery(".overlay").hide();
					   $.notify("Trigger archived", {
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
                          window.location.href = '<?php echo get_home_url(); ?>/casetriggers?case_id='+case_id;
                      }, 2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
						
                        //This code is just a bugfix for 500 error
					   if(textStatus === 'error')
					 {
						
						jQuery("#ajax_result").show();
						var resVal = "<span style='color:green'>";
						var ResponseText= '<?php echo "Successfully deleted trigger."; ?>';
						var ResponseVal = resVal+ResponseText+'</span>';
						jQuery("#ajax_result").html(ResponseVal);
						setTimeout(function() {
						window.location.href = '<?php echo get_home_url(); ?>/casetriggers?case_id='+case_id;
						}, 2000);
						jQuery("#ajax_result").css("color", "black");
								
					 }
					 console.log(textStatus, errorThrown);
                       jQuery("#button_export").hide();
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
	
		<?php
		}
		
	function getEventCustomTextValue($triggerId,$juriId,$caseId,$userId)
{
	$database_docketData = $GLOBALS['database_docketData'];
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	mysqli_select_db($docketDataSubscribe,$database_docketData);
	
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
		genesis();
		?>