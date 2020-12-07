<?php 
require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
/*
Template Name: View Calendar Event
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
	$archivecase = $_GET['archivecase'];
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

	$query_importEvents = "SELECT c.event_date,c.short_name,i.jurisdiction,dc.case_matter,c.case_event_id,c.eventtype,i.case_id,dc.case_matter,i.triggerItem,i.trigger_item,i.trigger_date,i.trigger_time,i.meridiem,ct.description,i.created_on as created_date,i.serviceType,i.attendees FROM docket_cases dc
	INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
	INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
	INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
	INNER JOIN courts as ct ON ct.systemID = i.jurisdiction
	WHERE dc.user_id IN (".$inClause.") AND c.import_event_id = ".$event_id." ";
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
	$description = $row_events['description'];
	$trigger_item = $row_events['trigger_item'];
	}

	$queryValidateUser = "SELECT user from docket_cases_users where case_id = ".$case_id." ";
	$resultValidateUser = mysqli_query($docketDataSubscribe,$queryValidateUser);
		while($resultattendeeData = mysqli_fetch_assoc($resultValidateUser))
			{
				$userAssignedData[] = $resultattendeeData['user'];
			}	
				if(in_array($_SESSION['author_id'],$userAssignedData))
				{
					$displayFlag=1;
				}
				else{
					$displayFlag=0;
				}
	$textValue = getEventCustomTextValue($row_events['case_event_id'],$row_events['trigger_item'],$row_events['jurisdiction'],$case_id,$_SESSION['userid']);		
?>

<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
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
</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div style="width: 80%;">
    <div style="float: left;width: 70%;"><h2>View Event</h2></div>
<div style="float: right;">Case:<a href="casetriggers?case_id=<?php echo $case_id;?>"><?php echo $row_events['case_matter'];?></a>&nbsp;
 <?php if($archivecase != "archvalue"){?>|&nbsp;<a href='#' onclick="javascript:edit_event(<?php echo $case_event_id; ?>);">Edit</a>&nbsp; <?php } ?>
|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></div>
</div>

<span id="ajax_result" style="padding-left:340px;color:red;"></span>


	<div class="widget FntCls">
        <table class="table table-striped" width="100%">
            <tr>
               <td style="width:15%">Event Date:</td><td><?php echo date("m/d/Y, h:i A",strtotime($event_date));?></td>
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
                <td>Jurisdication:</td><td><?php echo $description;?></td>
            </tr>
            <tr>
                <td>Trigger:</td><td><?php echo $row_events['triggerItem'];?></td>
            </tr>
            <tr>
                <td>Trigger Date:</td><td><?php echo $row_events['trigger_date'];?></td>
            </tr>
            <tr>
                <td>Trigger Time:</td><td><?php echo $row_events['trigger_time'].' '.$row_events['meridiem'];?></td>
            </tr>
            <tr>
                <td>Service Type:</td><td><?php echo $row_events['serviceType'];?></td>
            </tr>
            <tr>
                <td>Event Attendees:</td><td>
				<?php 
				
	$contactNames =array();
	$selectCaseAttendees =  "SELECT distinct(dca.attendee) FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id."";
	$caseAttendee = mysqli_query($docketDataSubscribe,$selectCaseAttendees);
	$totalNumRows = mysqli_num_rows($caseAttendee);
	if($totalNumRows > 0)
	{
	while ($totalAttendees = mysqli_fetch_array($caseAttendee))
	{

	array_push($contactNames,$totalAttendees['attendee']);
	}
	$query_case_customContact = "SELECT userContactName as name,userContactEmail as email FROM userContactUpdate  WHERE  userid = ".$_SESSION['userid']." and authenticator= '".$_SESSION['author_id']."'";
	$customContact = mysqli_query($docketDataSubscribe,$query_case_customContact);
	$totalRows_customContact = mysqli_num_rows($customContact);

	if($totalRows_customContact > 0)
	{
	while($row_customContact = mysqli_fetch_assoc($customContact))
	{
	$customCustomerarray[]= array('name'=>$row_customContact['name'], 'email'=>$row_customContact['email']);
	}
	}								
	function cmp($a, $b){
	if ($a == $b)
	return 0;
	return ($a['name'] < $b['name']) ? -1 : 1;
	}
	$contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp");    
	$contact_list = array_merge($contact_list, $customCustomerarray);

	if(isset($_SESSION['access_token'])) {

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
	}

	foreach($contact_list as $contact) 
	{ 
	if(in_array($contact['email'],$contactNames))
	{  echo $contact['name'].","; }
	} 
	}
					
				?>
				</td>
            </tr>
			<tr>
					<td>Event Custom Text:</td>
					<td><?php echo $textValue;?></td>
			</tr>
            <tr>
                <td>Created On:</td><td><?php echo date("m/d/Y, h:i A",strtotime($row_events['created_date']));?></td>
            </tr>
        </table>
	</div>
	<script type="text/javascript">
	jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
	  function edit_event(case_event_id)
    {
		 jQuery(".overlay").show();
        <?php if(!isset($_SESSION['access_token'])) { ?>
        var data = '<div style="padding:20px;">Please login into Google Authentication to access edit.</div>';
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
                window.location.href='<?php echo get_home_url(); ?>/google-login/?update_event='+case_event_id;
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
       <?php } else { ?>
               window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
        <?php } ?>
    }
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