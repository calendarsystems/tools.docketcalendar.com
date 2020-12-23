<?php
/* 
Template Name: courts
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	
	
include('Connections/docketData.php');
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];

include('globals/global_courts.php');

if ($_SESSION['userid'] <> '' && isset($_COOKIE['userid'])){
	mysqli_select_db($docketData,$database_docketData);
	$query_cartStateSub = sprintf("SELECT DISTINCT
	cart.id,
	cart.sessionid,
	cart.systemid,
	cart.courttype,
	courts.courtSystem_Description,
	court_pricing.Price
	FROM
	cart
	Inner Join courts ON cart.systemid = courts.courtSystem_SystemID
	Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
	WHERE cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype = 'state'");
	$cartStateSub = mysqli_query($docketData,$query_cartStateSub, $docketData) or die(mysqli_error($docketData));
	$row_cartStateSub = mysqli_fetch_assoc($cartStateSub);
	$totalRows_cartStateSub = mysqli_num_rows($cartStateSub);
	
	mysqli_select_db($docketData,$database_docketData);
	$query_cartSub = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.sessionid = '". $_SESSION['sessionID']."' AND cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype <> 'state'");

	$cartSub = mysqli_query($docketData,$query_cartSub, $docketData) or die(mysqli_error($docketData));
	$row_cartSub = mysqli_fetch_assoc($cartSub);
	$totalRows_cartSub = mysqli_num_rows($cartSub);
    
//echo $totalRows_cartSub;

//echo $query_cartSub;
//print_r($row_cartSub);
//exit();

//echo "states: ";
//print_r($row_States);

}

?>

<table><tr><td>
<?php 
//echo " | ".$totalRows_cartSub;
//echo "giggity ";
//print_r($_SESSION);
// print_r($_GET);
 ?>
 </td></tr></table>
 <?php  if ($_SESSION['fullname'] <> ''){ ?>
<h1>Manage your Subscription</h1> <?php } else { ?>
<h1>Create your Subscription</h1> <?php
}
 ?>


<?php // echo 'state: '. $_SESSION['state']; ?>
<?php // echo 'session ID: '. session_id(); ?>
<table>
  <tr>
  
    <td width="30%" align="left" valign="top" style="padding-right: 20px;"> 
     <?php if ($_SESSION['fullname'] == '') { ?>
    <h3>Trial Subscription</h3>
<p>To create a 14 day trial:</p>
<p>1. <strong>Add one user</strong> (Click plus sign in cart)</p>
<p>2. <strong>Add one court </strong>(Click plus sign next to court of your choice)</p>
<p>3. <strong>Click check out </strong><br />
</p> 
<?php } ?>
<h3>Paid Subscription</h3>
<p>Add as many courts and users as you want, and click checkout. The first month is pro-rated down based on the days remaining in the month, with a renewal on the first of subsequent months. </p><p>The original web user is considered the <strong>admin</strong>  and can modify the cart. Additional users can install the add-in, and use the  <a href="date-calculator">tools</a> but cannot modify the cart. For a full explanation of rates see our <a href="pricing">pricing page</a>. Cancel or modify your subscription anytime, right here. (You don't have to call us to cancel!) </p></td>
    <td align="left" valign="top"><div class="scrolldiv">
        <form id="search" name="search" method="get" action="">
        </label>
        <select name="state" class="stateselector" id="state" onchange="this.form.submit();">
          <option value="United States">United States</option>
          <?php
do {  
?>
          <option value="<?php echo $row_States['State']?>"<?php if (!(strcmp($row_States['State'], $colname_Courts))) {echo "selected=\"selected\"";} ?>><?php echo $row_States['State']?></option>
          <?php
} while ($row_States = mysqli_fetch_assoc($States));
  $rows = mysqli_num_rows($States);
  if($rows > 0) {
      mysql_data_seek($States, 0);
	  $row_States = mysqli_fetch_assoc($States);
  }
?>
        </select>
        </form>
   <img class="stateImage" src="assets/States_DL/<?php echo $row_Courts['courtSystem_Code']; ?>.jpg"  /><br clear="all" /><?php if (number_format($row_SelectedState['RecurringPrice'],2) == 0.00){ ?>
        <h4><?php echo $_GET['state']; ?></h4>
        <?php if ($_GET['state'] == 'United States'){ ?>
        The following US Federal courts are available for individual subscription.
        <?php }else{ ?>
        We currently do not offer rule-sets in this state, however we are constantly adding new jurisdictions. Please contact us if you would like to see this state prioritized as we release rule-sets based on customer demand.<br />
        <?php } ?>
	    <?php }else{ ?>
        <form id="state2" name="state" method="post" action="">
          <input name="court" type="hidden" value="<?php echo $row_Courts['courtSystem_SystemID']; ?>" />
            <?php //echo $totalRows_cartState; 
				  if ($totalRows_cartState < 1){
					   $row_cartState = array("empty");
				  }
				  if ($totalRows_cartStateSub < 1){
					   $row_cartStateSub = array("empty");
				  }
				  if ($totalRows_cartSub < 1){
					   $row_cartSub = array("empty");
				  }
				  //echo $row_Courts['courtSystem_SystemID']. '<BR>';
				  //print_r($row_cartStateSub).'<BR><BR>';
				  //print_r($row_cartSub).'<BR><BR>';
				  if (checkStateCourt($row_Courts['courtSystem_SystemID']) =='no') { ?>
                  <?php  if (in_array($row_Courts['courtSystem_SystemID'], $row_cartSub)){ ?>
                  
                  	  <?php   if ($_SESSION['parent']!="N") { ?>
                  
                   	  <a href="#" title="<?php echo $row_SelectedState['courtSystem_Description']; ?>" class="addStateLink"><img src="assets/icons/add.gif"  /></a> <?php } ?>
				  <?php }else{ ?>   <?php   if ($_SESSION['parent']!="N") { ?>
                  
                   	  <input name="" type="image" src="assets/icons/add.gif" /> <?php } ?>
                  <?php } ?>
             <?php }else{ ?>
            <?php $hasState = 1; ?>
            	  <?php   if ($_SESSION['parent']!="N") { ?>
            <a href="procs/remove_court_fromlist.php?state=<?php echo $colname_Courts; ?>&systemid=<?php echo $row_Courts['courtSystem_SystemID']; ?>"><img src="assets/icons/delete.gif"  align="absmiddle" /></a> <?php } ?>
            <?php } ?>
            <strong><?php echo $row_SelectedState['courtSystem_Description']; ?></label>
          - $<?php echo number_format($row_SelectedState['RecurringPrice'],2); ?>*</strong><br />
  <span class="stateDescription"><p><?php echo $row_SelectedState['StateText']; ?></p></span>
  <input type="hidden" name="MM_insert" value="state" />
        </form>
        <?php } ?>
               	       <?php
//			   if ($totalRows_cart2 < 1){
//                   $row_cart2 = array("empty"); 
//                }
				?>
        <?php //if ($row_Courts['price'] <> '0.00'){ ?>
        <?php do { 	 ?>
           			    <?php if ($row_Courts['price'] <> '0.00'){ ?>
					    <?php if ($title2 <>  $row_Courts['type_Description']){ ?>
                        <br clear="all">
                        <h4><?php echo $row_Courts['type_Description']; ?></h4>
                              
                       <?php } ?>
                      <?php //echo print_r($row_cart2); ?>
                        <form id="select" name="select" method="post" action="">  
                          <?php $title = $row_Courts['type_Description']; ?>
						  <input name="court" type="hidden" value="<?php echo $row_Courts['systemID']; ?>" />
                          <input name="courttype" type="hidden" value="<?php echo $row_Courts['courtSystem_SystemID']; ?>" />
                          <?php if (checkCourt($row_Courts['systemID']) == 'yes'){ ?>
                        <?php   if ($_SESSION['parent']!="N") { ?>   <a href="procs/remove_court_fromlist.php?state=<?php echo$colname_Courts; ?>&systemid=<?php echo $row_Courts['systemID']; ?>"><img src="assets/icons/delete.gif"  align="absmiddle" /></a> <?php } ?>
                          <?php }else{ ?>
                          <?php if ($hasState==1){
							  }else{ ?>
                         <?php   if ($_SESSION['parent']!="N") { ?>  <input name="" type="image" src="assets/icons/add.gif" /><?php } ?>
                          <?php } ?>
                          <?php } ?>
                          <p class="courtlist"><?php echo $row_Courts['description']; ?></p>  
                          <input type="hidden" name="MM_insert" value="select" />
                        </form>
					 
			  <?php
                    $title2 = $row_Courts['type_Description'];
							  }
				   } 
				  while ($row_Courts = mysqli_fetch_assoc($Courts));
				   ?>
        <?php //} ?>

    </div></td>
    <td align="left" valign="bottom" class="sidebar"><?php 
			  // if user logged in show current subscriptions
			  
			  
			  if ($_SESSION['userid'] <> ''){ ?>
                <div class="widget">
                <table class="widget-wrap" width>
                      <tr>
                        <td class="yourcart"><?php if ($_SESSION['fullname'] <> ''){ ?>
      	        
      	          <h4>Welcome 
				  <?php if ($_SESSION['parent'] != "N" ) { ?>
							<a style="color:#FF6600;" href="update-card"><?php echo $_SESSION['fullname']."</a>!! (admin) ";
							} else { ?>
                      	<?php echo $_SESSION['fullname']."!" ;  
						}
			  	 ?>
              </h4><br clear="all" />
			  <?php 
			  
			
			  
			  
			  
			  
			  } else{ ?>
<a href="<?php //echo $SSLDomain; ?>/login">Login</a><br clear="all" />
<?php } ?><h4>Your current subscriptions</h4></td>
                        <td class="yourcart">&nbsp;</td>
                      </tr>
                      <tr>
                        <td><div class="divider"></div><strong>Users (<?php echo $totalRows_attornys_sub; ?>)</strong></td>
                        <td></td>
                      </tr>
      	             <?php if ($totalRows_attornys_sub > 0) { // Show if recordset not empty ?>
					 <?php do { ?> <tr>
                       
  <td><?php echo $row_attornys_sub['name']; ?></td>
                         <td>  <?php   
						 if ($_SESSION['parent']!="N") { 
						 	if ($_SESSION['fullname']!=$row_attornys_sub['name']) {?> <a class="confirmLink" title="user 
							<?php echo $row_attornys_sub['name']; ?>" href="procs/remove_subscribed_attorney.php?id=<?php echo $row_attornys_sub['attorneyID']; ?>"><img src="assets/icons/delete.gif" width="16" height="16" border="0" /></a><?php 
							} 
 						}
						 
						 
						 ?>
                         
                         
                         </td>
                         
                     </tr><?php } while ($row_attornys_sub = mysqli_fetch_assoc($attornys_sub)); ?><?php } // Show if recordset not empty ?>
                      <tr>
                        <td><div class="divider"></div><strong>States  (<?php echo $totalRows_cartStateSub; ?>)</strong></td>
                        <td></td>
                      </tr>
                      <?php if ($totalRows_cartStateSub > 0) { // Show if recordset not empty ?>
                      <?php
						$statePrice = 0;
						 do { ?>
                      <tr>
                        <td><?php echo $row_cartStateSub['courtSystem_Description']; ?></td>
                        <td>  <?php   if ($_SESSION['parent']!="N") { ?> <a class="confirmLink" title="<?php echo $row_cartStateSub['courtSystem_Description']; ?>"  href="procs/remove_subscribed_court.php?id=<?php echo $row_cartStateSub['id']; ?>&amp;state=<?php echo $colname_Courts; ?>"><img src="assets/icons/delete.gif" width="16" height="16"  alt="delete" /></a><?php } ?></td>
                      </tr>
                      <?php $statePrice = $statePrice + $row_cartStateSub['Price']; ?>
                      <?php } while ($row_cartStateSub = mysqli_fetch_assoc($cartStateSub)); ?>
                      <?php } // Show if recordset not empty ?>
                      <?php 
					   $state_courts = $totalRows_cartStateSub;
					   if ($state_courts == 1){
							$state_court_cost = $statePrice;   
					   }elseif ($state_courts == 0){
						   $state_court_cost = 0;
					   }else{
							$state_court_cost = ($state_courts - 1) * 19.95 + $statePrice;   
					   }
					   ?>
                      <tr>
                        <td><div class="divider"></div><strong>Courts (<?php echo $totalRows_cartSub; ?>)</strong></td>
                        <td>&nbsp;</td>
                      </tr>
                      
   <?php if ($totalRows_cartSub > 0) { // Show if recordset not empty ?>
                      <?php do { ?>
                      <tr>
                        <td><?php echo $row_cartSub['description']; ?></td>
                        <td>  <?php   if ($_SESSION['parent']!="N") { ?> <a class="confirmLink" title="<?php echo $row_cartSub['description']; ?>" href="procs/remove_subscribed_court.php?id=<?php echo $row_cartSub['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" border="0" width="16" height="16" alt="cancel" /></a><?php } ?></td>
                      </tr>
                      <?php } while ($row_cartSub = mysqli_fetch_assoc($cartSub)); ?>
                      <?php } // Show if recordset not empty ?>
                      
                                            <?php 
					   $ind_courts = $totalRows_cartSub;
					   if ($ind_courts < 2){
						    // 1+1 package
							$ind_court_cost = 15.00;   
					   }elseif ($ind_courts >= 2 && $ind_courts <= 5){
						   $ind_court_cost = 50.00;
						}elseif ($ind_courts > 5 && $ind_courts <= 10){
						   $ind_court_cost = 95.00;   
					   }elseif ($ind_courts == 0){
						   $ind_court_cost = 0;
					   }else{
							// $ind_court_cost = ($ind_courts - 1) * 4.95 + 9.95;   
					   }
					  // $mo_cost = $ind_court_cost + $state_court_cost;
					   $mo_cost = $ind_court_cost;

					   ?>

<?php if ($row_userInfo['TrialAccount'] == 1) { ?>
                      <tr>
                        <td><div class="divider"></div>
                      <B>Note: your trial ends <?php echo date('D, M jS, Y', strtotime($row_userInfo['datecreated']. " +14 day")); ?></B><BR /><br /></td><td></td></tr>
                      <?php } ?>
                      

                       <?php include('include/inc_sub_pricing.php'); ?>
                      <tr>
                        <td align="right" class="cartTotal">Current Recurring Total: <strong>$<?php echo number_format($mo_cost, 2);?>/mo</strong></td>
                        <td align="right" class="cartTotal">
                        <?php
					  $updateSQL = sprintf("UPDATE users SET CurrentChargeAmount=%s WHERE id=%s",
                      GetSQLValueString(number_format($mo_cost, 2), "text"),
                      GetSQLValueString($row_userInfo['id'], "int"));
					  mysqli_select_db($docketData,$database_docketData);
					  $Result1 = mysqli_query($docketData,$updateSQL, $docketData) or die(mysqli_error($docketData));
						?>
                        </td>
                      </tr>
                    </table>
                    </div>
                    
                    
                    
                    
                    
                <?php } ?>
<form id="form2" name="form2" method="post" action="">
      	        <div class="widget"><table class="widget-wrap">
      	              <tr>
      	             <td width="80%" class="yourcart"><h4>Your Cart<br /><?php //echo session_id(); ?></h4></td>
      	                <td width="10%" class="yourcart">&nbsp;</td>
                  </tr>
      	              <tr>
					  <?php if ($totalRows_attornys_cart==0) { ?>
      	                <td><div class="divider"></div><strong>No <?php if ($totalRows_attornys_sub > 0) 
						{?>additional <?php } ?>users</strong></td> 
						<?php } else { ?>
                          <td width="10%"><div class="divider"></div><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
                          <?php } ?>

      	                <td>  <?php   if ($_SESSION['parent']!="N") { ?> <img id="create-user" src="assets/icons/add.gif"  alt="Add Attorney" /><?php } ?></td>
    	                </tr>
      	             <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php do { ?> <tr>
                       
  <td><?php echo $row_attornys_cart['name']."<br>"; ?></td>
                         <td width="10%">  <?php   if ($_SESSION['parent']!="N") { ?> <a href="procs/remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"><img src="assets/icons/delete.gif"  alt="delete" /></a><?php } ?></td>               
                     </tr><?php } while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
      	              <tr>
      	                <td><div class="divider"></div><strong>States  (<?php echo $totalRows_cartState; ?>)</strong></td>
      	                <td width="10%">&nbsp;</td>
    	                </tr>
      	               <?php
					   $statePrice = 0;
					    if ($totalRows_cartState > 0) { // Show if recordset not empty ?>
      	                <?php
						 do { ?> <tr>
      	                  <td><div class="divider"></div><?php echo $row_cartState['courtSystem_Description']; ?></td>
      	                  <td width="10%">  <?php   if ($_SESSION['parent']!="N") { ?> <a href="procs/remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/delete.gif" alt="delete" /></a><?php } ?></td>
      	                 
                        </tr> 
                        <?php $statePrice = $statePrice + $row_cartState['Price']; ?>
   	                    <?php } while ($row_cartState = mysqli_fetch_assoc($cartState)); ?>
                        <?php } // Show if recordset not empty ?>
                        
                       <?php 
					   
					   $state_courts = $totalRows_cartState;
					   if ($state_courts == 1){
							$state_court_cost = $statePrice;   
					   }elseif ($state_courts == 0){
						   $state_court_cost = 0;
					   }else{
							$state_court_cost = ($state_courts - 1) * 19.95 + $statePrice;   
					   }
					   ?>
      	                <tr>
      	                <td><div class="divider"></div><strong>Courts (<?php echo $totalRows_cart; ?>)</strong><div class="divider"></div></td>
      	                <td width="10%">&nbsp;</td>
                      
                      </tr>
                        <?php if ($totalRows_cart > 0) { // Show if recordset not empty ?>
      	                <?php do { ?> <tr>
      	             
      	                  <td><?php echo $row_cart['description']; ?></td>
      	                  <td width="10%">  <?php   if ($_SESSION['parent']!="N") { ?> <a href="procs/remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/delete.gif"  alt="delete" /></a><?php } ?></td>
      	                 
                        </tr> 
   	                    <?php } while ($row_cart = mysqli_fetch_assoc($cart)); ?>
                        <?php } // Show if recordset not empty ?>
                        <?php  include('include/inc_pricing.php'); ?>
      	              <tr>
      	                <td align="right" class="cartTotal"><p>&nbsp;</p>Recurring Total:&nbsp; <strong>$<?php echo number_format($new_mo_cost, 2);?>/mo</strong></td>
      	                <td width="10%" align="right" class="cartTotal">&nbsp;</td>
    	                </tr>
      	              <tr>
      	                <td align="right" class="cartTotal">Amount Charged Today:&nbsp;
                        <strong><?php
						// calculate days remaining for month
						if (date('j') > 1){ 
						//echo date("h:m:s",time());
							$daysused = date(d);
							//echo $daysused;
							$daysinmonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
							//echo $daysinmonth;
							$daysleft = $daysinmonth - $daysused;
							if ($daysleft==0) {
								$daysleft=1;
							}
							$current_charge = ($new_mo_cost/$daysinmonth) * $daysleft;
							echo '$'.number_format($current_charge, 2);
						}else{
							$current_charge = $new_mo_cost;
							echo '$'.number_format($new_mo_cost, 2);
						}
						?></strong>
                        </td>
      	                <td width="10%" class="smallText">&nbsp;</td>
    	                </tr>
      	              <tr>
      	                <td colspan="2" class="smallText"><br /><p>Your charge today will be prorated to the number of days remaining in this month. Beginning next month, your card will be charged the "Recurring Total" amount on the 1st of each month.</p>
                          <p class="checkout"><a class="buttonstyle" href="procs/clear_all.php?state=<?php echo $colname_Courts; ?>">Clear All</a>&nbsp;&nbsp;



                      	<?php 	if ($current_charge == 0){
									if ($totalRows_attornys_cart == 1 ||  $totalRows_cart == 1){ 
                            			if ($_SESSION['userid'] <> ''){ ?>
                                			<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/update">Checkout</a>&nbsp;&nbsp;
<?php
                                		} else { ?>
                                   			<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/checkout-trial">Checkout</a>&nbsp;&nbsp;
<?php
                                 		} 
									} else { 
                                    	if ($totalRows_attornys_cart == 0 &&  $totalRows_cart == 0) {
											echo "Add to your cart to Checkout"; 
										} else { ?>
	                                   		<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/checkout">Checkout</a>&nbsp;&nbsp;
<?php
										}
		                           	} 
								} else { 
									if ($_SESSION['userid'] <> ''){
										if (empty($row_userInfo['auth_payment_id'])){ ?>
                                 			<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/checkout-trial-convert">Checkout</a>&nbsp;&nbsp;
<?php
 										} else { ?>
                                     		<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/update">Checkout</a>&nbsp;&nbsp;
<?php 
                                		} 
									} else { ?>
                                 		<a  class="buttonstyle" href="<?php echo $SSLDomain; ?>/checkout">Checkout</a>&nbsp;&nbsp;
<?php
                             		} 
                       			} ?>
<?php // echo " | ".$_SESSION['userid']; ?>
                            
                          </p></td>
      	                </tr>
   	                </table>
                    </div>
                    
   	          </form>              </td>
    </tr>
      	    <tr>
            <td></td>
      	      <td align="left" valign="top">   
      	        
      	        
</td>
<td></td>
   	        </tr>
</table>




<div id="dialog" title="Confirmation Required">
  Cancelling your subscription to: <span id="courtName"></span>  will take place immediately. Your recurring charge will be updated as of the 1st of next month. <BR /><BR />Proceed with cancellation?
</div>
<div id="dialogAlert" title="Alert">
Please cancel individual court subscriptions to <span id="stateName"></span> in order to subscribe to the entire state.
</div>
	<div id="dialog-form" title="Add User">
	<p class="validateTips">All form fields are required.</p>
	<form action="<?php echo $editFormAction; ?>" id="addUserForm" method="post">
		<label for="fullname">Name</label>
		<input type="text" name="fullname" id="fullname" class="text ui-widget-content ui-corner-all" />
		<label for="email">Email <div id="msgbox2"></div></label>
		<input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
        <label for="username">Username <div id="msgbox"></div></label>
		<input type="text" name="username" id="username" class="text ui-widget-content ui-corner-all" />
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
    <input type="hidden" name="MM_insert" value="addUserForm" />
	</form>
</div>



<?php

//print_r($_SESSION);

mysqli_free_result($States);

mysqli_free_result($Courts);

mysqli_free_result($SelectedState);

mysqli_free_result($cart);

mysqli_free_result($stateComments);

mysqli_free_result($userInfo);

mysqli_free_result($attornys_cart);
?>
 
<?php
}

genesis();
?>