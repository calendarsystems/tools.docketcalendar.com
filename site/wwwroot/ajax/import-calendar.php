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
</style>


<?php
function custom_loop() {
    global $calendarData;
    global $attendee;
    global $response;
    global $events_array;
    global $case_name;
    global $dbCalendarId;
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
//echo '<pre>'; print_r($events_array);
$result_html['count'] = $numresults;
    ?>
<link rel="stylesheet" href="jquery/css/fastselect.css">
<script src="jquery/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
<link rel = "stylesheet" type = "text/css"    href = "jquery/css/standalone.css">
<style>
.clsFnt{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
}
.triggers
{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
}
</style>
<?php

if ($numresults > 0) {

    $result .= '<div><span class="clsFnt">Selected Case : <b>'.$case_name.'</b></span><div>';

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

        $result .= $response['CalendarRuleEvent']['ShortName'] . ' - ';

        if (isset($response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
            $result .= $response['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
        } else {
            foreach ($response['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                $result .= $Rule['RuleText'];
            }
        }
		$result .= '</a>';



        if (isset($response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'])) {
			// IF THERE IS ONE RULE
			$result .= '<ul class="triggersnav">
                    <li>
                        <a href="javascript:void(0);"><span class="court_rule">Court Rule:</span> ' . $response['CalendarRuleEvent']['CourtRules']['Rule']['RuleText'];

		} else {

			// IF THERE ARE MANY RULES
			foreach ($response['CalendarRuleEvent']['CourtRules']['Rule'] as $rule) {
				$result .= '<ul class="triggersnav">
                    <li>
                        <a href="javascript:void(0);"><span class="court_rule">Court Rule:</span> ' . $rule['RuleText'] . '</ul>';

			}
		}
		$result .= '</ul></ul>';
        $eventParentSystemID = $response['CalendarRuleEvent']['ParentSystemID'];
        $eventSystemID = $response['CalendarRuleEvent']['SystemID'];
        $eventResultsArray[] = $eventSystemID;
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
            if($alreadyExist == 1) { $style = "color:red;"; }
            $result .= '<ul class="triggersnav" style="padding: 0 0 0 15px !important;font-size: 15px;'.$style.'">
                       <li><input type="checkbox" checked="checked" onclick="checkResult('.$sysID.',this)" id='.$sysID.'_'.$parentID.' data='.$parentID.' class="evenetClass" name="events[]" '.$selected_child.' value="'.$sysID.'" style="float: left;margin: 5px -17px;"/>' . $montharray[$mo] . ' ' . substr($justdate, 8, 2) . ', ' . substr($justdate, 0, 4) .' '.$event_specific_time. ' - ';

                                if ($_POST['cmbMatter'] != "") {
                                    $result .= '(' . $_POST['cmbMatter'] . ') ';
                                }

                                $result .= $Event['CalendarRuleEvent']['ShortName'] . ' - ';

                                if (isset($Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'])) {
                                    $result .= $Event['CalendarRuleEvent']['DateRules']['Rule']['RuleText'];
                                } else {
                                    foreach ($Event['CalendarRuleEvent']['DateRules']['Rule'] as $Rule) {
                                        $result .= $Rule['RuleText'];
                                    }
                                }




            $result .= '</ul></ul>';
            $eventDocket = $Event['CalendarRuleEvent']['IsEventDocket'];
            $eventParentSystemID = $Event['CalendarRuleEvent']['ParentSystemID'];
            $eventSystemID = $Event['CalendarRuleEvent']['SystemID'];
            $eventResultsArray[] = $eventSystemID;
            $eve++;
            $selectedEvents++;
          }
        }  //for loop end
        //echo "<pre>"; print_r($eventResultsArray);
        $result .= '</div></td></tr>';
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
       function cmp($a, $b){
        if ($a == $b)
            return 0;
        return ($a['name'] < $b['name']) ? -1 : 1;
       }
       $contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp"); ?>
        <label for="lastName" class="last-name">Add Attendees</label>
        <?php if(isset($_SESSION['google_contacts'])) { ?>
        <select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]" style="height:200px;">
         <?php foreach($contact_list as $contact) { 
           if($_SESSION['author_id'] == $contact['email'])
                    {
                         unset($contact['email']); 
                    } 
          ?>
            <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>
         <?php } ?>
        </select>
        <br>
        <?php } else { ?>
        <textarea style="height:100px;width:200px;" name="attendees" id="attendees"></textarea>
        <br>
        <span style="font-size:12px;color:#1C528E;">Add Multiple Attendees Valid GMail Address using by comma separator. Example: xyz@gmail.com,abced@gmail.com</span>
        <?php } ?>
        <input name="docket_search_id" type="hidden" id="docket_search_id" value="<?php echo $_SESSION['docket_search_id']; ?>">
        <input id="btnImport" name="btnImport" style="width: 190px;" type="button" value="Import to Google Calendar"/>&nbsp;<span id="ajax_result"></span>
        </form>

<script type="text/javascript">
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
         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

        var array = []
        var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')

        for (var i = 0; i < checkboxes.length; i++) {
            array.push(checkboxes[i].value)
        }
         var docket_search_id =  jQuery("#docket_search_id").val();
         var attendees =  jQuery("#attendees").val();
         var calendar_id =  jQuery("#calendar_id").val();
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_import.php",
                type: "post",
                dataType: "json",
                data: { "docket_search_id":docket_search_id,"attendees":attendees,"calendar_id":calendar_id,"events":array },
                success: function (response) {
                   console.log(response);

                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response.html);
                   setTimeout(function() {
                         $('#ajax_result').hide(1000);
                         window.location.href = "<?php echo get_home_url(); ?>/uservalidate";
                    }, 1000);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });

      });
</script>

<?php }
genesis();
?>
