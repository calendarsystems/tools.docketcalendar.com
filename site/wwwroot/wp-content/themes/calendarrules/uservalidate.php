<?php 
/*
Template Name: Uservalidate
*/
//custom hooks below here...
require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
require_once('googleCalender/google-calendar-api.php');
//@error_reporting(E_ALL);
//@ini_set('display_errors', 1);
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	global $calendarData;
  	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $response;
    global $events_array;
    global $case_name;
    global $existEvents;

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

			if($_GET['importDocketId'] == "")
			{
				$importDocketId = $_SESSION['docket_search_id'];
				$displayVal = 1;
				
			}
			else{
				$importDocketId =$_GET['importDocketId'];
				$_SESSION['docket_search_id'] = $importDocketId;
				$displayVal = 2;
				
			}
		
  		$query_searchInfo = "SELECT * FROM import_docket_calculator WHERE import_docket_id = '".$importDocketId."' ";
        $searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
        //$row_searchInfo = mysqli_fetch_assoc($searchInfo);
        $totalRows_searchInfo = mysqli_num_rows($searchInfo);
        if($totalRows_searchInfo > 0)
        {	
        		while($row_searchInfo = mysqli_fetch_assoc($searchInfo))
            	{		
                 		$caseId = $row_searchInfo['case_id'];
                 		$triggerDate  = $row_searchInfo['trigger_date']; 
                 		$triggerTime  = $row_searchInfo['trigger_time']; 
                 		$triggerMeridiem  = $row_searchInfo['meridiem'];
                 		$triggerJurisdiction  = $row_searchInfo['jurisdiction'];
                 		$triggerItem  = $row_searchInfo['trigger_item'];
                 		$triggerItemName  = $row_searchInfo['triggerItem'];
                 		$triggerCalendarId  = $row_searchInfo['calendar_id'];
                 		if($row_searchInfo['location'])
                 		{
                 			$triggerLocation  = $row_searchInfo['location'];
                 		}
                 		if($row_searchInfo['serviceType'])
                 		{
                 			$triggerServiceType  = $row_searchInfo['serviceType'];
                 		}
                 		if($row_searchInfo['custom_text'])
                 		{
                 			$triggerCustomText  = $row_searchInfo['custom_text'];
                 		}
                 		$triggerEvent = $row_searchInfo['events'];
                 		
            	}
				
					
		$query_import_attendees = "SELECT distinct(ic.attendees) FROM import_docket_calculator ic INNER JOIN docket_cases dc  ON ic.case_id = dc.case_id  WHERE ic.import_docket_id = ".$importDocketId."  AND dc.created_by = '".$_SESSION['author_id']."'";
        $caseAttendee = mysqli_query($docketDataSubscribe,$query_import_attendees);
        $totalRows_attendees = mysqli_num_rows($caseAttendee);
	
		 if($totalRows_attendees > 0)
		{
			while($row_attendees = mysqli_fetch_assoc($caseAttendee))
				{
					$importDocket_attendee=explode(",",$row_attendees['attendees']) ;
				}
				$calIMP = 0;
			if (!empty($importDocket_attendee)) {
				$calIMP = 1;
			}
			$query_case_attendees = "SELECT distinct(attendee) FROM docket_cases_attendees dca WHERE  dca.case_id = ".$caseId." ";
			$caseAttendee = mysqli_query($docketDataSubscribe,$query_case_attendees);
			while($row_case_attendees = mysqli_fetch_assoc($caseAttendee))
				{
					$case_attendees[] = $row_case_attendees['attendee'];
				}
			$calCASEARR  = 0;	
			if (!empty($case_attendees)) {
				$calCASEARR = 2;
			}	
			
			$CalIMPCASE = $calIMP + $calCASEARR;
			$attendee = array();
			switch($CalIMPCASE )
			{
				CASE 3:
					$attendArr =array_merge($importDocket_attendee,$case_attendees);
					foreach($attendArr as $keyVal){
					$attendee[]=$keyVal;
					}
					break;
				CASE 2:
					foreach($case_attendees as $keyVal){
					$attendee[]=$keyVal;
					}
					break;
				CASE 1:
					foreach($importDocket_attendee as $keyVal){
					$attendee[]=$keyVal;
					}
					break;
			}
	
		}
		        $query_caseInfo = "SELECT * FROM docket_cases WHERE case_id = '".$caseId."' ";
		        $caseInfo = mysqli_query($docketDataSubscribe,$query_caseInfo);
		        $totalRows_caseInfo = mysqli_num_rows($caseInfo);
		       //$row_caseInfo = mysqli_fetch_assoc($caseInfo);
		        //echo "<pre>";print_r($row_caseInfo);
		        if($totalRows_caseInfo > 0)
		        {
		        	while( $row_caseInfo = mysqli_fetch_assoc($caseInfo))
            		{
            			$caseMatter = $row_caseInfo['case_matter'];
            		}	
			       	$query_eventsInfo = "SELECT ce.event_date,ie.system_id FROM import_docket_calculator as idc
			        INNER JOIN import_events as ie ON ie.import_docket_id = idc.import_docket_id
			        INNER JOIN case_events as ce ON ce.import_event_id = ie.import_event_id
			        WHERE idc.case_id = '".$row_searchInfo['case_id']."' ";
			        $searchEventsInfo = mysqli_query($docketDataSubscribe,$query_eventsInfo);
			        $totalRows_searchEventsInfo = mysqli_num_rows($searchEventsInfo);
			        $existEvents = array();
			        if($totalRows_searchEventsInfo > 0)
			        {
			             while($row_searchEventsInfo = mysqli_fetch_assoc($searchEventsInfo))
			             {
			                $eventDate = date("Y-m-d",strtotime($row_searchEventsInfo['event_date']));
			                $eventSystemID = $row_searchEventsInfo['system_id'];
			                $existEvents[$eventSystemID] = $eventDate;
			             }
			        }
		        }
		       
        }

        $query_importEvents = "SELECT i.access_token,e.authenticator,c.event_date,c.short_name,dc.case_matter,c.case_event_id,e.event_docket,dc.case_id,dc.case_matter,i.triggerItem FROM docket_cases dc
		INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
		INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
		INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
		INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
		WHERE dcu.case_id = '".$caseId."'  ORDER BY c.import_event_id desc limit 2";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);
		
		/*Changes done on 7 Jan 2019 */
		$querygetupdateeventsdescInfo = "SELECT * FROM updateeventsdesc WHERE importdocketid = '".$importDocketId."' and caseid = ".$caseId."";
		$querygetupdateeventsdescInfoData = mysqli_query($docketDataSubscribe,$querygetupdateeventsdescInfo);
		$totalRowsupdateeventsdescInfo = mysqli_num_rows($querygetupdateeventsdescInfoData);
		if($totalRowsupdateeventsdescInfo > 0)
		{
			$row_getupdateeventsdesc = mysqli_fetch_assoc($querygetupdateeventsdescInfoData);
		}
	
        
		   function cmp($a, $b){
		        if ($a == $b)
		            return 0;
		        return ($a['name'] < $b['name']) ? -1 : 1;
		       }
		       $contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp");    
		    
	if(isset($_SESSION['access_token'])) {
		if($_SESSION['CheckAccess']!="NoGmail")
		{
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
    }
		         
?>
<style>
.reviewdata {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    border-collapse:separate;
    border-radius:6px;
    -moz-border-radius:6px;
    border: 1px solid #E5EBEE;
}

.reviewdata td, #reviewdata th {
    border: 1px solid #F6F8F9;
    padding: 8px;
}

.reviewdata tr:hover {background-color: #ddd;}

.reviewdata th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
.reviewheader
{
	background-color: #b7deed;
	color:#1e5799;
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
</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<span id="ajax_result"></span>
<div id="review_main_content">
   <table align="right">
        <tr>
            <td>
              <div style="float: right;"><a href="https://tools.docketcalendar.com/date-calculator">Date Calculator</a> | <a href="https://tools.docketcalendar.com/docket-calculator">Docket Calculator</a> | <a href="https://tools.docketcalendar.com/docket-research">Docket Research</a></div>
            </td>  
        </tr>
    </table>
	<table class="reviewdata">
			<tr>
					<td class="reviewheader"> Jurisdiction </td>
					<td id="jurisdictionValtd"></td>
			</tr>	
			<tr>
					<td class="reviewheader"> Trigger Item </td>
					<td id="triggerValtd"></td>
			</tr>
			<tr>
					<td class="reviewheader"> Trigger Date </td>
					<td> <?php  echo date("dS M Y",strtotime($triggerDate)); ?> </td>
			</tr>
			<tr>
					<td class="reviewheader"> Trigger Time </td>
					<td> <?php echo $triggerTime .' '. $triggerMeridiem ; ?> </td>
			</tr>
			<?php if($triggerServiceType) { ?>	
			<tr>
					<td class="reviewheader"> Service Type </td>
					<td> <?php echo $triggerServiceType; ?> </td>
			</tr>
			<?php } ?>	
			<tr>
					<td class="reviewheader"> Case Name </td>
					<td> <?php echo $caseMatter; ?> </td>
			</tr>
			<?php if($triggerLocation) { ?>	
			<tr>
					<td class="reviewheader"> Location </td>
					<td> <?php echo $triggerLocation; ?> </td>
			</tr>	
			<?php } if($triggerCustomText) { ?>	
			<tr>
					<td class="reviewheader"> Custom Text </td>
					<td> <?php echo $triggerCustomText; ?> </td>
			</tr>
			<?php } ?>		
			<tr>
					<td class="reviewheader"> Calendar </td>
					<td> <?php 
					
					foreach ($calendarData as $calendar_list) {
						 $calendar_id = $calendar_list['id'];
	              		 $calendar_summary = $calendar_list['summary'];
						
						if($triggerCalendarId == "primary"){
				                 $calendarSummary = "Primary Calendar";
				              }else if($triggerCalendarId == $calendar_id) {
				                 $calendarID = $calendar_id;
				                 $calendarSummary = $calendar_summary;          
				              }
					}
					echo $calendarSummary; ?> </td>
			</tr>	
			<tr>
					<td class="reviewheader"> Attendees </td>
					<td> <?php 
					foreach($contact_list as $contact) { if(in_array($contact['email'],$attendee)) {  echo $contact['name'].","; }} ?> </td>
			</tr>	
			<tr>
					<td colspan="2" class="reviewheader"> Event(s) </td>
			</tr>		
	</table>
<?php
	$result = '';
	if($displayVal == 2)
	{	

		$result .= '<table class="reviewdata">';
		
		$query_importeventsInfo = "SELECT * FROM import_events WHERE import_docket_id = '".$importDocketId."' ";
		$importeventsInfo = mysqli_query($docketDataSubscribe,$query_importeventsInfo);
		
			while($row_importeventsInfo = mysqli_fetch_assoc($importeventsInfo))
			{
			
				$eventId = $row_importeventsInfo['import_event_id'];
					
				$query_caseeventInfo = "SELECT * FROM case_events WHERE import_event_id = '".$eventId."' ";
				$caseeventInfo = mysqli_query($docketDataSubscribe,$query_caseeventInfo);
				while($row_caseeventsInfo = mysqli_fetch_assoc($caseeventInfo))
				{
					$tt =  explode('-',$row_caseeventsInfo['event_date']);

					$result .= '<tr><td>'.substr($tt[2],0,2).' '.$montharray[$tt[1]].' '.$tt[0].' - '.$row_caseeventsInfo['short_name'].'</td></tr>';
				}
				
			}
		$result .= '</table>';	
		
	}
	else if($displayVal == 1)
	{
		$x = 0;
			$single = $response;
			$sort = 1;

			if (isset($single['Action'])) {
			    $numresults = 1;
			} else {
			    if ($sort == 2) {
			        function cust_sort($a, $b) {
			            return strtolower($a['CalendarRuleEvent']['EventDate']) < strtolower($b['CalendarRuleEvent']['EventDate']);
			        }
			        usort($response, 'cust_sort');
			        $numresults = sizeof($response);
			    } else if ($sort == 1) {
			        function cust_sort($a, $b) {
			            return strtolower($a['CalendarRuleEvent']['EventDate']) > strtolower($b['CalendarRuleEvent']['EventDate']);
			        }
			        usort($response, 'cust_sort');
			        $numresults = sizeof($response);
			    }
			}
		$result_html['count'] = $numresults;
			
		if ($numresults > 0) 
		{
			$result .= '
			<table class="reviewdata">';
			$eventResultsArray = array();
			$alreadyExistMessage = 0;

			if (isset($single['Action'])) {
				$selected = '';
				$justdate = substr($response['CalendarRuleEvent']['EventDate'], 0, 10);
				$mo = substr($justdate, 5, 2);

				$sysID = $response['CalendarRuleEvent']['SystemID'];
				$alreadyExist = 0;
				$explode_event_date = explode("T",$response['CalendarRuleEvent']['EventDate']);
				$event_specific_time = '';
				if($explode_event_date[1] != "00:00:00")
				{
				   $event_specific_time =  $explode_event_date[1];
				}
				if (array_key_exists($sysID,$existEvents))
				{
				  $eveDate = substr($justdate, 0, 4).'-'.substr($justdate, 5, 2).'-'.substr($justdate, 8, 2);
				  if($existEvents[$sysID] == $eveDate)
				  {
					$alreadyExist = 1;
					$alreadyExistMessage = 1;
				  }
				}

				if(@$_POST['import_docket_id'] != '')
				{
				   $selected = " checked='checked' ";
				}
				
				$style = '';
				$result .= '
				<tr><td>
					'. $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';
				if($totalRowsupdateeventsdescInfo > 0)
				{
					$result .= $row_getupdateeventsdesc['eventdesc'];
				}
				else{
					$result .= $response['CalendarRuleEvent']['ShortName'];
				}
				
				$result .='<br>';
				if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
					$result .= $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
				} else {
					foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
						$result .= $Rule['RuleText'];
					}
				}
				$result .='</br>';
				$result .= '</td></tr>';
				$eventParentSystemID = $response['CalendarRuleEvent']['ParentSystemID'];
				$eventSystemID = $response['CalendarRuleEvent']['SystemID'];
				$eventResultsArray[] = $eventSystemID;
			} else {
				// IF THERE ARE MULTIPLE EVENTS
				$eve = 1;
				$selected_child = '';

				$selectedEvents = 0;
				foreach ($response as $Event) {
				  $sysID = $Event['CalendarRuleEvent']['SystemID'];
				  $queryForMultipleUpdateEvents="SELECT eventdesc,eventid FROM updateeventsdesc WHERE importdocketid=".$_POST['docket_search_id']." AND caseid = ".$row_searchInfo['case_id']." AND eventid = '".$sysID."'";
					$resultForMultiple = mysqli_query($docketDataSubscribe,$queryForMultipleUpdateEvents);
					$totalRowsupdateeventsdescMultipleInfo=mysqli_num_rows($resultForMultiple);
					if($totalRowsupdateeventsdescMultipleInfo > 0)
					{
						$row_ForMultiple = mysqli_fetch_assoc($resultForMultiple);
					}
				  $alreadyExist = 0;
				   if(in_array($sysID,$events_array))
				  {
					$justdate = substr($Event['CalendarRuleEvent']['EventDate'], 0, 10);
					$mo = substr($justdate, 5, 2);
					  $explode_event_date = explode("T",$Event['CalendarRuleEvent']['EventDate']);
					$event_specific_time = '';
					if($explode_event_date[1] != "00:00:00")
					{
					   $event_specific_time =  $explode_event_date[1];
					}
					if (array_key_exists($sysID,$existEvents))
				   {
					  $eveDate = substr($justdate, 0, 4).'-'.substr($justdate, 5, 2).'-'.substr($justdate, 8, 2);
					  if($existEvents[$sysID] == $eveDate)
					  {
						 $alreadyExist = 1;
						 $alreadyExistMessage = 1;
					  }
				   }

					if(@$_POST['import_docket_id'] != '' && $events != '') {
						$events_array = unserialize($events);
						if(in_array($eve,$events_array)) {
						  $selected_child = " checked='checked' ";
						} else {
						  $selected_child = "";
						}
					}
					$sysID = $Event['CalendarRuleEvent']['SystemID'];
					$parentID = $Event['CalendarRuleEvent']['ParentSystemID'];
					$evenRecalc = $Event['CalendarRuleEvent']['DoNotRecaltulateFlag'];
					$style = '';
					
					$result .= '<tr><td>' . $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';
					if($totalRowsupdateeventsdescMultipleInfo > 0)
					{
						if($sysID == $row_ForMultiple['eventid'])
						{
							$result .= $row_ForMultiple['eventdesc'];
						}
						else{
							$result .= $Event['CalendarRuleEvent']['ShortName'];
						}
					}
					else{
						$result .= $Event['CalendarRuleEvent']['ShortName'];
						}
					
					$result .='<br>';
					if (isset($Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
						$result .= $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
					} else {
						foreach ($Event['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
							$result .= $Rule['RuleText'];
						}
					}
					$result .='<br>';
					$result .= '</td></tr>';
					$eventDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
					$eventParentSystemID = $Event['CalendarRuleEvent']['ParentSystemID'];
					$eventSystemID = $Event['CalendarRuleEvent']['SystemID'];
					$eventResultsArray[] = $eventSystemID;
					$eve++;
					$selectedEvents++;
				  }
				}    
			}
			$result .= '</table>';
		}
	}	
		 	
    echo $result;?>
</div>
<script src="jquery/js/jquery-1.8.3.js"></script>	
 <script>
    $( document ).ready(function() {
		$(".overlay").show();
	 setTimeout(function() {
						   $(".overlay").hide();
					   }, 5000);
    	$("#review_main_content").hide();
    	var jurisdicationVal = <?php echo $triggerJurisdiction;?>;
    	var triggerItemVal = <?php echo $triggerItem;?>;
  
         jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
            type: "post",
            data: {"jurisdicationVal":jurisdicationVal,"triggerVal":triggerItemVal},
            dataType: "json",
            success: function (response) {
               jQuery("#jurisdictionValtd").html(response.jurisdictionResultVal);
               jQuery("#triggerValtd").html(response.tiggersResultVal);
			   
               jQuery("#review_main_content").show(1000);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
          });
    });
    </script>	
<?php 
}
		//unset($_SESSION['JurisdictionData']);
		//unset($_SESSION['caseId']);
genesis();
?>
