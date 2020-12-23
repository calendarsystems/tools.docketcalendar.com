<?php
/* 
Template Name: Docket Research
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
function custom_loop() {
	require('globals/global_tools.php');
	require('globals/global_courts.php');
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
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
	
?>
 <h1 class="entry-title" style="float: left;">Docket Research</h1> <div style="float: right;"><a href="date-calculator">Date Calculator</a> | <a href="docket-calculator">Docket Calculator</a> | Docket Research</div>
 <br clear="all" />
 <?php
	if (isset($_SESSION['userid']) && $_SESSION['userid']!="") {
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
?>
<form id="researchform" method="POST" action="">
<input name="logintoken" type="hidden" value="<?php echo $row_userInfo['sessionID']; ?>" />
<script type="text/javascript"> 
//<![CDATA[
var theForm = document.forms['form1'];
if (!theForm) {
    theForm = document.form1;
}                 
function __doPostBack(eventTarget, eventArgument) {
    if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
        theForm.__EVENTTARGET.value = eventTarget;
        theForm.__EVENTARGUMENT.value = eventArgument;
        theForm.submit();
    }
}
</script>
<div id = "divContent" runat = "server">
<?php echo @$error; ?>
<fieldset class="grpBox">
<div id="tabs">   <div style="padding:  10px 20px"><table id="table" >
     <tr>
     <td colspan="4" ><h4><h4>
    
	<?php  //echo "<pre>"; print_r($_SESSION);
         if (isset($_SESSION['account_exist']) && $_SESSION['account_exist'] == "Y") {
             $action = $_SESSION['action'];
          ?>
         <a style="color:#FF6600;" target="_blank" href="<?php echo $subscribedoketlaw_url.'/external/shoppingCartui.php?action='.$action. '&eml='.$email_encoded ?>"><?php echo $_SESSION['fullname']; ?></a>  Welcome <span style="text-transform: lowercase;"><small>(<?php echo $_SESSION['author_id']; ?>)</small></span>
     <?php
         } else { ?>
                    <?php echo @$_SESSION['fullname']; ?>  <?php
         } ?>



<span class="userinfo"> (admin) </span>
				  		  	               </h4></td></tr><tr>
       <td><p>Jurisdiction:</p></td>
       <?php $numJuris=sizeof($theTotalJurisdictions); ?>
       <td class="style9"><p>
         <span id="ajax_jurisdiction"><select name="cmbJurisdictions" id="cmbJurisdictions">
                          <option value="0">-- select Court --</option></select>
                          </span>
       </p></td>
     </tr>
   </table></div>

 <ul>
<li><a href="#tabs-1">Triggers</a></li>
<li><a href="#tabs-2">Holidays</a></li>
<li><a href="#tabs-3">Service Types</a></li>
</ul>
<div id="tabs-1">   <h2>Triggers </h2>
 <table ID="table7" >
					
                    <tr>
                            <td>
                            <p>Area: </p></td>
                            <td>
                              <p>

                              <?php

							  if (@$_POST['boxTrigger'].@$_POST['boxEvent'].@$_POST['boxRule'] == "" ) {
								  $checkall=1;
								  } ?>

                                <input name="boxTrigger" type="checkbox" value="boxTrigger" <?php if(@$_POST['boxTrigger'] || $checkall==1) {echo ' checked="checked"'; } ?> />
                                Trigger
                            <input name="boxEvent" type="checkbox" value="boxEvent" <?php if(@$_POST['boxEvent'] || $checkall==1) {/* echo ' checked="checked"'; */} ?>/>Event
                            <input name="boxRule" type="checkbox" value="boxRule" <?php if(@$_POST['boxRule'] || $checkall==1) {/* echo ' checked="checked"'; */ } ?>/>Rule

                            &nbsp;&nbsp;</p>
							</td>
						</tr>
									 <tr>
                        <td>
                        <p>Matter Case:</p></td>
                        <td>	
							
							<select id="cmbMatter" name="cmbMatter" style="width: 400px;" >
								<option value="">---Select Case---</option>
							  <?php while ($row_events = mysqli_fetch_assoc($ImportEvents)) { ?>
								<option value="<?php echo $row_events['case_id']; ?>"><?php echo $row_events['case_matter']; ?></option>
							  <?php } ?>
							</select>
						
						</td>
                    </tr>
						<tr>	
									
                            <td><p>Search Term: </p></td>
                            <td>
                              <p>
                                <input name="txtSearch" id="txtSearch" type="text" value="<?php echo @$_POST['txtSearch'];  ?>" size="46" />
                            </p></td>
                            <td > <p>
                                <input name="btnSearchText" id="btnSearchText" type="button" value="Find" id="searchtextbutton" />
                          </p></td>
						</tr>
					
                    <tr>
						<td colspan="5"><span style="color: red" id="err_msg"></span></td>
					</tr>

            </table>
 <span id="ajax_result"></span>
                        </td></tr>
                    </table>
                      <?php // results of text search ?>
			<script>
                       function HideElement(controlID, comboboxID, buttonID) {
                            var element = document.getElementById(controlID);
                            var dropdownIndex = document.getElementById(comboboxID).selectedIndex;
                            var dropdownValue = document.getElementById(comboboxID)[dropdownIndex].text;

                            if (element != null) {
                                element.style.display = 'none';
                            }
                            document.getElementById(buttonID).disabled = dropdownValue == "";
                        }
                      </script>
        </div>
        <div id="tabs-2" >
        <h2>Holidays</h2>
           <table border="0" cellPadding="0" ID="table5">
                        <tr>
                            <td>
                                <p>Start Date:</p></td>
                            <td>
                                <p>
                                  <input type="text" name="txtStartDate" class="datepicker" id="datepicker" value="<?php 
								
								if (isset($_POST['txtStartDate'])) {
									echo $_POST['txtStartDate']; ?>" /> 
                                  <?php } else {
										echo date("m/d/Y"); ?>" /> 
                                  <?php
									} ?>
                            &nbsp;&nbsp;</p></td>
                            <td ><p>End Date:</p></td>
                            <td><p>
                                  <input type="text" name="txtEndDate" class="datepicker" id="datepicker2" value="<?php 
								
								
								if (isset($_POST['txtStartDate'])) {
									echo $_POST['txtEndDate']; ?>" /> 
                                  <?php } else {
										echo date("m/d/Y",strtotime('+1 year')); ?>" /> 
                                  <?php
									} ?>
                            </p></td>
                            <td><p>
                                <input name="btnSearchHoliday" type="button" value="Find" id="btnSearchHoliday" />
                            </p></td>
                        </tr>
                                   <tr><td colspan="5"><span style="color: red" id="err_msg2"></span></td></tr>
                                   <tr><td colspan="5"></td></tr>
                    </table>
                    <span id="ajax_result2"></span>
                    <?php // results of holiday search ?>
                </ContentTemplate>
            </igtab:Tab>
            <igtab:Tab Text="Service Types">
                <ContentTemplate>
                  </div>


                  <div id="tabs-3">
                   <table width="100%" id="table6" >
                        <tr>
                            <td width="20%"><h2>Service Types </h2></td>
                            <td  align="left">
                                <p>
                                  <input name="btnSearchServices" id="btnSearchServices" type="button" value="Find" <?php if (isset($_POST['btnSearchServices'])) {echo "autofocus";} ?>/>
                            </p></td>
                     </tr>
                      <tr><td colspan="2"><span style="color: red" id="err_msg3"></span></td></tr>
                        <tr>
                            <td colspan="2"><p>           <?php //print_r($theServices); ?>
                            </p><div class="divider"></div>
                            <span id="ajax_result3"></span>
                    </table></div></div>
<?php // results of service type search ?>

</fieldset>
</div>
</form><pre>
<?php
//print_r($theSearchResults);
echo "</pre>";
}
 genesis(); // <- everything important: make sure to include this. 
 if (isset($_SESSION['userid']))
 {
 ?>
 <script type="text/javascript">
      jQuery("#ajax_jurisdiction").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
      jQuery.ajax({
            url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
            type: "post",
            success: function (response) {
               jQuery("#ajax_jurisdiction").html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
      });

      jQuery("#btnSearchText").click(function(){


         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
		 var cmbCaseMatter =  jQuery("#cmbMatter").val();
         var txtSearch =  jQuery("#txtSearch").val();
         if(cmbJurisdictions == 0)
         {
          jQuery("#err_msg").html("Please select Jurisdiction");
          jQuery("#ajax_result").html("");
          return false;
         } else {
          jQuery("#err_msg").html("");
         }
         if(txtSearch == '')
         {
          jQuery("#err_msg").html("Please enter Search Term");
          jQuery("#ajax_result").html("");
          return false;
         } else {
          jQuery("#err_msg").html("");
         }
		 if(cmbCaseMatter == 0)
         {
          jQuery("#err_msg").html("Please select Matter Case");
          jQuery("#ajax_result").html("");
          return false;
         } else {
          jQuery("#err_msg").html("");
         }
         var boxTrigger = jQuery("input[name='boxTrigger']:checked").val();
         var boxEvent = jQuery("input[name='boxEvent']:checked").val();
         var boxRule = jQuery("input[name='boxRule']:checked").val();

         if(boxTrigger == undefined &&  boxEvent == undefined && boxRule == undefined)
         {
          jQuery("#err_msg").html("Please select any one of Area.");
          jQuery("#ajax_result").html("");
          return false;
         } else {
          jQuery("#err_msg").html("");
         }
         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_research_result.php",
                type: "post",
                data: { "cmbJurisdictions":cmbJurisdictions,"txtSearch":txtSearch,"boxTrigger":boxTrigger,"boxEvent":boxEvent,"boxRule":boxRule,"tab":"btnSearchText","cmbCaseMatter":cmbCaseMatter },
                success: function (response) {
                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response);
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    jQuery('ul.triggersnav').find('ul').hide();
                   //jQuery("#button_export").show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
         });

      });
		function get_jurisdictions_trigger(val)
		{

			 jQuery("#button_calc").hide();
			 jQuery("#button_export").hide();
			 jQuery("#ajax_result").hide();
			 
			 var cmbJurisdictions = jQuery("#cmbJurisdictions").find("option:selected").text();

			   jQuery("#ajax_jurisdiction_trigger").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			   jQuery("#ajax_trigger_service").html("Service Type not required");
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
      jQuery("#btnSearchHoliday").click(function(){


         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
         var txtStartDate =  jQuery("#datepicker").val();
         var txtEndDate =  jQuery("#datepicker2").val();

         if(cmbJurisdictions == 0)
         {
          jQuery("#err_msg2").html("Please select Jurisdiction");
          jQuery("#ajax_result2").html("");
          return false;
         } else {
          jQuery("#err_msg2").html("");
         }

         jQuery("#ajax_result2").show();
         jQuery("#ajax_result2").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_research_result.php",
                type: "post",
                data: { "cmbJurisdictions":cmbJurisdictions,"txtStartDate":txtStartDate,"txtEndDate":txtEndDate,"tab":"btnSearchHoliday" },
                success: function (response) {
                   jQuery("#ajax_result2").show();
                   jQuery("#ajax_result2").html(response);
                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    jQuery('ul.triggersnav').find('ul').hide();
                   //jQuery("#button_export").show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
         });

      });

      jQuery("#btnSearchServices").click(function(){


         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();

         if(cmbJurisdictions == 0)
         {
          jQuery("#err_msg3").html("Please select Jurisdiction");
          jQuery("#ajax_result3").html("");
          return false;
         } else {
          jQuery("#err_msg3").html("");
         }

         jQuery("#ajax_result3").show();
         jQuery("#ajax_result3").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_research_result.php",
                type: "post",
                data: { "cmbJurisdictions":cmbJurisdictions,"tab":"btnSearchServices" },
                success: function (response) {
                   jQuery("#ajax_result3").show();
                   jQuery("#ajax_result3").html(response);

                    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
                    jQuery(this).parent().children('ul').slideToggle(250);
                    jQuery(this).parent().children('a').toggleClass("arrowdown");
                    })
                    jQuery('ul.triggersnav').find('ul').hide();
                   //jQuery("#button_export").show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                }
         });
      });
  </script>
   <?php } ?>