<?php
/*
Template Name: Import Calendar New
*/
//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
?>
 <style>
  .form1 {
  display: grid;
  padding: 1em;
  background: #f9f9f9;
  border: 1px solid #c1c1c1;
  max-width: 650px;
  padding: 1em;
  margin-top: 30px;
}


.form1 label {
  padding: 0.5em 0.5em 0.5em 0;
}

.form1 input {
  padding: 0.7em;
  margin-bottom: 0.5rem;
}
.form1 input:focus {
  outline: 3px solid gold;
}

@media (min-width: 400px) {
  .form1 {
    grid-template-columns: 200px 1fr;
    grid-gap: 16px;
  }

  .form1 label {
    text-align: right;
    grid-column: 1 / 2;
  }

  input,
  button {
    grid-column: 2 / 3;
  }
}
.dynnamicButton
{
	margin-left:5px;
}
.dynnamicText
{
	margin-right:5px;
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

.clsFnt{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
}
.triggers
{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
}
.court_rule a{
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size:5px;
}
#showCalendarData {
  display: none;
}
</style>
<?php
function custom_loop() {
require_once('Connections/docketDataSubscribe.php');

	session_start();
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
    global $attendee;
    global $response;
    global $events_array;
    global $case_name;
	global $case_id;
    global $dbCalendarId;
    global $existEvents;
	global $triggerName;
	

    $result_html = array();

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
$querygetCalendarId = "SELECT calendar_id,caseEventColor FROM docket_cases dc WHERE  dc.case_id = ".$case_id."";
$dataCalendarId = mysqli_query($docketDataSubscribe,$querygetCalendarId);
$row_events = mysqli_fetch_assoc($dataCalendarId);
$dbCalendarId  = $row_events['calendar_id'];
$caseColor = $row_events['caseEventColor'];
foreach($calendarData as $calendarArr){
    $arryCalendarid[] = $calendarArr['id'];
}

	if($dbCalendarId!='primary')
	{
		
		if(!in_array($dbCalendarId,$arryCalendarid))
		{
			echo '<script type="text/javascript">'; 
			echo 'alert('.$case_name.' is assigned to different Calendar which is not shared with you )';
			echo 'window.location = "https://tools.docketcalendar.com/docket-calculator";';
			echo '</script>';
		}
	}
$result_html['count'] = $numresults;
    ?>
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/fastselect.css">
<link rel = "stylesheet" type = "text/css"    href = "https://tools.docketcalendar.com/jquery/css/standalone.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<div id="showCalendarData">
<?php
	$queryimportAllData="SELECT * FROM import_docket_calculator WHERE import_docket_id =".$_SESSION['docket_search_id']."";
	$resultimportAllData = mysqli_query($docketDataSubscribe,$queryimportAllData);
	$importAllData = mysqli_fetch_assoc($resultimportAllData);
	$jurisdiction = $importAllData['jurisdiction'];
	$trigger = $importAllData['trigger_item'];
	$serviceType = $importAllData['serviceType'];
	$meridiem = $importAllData['meridiem'];
	
	if($serviceType == "")
	{
		$serviceType="";
		$serviceTypeVal = 0;
	}else{
		$serviceTypeVal = 1;
	}
	
	$txtTriggerDate_val = $importAllData['trigger_date'];
	$txtTime_val = $importAllData['trigger_time'];
	$caseId = $importAllData['case_id'];
	$isTimeRequired_val = $importAllData['isTimeRequired'];
	$sort_date_val= $importAllData['sort_date'];
	$isServed_val = $importAllData['isServed'];
	$TriggersText_val = $importAllData['triggerItem'];
if ($numresults > 0) {

    $result .= '<div><span class="clsFnt">Selected Case : <b>'.$case_name.'</b></span></div><div><span class="clsFnt">Selected Trigger : <b>'.$triggerName.'</b></span></div>
	<div style="float:right;"><a style="padding-right:15px;" href="javascript:void(0);" onclick="javascript:Exlexport();">Excel</a><a style="padding-right:15px;" href="javascript:void(0);" onclick="javascript:CSVexport();">CSV</a><a style="padding-right:15px;" href="javascript:void(0);" onclick="javascript:Icalexport();">ICal</a><a style="padding-right:15px;" href="javascript:void(0);" onclick="javascript:Outlookexport();">Outlook</a><input type="button" style="width: 50px; margin-right:50px;" onclick="goBack()" value="Back"/></div>';

    $result .= '<div id="show_results_list"><span class="clsFnt">Selected events details as mentioned below :</span><br><div>';

    $result .= '
  <table class="triggers">';
    $eventResultsArray = array();
    $alreadyExistMessage = 0;

    if (isset($single['Action'])) {
        $selected = '';
        $result .= '<tr><td valign="top"><div style="padding-top:5px;">';

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
        if($alreadyExist == 1) { $style = "color:red;"; }
        $result .= '<input type="checkbox" checked="checked" class="evenetClass" name="events[]" '.$selected.' value="'.$sysID.'" style="float: left;margin: 5px -11px;"/><ul class="triggersnav"  style="padding: 0 0 0 15px !important;font-size: 15px;'.$style.'">
        <li>
            '. $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';

        if ($_POST['cmbMatter'] != "") {
            //$result .= '(' . $_POST["cmbMatter"] . ') ';
        }

        $result .="<span id='singleEventText'>".$response['CalendarRuleEvent']['ShortName']."</span>"."  "."<i id='changeTextSingle' onclick='singleTextEventChange();'  class='material-icons' style='font-size:15px;cursor:pointer;color:red'>border_color</i>";
		/*
        if (isset($response[CalendarRuleEvent][DateRules][Rule][RuleText])) {
            $result .= $response[CalendarRuleEvent][DateRules][Rule][RuleText];
        } else {
            foreach ($response[CalendarRuleEvent][DateRules][Rule] as $Rule) {
                $result .= $Rule[RuleText];
            }
        }*/
		$result .= '</a><div id="changeTextDivSingle"></div>';
		if($response['CalendarRuleEvent']['EventType']['Description'])
		{
			$result .= '<ul class="triggersnav"><li><a href="javascript:void(0);" style="font-size:12px;"><span class="court_rule" ><b>Event Type:</b></span> ' . $response['CalendarRuleEvent']['EventType']['Description']. '</li></a></ul>';
		}
			if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
				// IF THERE IS ONE RULE
				$result .= '<ul class="triggersnav"><li><a href="javascript:void(0);"><span class="court_rule"><b>Date Rule:</b></span> ' . $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText']. '</li></a></ul>';

				?>  <?php
                } else {

				// IF THERE ARE MANY RULES
				    foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
					$result .= '<ul class="triggersnav">
                                <li>
                                <a href="javascript:void(0);"><span class="court_rule"><b>Date Rule:</b></span> ' . $Rule['RuleText']. '</li></a></ul>';
					?>  <?php
                    }
			   }

      	if (isset($response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
			// IF THERE IS ONE RULE
			$result .= '<ul class="triggersnav" style="display:block !important;">
                    <li>
                        <a href="javascript:void(0);"><span class="court_rule"><b>Court Rule:</b></span> ' . $response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'];

		} else {

			// IF THERE ARE MANY RULES
			foreach ($response['CalendarRuleEvent']['CourtRules']['Rule'] as $rule) {
				$result .= '<ul class="triggersnav" style="display:block !important;">
                    <li>
                        <a href="javascript:void(0);"><span class="court_rule"><b>Court Rule:</b></span> ' . $rule['RuleText'] . '</ul>';

			}
		}
		$result .= '</ul></ul>';
        $eventParentSystemID = $response['CalendarRuleEvent']['ParentSystemID'];
        $eventSystemID = $response['CalendarRuleEvent']['SystemID'];
        $eventResultsArray[] = $eventSystemID;
        $result .='<br>';
		$result .='<br><br>';
        $result .='<div style="margin-top:15px;font-size: 15px;"><b>Total Event : '.$numresults.'</b></div>';
        if($alreadyExistMessage == 1)
        {
          $result .='<div style="font-size: 13px;color:red;margin-top:5px;">Selected event has already exist in this case with same date, do you want to continue?</div>';
        }
    } else {

        // IF THERE ARE MULTIPLE EVENTS

        $result .= '<tr><td valign="top"><div  style="padding-top:5px;">';
        $eve = 1;
        $selected_child = '';

        $selectedEvents = 0;
        foreach ($response as $Event) {
          $sysID = $Event['CalendarRuleEvent']['SystemID'];
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
            $style = '';
            if($alreadyExist == 1) { $style = "style=color:red;"; }
           	$result .= '<ul class="triggersnav" style="padding: 0 0 0 15px !important;">
                       <li><input type="checkbox" checked="checked"  onclick="checkResult('.$sysID.',this)" id='.$sysID.'_'.$parentID.' data='.$parentID.' class="evenetClass" name="events[]" '.$selected_child.' value="'.$sysID.'" style="float: left;margin: 5px -17px;"/><a href="javascript:void(0);" '.$style.'>' . $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4)  .' '.$event_specific_time.  ' - ';

			                    if ($_POST['cmbMatter'] != "") {
				                   // $result .= '(' . $_POST['cmbMatter'] . ') ';
			                    }

			                    $result .= "<span id='eventText".$sysID."'>".$Event['CalendarRuleEvent']['ShortName']."</span>"." "."<i id='changeTextMultiple".$sysID."' class='material-icons' onclick='callMultiText(".$sysID.")'								style='font-size:15px;cursor:pointer;color:red'>border_color</i>";
								/*
                                if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
                                    $result .= $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
                                } else {
                                    foreach ($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
                                        $result .= $Rule[RuleText];
                                    }
                                }
								*/

					$result .= '</a><span id='.$sysID.'></span>';
					if($Event['CalendarRuleEvent']['EventType']['Description'])
					{
						$result .= '<ul class="triggersnav"><li><a href="javascript:void(0);" style="font-size:12px;"><span class="court_rule" ><b>Event Type:</b></span> ' . $Event['CalendarRuleEvent']['EventType']['Description']. '</li></a></ul>';
						}
					if (isset($Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
					// IF THERE IS ONE RULE
					$result .= '<ul class="triggersnav"><li><a href="javascript:void(0);"><span class="" style="font-size:12px;"><b>Date Rule:</b> ' . $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'] . '</span></li></a></ul>';

					?>  <?php
					} else {

					// IF THERE ARE MANY RULES
					foreach ($Event['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
					$result .= '<ul class="triggersnav">
								<li>
								<a href="javascript:void(0);"><span class="" style="font-size:12px;"><b>Date Rule:</b> ' . $Rule['RuleText'] . '</span></li></a></ul>';
					?>  <?php
					}
			   }
			if (isset($Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
				// IF THERE IS ONE RULE
				$result .= '<ul class="triggersnav"><li><a style="font-size:12px;" href="javascript:void(0);"><span class="court_rule"><b>Court Rule:</b></span> ' . $Event['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'] . '</li></a>';

				?>  <?php
                } else {

				// IF THERE ARE MANY RULES
				    foreach ($Event['CalendarRuleEvent']['CourtRules']['Rule'] as $rule) {
					$result .= '<ul class="triggersnav">
                                <li>
                                <a style="font-size:12px;" href="javascript:void(0);"><span class="court_rule"><b>Court Rule:</b></span> ' . $rule['RuleText'] . '</li></a></ul>';
					?>  <?php
                    }
			   }
			$result .= '</ul></ul><span id="multiText'.$sysID.'"></span>';
            $eventDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
            $eventParentSystemID = $Event['CalendarRuleEvent']['ParentSystemID'];
            $eventSystemID = $Event['CalendarRuleEvent']['SystemID'];
            $eventResultsArray[] = $eventSystemID;
            $eve++;
            $selectedEvents++;
          }
        }  //for loop end
        //echo "<pre>"; print_r($eventResultsArray);
        $result .= '</div></div></td></tr>';
        $result .='<tr><td><div style="margin-top:15px;font-size: 15px;"><b>Total Events : '.$selectedEvents.'</b></div></td></tr>';
        if($alreadyExistMessage == 1)
        {
          $result .='<tr><td><div style="font-size: 13px;color:red;margin-top:5px;">Selected event has already exist in this case with same date, do you want to continue?</div></td></tr>';
        }
    }

    $result .= '</table></div>';
}
    echo $result;

?>

        <form class="form1">
            <label for="firstName" class="first-name">Calendars</label>
        <?php

        if(isset($calendarData))
        {   ?>
        <select type="select" name="calendar_id" id="calendar_id">
		<option value="0">---Select Calendar---</option>
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
       <?php
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
			
			$tempArr = array_unique(array_column($contact_list, 'email'));
	$contact_list = (array_intersect_key($contact_list, $tempArr));
		?>
        <label for="lastName" class="last-name">Add Attendees</label>
        <?php if(isset($_SESSION['google_contacts'])) { ?>
        <select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]" style="height:200px;">
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
					 <label for="firstName" class="first-name">Event Color</label>
			<select name="caseLevelImporteventColor" id="caseLevelImporteventColor" style="width:410px">
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
        <br>
        <?php } else { ?>
        <textarea style="height:100px;width:200px;" name="attendees" id="attendees"></textarea>
        <br>
        <span style="font-size:12px;color:#1C528E;">Add Multiple Attendees Valid GMail Address using by comma separator. Example: xyz@gmail.com,abced@gmail.com</span>
        <?php } ?>
        <input name="docket_search_id" type="hidden" id="docket_search_id" value="<?php echo $_SESSION['docket_search_id']; ?>">
		<input name="input_hidden_appointment_length" type="hidden" id="input_hidden_appointment_length" value="<?php if(isset($_SESSION['hiddenAppointmentLength'])) { echo $_SESSION['hiddenAppointmentLength'];} ?>">
		<div>
			<div style="float:left">
				<input id="btnImport" name="btnImport" style="width: 190px;" type="button" value="Save to Calendar"/>
			</div>
			<div style="float:right;">
				<input type="button" style="width: 50px; margin-right:50px;" onclick="goBack()" value="Back"/>
			</div>
		</div>
		&nbsp;<span id="ajax_result"></span>
        </form>
</div>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div style="display:none"> 
<form id="exportToExcelForm" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_result_export.php">
<input type="hidden" id="hidden_cmbJurisdictions_val" name="hidden_cmbJurisdictions_val">
<input type="hidden" id="hidden_JurisdictionsText_val" name="hidden_JurisdictionsText_val">
<input type="hidden" id="hidden_cmbTriggers_val" name="hidden_cmbTriggers_val">
<input type="hidden" id="hidden_TriggersText_val" name="hidden_TriggersText_val">
<input type="hidden" id="hidden_selectServiceType_val" name="hidden_selectServiceType_val">
<input type="hidden" id="hidden_selectServiceText_val" name="hidden_selectServiceText_val">
<input type="hidden" id="hidden_txtTriggerDate_val" name="hidden_txtTriggerDate_val">
<input type="hidden" id="hidden_txtTime_val" name="hidden_txtTime_val">
<input type="hidden" id="hidden_cmbMatter_val" name="hidden_cmbMatter_val">
<input type="hidden" id="hidden_cmbMatterText_val" name="hidden_cmbMatterText_val">
<input type="hidden" id="hidden_isTimeRequired_val" name="hidden_isTimeRequired_val">
<input type="hidden" id="hidden_sort_date_val" name="hidden_sort_date_val">
<input type="hidden" id="hidden_isServed_val" name="hidden_isServed_val">
<input type="hidden" id="hidden_eventarray_val" name="hidden_eventarray_val">
<input type="hidden" id="hidden_eventSpecificTime" name="hidden_eventSpecificTime">
<!-- Set Excel,iCal,Outlook Export -->
<input type="hidden" id="hidden_excelData" name="hidden_excelData">
<input type="hidden" id="hidden_iCalData" name="hidden_iCalData">
<input type="hidden" id="hidden_outllookData" name="hidden_outllookData">
<input type="hidden" id="hidden_csvData" name="hidden_csvData">
</form>
</div>		
<script type="text/javascript">		
jQuery(window).load(function() {
	// When the page has loaded
	jQuery("#showCalendarData").fadeIn(2500);
	jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
});
 function checkAll(ele) {
     var checkboxes = document.getElementsByTagName("input");
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
 function checkResult(val, parentElem)
 {
    var queryString = "input[data=\'"+val+"\']";
    var parent = document.querySelectorAll(queryString);
    parent.forEach(function(inputElem){
        inputElem.checked = parentElem.checked
    });
 }
 jQuery('.multipleSelect').fastselect();

 jQuery("#btnImport").click(function(){
        jQuery(".overlay").show();

        var array = []
        var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')

        for (var i = 0; i < checkboxes.length; i++) {
            array.push(checkboxes[i].value)
        }
        var docket_search_id =  jQuery("#docket_search_id").val();
        var attendees =  jQuery("#attendees").val();
        var calendar_id =  jQuery("#calendar_id").val();
		var hidden_appointment_length = jQuery("#input_hidden_appointment_length").val();
		var caseLevelImporteventColor = jQuery("#caseLevelImporteventColor").val();
		var meridiem = '<?php echo $meridiem; ?>';
		 if(calendar_id == 0)
		 {
			// alert("Please select Calendar");
			jQuery(".overlay").hide();
			 $.notify("Please select Calendar", {
			  type:"danger",
			  align:"center", 
			  verticalAlign:"middle",
			  animation:true,
			  animationType:"scale",
			  icon:"bell",
			  delay:2500,
			  blur: 0.8,
			 close: true,
			  buttonAlign: "center",
			});
			 return false;
		 }
	
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_import.php",
                type: "post",
                dataType: "json",
                data: { "docket_search_id":docket_search_id,"attendees":attendees,"calendar_id":calendar_id,"events":array,"hidden_appointment_length":hidden_appointment_length,"caseLevelImporteventColor":caseLevelImporteventColor,"meridiem":meridiem  },
                success: function (response) {
                   console.log(response);
				   jQuery(".overlay").hide();
					mySucess();
                   //jQuery("#ajax_result").show();
                   //jQuery("#ajax_result").html(response.html);
                   setTimeout(function() {
					     jQuery(".overlay").show();
                         window.location.href = "<?php echo get_home_url(); ?>/uservalidate";
                    }, 3000);
                },
                error: function(jqXHR, textStatus, errorThrown) {

					  console.log(textStatus, errorThrown);
					   jQuery(".overlay").hide();
					  mySucess();
					setTimeout(function() {
                         window.location.href = "<?php echo get_home_url(); ?>/uservalidate";
                    }, 3000);
                },
				 timeout:5000 //3 second timeout
         });

      });
	function goBack() 
	{
			jQuery(".overlay").show();
		    jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_delete_last_importdata.php",
                type: "post",
                success: function (response) {
                   console.log(response);
				   jQuery(".overlay").hide();
				   window.history.back();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
				   jQuery(".overlay").hide();	
                }
				
         });
   
	}
	function singleTextEventChange() {
		jQuery("#changeTextSingle").remove();
		jQuery("#editIcon1").remove();
		 changeTextDivSingle.append(createSingleTextField());
		 changeTextDivSingle.append(createSingleSaveButton());
		 changeTextDivSingle.append(createSingleCopyButton());
		 changeTextDivSingle.append(createSingleCancelButton());
		 changeTextDivSingle.append(createSigleDropDownList());
		 changeTextDivSingle.append(createSigleReminderDropDown());
		 changeTextDivSingle.append(createSiglePopupReminderDropDown());
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_retrivetextchange.php",
					type: "post",
					dataType: "json",
					data:{"caseid":caseid,"docket_search_id":docket_search_id},
					success: function (response) {
						if(response!=null)					
						{
							jQuery("#singleDiveData").val(response.eventdesc);
							jQuery("#singleEventColorVal").val(response.eventColor);
							jQuery("#singleReminderDropDown").val(response.eventreminderval);
							jQuery("#siglePopupReminderDropDown").val(response.eventpopupreminderval);
						}
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
						jQuery(".overlay").hide();	
				}	
			});	
		 
	}

	function cancelEventSingleText() {
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_retrivetextchange.php",
					type: "post",
					dataType: "json",
					data:{"caseid":caseid,"docket_search_id":docket_search_id},
					success: function (response) {
						console.log(response);
						//jQuery("#singleDiveData").val(response.html);
						if(response!== null)
							{	
								jQuery("#singleEventText").append("<i onclick='singleTextEventChange();' id='changeTextSingle' class='material-icons' style='font-size:15px;cursor:pointer;color:red'>border_color</i><i id='editIcon1'>(Edited)</i>");
							}else{
							
								jQuery("#singleEventText").append("<i onclick='singleTextEventChange();' id='changeTextSingle' class='material-icons' style='font-size:15px;cursor:pointer;color:red'>border_color</i>");	
							}	
							
						jQuery("#singleDiveData").remove();
						jQuery("#singleTextSave").remove();
						jQuery("#singleTextCopy").remove();
						jQuery("#singleTextCancel").remove();
						jQuery("#singleEventColorVal").remove();
						jQuery("#singleReminderDropDown").remove();
						jQuery("#siglePopupReminderDropDown").remove();
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
						jQuery(".overlay").hide();	
				}	
			});	
	
	}
	
	function createSingleTextField() {
	  var input = document.createElement('input');
	  input.type = 'text';
	  input.size = '85';
	  input.name = 'singleDiveData';
	  input.id = 'singleDiveData';
	  input.setAttribute("class", "dynnamicText");
	  return input;
    }
	function createSingleSaveButton() {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Save';
	  input.id = 'singleTextSave';
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "saveEventSingleText()");
	  return input;
    }
	function createSingleCopyButton() {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Copy';
	  input.id = 'singleTextCopy';
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "copyEventSingleText()");
	  return input;
    }
	function createSingleCancelButton() {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Cancel';
	  input.id = 'singleTextCancel';
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "cancelEventSingleText()");
	  return input;
    }
	function createSigleDropDownList()
	{
	  var input = document.createElement('select');
	  input.id = 'singleEventColorVal';
	  input.name = 'singleEventColorVal';
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("No Color","0"));
	  input.options.add( new Option("Tomato","11"));
	  input.options.add( new Option("Tangerine","6"));
	  input.options.add( new Option("Sage","2"));
	  input.options.add( new Option("Peacock","7"));
	  input.options.add( new Option("Lavender","1"));
	  input.options.add( new Option("Graphite","8"));
	  input.options.add( new Option("Flamingo","4"));
	  input.options.add( new Option("Banana","5"));
	  input.options.add( new Option("Basil","10"));
	  input.options.add( new Option("Blueberry","9"));
	  input.options.add( new Option("Grape","3"));
	  return input;
	}
	function createSigleReminderDropDown()
	{
	  var input = document.createElement('select');
	  input.id = 'singleReminderDropDown';
	  input.name = 'singleReminderDropDown';
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("--Email--",""));
	  input.options.add( new Option("5 Minutes","5"));
	  input.options.add( new Option("10 Minutes","10"));
	  input.options.add( new Option("15 Minutes","15"));
	  input.options.add( new Option("30 Minutes","30"));
	  input.options.add( new Option("1 hour","60"));
	  input.options.add( new Option("2 hour","120"));
	  input.options.add( new Option("3 hour","180"));
	  input.options.add( new Option("4 hour","240"));
	  input.options.add( new Option("5 hour","300"));
	  input.options.add( new Option("6 hour","360"));
	  input.options.add( new Option("7 hour","420"));
	  input.options.add( new Option("8 hour","480"));
	  input.options.add( new Option("9 hour","540")); 
	  input.options.add( new Option("10 hour","600")); 
	  input.options.add( new Option("11 hour","660")); 
	  input.options.add( new Option("0.5 Day","720")); 
	  input.options.add( new Option("18 hours","1080")); 
	  input.options.add( new Option("1 Day","1440")); 
	  input.options.add( new Option("2 Day","2880")); 
	  input.options.add( new Option("3 Day","4320")); 
	  input.options.add( new Option("4 Day","5760")); 
	  input.options.add( new Option("1 Week","10080")); 
	  input.options.add( new Option("2 Week","20160")); 
	  return input;
	}
	function createSiglePopupReminderDropDown()
	{
	  var input = document.createElement('select');
	  input.id = 'siglePopupReminderDropDown';
	  input.name = 'siglePopupReminderDropDown';
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("--PopUp--",""));
	  input.options.add( new Option("5 Minutes","5"));
	  input.options.add( new Option("10 Minutes","10"));
	  input.options.add( new Option("15 Minutes","15"));
	  input.options.add( new Option("30 Minutes","30"));
	  input.options.add( new Option("1 hour","60"));
	  input.options.add( new Option("2 hour","120"));
	  input.options.add( new Option("3 hour","180"));
	  input.options.add( new Option("4 hour","240"));
	  input.options.add( new Option("5 hour","300"));
	  input.options.add( new Option("6 hour","360"));
	  input.options.add( new Option("7 hour","420"));
	  input.options.add( new Option("8 hour","480"));
	  input.options.add( new Option("9 hour","540")); 
	  input.options.add( new Option("10 hour","600")); 
	  input.options.add( new Option("11 hour","660")); 
	  input.options.add( new Option("0.5 Day","720")); 
	  input.options.add( new Option("18 hours","1080")); 
	  input.options.add( new Option("1 Day","1440")); 
	  input.options.add( new Option("2 Day","2880")); 
	  input.options.add( new Option("3 Day","4320")); 
	  input.options.add( new Option("4 Day","5760")); 
	  input.options.add( new Option("1 Week","10080")); 
	  input.options.add( new Option("2 Week","20160")); 
	  return input;
	}
	
	function saveEventSingleText() {
		var changeTextValue = jQuery("#singleDiveData").val();
		var singleEventColorVal = jQuery("#singleEventColorVal").val();
		var singleReminderValue = jQuery("#singleReminderDropDown").val();
		var siglePopupReminderDropDown = jQuery("#siglePopupReminderDropDown").val();
		jQuery(".overlay").show();
		console.log("textval"+changeTextValue);
		console.log("color"+singleEventColorVal);

		if((changeTextValue!="") || (singleEventColorVal!=0))
		{
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
			if(changeTextValue == "")
			{
				jQuery("#singleDiveData").val(jQuery("#singleEventText").html());
				changeTextValue = jQuery("#singleDiveData").val();
			}
				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatetextchange.php",
					type: "post",
					dataType: "json",
					data:{"changeTextValue":changeTextValue,"caseid":caseid,"docket_search_id":docket_search_id,"singleEventColorVal":singleEventColorVal,"singleReminderValue":singleReminderValue,"popupReminderDropDown":siglePopupReminderDropDown},
					success: function (response) {
						console.log(response);
						//alert("Changes Saved");
						myChangeSaved();
						jQuery(".overlay").hide();
						jQuery("#singleDiveData").remove();
						jQuery("#singleTextSave").remove();
						jQuery("#singleTextCopy").remove();
						jQuery("#singleTextCancel").remove();
						jQuery("#singleEventColorVal").remove();
						jQuery("#singleReminderDropDown").remove();
						jQuery("#siglePopupReminderDropDown").remove();
						jQuery("#singleEventText").append("<i onclick='singleTextEventChange();' id='changeTextSingle' class='material-icons' style='font-size:15px;cursor:pointer;color:red'>border_color</i><i id='editIcon1'>(Edited)</i>");
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
						jQuery(".overlay").hide();	
				}	
			});	
		}else{
			
			 $.notify("Please provide event name", {
			  type:"danger",
			  align:"center", 
			  verticalAlign:"middle",
			  animation:true,
			  animationType:"scale",
			  icon:"bell",
			  delay:2500,
			  blur: 0.8,
			 close: true,
			  buttonAlign: "center",
			});
			jQuery(".overlay").hide();
			
		}
		
	}
	
	function copyEventSingleText()
	{
		jQuery("#singleDiveData").val(jQuery("#singleEventText").html());
	}
	function callMultiText(systemId)
	{
		jQuery("#changeTextMultiple"+systemId).remove();
		jQuery("#editIcon"+systemId).remove();
		jQuery("#multiText"+systemId).append(createMultipleTextField(systemId));
		jQuery("#multiText"+systemId).append(createMultipleSaveButton(systemId));
		jQuery("#multiText"+systemId).append(createMultipleCopyButton(systemId));
		jQuery("#multiText"+systemId).append(createMultipleCancelButton(systemId));
		jQuery("#multiText"+systemId).append(createMultipleDropDownList(systemId));
		jQuery("#multiText"+systemId).append(createMultipleEmailReminderDropDown(systemId));
		jQuery("#multiText"+systemId).append(createMultiplePopupReminderDropDown(systemId));
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_retrivetextchange.php",
					type: "post",
					dataType: "json",
					data:{"systemId":systemId,"caseid":caseid,"docket_search_id":docket_search_id},
					success: function (response) {
						console.log(response);
						if(response!== null)
						{
							jQuery("#multipleTextSave"+systemId).val(response.eventdesc);
							jQuery("#multipleEventColorVal"+systemId).val(response.eventColor);
							jQuery("#multipleEmailReminder"+systemId).val(response.eventreminderval);
							jQuery("#multiplePopupReminder"+systemId).val(response.eventpopupreminderval);
						}
						
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
						
				}	
			});	
		 
	}
	function createMultipleTextField(systemId)
	{
		  var input = document.createElement('input');
		  input.type = 'text';
		  input.size = '85';
		  input.name = 'nextInput';
		  input.setAttribute("id", "multipleTextSave"+systemId+"");
		  input.setAttribute("class", "dynnamicText");
		  return input;
	}
	function createMultipleSaveButton(systemId) {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Save';
	  input.id = 'multiTextSave'+systemId;
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "saveMultiEventText("+systemId+")");
	  return input;
    }
	function createMultipleCopyButton(systemId) {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Copy';
	  input.id = 'multiTextCopy'+systemId;
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "copyEventText("+systemId+")");
	  return input;
    }
	function createMultipleCancelButton(systemId) {
	  var input = document.createElement('input');
	  input.type = 'button';
	  input.value = 'Cancel';
	  input.id = 'multipleTextCopy'+systemId;;
	  input.setAttribute("class", "dynnamicButton");
	  input.setAttribute("onclick", "cancelEventText("+systemId+")");
	  return input;
    }
		function createMultipleDropDownList(systemId)
	{
	  var input = document.createElement('select');
	  input.id = 'multipleEventColorVal'+systemId;
	  input.name = 'multipleEventColorVal'+systemId;
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("No Color","0"));
	  input.options.add( new Option("Tomato","11"));
	  input.options.add( new Option("Tangerine","6"));
	  input.options.add( new Option("Sage","2"));
	  input.options.add( new Option("Peacock","7"));
	  input.options.add( new Option("Lavender","1"));
	  input.options.add( new Option("Graphite","8"));
	  input.options.add( new Option("Flamingo","4"));
	  input.options.add( new Option("Banana","5"));
	  input.options.add( new Option("Basil","10"));
	  input.options.add( new Option("Blueberry","9"));
	  input.options.add( new Option("Grape","3"));
	  return input;
	}
	function createMultipleEmailReminderDropDown(systemId)
	{
	  var input = document.createElement('select');
	  input.id = 'multipleEmailReminder'+systemId;
	  input.name = 'multipleEmailReminder'+systemId;
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("--Email--",""));
	  input.options.add( new Option("5 Minutes","5"));
	  input.options.add( new Option("10 Minutes","10"));
	  input.options.add( new Option("15 Minutes","15"));
	  input.options.add( new Option("30 Minutes","30"));
	  input.options.add( new Option("1 hour","60"));
	  input.options.add( new Option("2 hour","120"));
	  input.options.add( new Option("3 hour","180"));
	  input.options.add( new Option("4 hour","240"));
	  input.options.add( new Option("5 hour","300"));
	  input.options.add( new Option("6 hour","360"));
	  input.options.add( new Option("7 hour","420"));
	  input.options.add( new Option("8 hour","480"));
	  input.options.add( new Option("9 hour","540")); 
	  input.options.add( new Option("10 hour","600")); 
	  input.options.add( new Option("11 hour","660")); 
	  input.options.add( new Option("0.5 Day","720")); 
	  input.options.add( new Option("18 hours","1080")); 
	  input.options.add( new Option("1 Day","1440")); 
	  input.options.add( new Option("2 Day","2880")); 
	  input.options.add( new Option("3 Day","4320")); 
	  input.options.add( new Option("4 Day","5760")); 
	  input.options.add( new Option("1 Week","10080")); 
	  input.options.add( new Option("2 Week","20160")); 
	  return input;
	}
	function createMultiplePopupReminderDropDown(systemId)
	{
	 var input = document.createElement('select');
	  input.id = 'multiplePopupReminder'+systemId;
	  input.name = 'multiplePopupReminder'+systemId;
	  input.setAttribute("class", "dynnamicButton");
	  input.options.add( new Option("--PopUp--",""));
	  input.options.add( new Option("5 Minutes","5"));
	  input.options.add( new Option("10 Minutes","10"));
	  input.options.add( new Option("15 Minutes","15"));
	  input.options.add( new Option("30 Minutes","30"));
	  input.options.add( new Option("1 hour","60"));
	  input.options.add( new Option("2 hour","120"));
	  input.options.add( new Option("3 hour","180"));
	  input.options.add( new Option("4 hour","240"));
	  input.options.add( new Option("5 hour","300"));
	  input.options.add( new Option("6 hour","360"));
	  input.options.add( new Option("7 hour","420"));
	  input.options.add( new Option("8 hour","480"));
	  input.options.add( new Option("9 hour","540")); 
	  input.options.add( new Option("10 hour","600")); 
	  input.options.add( new Option("11 hour","660")); 
	  input.options.add( new Option("0.5 Day","720")); 
	  input.options.add( new Option("18 hours","1080")); 
	  input.options.add( new Option("1 Day","1440")); 
	  input.options.add( new Option("2 Day","2880")); 
	  input.options.add( new Option("3 Day","4320")); 
	  input.options.add( new Option("4 Day","5760")); 
	  input.options.add( new Option("1 Week","10080")); 
	  input.options.add( new Option("2 Week","20160")); 
	  return input;
	}
	function copyEventText(sysId)
	{	
		jQuery("#multipleTextSave"+sysId).val(jQuery("#eventText"+sysId).html());
	}
	function cancelEventText(sysId)
	{
			jQuery(".overlay").show();
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
				jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_retrivetextchange.php",
					type: "post",
					dataType: "json",
					data:{"systemId":sysId,"caseid":caseid,"docket_search_id":docket_search_id},
					success: function (response) {
						console.log(response);
						jQuery(".overlay").hide();
						if(response!== null)
						{
							jQuery("#eventText"+sysId).append("<i id='changeTextMultiple"+sysId+"' class='material-icons' onclick='callMultiText("+sysId+")' style='font-size:15px;cursor:pointer;color:red'>border_color</i><i id='editIcon"+sysId+"'>(Edited)</i>");
						}
						else{
							jQuery("#eventText"+sysId).append("<i id='changeTextMultiple"+sysId+"' class='material-icons' onclick='callMultiText("+sysId+")' style='font-size:15px;cursor:pointer;color:red'>border_color</i>");
						}
						jQuery("#multipleTextSave"+sysId).remove();
						jQuery("#multiTextSave"+sysId).remove();
						jQuery("#multiTextCopy"+sysId).remove();
						jQuery("#multipleTextCopy"+sysId).remove();
						jQuery("#multipleEventColorVal"+sysId).remove();
						jQuery("#multipleEmailReminder"+sysId).remove();
						jQuery("#multiplePopupReminder"+sysId).remove();
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
				}	
			});
			
		
		
	}
	function saveMultiEventText(sysId)
	{
		var changeTextValue = jQuery("#multipleTextSave"+sysId).val();
		var multipleEventColorVal = jQuery("#multipleEventColorVal"+sysId).val();
		var multipleEventReminderVal = jQuery("#multipleEmailReminder"+sysId).val();
		var multiplePopupReminderVal = jQuery("#multiplePopupReminder"+sysId).val();
		jQuery(".overlay").show();
		if((changeTextValue!="") || (multipleEventColorVal!=0))
		{
			var caseid = '<?php echo $case_id;?>';
			var docket_search_id =  jQuery("#docket_search_id").val();
			if(changeTextValue == "")
			{
				jQuery("#multipleTextSave"+sysId).val(jQuery("#eventText"+sysId).html());
				changeTextValue = jQuery("#multipleTextSave"+sysId).val();
			}
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updatetextchange.php",
					type: "post",
					dataType: "json",
					data:{"changeTextValue":changeTextValue,"caseid":caseid,"docket_search_id":docket_search_id,"systemId":sysId,"multipleEventColorVal":multipleEventColorVal,"singleReminderValue":multipleEventReminderVal,"popupReminderDropDown":multiplePopupReminderVal},
					success: function (response) {
						console.log(response);
						//alert("Changes Saved");
						myChangeSaved();
						jQuery(".overlay").hide();
						jQuery("#multipleTextSave"+sysId).remove();
						jQuery("#multipleTextSave"+sysId).remove();
						jQuery("#multiTextSave"+sysId).remove();
						jQuery("#multiTextCopy"+sysId).remove();
						jQuery("#multipleTextCopy"+sysId).remove();
						jQuery("#multipleEventColorVal"+sysId).remove();
						jQuery("#multipleEmailReminder"+sysId).remove();
						jQuery("#multiplePopupReminder"+sysId).remove();
						jQuery("#eventText"+sysId).append("<i id='changeTextMultiple"+sysId+"' class='material-icons' onclick='callMultiText("+sysId+")' style='font-size:15px;cursor:pointer;color:red'>border_color</i><i id='editIcon"+sysId+"'>(Edited)</i>");
						
					},
						error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
				}	
			});
		}
		else
		{
		
			$.notify("Please provide event name!", {
			  type:"danger",
			  align:"center", 
			  verticalAlign:"middle",
			  animation:true,
			  animationType:"scale",
			  icon:"bell",
			  delay:2500,
			  blur: 0.8,
			 close: true,
			  buttonAlign: "center",
			});
			jQuery(".overlay").hide();
		}
	
	}


function mySucess()
{
	  $.notify("Successfully imported to your Calendar", {
	  type:"success",
	  align:"center", 
	  verticalAlign:"middle",
	  animation:true,
	  animationType:"scale",
	  icon:"check",
	  delay:2500,
	  blur: 0.8,
	 close: true,
	  buttonAlign: "center",
	});
}

function myChangeSaved()
{
	  $.notify("Event Changes Saved!", {
	  type:"success",
	  align:"center", 
	  verticalAlign:"middle",
	  animation:true,
	  animationType:"scale",
	  icon:"check",
	  delay:2500,
	  blur: 0.8,
	 close: true,
	  buttonAlign: "center",
	});
}
function hiddenSetValues()
		{
		
				var array = [];
				var uncheckedarray = [];
				
				var checkboxes = document.querySelectorAll('input[type=checkbox]:checked');

				for (var i = 0; i < checkboxes.length; i++) {
					array.push(checkboxes[i].value)
				} 
			
			
			 jQuery("#hidden_cmbJurisdictions_val").val(<?php echo $jurisdiction; ?>);
			 jQuery("#hidden_cmbTriggers_val").val(<?php echo $trigger; ?>);
			 jQuery("#hidden_selectServiceType_val").val(<?php  echo $serviceTypeVal;  ?>);
			 jQuery("#hidden_txtTriggerDate_val").val(<?php echo "'".$txtTriggerDate_val."'"; ?>);
			 jQuery("#hidden_cmbMatter_val").val(<?php echo "'".$caseId."'"; ?>);
			 jQuery("#hidden_isTimeRequired_val").val(<?php echo $isTimeRequired_val; ?>);
			 jQuery("#hidden_sort_date_val").val(<?php echo $sort_date_val; ?>);
			 jQuery("#hidden_isServed_val").val(<?php echo "'".$isServed_val."'"; ?>);
			 jQuery("#hidden_txtTime_val").val(<?php echo "'".$txtTime_val."'"; ?>);
			 jQuery("#hidden_TriggersText_val").val(<?php echo "'".$TriggersText_val = str_replace("'", "", $TriggersText_val)."'"; ?>);
			 jQuery("#hidden_selectServiceText_val").val(<?php echo "'".$serviceType."'"; ?>);
			 jQuery("#hidden_cmbMatterText_val").val(<?php echo "'".$case_name."'"; ?>);
			 jQuery("#hidden_eventarray_val").val(array);
			 
			var jurisdicationVal = '<?php echo $jurisdiction ;?>';
			var triggerItemVal = '<?php echo $trigger;?>';

			jQuery.ajax({
			url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
			type: "post",
			data: {"jurisdicationVal":jurisdicationVal,"triggerVal":triggerItemVal},
			dataType: "json",
			success: function (response) {
			   jQuery('#ajax_result').hide(1000);
			   jQuery(".overlay").hide();
			   jQuery('#hidden_JurisdictionsText_val').val(response.jurisdictionResultVal);
			   jQuery("#exportToExcelForm").submit();
			   //jQuery("#jurisdictionValtd").html(response.jurisdictionResultVal);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			}
		  });
	
		}
	function Exlexport()
		{
			  				
				$.notify("Data Export to Excel", {
						  type:"info",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"alert",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				var setDataForExcelExport = "Excel";
				jQuery("#hidden_excelData").val(setDataForExcelExport);
				jQuery("#hidden_iCalData").val("");
				jQuery("#hidden_outllookData").val("");
				jQuery("#hidden_csvData").val("");
				hiddenSetValues();   
				jQuery("#exportToExcelForm").submit();
				 
		}
		function CSVexport()
		{
			  				
				$.notify("Data Export to CSV", {
						  type:"info",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"alert",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				var setDataForCSVExport = "CSV";
				jQuery("#hidden_csvData").val(setDataForCSVExport);
				jQuery("#hidden_excelData").val("");
				jQuery("#hidden_iCalData").val("");
				jQuery("#hidden_outllookData").val("");
				hiddenSetValues(); 
				jQuery("#exportToExcelForm").submit();	  
				
				 
		}
		   
		function Icalexport()
		{
				$.notify("Data Export to ICAL", {
						  type:"info",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"alert",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				var setDataForIcalExport = "iCal";
				jQuery("#hidden_iCalData").val(setDataForIcalExport);
				jQuery("#hidden_excelData").val("");
				jQuery("#hidden_outllookData").val("");
				jQuery("#hidden_csvData").val("");
				hiddenSetValues();  
				jQuery("#exportToExcelForm").submit();				
				
		}
		function Outlookexport()
		{
				
			$.notify("Data Export to OUTLOOK", {
					  type:"info",
					  align:"center", 
					  verticalAlign:"middle",
					  animation:true,
					  animationType:"scale",
					  icon:"alert",
					  delay:2500,
					  blur: 0.8,
					 close: true,
					  buttonAlign: "center",
					});
			var setDataForOutlookExport = "outlook";
			jQuery("#hidden_outllookData").val(setDataForOutlookExport);
			jQuery("#hidden_csvData").val("");
			jQuery("#hidden_iCalData").val("");
			jQuery("#hidden_excelData").val("");
			hiddenSetValues(); 
			jQuery("#exportToExcelForm").submit();			
			
			   
		}
</script>

<?php }
genesis();
?>
