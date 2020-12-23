<?php require_once('Connections/docketDataSubscribe.php');

/*
Template Name: Update Import Calendar
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
  max-width: 600px;
  padding: 1em;
}
.form1 input {
  background: #fff;
  border: 1px solid #9c9c9c;
}
.form1 button {
  background: lightgrey;
  padding: 0.7em;
  width: 100%;
  border: 0;
}
.form1 button:hover {
  background: gold;
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
        $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

    $query_importEvents = "SELECT i.* FROM import_docket_calculator as i
    WHERE i.import_docket_id = ".$_SESSION['docket_search_id']." ";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);
    $fetch_importEvents = mysqli_fetch_assoc($ImportEvents);
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
              $selected = "";
              if($calendar_summary == $calendar_id){
                 $calendarID = "primary";
                 $calendarSummary = "Primary Calendar";
              } else {
                 $calendarID = $calendar_id;
                 $calendarSummary = $calendar_summary;
              }
              if($fetch_importEvents['calendar_id'] == $calendarID) {
                 $selected = 'selected="selected"';
              }
              if($fetch_importEvents['attendees'] != "")
              {
                $explode_attendees = explode(",",$fetch_importEvents['attendees']);
              }
              ?>
            <option value="<?php echo $calendarID;?>" <?php echo $selected;?>><?php echo $calendarSummary;?></option>
           <?php

           } ?>
          </select>
       <?php } ?>
       <?php $contact_list = $_SESSION['google_contacts'];?>
        <label for="lastName" class="last-name">Add Attendees</label>
        <?php  if(isset($_SESSION['google_contacts'])) { ?>
        <select multiple="multiple" id="attendees" name="attendees[]">
         <?php foreach($contact_list as $contact) { $selected_att= ""; if(in_array($contact['email'],$explode_attendees)) { $selected_att = 'selected="selected"'; }?>
            <option value="<?php echo $contact['email'];?>" <?php echo $selected_att;?>><?php echo $contact['name'];?></option>
         <?php } ?>
        </select>
        <br>
        <span style="font-size:12px;color:#1C528E;">Click Ctrl button to select multiple attendees</span>
        <?php } else { ?>
        <textarea style="height:100px;" name="attendees" id="attendees"><?php echo $fetch_importEvents['attendees'];?></textarea>
        <br>
        <span style="font-size:12px;color:#1C528E;">Add Multiple Attendees Valid GMail Address using by comma separator. Example: xyz@gmail.com,abced@gmail.com</span>
        <?php } ?>
       
        <input name="docket_search_id" type="hidden" id="docket_search_id" value="<?php echo $_SESSION['docket_search_id']; ?>">
        <input id="btnImport" name="btnImport" style="width: 190px;" type="button" value="Import to Google Calendar"/>&nbsp;<span id="ajax_result"></span>
        </form>
<?php }
genesis();
?>
<script type="text/javascript">

 jQuery("#btnImport").click(function(){

         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         var docket_search_id =  jQuery("#docket_search_id").val();
         var attendees =  jQuery("#attendees").val();
         var calendar_id =  jQuery("#calendar_id").val();

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_update_docket_import.php",
                type: "post",
                dataType: "json",
                data: { "docket_search_id":docket_search_id,"attendees":attendees,"calendar_id":calendar_id },
                success: function (response) {
                   //console.log(response);
                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response.html);
                   setTimeout(function() {
                        window.location.href = '<?php echo get_home_url(); ?>/docket-cases';
                   }, 5000);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });

      });
</script>