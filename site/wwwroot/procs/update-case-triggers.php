		<?php require_once('Connections/docketDataSubscribe.php');
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

		global $docketDataSubscribe;
		global $calendarData;
		global $docketDataSubscribe;
		global $attendee;
		global $response;
		global $events_array;
		global $case_name;
		global $existEvents;
		$result_html = array();
		$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	   $docketData = $GLOBALS['docketData'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketData,$theValue) : mysqli_escape_string($docketData,$theValue);

	  switch ($theType) {
		case "text":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;    
		case "long":
		case "int":
		  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
		  break;
		case "double":
		  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
		  break;
		case "date":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;
		case "defined":
		  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
		  break;
	  }
	  return $theValue;
	}
}
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
		echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
		}

		if($_SESSION['access_token'] == '')
		{
		echo "<script>alert('Please login into Google Authentication.');window.location.href='http://googledocket.com/google-login/?update_event=".$_GET['id']."';</script>";
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

		$query_importEvents = "SELECT i.import_docket_id,dc.case_id,dc.case_matter,i.triggerItem,i.trigger_date,i.trigger_time,i.meridiem,i.trigger_item,i.jurisdiction,dc.created_by FROM docket_cases dc INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
		WHERE dcu.user = '".$_SESSION['author_id']."' and i.import_docket_id = ".$importDocketId."";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);

		if ($totalRows_importEvents == 0) {
		echo "No Events found";
		} else {
		$row_events = mysqli_fetch_assoc($ImportEvents);
		$importDocketId = $row_events['import_docket_id'];
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
		
				$query_case_attendees = "SELECT * FROM docket_cases_attendees dca WHERE  dca.case_id = ".$caseId."";
				$caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
				$totalRows_attendees = mysqli_num_rows($caseAttendee);
				 $attendee = array();
				 if($totalRows_attendees > 0)
				{
				   while($row_attendees = mysqli_fetch_assoc($caseAttendee))
					{
						$attendee[] = $row_attendees['attendee'];
					}	
				}

				   $event_attendees = $row_events['attendees'];
				   if($event_attendees != '')
				   {
					array_push($attendee,$event_attendees);
					array_unique($attendee);
				   }

						function cmp($a, $b){
							if ($a == $b)
							return 0;
							return ($a['name'] < $b['name']) ? -1 : 1;
						}
				$contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp");
		?>
		<!-- CSS-->
		<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
		<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
		<link rel="stylesheet" href="jquery/css/fastselect.css">
		<link rel = "stylesheet" type = "text/css"    href = "jquery/css/standalone.css">
		<link href="jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
		<!-- JS-->
		<script src="jquery/js/jquery-1.8.3.js"></script>
		<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
		<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
		<script src="jquery/js/jquery.datetimepicker.full.min.js"></script>
		<style type="text/css">
		.FntCls {
		font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
		</style>
		<script>
		jQuery(window).load(function() {
			jQuery('.multipleSelect').fastselect();
		  jQuery("#mainContent").hide();
		  jQuery("#ajax_result").show();
		  jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
		 
			var jurisdicationVal = <?php echo $triggerJurisdiction;?>;
			var triggerItemVal = <?php echo $triggerItem;?>;

		 jQuery.ajax({
			url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
			type: "post",
			data: {"jurisdicationVal":jurisdicationVal,"triggerVal":triggerItemVal},
			dataType: "json",
			success: function (response) {
			   jQuery('#ajax_result').hide(1000);
			   jQuery("#jurisdictionValtd").html(response.jurisdictionResultVal);
			   jQuery("#mainContent").show(1000);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			}
		  });
		});

		</script>
		<div id="ajax_result"></div>
		<div id="mainContent">
			<div style="width: 80%;">
				<div style="float: left;width: 70%;"><h2>Update Case Trigger(s)</h2></div>
				<div style="float: right;"><a href="docket-cases">Docket Cases</a>&nbsp;|&nbsp;<a href='casetriggers?case_id=<?php echo $caseId; ?>'>Back</a></div>
			</div>
				<?php if($checkFlag == 'updateFlag'){?>
				<div class="widget FntCls">
					<table class="table table-striped" width="100%">
							<tr>
								<td class="reviewheader">Jurisdiction:</td>
								<td id="jurisdictionValtd"></td>
							<tr>	
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
							<tr>
							<tr>
								<td>Trigger Date:</td>
								<td> <input type="text" id="datepicker" class="datepicker" name="trigger_date" value="<?php echo date('m/d/Y', strtotime($triggerDate));?>">&nbsp;&nbsp;Time:
								<input type="text" name="trigger_time" id="trigger_time" value="<?php echo date("H:i:s",strtotime($triggerTime)); ?>" style="width:65px;" />&nbsp;&nbsp;(<?php  echo date('dS M Y', strtotime($triggerDate)) .' '.date("H:i:s",strtotime($triggerTime)).' '.$triggerMeridiem;?>)
								<form id="triggerDatafrom" method="post" action="/recalculatetrigger">
									<input type="hidden" name="TriggerDate" id="TriggerDate"/>
									<input type="hidden" name="TriggerTime" id="TriggerTime"/>
									<input type="hidden" name="meridiem" id="meridiem"/>
									<input type="hidden" name="importDocketId" value="<?php echo $importDocketId; ?>"/>
								</form>
								</td>
								
							</tr>
							<tr>
							<td>Add Attendees:</td>
							<td>
								<select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]">
									<?php foreach($contact_list as $contact) { 
									if($_SESSION['author_id'] == $contact['email'])
									{
										 unset($contact['email']); 
									} 
									?>
									  <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?>
									  </option>
									<?php } ?>
							  </select>
							</td>
							<td>
								<input type="button" value="Update" onclick="javascript:update_event();">
							</td>
						</tr>
						</table>
						<table style="margin-bottom:25px;">			
						<tr>
							<!-- <td style="padding-right:20px;"><input id="saveTriggerDate" onclick="updateTriggerDate(<?php //echo $importDocketId; ?>)"
							type="button" value="Save" /></td> -->
							<td style="padding-right:20px;"><input id="" onclick="recalculateTriggerDate(<?php echo $importDocketId; ?>)"
							type="button" value="Recalculate" /></td>
						</tr>
						</table>
						<table class="table table-striped" style="margin-top:5px;">
						<tr>
								<td colspan="2"> <b>Event</b>(s) </td><td></td>
						<tr>
							<?php
						$queryShowEvents = "SELECT import_event_id from import_events where import_docket_id = '".$_REQUEST['importdocketid']."'";
						$getimport_event_id = mysqli_query($docketDataSubscribe,$queryShowEvents);
						while($row1=mysqli_fetch_assoc($getimport_event_id))
						{
						$queryShowcaseevets = "SELECT event_date,short_name from case_events where import_event_id = '".$row1['import_event_id']."'";
						$getcase_event_id = mysqli_query($docketDataSubscribe,$queryShowcaseevets);
							while($row2=mysqli_fetch_assoc($getcase_event_id))
							{
								echo '<tr><td width="25%">'.date("dS M Y, h:i A", strtotime($row2['event_date'])).'</td><td>'.$row2['short_name'].'</td></tr>';
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
			
		<script type="text/javascript">
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
		/*
		function updateTriggerDate(importDocketId){
		var TriggerDate = jQuery('#datepicker').val();
		var TriggerTime = jQuery('#trigger_time').val();
		 jQuery("#ajax_result").show();
		 jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			jQuery.ajax({
			url: "<?php echo get_home_url(); ?>/ajax/ajax_update_trigger.php",
			type: "post",
			dataType: "json",
			data: { "importDocketId":importDocketId,"TriggerDate":TriggerDate,"TriggerTime":TriggerTime },
			success: function (response) {
			   jQuery('#ajax_result').hide(1000);
			   jQuery("#output").html(response.html);
			   setTimeout(function(){ location.reload();}, 1000);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			}
		  });
		}
		*/
		function recalculateTriggerDate(importDocketId){
		 <?php if(!isset($_SESSION['access_token'])) { ?>
				alert('Please login into Google Authentication to access delete.');
				window.location.href='http://googledocket.com/google-login/?docket_case='+case_id;
			<?php } else { ?>
				jQuery("#ajax_result").show();
				jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				
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
				setTimeout(function() {
				jQuery("#ajax_result").hide();
				jQuery("#triggerDatafrom").submit();
					   }, 500);

			<?php } ?>
		}
		</script>

		<?php
		}
		genesis();
		?>