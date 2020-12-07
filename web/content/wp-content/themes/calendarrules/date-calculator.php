<?php
/*
Template Name: Date Calculator
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	require('globals/global_tools.php');
	require('globals/global_courts.php');
	

	// check for login
	if (isset($_SESSION['userid']) && $_SESSION['userid']!="") {
//		echo "woo!";
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
// 

?>

<form  id="form1" method="POST" action="date-calculator" class="widget">
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

<h1 class="entry-title" style="float: left;">Date Calculator</h1> <div style="float: right;">Date Calculator | <a href="https://tools.docketcalendar.com/docket-calculator">Docket Calculator</a> | <a href="https://tools.docketcalendar.com/docket-research">Docket Research</a></div><br clear="all" />
            						  
        
  <?php echo @$error; ?>
        
<fieldset class="grpBox">
    <table>
        <tr>
            <td>
                <table>
                 <tr><td colspan="4"><h4>
    
	Welcome 
    <?php
         if (isset($_SESSION['account_exist']) && $_SESSION['account_exist'] == "Y") {
             $action = $_SESSION['action'];
          ?>
         <a style="color:#851D3E;" target="_blank" href="https://subscribe.docketlaw.com/external/link.php?action=<?php echo $action;?>"><?php echo $_SESSION['fullname']; ?></a>
     <?php
         } else { ?>
                    <?php echo @$_SESSION['fullname']; ?>  <?php
         } ?>



<span class="userinfo"> (admin) </span>
				  		  	               </h4></td></tr>

                    <tr>
                      <td nowrap="nowrap" >
                        <p>
                        <?php
						$numJuris=sizeof($theJurisdictions);
						?>
                      Holiday Set:&nbsp;</p></td>
                      <td>

                        <p><span id="ajax_jurisdiction"><select name="cmbJurisdictions" id="cmbJurisdictions">
                          <option value="0">-- select Court --</option></select>
                          </span>
                      </p></td>
                  </tr>
                                      <tr>
                        <td valign="top" nowrap="nowrap" >
                          <p>Start Date:</p></td>
                        <td><table id="table7">
                              <tr>
                                <td valign = "top" ><p>
                            <input type="text" name="txtTriggerDate" id="datepicker" class="datepicker" size="15" value="<?php
							if (isset($_POST['txtTriggerDate'])) {
							echo $_POST['txtTriggerDate'];
							} else {
							
							echo date("m/d/Y"); } ?>" />
                               &nbsp; <span style="color: red" id="err_msg"></span></p>
                                  <table><tr><td valign="top"><input id="txtUnitCount" name="txtUnitCount" type="text" value="<?php echo @$_POST['txtUnitCount']; ?>" maxlength="5" onchange="OnChangeEventtxtunitcount()" size="5"></td><td valign="top"><input name="rbUnits" type="radio" value="1" <?php if (@$_POST['rbUnits']=="1" || !isset($_POST['rbUnits']) ) { echo "checked";} ?>>Calendar Days<br />
                                    <input name="rbUnits" type="radio" value="5" <?php if(isset($_POST['rbUnits']) && $_POST['rbUnits']=="5") { echo "checked"; }?>>Court Days<br />
                                    <input name="rbUnits" type="radio" value="2" <?php if(isset($_POST['rbUnits']) && $_POST['rbUnits']=="2") { echo "checked";} ?>>Weeks<br />
                                    <input name="rbUnits" type="radio" value="3" <?php if(isset($_POST['rbUnits']) && $_POST['rbUnits']=="3") { echo "checked"; }?>>Months<br />
                                    <input name="rbUnits" type="radio" value="6" <?php if(isset($_POST['rbUnits']) && $_POST['rbUnits']=="6") { echo "checked"; }?>>Years                     <p>&nbsp;   </td>
                                    <td valign="top">
                                      <input name="rbRollDirection" type="radio" value="0"  <?php if (@$_POST['rbRollDirection']=="0" || !isset($_POST['rbRollDirection']) ) { echo "checked"; }?>>No Roll<br>
                                      <input name="rbRollDirection" type="radio" value="2"  <?php if (@$_POST['rbRollDirection']=="2" ) { echo "checked"; }?>>Roll Forward<br>
                                    <input name="rbRollDirection" type="radio" value="1"  <?php if (@$_POST['rbRollDirection']=="1" ) { echo "checked"; }?>>Roll Backward</td>
                                    <td valign="top"><input name="rbDirection" type="radio" value="1"  <?php if (@$_POST['rbDirection']=="1" || !isset($_POST['rbDirection']) ) { echo "checked"; }?>>Count Forward<br>
                                      <input name="rbDirection" type="radio" value="0" <?php if (@$_POST['rbDirection']=="0") { echo "checked"; }?>>Count Backward
                                    </td>
                                    </tr>
                                    <tr>
                                      <td valign="top">&nbsp;</td>
                                      <td colspan="3" valign="top"><table>
                    <tr>
                        <td>
                        <input name="btnCalculate" id="btnCalculate" type="button"  value="  Calculate  ">
                
                        </td>
                        <td>
                            <input name="btnReset" type="button" value="  Reset   "   onclick="clearForm()"></td>


                        <td>  <span id="ajax_result"></span>
                        </td>
                    </tr> 
                </table></td>
                                    </tr>
                                  </table>
                                  
                      </td>
                          </tr> 
                        </table></td>
                  </tr>
                                      
                                      
                </table> 
          </td>
        </tr>
        <tr>
            <td>
                
            <br /><?php //echo $file; ?></td> 
        </tr>
    </table>
</fieldset>


</form>

<!--<script type='text/javascript'><!--
try{igcal_init("txtTriggerDate_DrpPnl_Calendar1",",,,2012,9,1,1,1,9999,12,31,0","txtTriggerDate$DrpPnl$Calendar1,1,1,,%%; #,0,0,0,,,1,0,txtTriggerDate_DrpPnl_Calendar10,txtTriggerDate_DrpPnl_Calendar11,txtTriggerDate_DrpPnl_Calendar12,txtTriggerDate_DrpPnl_Calendar13,txtTriggerDate_DrpPnl_Calendar14,,-1","January,February,March,April,May,June,July,August,September,October,November,December,,,,,,,");}catch(i){window.status="Can't init script for WebCalendar";};try{var otxtTriggerDate=igdc_initDateChooser('txtTriggerDate',['txtTriggerDate',['/web/WebResource.axd?d=ssUdTQp3-iXcsKLOTINTBT2dx-r0ZwsMFWfp9bSR4D-1cRyTdglEJlgaBgxYNSxOdOKl4NME9DKzTcRJaoZyPWoOpUMwuEha53x-KPgGkXvUewPRchs5pmRmsVrJohWrbs13n8hH71n2cWzRvJFtZDMcwlPQ27z6ie_Gkc4Ky5xYZs6qrKg6m9BOb2d5g3ldFYaba0KFlQaSQrzg0jddgQ2&t=634694306866816250','','/web/WebResource.axd?d=Kb67liC5LCnjJ86t_meWPnnzBoR0eJMEpEc9aRTVI2pezYRNYKhzvr9fKSytYsG_bz5wSjjo4k-ykJBovxpEeV5rRMXEQteTISTFIneZtgFc-1ezr5dAdVb3WJqz6F37_WDc9QU47f-aj2w2EWWaWpd682uXppHF4VJqXnSUgIF-tBuVCrKV8I_AFB7o0MSA8JAcRxudbWNFrfKk_dHbAA2&t=634694306866816250','',""],["300","-1","NotSet","LightGrey","0","0"],false,"",true,true,false,0,true,false,[2012,9,19],null,null,"Null",0,true,false,-1],[["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],["January","February","March","April","May","June","July","August","September","October","November","December",""],["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec",""],"M/d/yyyy","dddd, MMMM dd, yyyy","/","M/d/yyyy","MMMM dd","MMMM, yyyy"],["","","","","","","","","","","",""],'txtTriggerDate_DrpPnl_Calendar1');}catch(i){window.status="Can't init script for WebDateChooser";}
//</script>-->

<?php 
//print_r($_SESSION);

    

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

      jQuery("#btnCalculate").click(function(){


         var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
         var txtTriggerDate =  jQuery("#datepicker").val();


         if(jQuery("#txtUnitCount").val() == '')
         {
          jQuery("#err_msg").html("Unit Count is required");
          jQuery("#ajax_result").html("");
          return false;
         } else {
          jQuery("#err_msg").html("");
         }
         var rbUnits = jQuery("input[name='rbUnits']:checked").val();
         var rbRollDirection = jQuery("input[name='rbRollDirection']:checked").val();
         var rbDirection = jQuery("input[name='rbDirection']:checked").val();
         var txtUnitCount =  jQuery("#txtUnitCount").val();
         var txtUnitCountTrim = txtUnitCount.replace(/^\s+|\s+$/g, '');

         jQuery("#ajax_result").show();
         jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_date_result.php",
                type: "post",
                data: { "cmbJurisdictions":cmbJurisdictions,"txtTriggerDate":txtTriggerDate,"txtUnitCount":txtUnitCountTrim,"rbUnits":rbUnits,"rbRollDirection":rbRollDirection,"rbDirection":rbDirection },
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

      function OnChangeEventtxtunitcount()
      {
        if (!IsNumeric(document.getElementById('txtUnitCount').value)) {
          jQuery("#err_msg").html("Supplied value must be numeric");
          jQuery("#ajax_result").html("");
          return false;
        }
      }

      function clearForm()
      {
        jQuery("#txtUnitCount").val("");
        jQuery("#ajax_result").html("");
      }
  </script>
  <?php } ?> 