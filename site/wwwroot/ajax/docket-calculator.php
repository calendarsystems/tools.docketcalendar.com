<?php require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
/*
Template Name: Docket Calculator New
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
    global $docketDataSubscribe;
  require('globals/global_tools.php');
  require('globals/global_courts.php');


    unset($_SESSION['docket_search_id']);

    $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
    if(!isset($_SESSION['author_id']))
    {
        $_SESSION['docket_calculator'] = "docket";
    }
    if($_GET['action'] == 'googleAuth'){
     echo "<script>window.location.href='".$login_url."';</script>";
    }
  // check for login
  if (isset($_SESSION['userid'])) {
    //echo $_SESSION['userid'];
  }
  else {
    if (@$_GET['e']=='99'){ ?>
          <span class="loginFailed">Login failed</span><BR />
          <a href="forgot-password">Forgot Username/Password? </a>
        <?php }
    if (@$_GET['e']=='66'){ ?>
          <span class="loginFailed">Sorry, this ID is not a valid login for this site.</span><BR />
          <a href="forgot-password">Forgot Username/Password? </a>
        <?php } 
    require ('include/login_block.php');
  }
// print
 if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
 {
    $query_importEvents = "SELECT * from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id ";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);

 }
?>
<style>
 #divform1 { font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;}
    .xdsoft_datetimepicker .xdsoft_timepicker {
     width:70px !important;
   }
</style>
<div id="divform1">
<form id="form1" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_result_import.php" onsubmit="return CheckEvents();">
<input name="userid" type="hidden" value="<?php echo $_SESSION['userid']; ?>" />
<input name="auth_token" type="hidden" value="<?php echo @$_SESSION['access_token']; ?>" />
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
  <?php //echo "<pre>"; print_r($_SESSION); ?>
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
                <tr><td colspan="4"><h4>
    
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
                                 </h4></td><td><!-- <a href='event-option'>Event Option</a> --></td></tr>
                  <tr>
                      <td>
                            <p>Jurisdiction:</p>
                      </td>
                      <td>
                          <p>
                          <span id="ajax_jurisdiction"><select name="cmbJurisdictions" id="cmbJurisdictions" class="chosen">
                          <option value="0">---Select Court---</option></select>
                          </span>
                          </p>
                    </td>
                  </tr>

      <?php

//    if ($selectJurisdiction!=0) {
    if (1==1) { ?>
                    <tr>
                        <td>
                        <p>Trigger Item:</p></td>
                        <td>
                          <p><span id="ajax_jurisdiction_trigger">Must select Jurisdiction first.</span></p>
                            <div id="ex3" class="modal">
                            </div>
                            <input type="hidden" id="hidden_trigger_item" name="hidden_trigger_item">
                        </td>
                    </tr>


      <?php
    if (1==1) { 
    //if ($selectTriggerItem!=0) { ?>
                    <tr>
                        <td> 
                        <p>Trigger Date:</p></td>
                        <td>
							<table>
								<tr>
									<td>
										<input type="text" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($_POST['txtTriggerDate'])) { echo $_POST['txtTriggerDate']; } else { echo date("m/d/Y"); } ?>" /> 
									</td>
									<td>
									&nbsp;Time:&nbsp;
									</td>
									<td>
										 <input type="text" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime(@$_POST['txtTime'])); ?>" style="width:60%" />
									</td>
									<td>
										 &nbsp;&nbsp;<span id="show_msg_time" style="color:red;">
									</td>
								</tr>
							</table>
						</td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"  >
                        <p>Service Type:</p></td>
                        <td >
                          <p><span id="ajax_trigger_service" style="margin-left:2px;">Service type not required</span>
                           <input type="hidden" id="hidden_service_type" name="hidden_service_type">
                           &nbsp;&nbsp;<span id="show_msg_ser" style="color:red;"></span>
                        </p></td>
                    </tr>

            <?php } ?>


                    <tr>
                    <td><p>Matter Cases:</p></td>
                    <td>
						<table>
							<tr>
								<td>	
										<?php if($totalRows_importEvents > 0 && $_SESSION['author_id'] != '') { ?>&nbsp;
													<select id="cmbMatter" name="cmbMatter" style="width: auto;padding-right:5px;">
														<option value="">---Select Case---</option>
													  <?php while ($row_events = mysqli_fetch_assoc($ImportEvents)) { ?>
														<option value="<?php echo $row_events['case_id']; ?>"><?php echo $row_events['case_matter']; ?></option>
													  <?php } ?>
													</select>&nbsp;&nbsp;<span id="show_msg" style="color:red;"></span>
											<?php } else if($totalRows_importEvents == 0 && $_SESSION['author_id'] != '') { ?>
												   <a href="/add-case">Please add case</a>
											<?php } else { ?>
												   <a href="<?php echo $login_url;?>">Please login Google Authentication to access cases.</a>
											<?php } ?>
								</td>
								<td>Appointment length:</td>
								<td>
								<?php
									$query_authInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' ";
									$authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
									$totalRows_authInfo = mysqli_num_rows($authInfo);
									$row_authInfo = mysqli_fetch_assoc($authInfo);
								?>
							   <select name="appointment_length" id="appointment_length" style="width:100px;">
							   <option value="1800" <?php if($row_authInfo['appointment_length'] == 1800) { ?> selected="selected" <?php } ?>>30 Minutes</option>
							   <option value="3600" <?php if($row_authInfo['appointment_length'] == 3600) { ?> selected="selected" <?php } ?>>1 hour</option>
							   <option value="7200" <?php if($row_authInfo['appointment_length'] == 7200) { ?> selected="selected" <?php } ?>>2 hours</option>
							   <option value="10800" <?php if($row_authInfo['appointment_length'] == 10800) { ?> selected="selected" <?php } ?>>3 hours</option>
							   <option value="14400" <?php if($row_authInfo['appointment_length'] == 14400) { ?> selected="selected" <?php } ?>>4 hours</option>
							   <option value="18000" <?php if($row_authInfo['appointment_length'] == 18000) { ?> selected="selected" <?php } ?>>5 hours</option>
							   <option value="21600" <?php if($row_authInfo['appointment_length'] == 21600) { ?> selected="selected" <?php } ?>>6 hours</option>
							   <option value="25200" <?php if($row_authInfo['appointment_length'] == 25200) { ?> selected="selected" <?php } ?>>7 hours</option>
							   <option value="28800" <?php if($row_authInfo['appointment_length'] == 28800) { ?> selected="selected" <?php } ?>>8 hours</option>
							   <option value="32400" <?php if($row_authInfo['appointment_length'] == 32400) { ?> selected="selected" <?php } ?>>9 hours</option>
							   <option value="36000" <?php if($row_authInfo['appointment_length'] == 36000) { ?> selected="selected" <?php } ?>>10 hours</option>
							   <option value="39600" <?php if($row_authInfo['appointment_length'] == 39600) { ?> selected="selected" <?php } ?>>11 hours</option>
							  <option value="43200" <?php if($row_authInfo['appointment_length'] == 43200) { ?> selected="selected" <?php } ?>>12 hours</option>
							   </select>
							   <input type="hidden" id="hidden_appointment_length" name="hidden_appointment_length">
								</td>
							</tr>
						</table>
					</td>
                    </tr>

                    <tr>
                    <td><p>Location:</p></td>
                    <td>&nbsp;<input type="text" style="width:400px;" name="location" id="location" value="<?php echo @$_POST['location'];?>" /></td>
                    </tr>

                    <tr>
                    <td><p>Custom Text:</p></td>
                    <td>&nbsp;<textarea id="custom_text" name="custom_text" style="width:400px;"><?php echo @$_POST['custom_text'];?></textarea></td>
                    </tr>

                    <tr>
                        <td><p>&nbsp;</p></td>
                        <td>
<span id="button_calc" style="display:none;">

<table>
    <tr>
        <td>
                 <?php if($totalRows_importEvents > 0 && $_SESSION['author_id'] != '') { ?>
                   <input id="btnCalculate" name="btnCalculate" type="button"  value="  Calculate  ">
                    <?php } ?>
        </td>
        <td>
        <span id="button_export" style="display:none;">
            <input id="btnImport" name="btnImport" type="submit" value="  Import to Google Calendar  "  />
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
</div>
<pre>
<?php if (isset($_SESSION['userid']) && $_SESSION['userid'] == "bens" ){
  print_r($response);
} ?></pre>
<?php


if (isset($_SESSION['userid']))
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
      <?php if(@$_GET['cmbJurisdictions'] != '') { ?>
          jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
            type: "post",
            data: { "page":"docket_calculator","cmbJurisdictions":<?php echo $_GET['cmbJurisdictions'];?> },
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
            data: { "page":"docket_calculator" },
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
                     <?php if( isset($_GET['cmbTriggers']) && isset($_GET['cmbJurisdictions'])) { ?> jQuery("#cmbTriggers").val(<?php echo $_GET['cmbTriggers'];?>); get_trigger_service(<?php echo $_GET['cmbTriggers'];?>,<?php echo $_GET['cmbJurisdictions'];?>); <?php } ?>
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

      function get_trigger_service(val,val_parent)
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
                data: { "cmbJurisdictions":val_parent,"cmbTriggers":val },
                success: function (response) {
                   jQuery("#ajax_trigger_service").html(response);
                   jQuery("#button_calc").show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
           });

      }

        function PrintResult() {
            var divToPrint = document.getElementById("show_results_list");
            jQuery('ul.triggersnav').find('ul').hide();
            jQuery('ul.triggersnav').find('.evenetClass').hide();
            jQuery('#show_search_term').show();
            //jQuery('.court_rule').hide();
            jQuery('.triggersnav a').css('text-decoration','none');
            var popupWin = window.open('', '_blank', 'width=300,height=300');
            popupWin.document.open();
            popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            //jQuery('ul.triggersnav').find('ul').show();
            jQuery('ul.triggersnav').find('.evenetClass').show();
            jQuery('#show_search_term').hide();
            //jQuery('.court_rule').show();
            popupWin.document.close();
        }
      jQuery("#btnCalculate").click(function(){

         <?php if($_SESSION['access_token'] == '') { ?>
               alert("Session has expired in your browser, Please once again login.");
               window.location.href='<?php echo $login_url;?>';
         <?php } ?>
         var cmbJurisdictions 		=  jQuery("#cmbJurisdictions").val();
         var cmbTriggers 			=  jQuery("#cmbTriggers").val();
         var selectServiceType 		=  jQuery("#cmbServiceTypes").val();
         var txtTriggerDate 		=  jQuery("#datepicker").val();
         var txtTime 				=  jQuery("#txtTime").val();
         var cmbMatter 				=  jQuery("#cmbMatter").val();
         var isTimeRequired 		=  jQuery("#isTimeRequired").val();
         var sort_date 				=  jQuery("#sort_date").val();
         var isServed 				=  jQuery("#isServed").val();
		 var appointment_length		=  jQuery("#appointment_length").val();
		 jQuery("#hidden_appointment_length").val(appointment_length);

         if(txtTime != '') {
             txtTime_arr = txtTime.split(':');
             txtTime_arr_2 = txtTime_arr[1].split(' ');
             txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
         }

         if(txtTime == '' || txtTime == '00:00:00')
         {

                jQuery("#show_msg_time").html("Please enter time");
                jQuery("#txtTime").css('border-color', 'red');
                return false;
         }
         else
         {
             jQuery("#show_msg_time").html("");
             jQuery("#txtTime").css('border-color', '');
         }
         if(selectServiceType == '')
         {
                jQuery("#show_msg_ser").html("Please select service type");
                jQuery("#cmbServiceTypes").css('border-color', 'red');
                return false;
         }  else {
                jQuery("#show_msg_ser").html("");
                jQuery("#cmbServiceTypes").css('border-color', '');
         }
        

         <?php if($totalRows_importEvents > 0) { ?>
             if(cmbMatter == '')
             {
                jQuery("#show_msg").html("Please select case");
                jQuery("#cmbMatter").focus();
                jQuery("#cmbMatter").css('border-color', 'red');
                return false;
             } else {
                jQuery("#show_msg").html("");
                jQuery("#cmbMatter").css('border-color', '');
             }
         <?php } ?>
		
         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":sort_date },
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
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":val },
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
					/*
                    var checkboxes = document.getElementsByTagName("input");
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == "checkbox") {
                            checkboxes[i].checked = true;
                        }
                    }*/
                    jQuery('ul.triggersnav').find('ul').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });
   }

   function tree_view()
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
         var isServed =  jQuery("#isServed").val();


         if(txtTime != '') {
         txtTime_arr = txtTime.split(':');
         txtTime_arr_2 = txtTime_arr[1].split(' ');
         txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
         }

         jQuery("#sort_date").val(1);

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_tree_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":1 },
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

                       jQuery("#asc_link").hide();
                       jQuery("#desc_link").show();

                   } else {
                       jQuery("#button_export").hide();
                   }
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    /*var checkboxes = document.getElementsByTagName("input");
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == "checkbox") {
                            checkboxes[i].checked = true;
                        }
                    }*/
                    jQuery('ul.triggersnav').find('ul').hide();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   jQuery("#button_export").hide();
                }
         });
   }

   function normal_view()
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
         var isServed =  jQuery("#isServed").val();


         if(txtTime != '') {
         txtTime_arr = txtTime.split(':');
         txtTime_arr_2 = txtTime_arr[1].split(' ');
         txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
         }

         jQuery("#sort_date").val(1);

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":1 },
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

                       jQuery("#asc_link").hide();
                       jQuery("#desc_link").show();

                   } else {
                       jQuery("#button_export").hide();
                   }
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
					/*
                    var checkboxes = document.getElementsByTagName("input");
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == "checkbox") {
                            checkboxes[i].checked = false;
                        }
                    }*/
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
  <script src="jquery/js/jquery.datetimepicker.full.min.js"></script>
  <script>
   jQuery('#txtTime').datetimepicker({
             datepicker:false,
             format:'h:i a',
             formatTime: 'h:i a',
             step:30,
             ampm: true,
             value:'00:00',      
   });
   jQuery('#txtTime').click(function() {
     jQuery('#txtTime').datetimepicker({
             value:'08:00', 
             datepicker:false,
             format:'h:i a',
             formatTime: 'h:i a',
             step:30,
             ampm: true,     
        });
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

 ?>
