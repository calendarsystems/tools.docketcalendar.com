<?php
/* 
Template Name: Docket Calculator Test

*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
function custom_loop() {
 
	require('globals/global_tools.php');
	require('globals/global_courts.php');
	
//	print_r($_SESSION);
	
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
<form id="form1" method="POST" action="docket-calculator-test">
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
                  
<?						$numJuris=sizeof($theTotalJurisdictions);
//							  print_r($theTotalJurisdictions);

 ?>
                      <td>
                          <p> 
                            <select onchange="submit()" name="cmbJurisdictions" id="cmbJurisdictions" >
                              <option value="0">-- select Court --</option>
                              
                              <?php
							  
							  
							  
				if (isset($theTotalJurisdictions['Code'])) {
?>                              <option value="<?php echo $theTotalJurisdictions['SystemID']; ?>" 
        <?php if ($selectJurisdiction==$theTotalJurisdictions['SystemID']) {
										echo 'selected = "selected"';
									} 
								
									if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$theTotalJurisdictions['SystemID']){
										
										echo 'selected = "selected"';
									}
									
									?>
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
        <?php if ($selectJurisdiction==$juris['SystemID']) {
										echo 'selected = "selected"';
									}
								if (!isset($_POST['cmbJurisdictions']) && $masterCourt==$juris['SystemID']){
										
										echo 'selected = "selected"';
									}									
									
									
									 ?>>
                              <?php 	echo $juris['Description']; ?>
                              </option>
                              <?php	
								//}
						//$x=$x+1;
						} 
				}
                        
  ?>   
                            </select></p></td>
                    </tr>
        
      <?php
	  
//	  if ($selectJurisdiction!=0) { 
	  if (1==1) { ?>
                    <tr>
                        <td> 
                        <p>Trigger Item: </p></td>
                        <td>
                          <p>
                            <?php						
						//print_r ($theArray); 
						
						$numTriggers=sizeof($theTriggers);
						if ($numTriggers>0) {?>
                            <select onchange="submit()" name="cmbTriggers" > 
                              <option value="0">-- select Trigger --</option>
                              <?php 
                        
							$x=0;
						
							do { ?>
                              <option <?php if ($theTriggers[$x]['SystemID']==$selectTriggerItem) {
									echo ' selected = "selected" ';
								}?>
 	value="<?php echo $theTriggers[$x]['SystemID']; ?>"
    
     <?php if ($selectTriggerItem==$theTriggers[$x]['SystemID']) {
										echo 'selected = "selected"';
									} ?>>
                              <?php 	
								echo substr($theTriggers[$x]['Description'],0,65); ?>
                              </option> 
                              <?php
							$x=$x+1;
							} while ($x < $numTriggers); 
                    	?>
                            </select>
                            <?php                        
						} else {
							echo "<i>Must select Jurisdiction first.</i>";
						}
  ?>    
                          </p></td>
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


             <input type="time" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime($_POST['txtTime'])); ?>" /> 
             
        </td>
    </tr>
</table>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap"  > 
                        <p>Service Type: </p></td>
                        <td >
                          <p>
                            <?						//print_r ($theArray); 
						
						$numServes=sizeof($theServices);
						if ($isServed=="Y") {
							$x=0;  

							?>
                            <select name="cmbServiceTypes">
                              <br />
                              <option value="0">-- select Service --</option>
                              <?php
            
$servList="---";

foreach ($theServices as $service) {

	if (strpos($servList,$service['Description']) > 0 ) {
	} else {
		$servList=$servList." ".$service['Description'];
	?><option<?php
   		if ($service['SystemID']==$selectServiceType) {
			echo ' selected="selected" ';
		}?> value="<?php echo $service['SystemID']; ?>">
    	<?php echo $service['Description']; ?>
    </option> <?php
	$x=$x+1;
	}
} ?> 
</select> 

<?php
} 
						 else {
							echo '<i>Service type not required</i>'; ?>
                            
                            <input type="hidden" name="cmbServiceTypes" id="cmbServiceTypes" value="0" /> 
                            <?php

						}
  ?>   
                            
                        </p></td>
                    </tr>
                    
            <?php } ?>


<tr>
                        <td><p>Matter:</p></td>
                        <td>
                         <input type="text" name="cmbMatter" id="cmbMatter" value="<?php echo $_POST['cmbMatter'];?>" /> 
</td> 

</tr>

                    <tr>
                        <td><p>&nbsp;</p></td>
                        <td>
<table>
    <tr>
        <td>
                           <?php         if ($numServes>0 || ($numServes==0 && $selectTriggerItem != 0 && $selectJurisdiction != 0)){ ?>
                   <input id="btnCalculate" name="btnCalculate" type="submit"  value="  Calculate  "> <?php } ?>
        </td>
        <td>
        
        <?php if ($_POST['btnCalculate'] != '' || $_POST['btnExport'] != '') { ?>
       <input id="btnExport" name="btnExport" type="submit" value="  Export  "  />
       <input type ="radio" name="radioExportType" value="excel" checked />Excel	   
       <input type ="radio" name="radioExportType" value="vcal" />vCal  
       <input type ="radio" name="radioExportType" value="ical" />iCal  
	   <?php } ?>
        </td>
    </tr>
</table>    
                        </td>
                    </tr></table>
                      
            </td>
        </tr> 
                  
    </table>  
    <table width="100%"><tr>
                      <td colspan="2"><div class="divider"></div> <?php if ($_POST['btnCalculate'] != '' || $_POST['btnExport'] != '') { ?>
                <h4>
                
                
                
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
				
		
	$x=0;
	$single=$response;
				
	//			echo '<pre>';
	//	print_r($response);		
	//							echo '</pre>';
	if (isset($single['Action'])) {
		$numresults=1;	
	} else {
		$numresults=sizeof($response);
	}?>
                
     RESULTS (<?php echo $numresults;?>)
                                     
                </h4> <table class="triggers">
<?php	

	if (isset($single['Action'])) {
	
	echo "<tr><td valign=\"top\"><div>";

								
		$justdate=substr($response[CalendarRuleEvent][EventDate],0,10);
		$mo=substr($justdate,5,2);
			?>
    <ul class="triggersnav">
    	<li>
        	<a href="javascript:void"> <?php

		echo $montharray[$mo]." ".substr($justdate,8,2).", ".substr($justdate,0,4) ." - ";

		if ($_POST['cmbMatter'] != "" ) {
			echo "(".$_POST['cmbMatter'].") ";
		} 

		echo $response[CalendarRuleEvent][ShortName]." - ";

					if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
						echo $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
					} else {
						foreach($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
							echo $Rule[RuleText]." ";
							}		
					}

		?> </a> <?php

				if (isset($response[CalendarRuleEvent][CourtRules][Rule][RuleText])) {	
				// IF THERE IS ONE RULE ?>
				<ul class="triggersnav">
    				<li>
        				<a href="javascript:void"> <?php
								//echo "one rule";
							echo "Court Rule: ".$response[CalendarRuleEvent][CourtRules][Rule][RuleText];
							
						?>  <?php
				} else {

				// IF THERE ARE MANY RULES
					foreach( $response[CalendarRuleEvent][CourtRules][Rule] as $rule) {?>
			    <ul class="triggersnav">
    				<li>
        				<a href="javascript:void"> <?php			
								echo "Court Rule: ".$rule[RuleText];
								echo "</ul>";
						?>  <?php
                                }
				}
	echo "</ul></ul>";
	} else { 
		
	// IF THERE ARE MULTIPLE EVENTS 
	
echo "<tr><td valign=\"top\"><div>";



foreach ($response as $Event) { ?>
	<?php

	$justdate=substr($Event[CalendarRuleEvent][EventDate],0,10);
	$mo=substr($justdate,5,2);
	?>
    <ul class="triggersnav">
    	<li>
        	<a href="javascript:void"> <?php
				echo $montharray[$mo]." ".substr($justdate,8,2).", ".substr($justdate,0,4). " - " ;

		if ($_POST['cmbMatter'] != "" ) {
			echo "(".$_POST['cmbMatter'].") ";
		} 

				echo $Event[CalendarRuleEvent][ShortName]. " - ";
	
	
	
//				echo $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
	
	
					if (isset($Event[CalendarRuleEvent][DateRules][Rule][RuleText])) {
						echo $Event[CalendarRuleEvent][DateRules][Rule][RuleText];
					} else {
						foreach($Event[CalendarRuleEvent][DateRules][Rule] as $Rule) {
							echo $Rule[RuleText]." ";
							}		
					}
		
	
			?> </a> <?php

				if (isset($Event[CalendarRuleEvent][CourtRules][Rule][RuleText])) {	
				// IF THERE IS ONE RULE ?>
				<ul class="triggersnav">
    				<li>
        				<a href="javascript:void"> <?php
								//echo "one rule";
							echo "Court Rule: ".$Event[CalendarRuleEvent][CourtRules][Rule][RuleText];
							
						?>  <?php
				} else {

				// IF THERE ARE MANY RULES
					foreach( $Event[CalendarRuleEvent][CourtRules][Rule] as $rule) {?>
			    <ul class="triggersnav">
    				<li>
        				<a href="javascript:void"> <?php			
								echo "Court Rule: ".$rule[RuleText];
								echo "</ul>";
						?>  <?php
                                }
				}
	echo "</ul></ul>";
}
	echo "</div></td></tr>";

}
?>
                
                
                </table></p> <?php 
				
//												echo $x;
				
											} ?>&nbsp;</td>
                    </tr>
                    
                    <?php } ?>
                </table>
         
</fieldset>
</div>
</form><pre>
<?php if ($_SESSION['userid'] == "bens" ){
	print_r($response);
} ?></pre>
<?php


echo date('Y-m-d H:i:s'). ": the end of template<br />";	
//echo '<pre>';
//print_r($response);		
//echo '</pre>';
}

 genesis(); // <- everything important: make sure to include this. 
 
 ?>