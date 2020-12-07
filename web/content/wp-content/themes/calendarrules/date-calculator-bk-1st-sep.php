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
	if ($_SESSION['userid']!="") {
//		echo "woo!";
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
 
 



            <h1 class="entry-title" style="float: left;">Date Calculator</h1> <div style="float: right;">Date Calculator | <a href="docket-calculator">Docket Calculator</a> | <a href="docket-research">Docket Research</a></div><br clear="all" />
            						  
        
  <?php echo $error; ?>
        
<fieldset class="grpBox">
    <table>
        <tr>
            <td>
                <table>
                 <tr><td colspan="4"><h4>
    
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
                      <td nowrap="nowrap" >
                        <p>
                        <?php 					
						$numJuris=sizeof($theJurisdictions);
						?>
                      Holiday Set:&nbsp;</p></td>
                      <td>
						
                        <p>

<?php 

//print_r($theJurisdictions);

if (isset ($theJurisdictions['Code'])){
?>	<select name="cmbJurisdictions" id="cmbJurisdictions" >     
                          	<option value="0">-- select Court --</option>  
                           		<option value="<?php echo $theJurisdictions['SystemID']; ?>"
                                    
                                    <?php
								if ($_POST[cmbJurisdictions]== $theJurisdictions['SystemID']) {
									echo 'selected="selected"'; }
									
								if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$theJurisdictions['SystemID']){
									echo 'selected = "selected"';
								} ?>
		                            >
                            			<?php 
									echo $theJurisdictions['Description']; ?>
                            		</option> 
                                        </select>
  <?php 	
	
} else {
	
?>	<select name="cmbJurisdictions" id="cmbJurisdictions" >     
                          	<option value="0">-- select Court --</option>   
                            <?php 
						$x=0;
						
						if ($numJuris > 0) {
						do { 		 ?>
                            		<option value="<?php echo $theJurisdictions[$x]['SystemID']; ?>"
                                    
                                    <?php
								if ($_POST[cmbJurisdictions]== $theJurisdictions[$x]['SystemID']) {
									echo 'selected = "selected"';
								}
								if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$theJurisdictions[$x]['SystemID']){
									echo 'selected = "selected"';
								}
									?>>
                            			<?php 	echo $theJurisdictions[$x]['Description']; ?>
                            		</option>
                            		<?php	
							
							
							$x=$x+1;
						} while ($x < $numJuris);
						}
                        
                              ?>   
                          </select>
  <?php } ?>                        
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
                               &nbsp; <span style="color: red"><?php echo $txtUnitCountErrormsg; ?></span></p>
                                  <table><tr><td valign="top"><input id="txtUnitCount" name="txtUnitCount" type="text" value="<?php echo $_POST['txtUnitCount']; ?>" maxlength="5" onchange="OnChangeEvent(txtUnitCount)" size="5"></td><td valign="top"><input name="rbUnits" type="radio" value="1" <?php if ($_POST['rbUnits']=="1" || !isset($_POST['rbUnits']) ) { echo "checked";} ?>>Calendar Days<br />
                                    <input name="rbUnits" type="radio" value="5" <?php if ($_POST['rbUnits']=="5") { echo "checked"; }?>>Court Days<br />
                                    <input name="rbUnits" type="radio" value="2" <?php if ($_POST['rbUnits']=="2") { echo "checked";} ?>>Weeks<br />
                                    <input name="rbUnits" type="radio" value="3" <?php if ($_POST['rbUnits']=="3") { echo "checked"; }?>>Months<br />
                                    <input name="rbUnits" type="radio" value="6" <?php if ($_POST['rbUnits']=="6") { echo "checked"; }?>>Years                     <p>&nbsp;   </td>
                                    <td valign="top">
                                      <input name="rbRollDirection" type="radio" value="0"  <?php if ($_POST['rbRollDirection']=="0" || !isset($_POST['rbRollDirection']) ) { echo "checked"; }?>>No Roll<br>
                                      <input name="rbRollDirection" type="radio" value="2"  <?php if ($_POST['rbRollDirection']=="2" ) { echo "checked"; }?>>Roll Forward<br>
                                    <input name="rbRollDirection" type="radio" value="1"  <?php if ($_POST['rbRollDirection']=="1" ) { echo "checked"; }?>>Roll Backward</td>
                                    <td valign="top"><input name="rbDirection" type="radio" value="1"  <?php if ($_POST['rbDirection']=="1" || !isset($_POST['rbDirection']) ) { echo "checked"; }?>>Count Forward<br>
                                      <input name="rbDirection" type="radio" value="0" <?php if ($_POST['rbDirection']=="0") { echo "checked"; }?>>Count Backward
                                    </td>
                                    </tr>
                                    <tr>
                                      <td valign="top">&nbsp;</td>
                                      <td colspan="3" valign="top"><table>
                    <tr>
                        <td>
                        <input name="btnCalculate" type="submit"  value="  Calculate  ">
                
                        </td>
                        <td>
                            <input name="btnReset" type="button" value="  Reset   "   onclick="clearForm()"></td>
                        <td>
                        
                        
                        <?php 
						$montharray=array(
"01"=>"January",
"02"=>"February",
"03"=>"March",
"04"=>"April",
"05"=>"May",
"06"=>"June",
"07"=>"July",
"08"=>"August",
"09"=>"September",
"10"=>"October",
"11"=>"November",
"12"=>"December"
); 

$justdate=substr($theFinalDate,0,10);
									$mo=substr($justdate,5,2);

$dayofweek=date("l",strtotime($justdate));

?>
                        <input style="border: none; font-size: 18px; color: #f60;" name="txtCalculatedDate" type="text" value="<?php if (isset($_POST['btnCalculate']) && $txtUnitCountErrormsg=="") { echo $dayofweek.", ". $montharray[$mo]." ".substr($justdate,8,2).", ".substr($justdate,0,4);} ?>" size="30" readonly>
                     
                   
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
 
 ?>