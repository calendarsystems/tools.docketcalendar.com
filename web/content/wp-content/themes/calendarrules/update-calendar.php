<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Update Docket Calendar
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

    require('globals/global_tools.php');
    require('globals/global_courts.php');
    //echo "<pre>"; print_r($_GET);
    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    if($_GET['id'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-cases';</script>";
    }

    $id = $_GET['id'];

    $query_importEvents = "SELECT i.*,c.event_date,c.short_name,dc.case_matter,c.case_event_id,i.case_id,dc.case_matter,i.triggerItem FROM docket_cases dc
INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
WHERE dc.user_id = ".$_SESSION['userid']." AND c.import_event_id = ".$id." ";

    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);

    $query_importCaseEvents = "SELECT * from docket_cases WHERE user_id = ".$_SESSION['userid']." ORDER BY case_id desc";
    $ImportCaseEvents = mysqli_query($docketDataSubscribe,$query_importCaseEvents);
    $totalRows_importCaseEvents = mysqli_num_rows($ImportCaseEvents);

    if($totalRows_importEvents == 0)
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-cases';</script>";
    }

    if($totalRows_importEvents > 0)
    {
      $fetch_importEvents = mysqli_fetch_assoc($ImportEvents);

      $jurisdiction = $fetch_importEvents['jurisdiction'];
      $trigger_item = $fetch_importEvents['trigger_item'];
      $trigger_date = $fetch_importEvents['trigger_date'];
      $trigger_time = $fetch_importEvents['trigger_time'];
      $meridiem = $fetch_importEvents['meridiem'];

      $triggerItem = $fetch_importEvents['triggerItem'];
      $service_Type = $fetch_importEvents['serviceType'];

      $servicetype = "";
      if($fetch_importEvents['service_type'] != "")
      {
        $servicetype = $fetch_importEvents['service_type'];
      }
      $case_id = $fetch_importEvents['case_id'];
      $import_docket_id = $fetch_importEvents['import_docket_id'];
      $trigger_time = date("H:i a",strtotime($trigger_time."".$meridiem));
	?>

<form id="form1" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_update_result_import.php" onsubmit="return CheckEvents();">
<input name="userid" type="hidden" value="<?php echo $_SESSION['userid']; ?>" />
<input name="auth_token" type="hidden" value="<?php echo @$_SESSION['access_token']; ?>" />
<input name="import_docket_id" id="import_docket_id" type="hidden" value="<?php echo $fetch_importEvents['import_docket_id']; ?>" />

<script type="text/javascript">
//<![CDATA[
var theForm = document.forms['form1'];
if (!theForm) {
    theForm = document.form1;
}
//alert("here!")

function __doPostBack(eventTarget, eventArgument) {
    if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
        theForm.__EVENTTARGET.value = eventTarget;
        theForm.__EVENTARGUMENT.value = eventArgument;
        theForm.submit();
    }
}
//]]>
</script>
<style>
   .xdsoft_datetimepicker .xdsoft_timepicker {
     width:70px !important;
   }
</style>

 <div id = "divContent" runat = "server">
 <h1 class="entry-title" style="float: left;">Docket Calculator</h1> <div style="float: right;"><a href="date-calculator">Date Calculator</a> | Docket Calculator | <a href="docket-research">Docket Research</a></div><br clear="all" /><table>
    <tr>
        <td>
        </td>
        <td><?php echo @$error; ?>
        </td>
    </tr>
</table>
<fieldset class="grpBox">
    <table>
        <tr>
            <td>
                <table id="table4">
                <tr><td colspan="5"><h4>

    Welcome
    <?php  //echo "<pre>"; print_r($_SESSION);
         if (isset($_SESSION['account_exist']) && $_SESSION['account_exist'] == "Y") {
             $action = $_SESSION['action'];
          ?>
         <a style="color:#FF6600;" target="_blank" href="https://subscribe.docketlaw.com/external/link.php?action=<?php echo $action;?>"><?php echo $_SESSION['fullname']; ?></a>
     <?php
         } else { ?>
                    <?php echo @$_SESSION['fullname']; ?>  <?php
         } ?>

    <?php if(isset($_COOKIE['sortDateCookie']) && $_COOKIE['sortDateCookie'] != '') { $sort_date = $_COOKIE['sortDateCookie']; } else { $sort_date = 2; } ?>
      <input type="hidden" id="sort_date" name="sort_date" value="<?php echo $sort_date;?>">
<span class="userinfo"> (admin) </span>
                                               </h4></td></tr>
                    <tr>
                        <td>
                          <p>Jurisdiction:</p></td>

                    <td>
                          <p>
                          <span id="ajax_jurisdiction">
                          <select name="cmbJurisdictions" id="cmbJurisdictions">
                          <option value="0">-- select Court --</option></select>
                          </span>
                          </p></td>
                    </tr>

      <?php

//      if ($selectJurisdiction!=0) {
      if (1==1) { ?>
                    <tr>
                        <td>
                        <p>Trigger Item: </p></td>
                        <td>
                          <p><span id="ajax_jurisdiction_trigger">Must select Jurisdiction first.</span></p>
                            <div id="ex3" class="modal">
                            </div>
                            <input type="hidden" id="hidden_trigger_item" name="hidden_trigger_item" value="<?php echo $triggerItem;?>">
                        </td>
                    </tr>


      <?php
      if (1==1) {
      //if ($selectTriggerItem!=0) { ?>
                    <tr>
                        <td>
                        <p>Trigger Date: </p></td>
                        <td >
<table>
    <tr>
        <td>
            <input type="text" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($trigger_date)) { echo $trigger_date; } else { echo date("m/d/Y"); } ?>" />
        </td>
        <td>
        &nbsp;Time:&nbsp;
        </td>
        <td>


             <input type="text" name="txtTime" id="txtTime" value="" style="width:60%" />

        </td>
    </tr>
</table>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"  >
                        <p>Service Type: </p></td>
                        <td >
                          <p><span id="ajax_trigger_service">Service type not required</span>
                          <input type="hidden" id="hidden_service_type" name="hidden_service_type" value="<?php echo $service_Type;?>">

                        </p></td>
                    </tr>

            <?php } ?>


<tr>
                        <td><p>Matter:</p></td>
                        <td>
                         <select id="cmbMatter_exist" name="cmbMatter_exist" style="width: auto;">
                                  <?php while ($row_caseevents = mysqli_fetch_assoc($ImportCaseEvents)) { ?>
                                    <option value="<?php echo $row_caseevents['case_id']; ?>" <?php if($case_id == $row_caseevents['case_id']) { ?> selected="selected" <?php }?>><?php echo $row_caseevents['case_matter']; ?></option>
                                  <?php } ?>
                         </select>
</td>

</tr>

                    <tr>
                        <td><p>&nbsp;</p></td>
                        <td>
<span id="button_calc" style="display:none;">

<table>
    <tr>
        <td>
                           <?php       //  if ($numServes>0 || ($numServes==0 && $selectTriggerItem != 0 && $selectJurisdiction != 0)){ ?>
                   <input id="btnCalculate" name="btnCalculate" type="button"  value=" Calculate  ">
                    <?php //} ?>
        </td>
        <input type="hidden" id="clear_checkbox" name="clear_checkbox" value="">
        <td>
        <span id="button_export" style="display:none;">
            <input id="btnImport" name="btnImport" type="submit" value=" Update Import to Google Calendar  "  />
        </span>
        </td>
    </tr>
</table>
</form>
</span>
                        </td>
                    </tr></table>

            </td>
        </tr>

    </table>
    <table width="100%"><tr>
                      <td colspan="2"><div class="divider"></div><span id="ajax_result"></span>
&nbsp;</td>
                    </tr>

                    <?php } ?>
                </table>

</fieldset>
</div>
<pre>
<?php if (isset($_SESSION['userid']) && $_SESSION['userid'] == "bens" ){
    print_r($response);
} ?></pre>
<?php

}

 if (isset($_SESSION['userid']) && $totalRows_importEvents > 0)
 {
 ?>

 <script type="text/javascript">
      function CheckEvents(){
        var checked=false;
        var elements = document.getElementsByName("events[]");
        for(var i=0; i < elements.length; i++){
            if(elements[i].checked) {
                checked = true;
            }
        }
        if (!checked) {
            alert("Please check at least one checkbox.");
            return false;
        }
        return true;
      }
      jQuery("#ajax_jurisdiction").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
      <?php if($jurisdiction != '') { ?>
          jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
            type: "post",
            data: { "page":"docket_calculator","cmbJurisdictions":<?php echo $jurisdiction;?>,"import_docket":<?php echo $import_docket_id;?> },
            success: function (response) {
               jQuery("#ajax_jurisdiction").html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
          });
      <?php } else { ?>
         jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
            type: "post",
            data: { "page":"docket_calculator","import_docket":<?php echo $import_docket_id;?> },
            success: function (response) {
               jQuery("#ajax_jurisdiction").html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
          });
      <?php } ?>

      function get_jurisdictions_trigger(val)
      {

         jQuery("#button_calc").hide();
         jQuery("#button_export").hide();
         jQuery("#ajax_result").hide();
         var cmbJurisdictions = jQuery("#cmbJurisdictions").find("option:selected").text();

           jQuery("#ajax_jurisdiction_trigger").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
           jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction_trigger.php",
                type: "post",
                data: { "cmbJurisdictions":val },
                success: function (response) {
                   if(response.trim() != '')
                   {
                     jQuery("#ajax_jurisdiction_trigger").html(response);
                     <?php if( isset($trigger_item) && isset($jurisdiction)) { ?>
                     jQuery("#cmbTriggers").val(<?php echo $trigger_item;?>);
                     get_trigger_service(<?php echo $trigger_item;?>,<?php echo $jurisdiction;?>,<?php echo $servicetype;?>);
                     <?php } ?>
                   } else {
                     jQuery("#ajax_jurisdiction_trigger").html("Must select Jurisdiction first.");
                   }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
           });


           jQuery("#ex3").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
           jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_get_all_triggers.php",
                type: "post",
                data: { "cmbJurisdictions":val,"cmbJurisdictions_name":cmbJurisdictions },
                success: function (response) {
                   if(response.trim() != '')
                   {
                     jQuery("#ex3").html(response);
                   } else {
                     jQuery("#ex3").html("");
                   }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
           });

      }

      function get_trigger_service(val,val_parent,type=null,clearcheckbox=null)
      {
         jQuery("#button_calc").hide();
         jQuery("#button_export").hide();
         jQuery("#ajax_result").hide();

         var triggerItem = jQuery.trim(jQuery('#cmbTriggers').find('option:selected').text());
         jQuery("#hidden_trigger_item").val(triggerItem);

         jQuery("#ajax_trigger_service").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_trigger_service.php",
                type: "post",
                data: { "cmbJurisdictions":val_parent,"cmbTriggers":val,"service_type":type },
                success: function (response) {
                   jQuery("#ajax_trigger_service").html(response);
                   jQuery("#button_calc").show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
         });

         if(clearcheckbox == 'clear')
         {
           jQuery("#clear_checkbox").val('clear');
         } else {
           jQuery("#clear_checkbox").val('');
         }
      }

        function PrintResult() {
            var divToPrint = document.getElementById("show_results_list");
            jQuery('ul.triggersnav').find('ul').hide();
            jQuery('#show_search_term').show();
            //jQuery('.court_rule').hide();
            jQuery('.triggersnav a').css('text-decoration','none');
            var popupWin = window.open('', '_blank', 'width=300,height=300');
            popupWin.document.open();
            popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            //jQuery('ul.triggersnav').find('ul').show();
            jQuery('#show_search_term').hide();
            //jQuery('.court_rule').show();
            popupWin.document.close();
        }
      jQuery("#btnCalculate").click(function(){
         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
         var cmbTriggers =  jQuery("#cmbTriggers").val();
         var selectServiceType =  jQuery("#cmbServiceTypes").val();
         var txtTriggerDate =  jQuery("#datepicker").val();
         var txtTime =  jQuery("#txtTime").val();
         var cmbMatter_exist =  jQuery("#cmbMatter_exist").val();
         var isTimeRequired =  jQuery("#isTimeRequired").val();
         var sort_date =  jQuery("#sort_date").val();
         var isServed =  jQuery("#isServed").val();
         var import_docket_id = jQuery("#import_docket_id").val();
         var clear_checkbox =  jQuery("#clear_checkbox").val();

         if(txtTime != '') {
             txtTime_arr = txtTime.split(':');
             txtTime_arr_2 = txtTime_arr[1].split(' ');
             txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
         }


         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter_exist,"sort":sort_date,"import_docket_id":import_docket_id,"clear_checkbox":clear_checkbox },
                success: function (response) {
                   //console.log(response);
                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response.html);
                   jQuery("#show_jurisdiction_print").html(jQuery("#cmbJurisdictions option:selected").text());
                   jQuery("#show_trigger_print").html(jQuery("#cmbTriggers option:selected").text());
                   if(jQuery("#cmbServiceTypes option:selected").text() != '')
                   {
                     jQuery("#show_service_print").html("Service Type: <b>"+jQuery("#cmbServiceTypes option:selected").text()+"</b>");
                   }
                   if(response.count > 0) {
                       jQuery("#button_export").show();
                       if(sort_date == 1)
                       {
                           jQuery("#asc_link").hide();
                           jQuery("#desc_link").show();
                       } else if(sort_date == 2)
                       {
                           jQuery("#asc_link").show();
                           jQuery("#desc_link").hide();
                       }
                   } else {
                       jQuery("#button_export").hide();
                   }
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    var checkboxes = document.getElementsByTagName("input");
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == "checkbox") {
                            checkboxes[i].checked = true;
                        }
                    }
                    jQuery('ul.triggersnav').find('ul').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });



      });

   function getServiceType()
   {
     var ServiceTypes = jQuery.trim(jQuery('#cmbServiceTypes').find('option:selected').text());
     jQuery("#hidden_service_type").val(ServiceTypes);
   }

   function sort_date(val)
   {
     jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
         var cmbTriggers =  jQuery("#cmbTriggers").val();
         var selectServiceType =  jQuery("#cmbServiceTypes").val();
         var txtTriggerDate =  jQuery("#datepicker").val();
         var txtTime =  jQuery("#txtTime").val();
         var cmbMatter =  jQuery("#cmbMatter").val();
         var isTimeRequired =  jQuery("#isTimeRequired").val();
         var import_docket_id = jQuery("#import_docket_id").val();
         var isServed =  jQuery("#isServed").val();
         if(txtTime != '') {
         txtTime_arr = txtTime.split(':');
         txtTime_arr_2 = txtTime_arr[1].split(' ');
         txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
         }

         jQuery("#sort_date").val(val);

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":val,"import_docket_id":import_docket_id },
                success: function (response) {
                   //console.log(response);
                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response.html);
                   jQuery("#show_jurisdiction_print").html(jQuery("#cmbJurisdictions option:selected").text());
                   jQuery("#show_trigger_print").html(jQuery("#cmbTriggers option:selected").text());
                   if(jQuery("#cmbServiceTypes option:selected").text() != '')
                   {
                     jQuery("#show_service_print").html("Service Type: <b>"+jQuery("#cmbServiceTypes option:selected").text()+"</b>");
                   }
                   if(response.count > 0) {
                       jQuery("#button_export").show();
                       if(val == 1)
                       {
                           jQuery("#asc_link").hide();
                           jQuery("#desc_link").show();
                       } else if(val == 2)
                       {
                           jQuery("#asc_link").show();
                           jQuery("#desc_link").hide();
                       }
                   } else {
                       jQuery("#button_export").hide();
                   }
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    var checkboxes = document.getElementsByTagName("input");
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == "checkbox") {
                            checkboxes[i].checked = true;
                        }
                    }
                    jQuery('ul.triggersnav').find('ul').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });
   }
  </script>
  <link href="jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
  <script src="jquery/js/jquery.datetimepicker.full.min.js"> type="text/javascript"></script>
  <script>
  jQuery('#txtTime').datetimepicker({
            datepicker:false,
            format:'h:i a',
            value:'<?php echo $trigger_time;?>',
            formatTime: 'g:i a',
            step:30,
            ampm: true
   });
  </script>
  <link href="jquery/css/jquery.modal.css" rel="stylesheet" type="text/css">
  <script src="jquery/js/jquery.modal.js" type="text/javascript"></script>
    <style type="text/css" media="screen">
    .modal {
        display: none;
    }

    </style>
    <style type="text/css">
  .modal a.close-modal[class*="icon-"] {
    top: -10px;
    right: -10px;
    width: 20px;
    height: 20px;
    color: #fff;
    line-height: 1.25;
    text-align: center;
    text-decoration: none;
    text-indent: 0;
    background: #900;
    border: 2px solid #fff;
    -webkit-border-radius: 26px;
    -moz-border-radius: 26px;
    -o-border-radius: 26px;
    -ms-border-radius: 26px;
    -moz-box-shadow:    1px 1px 5px rgba(0,0,0,0.5);
    -webkit-box-shadow: 1px 1px 5px rgba(0,0,0,0.5);
    box-shadow:         1px 1px 5px rgba(0,0,0,0.5);
  }
</style>

 <?php }

}
 genesis(); // <- everything important: make sure to include this.
