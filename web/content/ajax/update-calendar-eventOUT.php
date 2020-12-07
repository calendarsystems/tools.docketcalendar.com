<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Update Calendar Event
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

  global $docketDataSubscribe;

    require('globals/global_tools.php');
    require('globals/global_courts.php');

    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
    }
    if($_GET['id'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-cases';</script>";
    }
    if($_SESSION['access_token'] == '')
    {
      echo "<script>alert('Please login into Google Authentication.');window.location.href='http://googledocket.com/google-login/?update_event=".$_GET['id']."';</script>";
    }
    unset($_SESSION['update_event']);
    $event_id = $_GET['id'];
  ?>

<?php
$query_importEvents = "SELECT c.event_date,c.short_name,dc.case_matter,c.case_event_id,i.import_docket_id,i.case_id,dc.case_matter,i.triggerItem,i.trigger_date,i.trigger_time,e.event_docket,e.has_child,i.attendees FROM docket_cases dc
INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
WHERE dc.user_id = ".$_SESSION['userid']." AND c.import_event_id = ".$event_id." ";
$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
$totalRows_importEvents = mysqli_num_rows($ImportEvents);

if ($totalRows_importEvents == 0) {
    echo "No Events found";
} else {
$row_events = mysqli_fetch_assoc($ImportEvents);

    $short_name = $row_events['short_name'];
    $event_date = $row_events['event_date'];
    $case_event_id = $row_events['case_event_id'];
    $case_id = $row_events['case_id'];
	$triggerDate  = $row_events['trigger_date']; 
    $triggerTime  = $row_events['trigger_time']; 
    $triggerMeridiem  = $row_events['meridiem'];

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

    $query_case_attendees = "SELECT * FROM docket_cases_attendees dca WHERE  dca.case_id = ".$case_id."";
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
<style>
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
    }
</style>
<div style="width: 80%;">
    <div style="float: left;width: 70%;"><h2>Update Calendar Event</h2></div>
    <div style="float: right;"><a href="docket-cases">Docket Cases</a>&nbsp;|&nbsp;<a href='calendar-events?case_id=<?php echo $case_id;?>'>Back</a></div>
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
                <td>Case:</td><td><?php echo $row_events['case_matter'];?></td>
            </tr>
            <tr>
                <td>Trigger:</td><td><?php echo $row_events['triggerItem'];?></td>
            </tr>
           	<tr>
                <td>Trigger Date </td><td> <input type="text" id="datepicker" class="datepicker" name="trigger_date" value="<?php echo date('m/d/Y', strtotime($triggerDate));?>">&nbsp;&nbsp;Time:
             <input type="text" name="trigger_time" id="trigger_time" value="<?php echo date("H:i:s",strtotime($triggerTime)); ?>" style="width:60px;" />&nbsp;&nbsp;(<?php  echo date('dS M Y', strtotime($triggerDate)) .' '.date("H:i:s",strtotime($triggerTime)).' '.$triggerMeridiem;?>)</td>
        </td>
            </tr>
             <tr>
                <td>Add Attendees:</td><td><select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]">
                    <?php foreach($contact_list as $contact) { 
           if($_SESSION['author_id'] == $contact['email'])
                    {
                         unset($contact['email']); 
                    } 
          ?>
          <option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$attendee)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>
          <?php } ?>
              </select></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input type="button" value="Update" onclick="javascript:update_event(<?php echo $case_event_id;?>,<?php echo $case_id;?>,'<?php echo $case; ?>');">
                &nbsp;<span id="ajax_result"></span></td>
            </tr>
        </table>
  </div>
    <script type="text/javascript">
        jQuery('.multipleSelect').fastselect();
        function update_event(case_event_id,case_id,caselab)
        {
            var attendees =  jQuery("#attendees").val();

            if(caselab == 'eventdocket')
            {
             
               jQuery.dialogbox({
                type:'msg',
                title:'',
                content:'Do you wish to re-calculate all other events dates?',
                closeBtn:true,
                btn:['Confirm','Cancel'],
                call:[
                function(){
                    jQuery("#ajax_result").show();
                    jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
                    var event_date = jQuery("#event_date").val();
                    console.log(event_date);
                    jQuery.ajax({
                        url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
                        type: "post",
                        dataType: "json",
                        data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date, "caselab":caselab },
                        success: function (response) {
                           alert("YID"+response);
                           console.log(response);
                           jQuery("#ajax_result").show(4500);
                           jQuery("#ajax_result").html(response.html);
                           setTimeout(function() {
                                window.location.href = 'http://googledocket.com/calendar-events?case_id='+case_id;
                           }, 500);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
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
            }

            if(caselab == 'parent')
            {
             
               jQuery.dialogbox({
                type:'msg',
                title:'',
                content:'Updating this event will change dates for other events, do you wish to proceed?',
                closeBtn:true,
                btn:['Confirm','Cancel'],
                call:[
                function(){
                    jQuery("#ajax_result").show();
                    jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
                    var event_date = jQuery("#event_date").val();
                    console.log(event_date);
                    jQuery.ajax({
                        url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
                        type: "post",
                        dataType: "json",
                        data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date, "caselab":caselab },
                        success: function (response) {
                            alert("YID"+response);
                           console.log(response);
                           jQuery("#ajax_result").show(4500);
                           jQuery("#ajax_result").html(response.html);
                           setTimeout(function() {
                                window.location.href = 'http://googledocket.com/calendar-events?case_id='+case_id;
                           }, 500);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
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
            }

            if(caselab == 'normal')
            {
              
                jQuery("#ajax_result").show();
                jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
                var event_date = jQuery("#event_date").val();
                //console.log(event_date);
                jQuery.ajax({
                        url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
                        type: "post",
                        dataType: "json",
                        data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date },
                        success: function (response) {
                           jQuery("#ajax_result").show(4500);
                           jQuery("#ajax_result").html(response.html);
                           setTimeout(function() {
                                window.location.href = 'http://googledocket.com/calendar-events?case_id='+case_id;
                           }, 500);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           console.log(textStatus, errorThrown);
                           jQuery("#button_export").hide();
                        }
                 });
            }
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
        jQuery('#trigger_time').datetimepicker({
              datepicker:false,
             format:'h:i a',
             formatTime: 'h:i a',
             step:30,
             ampm: true,
             value:'<?php echo $triggerTime;?>',  
			 		 
     });
    </script>
<?php
}
genesis();
?>