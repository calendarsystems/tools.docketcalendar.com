<?php 
/*
Template Name: Docket Calculator New
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
require('globals/global_tools.php');
require('globals/global_courts.php');
session_start();

//$_SESSION['author_id'] = "adamantlawfirm@gmail.com";
unset($_SESSION['docket_search_id']);

    $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
    if(!isset($_SESSION['author_id']))
    {
        $_SESSION['docket_calculator'] = "docket";
    }
    if($_SESSION['spoofsess'] == "")
	{
		 if($_GET['action'] == 'googleAuth'){
		 echo "<script>window.location.href='".$login_url."';</script>";
		}
	}
	if($_SESSION['spoofsess'] == "SPOOF")
	{
		$_SESSION['author_id'] = $_SESSION['email'];
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

 if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
 {
	
$arrayForCaseIdForUserEmail   = array();
	$arrayForCaseIdForAssignEmail = array();
	$arrayForArchiveCase          = array();
	$arrayForCaseId = array();
	
	$getAllCaseIdForUserEmail="SELECT dcu.case_id from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
	$dataCaseIdForUserEmail = mysqli_query($docketDataSubscribe,$getAllCaseIdForUserEmail);
    $totalCaseIdForUserEmail = mysqli_num_rows($dataCaseIdForUserEmail); 
	if($totalCaseIdForUserEmail > 0)
	{
		while($rowCaseIdForUserEmail = mysqli_fetch_assoc($dataCaseIdForUserEmail))
		{
			$arrayForCaseIdForUserEmail[] = $rowCaseIdForUserEmail["case_id"];
		}
		
	}

	$getAllCaseIdForAssignEmail="SELECT dc.case_id from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id != '".$_SESSION['userid']."'  AND dc.created_by != '".$_SESSION['author_id']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
	$dataCaseIdForAssignEmail = mysqli_query($docketDataSubscribe,$getAllCaseIdForAssignEmail);
    $totalCaseIdForAssignEmail = mysqli_num_rows($dataCaseIdForAssignEmail); 
	if($totalCaseIdForAssignEmail > 0)
	{
		while($rowCaseIdForAssignEmail = mysqli_fetch_assoc($dataCaseIdForAssignEmail))
		{
			//$arrayForCaseIdForAssignEmail[] = $rowCaseIdForAssignEmail["case_id"];\
			$arrayForCaseIdForAssignEmailValidate[] = $rowCaseIdForAssignEmail["case_id"];
		}
		
	}
	
	/* NEW CODE FOR ASSIGN CASE START */
	foreach($arrayForCaseIdForAssignEmailValidate as $caseValidateId)
	{
		$getCreatedByEmail = "SELECT created_by from docket_cases WHERE case_id = ".$caseValidateId."";
		$datagetCreatedByEmail = mysqli_query($docketDataSubscribe,$getCreatedByEmail);
		while($rowgetCreatedByEmail = mysqli_fetch_assoc($datagetCreatedByEmail))
		{
			$emailIdoFUser = $rowgetCreatedByEmail["created_by"];
			
			$getUserNameFromSubscribeDocketlaw = "SELECT username from users WHERE email = '".$emailIdoFUser."'";
			$datagetUserNameFromSubscribeDocketlaw = mysqli_query($docketDataSubscribe,$getUserNameFromSubscribeDocketlaw);
			while($rowgetUserNameFromSubscribeDocketlaw = mysqli_fetch_assoc($datagetUserNameFromSubscribeDocketlaw))
			{
					if($rowgetUserNameFromSubscribeDocketlaw['username'] == $_SESSION['username'])
					{
						$arrayForCaseIdForAssignEmail[] = $caseValidateId;
					}
			}
		}
	}
	
	/* NEW CODE FOR ASSIGN CASE END */
	if (!empty($arrayForCaseIdForUserEmail)) {
		if (!empty($arrayForCaseIdForAssignEmail)) 
		{
			$output = array_merge($arrayForCaseIdForUserEmail,$arrayForCaseIdForAssignEmail);
			$output = array_unique($output);
		}else
		{
			$output = array_unique($arrayForCaseIdForUserEmail);
		}
	}
	
	//print_r($output);
	foreach($output as $caseId)
	{
		$arrayForCaseId[] = $caseId;
	}

	$querySelectAllCaseFromArchive = "SELECT caseid FROM docket_cases_archive WHERE userid=".$_SESSION['userid']." AND case_delete = 2";
	$resultAllCaseFromArchive = mysqli_query($docketDataSubscribe,$querySelectAllCaseFromArchive);
    $totalRowsAllCaseFromArchive = mysqli_num_rows($resultAllCaseFromArchive);
	if($totalRowsAllCaseFromArchive > 0)
	{
		 while ($rowDataAllCaseFromArchive = mysqli_fetch_assoc($resultAllCaseFromArchive)) {
			 $arrayForArchiveCase[] = $rowDataAllCaseFromArchive['caseid'];
		 }
		 $result = array_diff($arrayForCaseId,$arrayForArchiveCase);
		 $inClause = implode(",",$result);
		 
	}
	else{
		
		$inClause = implode(",",$arrayForCaseId);
	}
	
	$query_importEvents = "SELECT dc.case_id,dc.case_matter as case_matter,dc.created_by as createdBy,dc.modified_by,dc.created_on as createdOn from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dc.case_id IN (".$inClause.") GROUP BY dc.case_id ORDER BY dc.case_id DESC";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);
 }
 
?>
<style>
 #divform1 { font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;}
    .xdsoft_datetimepicker .xdsoft_timepicker {
     width:70px !important;
   }
.popup-overlay{
  /*Hides pop-up when there is no "active" class*/
  visibility:hidden;
  position:absolute;
  background:#FFF;
  border:3px solid #666666;
  width:268px;
  height:95px%;
  left:36%; 
  top:65%;
  z-index: 15;
}
.popup-overlay.active{
  /*displays pop-up when "active" class is present*/
  visibility:visible;
  text-align:center;
}

.popup-content {
  /*Hides pop-up content when there is no "active" class */
 visibility:hidden;
}

.popup-content.active {
  /*Shows pop-up content when "active" class is present */
  visibility:visible;
}

.CancelPopUp{
  display:inline-block;
  vertical-align:middle;
  border-radius:30px;
  margin:.20rem;
  font-size: 1rem;
  color:#666666;
  background:   #ffffff;
  border:1px solid #666666;  
}

.CancelPopUp:hover{
  border:1px solid #666666;
  background:#666666;
  color:#ffffff;
}
</style>
<!-- <script src="jquery/js/jquery-1.8.3.js"></script> --> 
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
<script src="https://tools.docketcalendar.com/jquery/js/select2.min.js"></script>
<link href="https://tools.docketcalendar.com/jquery/css/select2.min.css" rel="stylesheet" />

<div class="popup-overlay">
	<div class="popup-content">
      <p> This trigger includes a time event . Do you want to set time ?</p>
		<button id="proceedPopUp" class="CancelPopUp">Proceed</button>   
		<button id="okPopUp" class="CancelPopUp">Ok</button> 	
		<button id="cancelPopUp" class="CancelPopUp">Cancel</button> 		
	</div>
</div>
<!--Content shown when popup is not displayed-->
<div id="divform1">
<form id="form1" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_result_import.php" onsubmit="return CheckEvents();">
<input name="userid" type="hidden" value="<?php echo $_SESSION['userid']; ?>" />
<input name="auth_token" type="hidden" value="<?php echo @$_SESSION['access_token']; ?>" />
<script type="text/javascript">
//<![CDATA[
jQuery("#cancelPopUp").on("click", function(){
	jQuery(".popup-overlay, .popup-content").removeClass("active");
});

jQuery("#proceedPopUp").on("click", function(){
	jQuery("#ajax_result").show();
	jQuery(".popup-overlay, .popup-content").removeClass("active");
});

jQuery("#okPopUp").on("click", function(){
	jQuery("#txtTime").focus();
	jQuery(".popup-overlay, .popup-content").removeClass("active");
});


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
 <h1 class="entry-title" style="float: left;">Docket Calculator</h1> <div style="float: right;"><a href="https://tools.docketcalendar.com/date-calculator">Date Calculator</a> | Docket Calculator | <a href="https://tools.docketcalendar.com/docket-research">Docket Research</a></div><br clear="all" /><table>
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
         <a style="color:#851D3E;" target="_blank" href="https://subscribe.docketlaw.com/external/link.php?action=<?php echo $action;?>"><?php echo $_SESSION['fullname']; ?></a>
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
                        <p>Matter Case:</p></td>
                        <td>	
							<?php if($totalRows_importEvents > 0 && $_SESSION['author_id'] != '') { ?>
								<select id="cmbMatter" name="cmbMatter" style="width: 400px;" >
											<option value="">---Select Case---</option>		
								<?php 
								while ($row_events = mysqli_fetch_assoc($ImportEvents))
								{	
								?>
								<option value="<?php echo $row_events['case_id']; ?>" <?php 
								if($_REQUEST['cmbCaseMatter'] == $row_events['case_id'])
								{
									echo "selected";
								}
								?>
								><?php echo $row_events['case_matter']; ?></option>
								<?php 	
								}			
								?>
								</select>&nbsp;<span id="show_msg" style="color:red;"></span>&nbsp;&nbsp;<span style="padding-left:20px;"><a href="https://tools.docketcalendar.com/add-case">Add New Case</a></span>
								<?php } else if($totalRows_importEvents == 0 && $_SESSION['author_id'] != '') { ?>
									   <a href="https://tools.docketcalendar.com/add-case">Please add case</a>
								<?php } else { ?>
									   <a href="<?php echo $login_url;?>">Please login Google Authentication to access cases.</a>
								<?php } ?>
						</td>
                    </tr>			 
                  <tr>
                      <td>
                            <p>Jurisdiction:</p>
                      </td>
                      <td>
                          <p>
                          <span id="ajax_jurisdiction">
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
							<span id="show_msg_trigger" style="color:red;"></span>
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
										<input type="text" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($_POST['txtTriggerDate'])) { echo $_POST['txtTriggerDate']; } else { 
										date_default_timezone_set('America/Los_Angeles');
										echo $timestamp = date("m/d/Y");
									 } ?>" /> 
									</td>
									<td>
									&nbsp;Time:&nbsp; <input type="text" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime(@$_POST['txtTime'])); ?>" style="width:60px;" />&nbsp;&nbsp;<span id="show_msg_time" style="color:red;">
									</td>
									
								</tr>
							</table>
						</td>
                    </tr>
            <?php } ?>


                    <tr>
                    <td nowrap="nowrap"><p>Service Type:</p></td>
                    <td>
						<table style="margin-left:-7px;">
							<tr>
							  <td >
							<p><span id="ajax_trigger_service" style="margin-left:5px;">Service Type not required</span>
                           <input type="hidden" id="hidden_service_type" name="hidden_service_type">
                           &nbsp;&nbsp;<span id="show_msg_ser" style="color:red;"></span>
							</p></td>
								
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
        
        <?php if($totalRows_importEvents > 0 && $_SESSION['author_id'] != '') { ?>
		<td>
            <input id="btnCalculate" name="btnCalculate" type="button"  value="Calculate">
		</td>
		<td>
          <input id="resetForm" name="resetForm" type="button" value="Reset"  />
        </td>
        <?php } ?>
        <td>
        <span id="button_export" style="display:none;">
        <input id="btnImport" name="btnImport" type="submit" value="Import to Calendar"  /> 
        </span>
        </td>
    </tr>
</table>
</form>
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
 jQuery(document).ready(function(){
//jQuery('.chosen').chosen();
 jQuery('#cmbMatter').select2();
 jQuery('#cmbJurisdictions').select2();
 jQuery('#cmbTriggers').select2();
});
 var jq = jQuery.noConflict();
      function CheckEvents(){
        var checked=false;
        var elements = document.getElementsByName("events[]");
        for(var i=0; i < elements.length; i++){
            if(elements[i].checked) {
                checked = true;
            }
        }
        if (!checked) {
            //alert("Please check at least one checkbox.");
			jq.notify("Please check at least one checkbox", {
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
        return true;
      }
      jQuery("#ajax_jurisdiction").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
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

			   jQuery("#ajax_jurisdiction_trigger").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			   jQuery("#ajax_trigger_service").html("Service Type not required");
			   jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction_trigger.php",
					type: "post",
					data: { "cmbJurisdictions":val },
					success: function (response) {
					   if(response.trim() != '')
					   {
						 jQuery("#ajax_jurisdiction_trigger").html(response);
                     <?php if( isset($_GET['cmbTriggers']) && isset($_GET['cmbJurisdictions'])) { ?> 
					 jQuery("#cmbTriggers").val(<?php echo $_GET['cmbTriggers'];?>); 
					 jQuery("#select2-cmbTriggers-container").text(jQuery("#cmbTriggers  option:selected").text());
					 get_trigger_service(<?php echo $_GET['cmbTriggers'];?>,<?php echo $_GET['cmbJurisdictions'];?>); <?php } ?>
					   } else {
						 jQuery("#ajax_jurisdiction_trigger").html("Must select Jurisdiction first.");
					   }
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					}
			   });


			   jQuery("#ex3").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
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
			 jQuery("#show_msg_trigger").html("");
             jQuery("#cmbTriggers").css('border-color', '');
			 var triggerItem = jQuery.trim(jQuery('#cmbTriggers').find('option:selected').text());
			 jQuery("#hidden_trigger_item").val(triggerItem);
			 jQuery("#ajax_trigger_service").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
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
		function hiddenSetValues()
		{
		
					var array = [];
					var uncheckedarray = [];
					
					var checkboxes = document.querySelectorAll('input[type=checkbox]:checked');

					for (var i = 0; i < checkboxes.length; i++) {
						array.push(checkboxes[i].value)
					} 
				 var cmbJurisdictions 		=  jQuery("#cmbJurisdictions").val();
				 var JurisdictionsText      =  jQuery('#cmbJurisdictions :selected').text();
				 var cmbTriggers 			=  jQuery("#cmbTriggers").val();
				 var TriggersText           =  jQuery('#cmbTriggers :selected').text();
				 var selectServiceType 		=  jQuery("#cmbServiceTypes").val();
				 var selectServiceText      =  jQuery('#cmbServiceTypes :selected').text();
				 var txtTriggerDate 		=  jQuery("#datepicker").val();
				 var txtTime 				=  jQuery("#txtTime").val();
				 var cmbMatter 				=  jQuery("#cmbMatter").val();
				 var cmbMatterText          =  jQuery('#cmbMatter :selected').text();
				 var isTimeRequired 		=  jQuery("#isTimeRequired").val();
				 var sort_date 				=  jQuery("#sort_date").val();
				 var isServed 				=  jQuery("#isServed").val();

				
				 jQuery("#hidden_cmbJurisdictions_val").val(cmbJurisdictions);
				 jQuery("#hidden_cmbTriggers_val").val(cmbTriggers);
				 jQuery("#hidden_selectServiceType_val").val(selectServiceType);
				 jQuery("#hidden_txtTriggerDate_val").val(txtTriggerDate);
				 
				 jQuery("#hidden_cmbMatter_val").val(cmbMatter);
				 jQuery("#hidden_isTimeRequired_val").val(isTimeRequired);
				 jQuery("#hidden_sort_date_val").val(sort_date);
				 jQuery("#hidden_isServed_val").val(isServed);
				 if(txtTime != '') {
					 txtTime_arr = txtTime.split(':');
					 txtTime_arr_2 = txtTime_arr[1].split(' ');
					 txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
				 }
				 jQuery("#hidden_txtTime_val").val(txtTime);
				 
				 jQuery("#hidden_JurisdictionsText_val").val(JurisdictionsText);
				 jQuery("#hidden_TriggersText_val").val(TriggersText);
				 jQuery("#hidden_selectServiceText_val").val(selectServiceText);
				 jQuery("#hidden_cmbMatterText_val").val(cmbMatterText);
				 jQuery("#hidden_eventarray_val").val(array);
		}		
		function Exlexport()
		   {
			  				
			jq.notify("Data Export to Excel", {
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
		   
		function Icalexport()
			{
					jq.notify("Data Export to ICAL", {
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
					jq.notify("Data Export to OUTLOOK", {
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
				jQuery("#hidden_iCalData").val("");
				jQuery("#hidden_excelData").val("");
				jQuery("#hidden_csvData").val("");
				hiddenSetValues();   
				jQuery("#exportToExcelForm").submit();
			   
			}	
			function CSVexport()
		{
			  				
				jq.notify("Data Export to CSV", {
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
		
         <?php 
		  if($_SESSION['CheckAccess']!="NoGmail")
		  {
			  
			 if($_SESSION['access_token'] == '') { ?>
				   alert("Session has expired in your browser, Please once again login.");
				   window.location.href='<?php echo $login_url;?>';
				   
			 <?php 
			 }
		 } 
		 ?>
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
		 
		
		  if(cmbTriggers === undefined)
		  {
			  jq.notify("Please select Juri", {
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
        
		if(cmbTriggers == 0)
		{
				jQuery("#show_msg_trigger").html("Please select Trigger");
                jQuery("#cmbTriggers").css('border-color', 'red');
                return false;
		}
		else{
			 jQuery("#show_msg_trigger").html("");
             jQuery("#cmbTriggers").css('border-color', '');
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
         jQuery("#ajax_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
		
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
	 if(ServiceTypes != "")
			{
				jQuery("#show_msg_ser").html("");
			}
     jQuery("#hidden_service_type").val(ServiceTypes);
   }
   function sort_date(val)
   {
	     var sortViewMsg = confirm("Switching to Sort By Date View will check all un-checked events. Do you wish to proceed?");
		 
		if(sortViewMsg == true)
		{
				 jQuery("#ajax_result").show();
			 jQuery("#ajax_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");

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
        
   }

   function tree_view()
   {
		
		var treeViewMsg = confirm("Switching to Tree View will check all un-checked events. Do you wish to proceed?");
		if(treeViewMsg==true)
		{
		 jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");	
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
      
   }
	 jQuery("#cmbMatter").change(function() {
		
				var caseid = jQuery("#cmbMatter").val();
				if(caseid != "")
				{
					jQuery("#show_msg").html("");
				}
			    jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_casedata.php",
                type: "post",
                dataType: "json",
                data: { "caseid":caseid },
                success: function (response) {
                console.log(response);
				 
				
					if(response.caseJurisdiction != 0)
					{
						jQuery("#cmbJurisdictions").val(response.caseJurisdiction);
						get_jurisdictions_trigger(response.caseJurisdiction);
						jQuery("#select2-cmbJurisdictions-container").text(jQuery("#cmbJurisdictions  option:selected").text());
						
					}else{
						jQuery("#select2-cmbJurisdictions-container").text("---Select Court---");
						//jQuery("#select2-cmbTriggers-container").text("Must select Jurisdiction first.");
						jQuery('#cmbTriggers').hide();
						jQuery('#ajax_jurisdiction_trigger').text("Must select Jurisdiction first.");
						jQuery('#cmbTriggers').select2('destroy');
					}
					
				
				
				if(response.caseLocation != null)
				{
					jQuery("#location").val(response.caseLocation);
				}
				else
				{
					jQuery("#location").val('');
				}
				if(response.caseCustomtext != null)
				{				
					jQuery("#custom_text").val(response.caseCustomtext); 
				}
				else
				{
					jQuery("#custom_text").val('');
				}	

                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
         });
		
	   });
	   
	   
   function normal_view()
   {
		var normalViewMsg = confirm("Switching back to Normal View will check all un-checked events. Do you wish to proceed?");
		if(normalViewMsg==true)
		{
			 jQuery("#ajax_result").show();
			 jQuery("#ajax_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");

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
		
   }
	jQuery("#resetForm").click(function(){
	jQuery("#ajax_result").hide();
	jQuery("#cmbMatter").val(0);
	jQuery("#select2-cmbMatter-container").text("---Select Court---");
	jQuery("#cmbJurisdictions").val(0);
	jQuery("#select2-cmbJurisdictions-container").text("---Select Court---");
	jQuery("#cmbTriggers").val(0);
	jQuery("#select2-cmbTriggers-container").text("----Select Trigger---");
	jQuery("#location").val("");
	jQuery("#custom_text").val("");
	jQuery("#cmbServiceTypes").val(0);
    });	
	
	
  </script>
  <link href="https://tools.docketcalendar.com/jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
  <script src="https://tools.docketcalendar.com/jquery/js/jquery.datetimepicker.full.min.js"></script>
  <script>
   jQuery('#txtTime').datetimepicker({
             datepicker:false,
             format:'h:i a',
             formatTime: 'h:i a',
             step:30,
             ampm: true,
             value:'08:00',      
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
  <link href="https://tools.docketcalendar.com/jquery/css/jquery.modal.css" rel="stylesheet" type="text/css">
  <script src="https://tools.docketcalendar.com/jquery/js/jquery.modal.js" type="text/javascript"></script>
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
