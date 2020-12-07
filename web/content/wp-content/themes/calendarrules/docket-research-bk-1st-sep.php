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


?>
 <h1 class="entry-title" style="float: left;">Docket Research</h1> <div style="float: right;"><a href="date-calculator">Date Calculator</a> | <a href="docket-calculator">Docket Calculator</a> | Docket Research</div>
 <br clear="all" />
 <?php

	
	
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
// 

// check for terms




// get the date

?>



<form id="researchform" method="POST" action="">
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
<?php echo $error; ?>

<fieldset class="grpBox">
<div id="tabs">   <div style="padding:  10px 20px"><table id="table" >
     <tr>
     <td colspan="2" ><h4><h4>
    
	Welcome 
    <?php 
		 if ($_SESSION['parent'] != "N") { ?>
			 		 <a style="color:#FF6600;" href="update-<?php if ($_SESSION['trial']=="Y") { echo "trial"; } else { echo "card"; } ?>"> <?php echo $_SESSION['fullname']; ?></a>  <?php
		 } else { ?>
					<?php echo $_SESSION['fullname']; ?>  <?php
		 } ?>



<span class="userinfo"> (admin) </span>
				  		  	               </h4></td></tr><tr>
       <td><p>Jurisdiction:</p></td>
       <?php					
						
						
						
						$numJuris=sizeof($theTotalJurisdictions);
 ?>
       <td class="style9"><p>
         <select name="cmbJurisdictions" id="cmbJurisdictions" >
           <option value="0">-- select Court --</option>
           <?php
							  
							  
							  				if (isset($theTotalJurisdictions['Code'])) {
?>
           <option value="<?php echo $theTotalJurisdictions['SystemID']; ?>" 
        <?php if ($_POST['cmbJurisdictions']==$theTotalJurisdictions['SystemID']) {
										echo 'selected = "selected"';
									}
										if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$theTotalJurisdictions['SystemID']){
										
										echo 'selected = "selected"';
									} ?>
        >
             <?php 	echo $theTotalJurisdictions['Description']; ?>
             </option>
           <?php	
		
		
				} else {

						foreach($theTotalJurisdictions as $juris)
						
						 {   
						
							
								
						
								
								//if  ($theTotalJurisdictions[$x]['SystemID']==$subbed[$y] ) {
								 ?>
           <option value="<?php echo $juris['SystemID']; ?>" 
                                    <?php if ($_POST['cmbJurisdictions']==$juris['SystemID'] ) {
										echo 'selected = "selected"';
									} 
									if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$juris['SystemID']){
										
										echo 'selected = "selected"';
									}
									
									
									?>
                                    
                                    
                                    >
             <?php 	echo $juris['Description']; ?>
             </option>
           <?php	
								//}
							
					
							//$x=$x+1;
						} 
                        
                        
                              ?>
         </select> 
         <?php 
					
							
				}?>
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
                            <td > 
                              <p>
                              
                              <?php
							 
							  if ($_POST['boxTrigger'].$_POST['boxEvent'].$_POST['boxRule'] == "" ) {
								  $checkall=1; 
								  } ?>
                              
                                <input name="boxTrigger" type="checkbox" value="boxTrigger" <?php if($_POST['boxTrigger'] || $checkall==1) {echo ' checked="checked"'; } ?> />
                                Trigger
                            <input name="boxEvent" type="checkbox" value="boxEvent" <?php if($_POST['boxEvent'] || $checkall==1) {echo ' checked="checked"'; } ?>/>Event
                            <input name="boxRule" type="checkbox" value="boxRule" <?php if($_POST['boxRule'] || $checkall==1) {echo ' checked="checked"'; } ?>/>Rule
                            
                            &nbsp;&nbsp;</p></td>
                            <td><p>Search Term: </p></td>
                            <td>
                              <p>
                                <input name="txtSearch" type="text" value="<?php echo $_POST['txtSearch'];  ?>" size="35" />
                            <?php echo $txtSearchErrormsg; ?></p></td>
                            <td > <p>
                                <input name="btnSearchText" type="submit" value="Find" id="searchtextbutton" />
                          </p></td>
                        </tr>
                                                 
            </table>
   <div class="searchTextResults">
                        
<table class="triggers" width="100%"><tr><td><div class="divider"></div><h4>Results</h4></td></tr>
                        <?php echo "<pre>";
                      //  print_r($theSearchResults);
                        echo "</pre>";
 	if (isset($theSearchResults[tmp_sys_id])  ) {
								echo '<tr><td><div> ';
//echo "SINGLE ITEM!";
									
								
								echo '<ul class="triggersnav"><a target="_top" class="docketcalclink" href="docket-calculator?cmbJurisdictions='.$_POST[cmbJurisdictions].'&cmbTriggers='.$theSearchResults[trg_item_sys_id].'">use in docket calculator &gt;</a><li><a href="javascript:void"> '.highlight($theSearchResults[trg_item_desc],$_POST['txtSearch']);				

								echo '<ul ><li><a href="javascript:void">'.highlight($theSearchResults[short_name_desc],$_POST['txtSearch']).'<br>';
								echo highlight($theSearchResults[court_rule],$_POST['txtSearch'])."<br>";								
								echo "Date Rule: ".highlight($theSearchResults[date_rule],$_POST['txtSearch']);
								echo '<ul id="courtdesc"><li><a href="javascript:void">'.highlight($theSearchResults[court_rule_desc],$_POST['txtSearch']);
								echo "</a></li></ul></li>  </ul></li> </ul></div></td></tr>";		
								?>
                                
                                <?php
						
								
							} else {
//echo "MULTIPLE ITEMS!".is_array($theSearchResults[0]);
						 						
  							$last="";
  
                           foreach ($theSearchResults as $result) { 									
							if (isset($result['tmp_sys_id'])) {
								if ($result['trg_item_sys_id'] != $last && $last !="") { ?>
								
											</li>
										</ul>
									</div>
								</td>
							</tr>	
							<?php }	
							
							if ($result['trg_item_sys_id'] != $last) { ?>
							
							<tr>
								<td>
									<div>
										<ul class="triggersnav"><a target="_top" class="docketcalclink" href="docket-calculator?cmbJurisdictions=<?php echo $_POST[cmbJurisdictions].'&cmbTriggers='.$result[trg_item_sys_id]; ?>">use in docket calculator &gt;</a>
						
											<li><a href="javascript:void"> <?php echo highlight($result[trg_item_desc],$_POST['txtSearch']); ?>
											 <?php } ?>
												<ul>
													<li> 
														<a href="javascript:void"><?php echo highlight($result[short_name_desc],$_POST['txtSearch']).'<br>'; 
														echo highlight($result[court_rule],$_POST['txtSearch'])."<br>";
														echo "Date Rule: ".highlight($result[date_rule],$_POST['txtSearch']); ?>
														
														<ul id="courtdesc">
															<li>
																<a href="javascript:void"><?php echo highlight($result[court_rule_desc],$_POST['txtSearch']); ?></a>
															</li>
														</ul>
													</li>
												</ul>
											
							
								
                                
                                <?php 		$last=$result[trg_item_sys_id];
								
								
							}
							
						   }
							
						}
                        ?>
                      
          </table></div>
                        
                        
                        
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
                                <input name="btnSearchHoliday" type="submit" value="Find" id="btnSearchHoliday" />
                            </p></td>                            
                        </tr>
                                   <tr><td colspan="5"></td></tr>
                    </table>
                    <div class="searchHolidaysResults">
                                   <table width="100%">
                                   <tr><td colspan="2"><div class="divider"></div></td></tr>
                                   	<tr><td width="20%"><h4>Date</h4></td><td><h4>Holiday</h4></td>
                                   <?php foreach ($theHolidays as $holiday) {
									  ?> 
								     <tr><td><p><?php echo substr($holiday['Date'],0,10); ?></p></td>
									  
									  <td><p>
									    <?php  echo $holiday['Description'];?>
								      </p></td> </tr>
									   
						<?php			   
								   }
                                    ?>
                                   
                                   
                                   
                                   
                                   
                                   
                      </table>
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
          </div>
                    <?php // results of holiday search ?>
                </ContentTemplate>
            </igtab:Tab>
            <igtab:Tab Text="Service Types">
                <ContentTemplate>
                  </div><div id="tabs-3"> 
                   <table width="100%" id="table6" >
                        <tr>
                            <td width="20%"><h2>Service Types </h2></td>
                            <td  align="left">
                                <p>
                                  <input name="btnSearchServices" type="submit" value="Find" <?php if (isset($_POST['btnSearchServices'])) {echo "autofocus";} ?>/>
                            </p></td>
                     </tr>
                        <tr>
                            <td colspan="2"><p>           <?php //print_r($theServices); ?>
                            </p><div class="divider"></div>
                              <table width="100%">
                                   	<tr>
                                   	  <td><h4>Service Type</h4></td><td><h4>Days Type</h4></td><td><h4>Days Count</h4></td>
                               
                                                   

                               
                                   <?php 
								   $servList="---";
								   
								   
								   foreach ($theServices as $service) {
									  
									  if (strpos($servList,$service['Description']) > 0 ) {
										  
										  
										  
									  } else {
										  //echo $servList;
										  $servList=$servList." ".$service['Description'];
									  ?> 
							    <tr><td><p><?php echo $service['Description']; ?></p></td>
									  
									  <td><p>
									    <?php  echo  $service['DaysType'];?>
									    </p></td>

									  <td><p>
									    <?php  echo $service['DaysAdd'];?>
									    </p></td> </tr>
									   
						<?php			   }
								   }
                                    ?>
                                   
                                   
                                   
                                   
                  
                                   
                              </table>
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
 
 ?>