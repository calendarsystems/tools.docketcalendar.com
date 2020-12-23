<?php
/* 
Template Name: Docket Calculator New
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
    
	require('globals/global_tools.php');
	require('globals/global_courts.php');

	// check for login
	if ($_SESSION['userid']!="") {
		//echo $_SESSION['userid'];
	} 
	else {
		if ($_GET['e']=='99'){ ?>
        	<span class="loginFailed">Login failed</span><BR />
        	<a href="forgot-password">Forgot Username/Password? </a>
       	<?php }
		if ($_GET['e']=='66'){ ?>
        	<span class="loginFailed">Sorry, this ID is not a valid login for this site.</span><BR />
        	<a href="forgot-password">Forgot Username/Password? </a>
        <?php } 
		require ('include/login_block.php');
	}
// print


?>

<form id="form1" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_result_export.php">
<input name="logintoken" type="hidden" value="<?php echo $row_userInfo['sessionID']; ?>" />
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


 <div id = "divContent" runat = "server">
 <h1 class="entry-title" style="float: left;">Docket Calculator</h1> <div style="float: right;"><a href="date-calculator">Date Calculator</a> | Docket Calculator | <a href="docket-research">Docket Research</a></div><br clear="all" /><table>
    <tr>
        <td>
        </td>
        <td><?php echo $error; ?>
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
    <?php 
		 if ($_SESSION['parent'] != "N") { ?>
			 		 <a style="color:#FF6600;" href="update-<?php if ($_SESSION['trial']=="Y") { echo "trial"; } else { echo "card"; } ?>"> <?php echo $_SESSION['fullname']; ?></a>  <?php
		 } else { ?>
					<?php echo $_SESSION['fullname']; ?>  <?php
		 } ?>



<span class="userinfo"> (admin) </span>
				  		  	               </h4></td></tr>
                    <tr>
                        <td>
                          <p>Jurisdiction:</p></td>

                    <td>
                          <p>
                          <span id="ajax_jurisdiction"><select name="cmbJurisdictions" id="cmbJurisdictions">
                          <option value="0">-- select Court --</option></select>
                          </span>
                          </p></td>
                    </tr>

      <?php

//	  if ($selectJurisdiction!=0) {
	  if (1==1) { ?>
                    <tr>
                        <td>
                        <p>Trigger Item: </p></td>
                        <td>
                          <p><span id="ajax_jurisdiction_trigger">Must select Jurisdiction first.</span></p>
                            <div id="ex3" class="modal">
                            </div>
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
            <input type="text" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($_POST['txtTriggerDate'])) { echo $_POST['txtTriggerDate']; } else { echo date("m/d/Y"); } ?>" /> 
        </td>
        <td>
        &nbsp;Time:&nbsp;
        </td>
        <td>


             <input type="text" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime($_POST['txtTime'])); ?>" style="width:60%" />

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


                        </p></td>
                    </tr>
                    
            <?php } ?>


<tr>
                        <td><p>Matter:</p></td>
                        <td>
                         <input type="text" style="width:400px;" name="cmbMatter" id="cmbMatter" value="<?php echo $_POST['cmbMatter'];?>" />
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
                   <input id="btnCalculate" name="btnCalculate" type="button"  value="  Calculate  ">
                    <?php //} ?>
        </td>
        <td>
        <span id="button_export" style="display:none;">
        <?php //if ($_POST['btnCalculate'] != '' || $_POST['btnExport'] != '') { ?>
       <input id="btnExport" name="btnExport" type="submit" value="  Export  "  />
       <input type ="radio" name="radioExportType" value="excel" checked />Excel
       <input type ="radio" name="radioExportType" value="vcal" />vCal
       <input type ="radio" name="radioExportType" value="ical" />iCal
       </span>
	   <?php //} ?>
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
<?php if ($_SESSION['userid'] == "bens" ){
	print_r($response);
} ?></pre>
<?php

}

 genesis(); // <- everything important: make sure to include this. 
 if (isset($_SESSION['userid']))
 {
 ?>

 <script type="text/javascript">
     jQuery("#ajax_jurisdiction").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
      <?php if($_GET['cmbJurisdictions'] != '') { ?>
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

      jQuery("#btnCalculate").click(function(){
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

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_docket_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter },
                success: function (response) {
                   //console.log(response);
                   jQuery("#ajax_result").show();
                   jQuery("#ajax_result").html(response.html);
                   if(response.count > 0) {
                       jQuery("#button_export").show();
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
  </script>
  <link href="jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
  <script src="jquery/js/jquery.datetimepicker.full.min.js"> type="text/javascript"></script>
  <script>
  jQuery('#txtTime').datetimepicker({
            datepicker:false,
            format:'h:i A',
            value:'00:00',
            step:5
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
 <?php } ?>