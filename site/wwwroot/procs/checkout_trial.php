<?php require_once('Connections/docketData.php'); 
session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	   $docketData = $GLOBALS['docketData'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketData,$theValue) : mysqli_escape_string($docketData,$theValue);

	  switch ($theType) {
		case "text":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;    
		case "long":
		case "int":
		  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
		  break;
		case "double":
		  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
		  break;
		case "date":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;
		case "defined":
		  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
		  break;
	  }
	  return $theValue;
	}
}



//add to cart
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "select")) {
  $insertSQL = sprintf("INSERT INTO cart (sessionid, systemid, courttype) VALUES (%s, %s, %s)",
                       GetSQLValueString(session_id(), "text"),
                       GetSQLValueString($_POST['court'], "int"),
                       GetSQLValueString($_POST['courttype'], "int"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}
// add state to cart
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "state")) {
   // remove any individual courts first for this state.
  $deleteSQL = sprintf("DELETE FROM cart WHERE courttype=%s AND sessionid=%s",
                       GetSQLValueString($_POST['court'], "text"), 
					   GetSQLValueString(session_id(), "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$deleteSQL) or die(mysqli_error($docketData));
  
  $insertSQL = sprintf("INSERT INTO cart (sessionid, systemid, courttype) VALUES (%s, %s, %s)",
                       GetSQLValueString(session_id(), "text"),
                       GetSQLValueString($_POST['court'], "int"),
                       GetSQLValueString('state', "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}

mysqli_select_db($docketData,$database_docketData);
$query_States = "SELECT * FROM court_pricing ORDER BY State ASC";
$States =mysqli_query($docketData,$query_States) or die(mysqli_error($docketData));
$row_States = mysqli_fetch_assoc($States);
$totalRows_States = mysqli_num_rows($States);

$colname_Courts = "-1";
if (isset($_GET['state'])) {
  $colname_Courts = $_GET['state'];
} else {
$colname_Courts = $_SESSION['state'];	
}
mysqli_select_db($docketData,$database_docketData);
$query_Courts = sprintf("SELECT * FROM courts WHERE courtSystem_Description = %s", GetSQLValueString($colname_Courts, "text"));
$Courts =mysqli_query($docketData,$query_Courts) or die(mysqli_error($docketData));
$row_Courts = mysqli_fetch_assoc($Courts);
$totalRows_Courts = mysqli_num_rows($Courts);

$colname_SelectedState = "-1";
if (isset($_GET['state'])) {
  $colname_SelectedState = $_GET['state'];
} else {
  $colname_SelectedState = $_SESSION['state'];
}
mysqli_select_db($docketData,$database_docketData);
$query_SelectedState = sprintf("SELECT
courts.courtid,
courts.code,
courts.courtSystem_Description,
courts.courtSystem_SystemID,
courts.courtSystem_Code,
courts.description,
courts.price,
courts.systemID,
courts.type_Description,
courts.type_SystemID,
court_pricing.Price AS RecurringPrice,
court_pricing.Description AS StateText
FROM
courts
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE courtSystem_Description = %s AND courts.price <> '0.00'
ORDER BY
courts.type_Description ASC
", GetSQLValueString($colname_SelectedState, "text"));
$SelectedState =mysqli_query($docketData,$query_SelectedState) or die(mysqli_error($docketData));
$row_SelectedState = mysqli_fetch_assoc($SelectedState);
$totalRows_SelectedState = mysqli_num_rows($SelectedState);


$colname_stateComments = $colname_Courts;

mysqli_select_db($docketData,$database_docketData);
$query_stateComments = sprintf("SELECT * FROM court_pricing WHERE `State` = %s", GetSQLValueString($colname_stateComments, "text"));
$stateComments =mysqli_query($docketData,$query_stateComments) or die(mysqli_error($docketData));
$row_stateComments = mysqli_fetch_assoc($stateComments);
$totalRows_stateComments = mysqli_num_rows($stateComments);


mysqli_select_db($docketData,$database_docketData);
$query_cartState = sprintf("SELECT DISTINCT
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
WHERE cart.subscribed = '0' AND cart.courttype = 'state' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
$cartState =mysqli_query($docketData,$query_cartState) or die(mysqli_error($docketData));
$row_cartState = mysqli_fetch_assoc($cartState);
$totalRows_cartState = mysqli_num_rows($cartState);

mysqli_select_db($docketData,$database_docketData);
$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.subscribed = '0' AND cart.courttype <> 'state' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
$cart =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
$cart2 =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
$row_cart = mysqli_fetch_assoc($cart);
$row_cart2 = mysqli_fetch_assoc($cart2);
$totalRows_cart = mysqli_num_rows($cart);
$totalRows_cart2 = mysqli_num_rows($cart2);

$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s and isActive is null or sessionid = '". session_id() ."' and isActive is null ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);

function checkCourt($systemID){
			global $database_docketData;
			mysqli_select_db($docketData,$database_docketData);
			$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.courttype <> 'state' AND cart.systemid = '". $systemID ."' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
			$cart =mysqli_query($docketData,$query_cart) or die(mysqli_error($docketData));
			$row_cart = mysqli_fetch_assoc($cart);
    if ($row_cart['id'] != ''){
	return 'yes';
	}else{
	return 'no';
}
}

?>

<?php include('inc_top.php'); ?>

      <div class="middle_blank"> 
      	<div>
      	<h1>Checkout

      	  </h1><table width="100%" border="0" cellpadding="18">
      	    <tr>
      	      <td align="center" valign="top">
              <div class="orderForm">
              <form action="process_trial.php" method="post">
              <p class="formTitles">Order Summary </p>
              <table width="500" border="0" align="center" cellpadding="3">
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	         <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php do { ?> 
                <tr>
                  <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_attornys_cart['name']; ?></td>
      	          <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"></a></td>
    	          </tr>
      	        <tr>
                <?php } while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
      	            <tr>
      	              <td>&nbsp;</td>
      	              <td><strong><br />
      	              States  (<?php echo $totalRows_cartState; ?>)</strong></td>
      	          <td width="14">&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cartState > 0) { // Show if recordset not empty ?>
      	        <?php
						$statePrice = 0;
						 do { ?>
      	        <tr>
      	          <td><a href="remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <?php if ($statePrice < $row_cartState['Price']){
							$statePrice = $row_cartState['Price'];
						}
						?>
      	          <td><?php echo $row_cartState['courtSystem_Description']; ?>
                  <?php $stateIDs = $stateIDs .  $row_cartState['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
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
      	          <td>&nbsp;</td>
      	          <td><strong><br />
      	            Courts (<?php echo $totalRows_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cart > 0) { // Show if recordset not empty ?>
      	        <?php do { ?>
      	        <tr>
      	          <td><a href="remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"><img src="assets/icons/cancl_16.gif" width="16" height="16" border="0" /></a></td>
      	          <td><?php echo $row_cart['description']; ?>
                  <?php $courtIDs = $courtIDs . $row_cart['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cart = mysqli_fetch_assoc($cart)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
   	            </tr>
      	        <?php include('inc_pricing.php'); ?>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">Recurring Total: $<?php echo number_format($mo_cost, 2);?>/mo</td>
      	          <td align="right" class="cartTotal"><span class="smallText">
      	            <input name="CurrentChargeAmount" type="hidden" value="<?php echo number_format($mo_cost, 2);?>" />
      	          </span></td>
   	            </tr>
      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">Amount Charged Today:
      	            <?php
						// calculate days remaining for month
						if (date('j') > 1){
						$daysused = date(d);
							//echo $daysused;
							$daysinmonth = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
							$daysleft = $daysinmonth - $daysused;
							$current_charge = ($mo_cost/$daysinmonth) * $daysleft;
							echo '$'.number_format($current_charge, 2);
						}else{
							echo '$'.number_format($mo_cost, 2);
						}
						?></td>
      	          <td class="smallText"><input name="chrgAmount" type="hidden" value="<?php echo number_format($current_charge, 2); ?>" /></td>
   	            </tr>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">Your charge today will be prorated to the number of days remaining in this month. Beginning next month, your card will be charged the &quot;Recurring Total&quot; amount on the 1st of each month.</td>
      	          <td align="right">&nbsp;</td>
      	          </tr>
   	          </table>
              
              <p><br />
                <img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Create Login</span></p>
              <table width="700" align="center" cellpadding="5">
                <tr>
                  <td colspan="2">Already have an account? <a href="login.php"> Login!</a></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td width="151" height="41"><strong>Username:</strong></td>
                  <td width="243"><label for="firstname"></label>
                    <span id="sprytextfield1">
                    <input name="username" id="username" autocomplete="off" type="text" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span><div id="msgbox"></div>
                    </td>
                  <td width="266">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Password:</strong></td>
                  <td><label for="password"></label>
                    <span id="sprytextfield2">
                    <input name="password" type="text" id="password" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td align="center">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Email:</strong></td>
                  <td><label for="email"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
                    <div id="msgbox2"></div>
                    </td>
                  <td align="center">&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap"><strong>Firm/Practice Name:</strong></td>
                  <td><label for="cvv"></label>
                    <input name="firm" type="text" id="firm" size="40" /></td>
                  <td align="center">&nbsp;</td>
                </tr>
              </table>
              <p><img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Terms and Conditions</span></p><div class="terms">
               DocketLaw\CalendarRules.com Terms of Service and End User License Agreement<br />
  December 2011
  <ol>
    <li>DEFINITIONS</li>
    <ol>
      <li>&ldquo;Software and Services&rdquo; means the DocketLaw Web site, related materials and applications, mobile applications, court rules, and any other capabilities and content provided by DocketLaw.  </li>
      <li>&ldquo;DocketLaw&rdquo; means DocketLaw and its affiliates, including CalendarRules.com which provides the underlying rules content for the Services.  </li>
      <li>&ldquo;Term of Agreement&rdquo; means the period from the date a subscription is purchased through the expiration of the term purchased.</li>
      <li>&ldquo;Subscriber&rdquo; (sometimes referred to as &ldquo;you&rdquo;) means the individual or entity who purchases the subscription under this agreement</li>
      <li>&ldquo;Customer&rdquo; means Subscriber and any entity or individual who uses the Service.<br />
      </li>
    </ol>
  </ol>
  <ol start="2">
    <li>GENERAL TERMS</li>
    <ol>
      <li>By accessing and using the Services, you signify your acceptance of this agreement, without limitation or qualification.</li>
      <li>DocketLaw is not a law firm or attorney, and may not perform services performed by an attorney.  No attorney-client relationship or privilege is created between you and DocketLaw.</li>
      <li>DocketLaw exists solely within the County of Contra Costa in the State of California.  I agree that regardless of where I reside or where my browser or mobile device is physically located, my viewing and use of DocketLaw occurs solely within the County of Contra Costa in the State of California, and that all content and services shall be deemed to be served from, and performed wholly within, Contra Costa County, California, as if I had physically traveled there to obtain such service.  I agree that California law shall govern any disputes arising from my use of this website or service. Disputes shall be resolved through binding arbitration or small claims court as provided for in DocketLaw's Arbitration Agreement, as described later in this agreement.<br />
  </li>
    </ol>
  </ol>
  <ol start="3">
    <li>SUBSCRIPTION TERMS</li>
    <ol>
      <li>As part of this subscription, Subscriber receives a License as described below. DocketLaw will provide updates to the Software and Rules, such as corrections of &quot;bugs&quot; and certain limited improvements to existing functionality of the Software as Docketlaw may choose to provide. </li>
      <li>DocketLaw charges a monthly fee, depending on the number of courts subscribed to for the Subscription.  If you purchase this subscription, you are authorizing DocketLaw to immediately bill your credit card for the first month and to bill your credit card each month thereafter in approximately 30 day intervals.  If for any reason any of our charges for these fees are rejected or refused by your credit card issuer, this Agreement and your subscription and license to Use the Software will automatically terminate without notice. It is your sole responsibility to ensure that payment is made and to notify DocketLaw (via support@docketlaw.com) of any different billing instructions if you cancel or wish to change the credit card for our billing purposes.</li>
      <li>DocketLaw will discontinue billing your credit card for any months after the month in which termination occurs. We will not pro-rate or refund any fees paid for the month in which termination occurs.</li>
      <li>DocketLaw may terminate the subscription and Software License immediately without prior notice for failure to comply with any terms of this Agreement, including Software License terms or Terms of Use. Immediately upon termination, Customer will no longer have any right to Use the Software.</li>
      <li>Subscriber may not assign or transfer this Agreement. Any such attempted assignment or transfer will be null and void. DocketLaw may terminate this Agreement in the event of any such attempted assignment or transfer.</li>
      <li>By accepting this Agreement and purchasing this Subscription, Subscriber represents and warrants that, if a natural person, Subscriber is at least 18 years of age and/or is otherwise legally able to enter into a binding contract.</li>
      <li>Cancellation may be effected by following the procedures posted on our Web Site (www.docketlaw.com) at the time you wish to cancel.</li>
    </ol>
    <li>LICENSE GRANT AND TERMS</li>
    <ol>
      <li>During the Term of Agreement, subject to continuing payment of monthly fees as set forth herein and to compliance with these terms, Subscriber will have a license that entitles a single user to download and Use the Software. If Subscriber allows another individual to download or Use the Software, Subscriber will be liable for compliance with this Agreement, and for any violations by that user of the Terms of Use or Software License.</li>
      <li>The Software is owned, including all Intellectual Property rights, by DocketLaw or by third party suppliers. The Software License confers no title or ownership and is not a sale of any rights in the Software. Customer is granted only the right to Use the Software without right of sublicense.</li>
      <li>Customer must retain all patent, copyright notices and other proprietary legends in or on the original Software. Customer may not remove from the Software, or alter, any of the DocketLaw trademarks, trade names, logos, patent or copyright notices or markings, or add any other notices or markings to the Software. Customer may not copy the Software onto any public or distributed network.</li>
      <li>Customer may not modify, reverse engineer, disassemble, decompile or otherwise attempt to access or determine the source code of the Software or Services, copy, reproduce or distribute the Software or Services in any way in whole or in part or create any derivative work based on the Software. Any use of these materials on any other website or networked computer environment for any purpose is prohibited. The Software and Services are copyrighted and any unauthorized use of them is prohibited. If Customer breaches any of these terms, the License to Use the Software and Services automatically terminates and Customer must immediately destroy any downloaded or printed materials.</li>
      <li>Customer may not export or re-export this software or any copy or adaptation in violation of any applicable laws or regulations.</li>
      <li>Customer agrees that Software contains proprietary information including trade secrets, knowhow and confidential information that is the exclusive property of DocketLaw or its affiliates.  During the period this Agreement is in effect and at all times after its termination, Customer and its employees and agents shall maintain the confidentiality of this information and not sell, license, publish, display, distribute, disclose or otherwise make available this information to any third party nor use such proprietary information concerning the Software, including any flow charts, logic diagrams, user manuals and screens, to persons not an employee of Customer without the prior written consent of DocketLaw.</li>
    </ol>
    <li>DISCLAIMERS AND LIMITATIONS</li>
    <ol>
      <li>DOCKETLAW MAKES NO REPRESENTATIONS ABOUT THE SUITABILITY, RELIABILITY, AVAILABILITY, TIMELINESS, OR ACCURACY OF THE SOFTWARE AND SERVICES PROVIDED.  TO THE EXTENT ALLOWED BY LAW, THE SOFTWARE AND SERVICES AND ACCESS TO OUR SERVER ARE PROVIDED TO YOU &quot;AS IS&quot; WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, WHETHER ORAL OR WRITTEN, EXPRESS OR IMPLIED, DOCKETLAW SPECIFICALLY DISCLAIMS ANY IMPLIED WARRANTIES OR CONDITIONS OF MERCHANTABILITY, SATISFACTORY QUALITY, NON-INFRINGEMENT, TITLE, ACCURACY OF INFORMATIONAL CONTENT, AND FITNESS FOR A PARTICULAR PURPOSE, THE ENTIRE RISK AS TO THE RESULTS AND PERFORMANCE OF THE SOFTWARE IS ASSUMED BY YOU. NO ORAL OR WRITTEN INFORMATION OR ADVICE GIVEN BY DOCKETLAW OR DOCKETLAW&rsquo;SAUTHORIZED REPRESENTATIVES SHALL CREATE A WARRANTY. DOCKETLAW DOES NOT REPRESENT THAT ITS SERVER WILL BE AVAILABLE AT ALL TIMES OR WILL BE FUNCTIONING PROPERLY WHEN YOU OR OTHERS WISH TO ACCESS THE FUNCTIONALITY OF THE SOFTWARE. OUR SERVER IT MAY BE UNAVAILABLE AT TIMES FOR MAINTENANCE OR FOR A VARIETY OF OTHER REASONS. WE ARE NOT LIABLE TO YOU FOR ANY PERIODS OF UNAVAILABILITY AND YOU WILL NOT BE ENTITLED TO ANY REFUNDS FOR THOSE PERIODS WHEN THE SERVER IS NOT AVAILABLE OR IS NOT FUNCTIONING PROPERLY. Some jurisdictions do not allow exclusions of implied warranties or conditions, so the above exclusion may not apply to you to the extent prohibited by applicable laws.</li>
      <li>EXCEPT TO THE EXTENT PROHIBITED BY LAW, IN NO EVENT WILL DOCKETLAW OR ITS SUBSIDIARIES, AFFILIATES, DIRECTORS, OFFICERS, EMPLOYEES, AGENTS, CONTRACTORS OR SUPPLIERS BE LIABLE FOR DIRECT, INDIRECT, SPECIAL, INCIDENTAL, CONSEQUENTIAL, PUNITIVE, OR OTHER DAMAGES (INCLUDING LOST PROFIT, LOST DATA, OR DOWNTIME COSTS), ARISING OUT OF THE USE, INABILITY TO USE, OR THE RESULTS OF USE OF THE SOFTWARE, WHETHER BASED IN WARRANTY, CONTRACT, TORT OR OTHER LEGAL THEORY, AND WHETHER OR NOT DOCKETLAW WAS ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. DOCKETLAW&rsquo;S and its suppliers' entire liability and your exclusive remedy shall be, at Docketlaw&rsquo;s option from time to time exercised subject to applicable law, (a)return of the price paid (if any) for the Product, or (b) repair or replacement of the Product.</li>
      <li>NOTE, EXCEPT TO THE EXTENT ALLOWED BY LOCAL LAW, THESE WARRANTY TERMS DO NOT EXCLUDE, RESTRICT OR MODIFY, AND ARE IN ADDITION TO, THE MANDATORY STATUTORY RIGHTS APPLICABLE TO THE LICENSE OF THE SOFTWARE TO YOU; PROVIDED, HOWEVER, THAT THE CONVENTION ON CONTRACTS FOR THE INTERNATIONAL SALE OF GOODS IS SPECIFICALLY DISCLAIMED AND SHALL NOT GOVERN OR APPLY TO THE SOFTWARE PROVIDED IN CONNECTION WITH THIS WARRANTY STATEMENT.</li>
    </ol>
    <li>LICENSEE OBLIGATIONS </li>
    <ol>
      <li>Definitions. The following definitions shall apply to this Section:</li>
    </ol>
  </ol>
  <br />
  <p dir="ltr">(i) Court Rules shall mean all rules of practice and procedure of any court in any jurisdiction under the laws of the United States, as used in the Services, to calculate dates.</p>
  <p dir="ltr">(ii) Holiday List shall mean all days on which the court or other similar tribunal is closed or does not convene whether in observance of, or pursuant to, its own rules and procedures, Court Rules or local, state or national holidays.</p>
  <ol start="2">
    <ol start="2">
      <li>You agree and understand that use of the Services is strictly prohibited unless you are a licensed attorney, or unless you are directly supervised by a licensed attorney.</li>
      <li>You agree and understand that (1) the court rules and other laws underlying the Services may change from time to time, and (2) that your use of the Services is not intended, nor should it be considered by you, to substitute for your compliance with your professional duties or the use of your professional judgment in reading and interpreting the court rules, which duties include an obligation on your part to do the following:</li>
    </ol>
  </ol>
  <br />
  <br />
  <p dir="ltr">(i) Obtaining a copy of the current applicable Court Rules for the applicable courts and comparing and evaluating the information retrieved by your use of the Services in light of your own information and interpretation about, and of, those Court Rules; and then</p>
  <p dir="ltr">(ii) Obtaining a copy of the current Holiday List for the applicable courts and applying the Court Rules obtained in accordance with Section 6(c)(i) above in light of such Holiday List and further testing the accuracy of the Information.</p>
  <ol>
    <li>YOU AGREE AND UNDERSTAND THAT DOCKETLAW PROVIDES THE SERVICES FOR YOUR CONVENIENCE, AND IN NO EVENT SHALL DOCKETLAW BE LIABLE FOR ANY LOSSES OR EXPENSES INCURRED AS A RESULT OF YOUR FAILURE TO COMPLY WITH YOUR PROFESSIONAL DUTIES OR YOUR FAILURE TO PROPERLY READ, REVIEW AND INTERPRET THE COURT RULES. YOU ACKNOWLEDGE THAT INTERVENING CIRCUMSTANCES MAY ARISE AFTER THE DATE THAT DOCKETLAW PROVIDES YOU SERVICES THAT MAY CAUSE THE SERVICES TO CEASE BEING ACCURATE, AND IN NO EVENT SHALL DOCKETLAW BE LIABLE FOR ANY LOSSES OR EXPENSES INCURRED AS A RESULT OF SUCH INTERVENING CIRCUMSTANCES. </li>
  </ol>
  <br />
  <ol>
    <li>MEMBER ACCOUNT, PASSWORD, AND SECURITY</li>
  </ol>
  <br />
  <br />
  <p dir="ltr">If any of the Services requires you to open an account, you must complete the registration process by providing us with current, complete and accurate information as prompted by the applicable registration form. You also will choose a password and a user name. You are entirely responsible for maintaining the confidentiality of your password and account. Furthermore, you are entirely responsible for any and all activities that occur under your account. You agree to notify DocketLaw immediately of any unauthorized use of your account or any other breach of security.  DocketLaw will not be liable for any loss that you may incur as a result of someone else using your password or account, either with or without your knowledge. However, you could be held liable for losses incurred by DocketLaw or another party due to someone else using your account or password. You may not use anyone else's account at any time, without the permission of the account holder.</p>
  </strong>
  <ol>
    <strong id="internal-source-marker_0.6140183666720986">
      <li>DISPUTE RESOLUTION BY BINDING ARBITRATION <br />
        <br />
        Most customer concerns can be resolved quickly and to the customer's satisfaction by emailing our Customer Care Center at <a href="mailto:support@docketlaw.com">support@docketlaw.com</a>.  In the unlikely event that DocketLaw's Customer Care Center is unable to resolve your complaint to your satisfaction (or if DocketLaw has not been able to resolve a dispute it has with you after attempting to do so informally), we each agree to resolve those disputes through binding arbitration or in small claims court rather than in a court of general jurisdiction. Arbitration is less formal than a lawsuit in court. Arbitration uses a neutral arbitrator instead of a judge or jury, allows for more limited discovery than a court does, and is subject to very limited review by courts. Any arbitration under these Terms will take place on an individual basis; class arbitrations and class actions are not permitted.   The parties will share the costs of arbitration equally.  Moreover, in arbitration the prevailing party may recover attorney's fees. <br />
        <br />
        Arbitration Agreement: <br />
        <br />
        (a) DocketLaw and you agree to arbitrate all disputes and claims between us before a single arbitrator. The types of disputes and claims we agree to arbitrate are intended to be broadly interpreted. It applies, without limitation, to: </li>
      <ul>
        <li>claims arising out of or relating to any aspect of the relationship between us, whether based in contract, tort, statute, fraud, misrepresentation, or any other legal theory;</li>
        <li>claims that arose before these or any prior Terms (including, but not limited to, claims relating to advertising);</li>
        <li>claims that are currently the subject of purported class action litigation in which you are not a member of a certified class; and</li>
        <li>claims that may arise after the termination of these Terms.</li>
      </ul>
      <br />
      <p dir="ltr">For the purposes of this Arbitration Agreement, references to &quot;DocketLaw,&quot; &quot;you,&quot; and &quot;us&quot; include our respective subsidiaries, affiliates, agents, employees, predecessors in interest, successors, and assigns, as well as all authorized or unauthorized users or beneficiaries of services or products under these Terms or any prior agreements between us. <br />
        <br />
        Notwithstanding the foregoing, either party may bring an individual action in small claims court. This arbitration agreement does not preclude your bringing issues to the attention of federal, state, or local agencies. Such agencies can, if the law allows, seek relief against us on your behalf. You agree that, by entering into these Terms, you and DocketLaw are each waiving the right to a trial by jury or to participate in a class action. These Terms evidence a transaction or website use in interstate commerce, and thus the Federal Arbitration Act governs the interpretation and enforcement of this provision. This arbitration provision will survive termination of these Terms. <br />
        <br />
        (b) A party who intends to seek arbitration must first send, by U.S. certified mail, a written Notice of Dispute (&quot;Notice&quot;) to the other party. A Notice to DocketLaw should be addressed to: Notice of Dispute, General Counsel, DocketLaw, 3000F Danville Blvd. #276, Alamo, CA 94507 (the &quot;Notice Address&quot;). The Notice must (a) describe the nature and basis of the claim or dispute and (b) set forth the specific relief sought (&quot;Demand&quot;). If DocketLaw and you do not reach an agreement to resolve the claim within 30 days after the Notice is received, you or DocketLaw may commence an arbitration proceeding. During the arbitration, the amount of any settlement offer made by DocketLaw or you shall not be disclosed to the arbitrator until after the arbitrator determines the amount, if any, to which you or DocketLaw is entitled. <br />
        <br />
        You may download or copy a form to initiate arbitration from the AAA website at<a href="http://www.adr.org/si.asp?id=3477">http://www.adr.org/si.asp?id=3477</a>. (There is a separate form for California residents, also available on the AAA's website at <a href="http://www.adr.org/si.asp?id=3485">http://www.adr.org/si.asp?id=3485</a>). <br />
        <br />
        (c)  The arbitration will be governed by the Commercial Dispute Resolution Procedures and the Supplementary Procedures for Consumer Related Disputes (collectively, the &quot;AAA Rules&quot;) of the American Arbitration Association (the &quot;AAA&quot;), as modified by these Terms, and will be administered by the AAA. The AAA Rules are available online at <a href="http://www.adr.org/">www.adr.org</a> or by calling the AAA at 1-800-778-7879. The arbitrator is bound by these Terms. Unless DocketLaw and you agree otherwise, any arbitration hearings will take place in Contra Costa County.  If your claim is for $10,000 or less, you may choose whether the arbitration will be conducted solely on the basis of documents submitted to the arbitrator, by a telephonic hearing, or by an in-person hearing as established by the AAA Rules. If you choose to proceed either in person or by telephone, we may choose to respond only by telephone or submission. If your claim exceeds $10,000, the AAA Rules will determine whether you have a right to a hearing. Regardless of the manner in which the arbitration is conducted, the arbitrator shall issue a reasoned written decision sufficient to explain the essential findings of fact and conclusions of law on which the award is based. The parties agree that any awards or findings of fact or conclusions of law made in an arbitration of their dispute or claim are made only for the purposes of that arbitration, and may not be used by any other person or entity in any later arbitration of any dispute or claim involving DocketLaw. The parties agree that in any arbitration of a dispute or claim, neither party will rely for preclusive effect on any award or finding of fact or conclusion of law made in any other arbitration of any dispute or claim to which DocketLaw was a party.   The payment of all fees will be governed by the AAA Rules. In such case, you agree to reimburse DocketLaw for all monies previously disbursed by it that are otherwise your obligation to pay under the AAA Rules. In addition, if you initiate an arbitration in which you seek more than $75,000 in damages, the payment of these fees will be governed by the AAA rules. An award may be entered against a party who fails to appear at a duly noticed hearing. <br />
        <br />
        (d) The arbitrator may make rulings and resolve disputes as to the payment and reimbursement of fees, expenses, and the alternative payment and the attorney's fees at any time during the proceeding and upon request from either party made within 14 days of the arbitrator's ruling on the merits. <br />
        <br />
        (e) The arbitrator may award injunctive relief only in favor of the individual party seeking relief and only to the extent necessary to provide relief warranted by that party's individual claim. YOU AND DOCKETLAW AGREE THAT EACH MAY BRING CLAIMS AGAINST THE OTHER ONLY IN YOUR OR ITS INDIVIDUAL CAPACITIES AND NOT AS PLAINTIFFS OR CLASS MEMBERS IN ANY PURPORTED CLASS OR REPRESENTATIVE PROCEEDING OR IN THE CAPACITY OF A PRIVATE ATTORNEY GENERAL. The arbitrator shall not have the power to commit errors of law or legal reasoning, and the parties agree that any injunctive award may be vacated or corrected on appeal by either party to a court of competent jurisdiction for any such error. Each party will bear its own costs and fees on any such appeal. The arbitrator shall not award relief in excess of what these Terms provide or award punitive damages or any other damages not measured by actual damages. Further, unless both you and DocketLaw agree otherwise, the arbitrator may not consolidate more than one person's claims, and may not otherwise preside over any form of a representative or class proceeding. If this specific proviso is found to be unenforceable, then the entirety of this arbitration provision shall be null and void. <br />
        <br />
        (f) All aspects of the arbitration proceeding, and any ruling, decision or award by the arbitrator, will be strictly confidential, other than as part of an appeal to a court of competent jurisdiction. <br />
        <br />
        (g) The Arbitrator, and not any federal, state, or local court or agency, shall have exclusive authority to resolve any dispute relating to the interpretation, applicability, enforceability or formation of this Agreement including, but not limited to, any claim that all or any part of this Agreement is void or voidable. If this specific proviso is found to be unenforceable, it is severable from the rest of the arbitration agreement.</p>
      <ol>
        <ol>
          <li>Disputed Charges. I understand that if I dispute a charge to my credit card, I should send an email to <a href="mailto:support@docketlaw.com">support@docketlaw.com</a> immediately and DocketLaw will investigate the matter.</li>
          <li>Account Information. I agree to notify DocketLaw immediately of any changes to my credit card number, its expiration date, and/or my billing address, or if my credit card expires or is cancelled for any reason. I understand that if my failure to provide DocketLaw with accurate, complete, and current information results in delinquent payments, DocketLaw may restrict my ability to purchase other DocketLaw products, report information about this delinquency to credit bureaus, and/or pursue further collection efforts.</li>
        </ol>
      </ol>
      <ol>
        <li>FORCE MAJEUR</li>
      </ol>
      <br />
      <p dir="ltr"> DocketLaw shall not be considered in breach of or default under these Terms of Service or any contract with you, and shall not be liable to you for any cessation, interruption, or delay in the performance of its obligations hereunder by reason of earthquake, flood, fire, storm, lightning, drought, landslide, hurricane, cyclone, typhoon, tornado, natural disaster, act of God or the public enemy, epidemic, famine or plague, action of a court or public authority, change in law, explosion, war, terrorism, armed conflict, labor strike, lockout, boycott or similar event beyond our reasonable control, whether foreseen or unforeseen (each a &quot;Force Majeure Event&quot;). If a Force Majeure Event continues for more than sixty (60) days in the aggregate, DocketLaw may immediately terminate these Terms of Service and shall have no liability to you for or as a result of any such termination.</p>
      <ol start="2">
        <li>RIGHT TO TERMINATE</li>
      </ol>
      <br />
      <br />
      <p dir="ltr">DocketLaw may terminate, change, suspend or discontinue any aspect of THE SERVICES OR INFORMATION AVAILABLE VIA THIS Website and Mobile Applications, including the availability of any features, at any time. DocketLaw may also impose limits on features and services offered on the Site, or restrict your access to all or any portion of the Website and Mobile Applications, without PRIOR notice AND WITHOUT liability. DocketLaw may terminate any rights granted pursuant to this Agreement at any time, which right shall include the right to credit your charge card for any fees collected and to terminate any license granted to you hereunder, and upon such termination, you shall immediately cease to use and destroy all materials from this Website and Mobile Applications in your possession.</p>
      <ol start="3">
        <li>ACKNOWLEDGEMENT</li>
      </ol>
      <br />
      <p dir="ltr">By proceeding with my purchase, I agree to all the terms above.</p>
    </strong>
  </ol>

                </div></p>
                <span id="sprycheckbox1">
                <input style="margin-left: 600px; " type="checkbox" name="Agree" id="Agree" />
				<label for="Agree">Accept</label><br />
                <span class="checkboxRequiredMsg" style="text-align:right;">Please agree to the terms & conditions.</span></span>
                
              <p><img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
                <span class="formTitles">Payment Information </span></p>
              <table width="543" align="center" cellpadding="5">
                <tr>
                  <td width="171"><strong>First Name</strong></td>
                  <td width="265"><label for="firstname"></label>
                    <span id="sprytextfield4">
                    <input name="firstname" type="text" id="firstname" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td width="59">&nbsp;</td>
                </tr>
                <tr>
                  <td><strong>Last Name</strong></td>
                  <td><label for="lastname"></label>
                    <span id="sprytextfield5">
                    <input name="lastname" type="text" id="lastname" size="50" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap="nowrap">&nbsp;</td>
                  <td colspan="2" align="right"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                </tr>
              </table>
              <input name="stateIDs" type="hidden" value="<?php echo $stateIDs; ?>" />
              <input name="courtIDs" type="hidden" value="<?php echo $courtIDs; ?>" />
              </form>
              </div></td>
   	        </tr>
   	      </table>
    	  
<p>&nbsp;</p>

      	<div style="clear:both;"></div>
      	</div>

      </div><!-- end middle -->
   <?php include('inc_footer.php'); ?>

<?php
mysqli_free_result($States);

mysqli_free_result($Courts);

mysqli_free_result($SelectedState);

mysqli_free_result($cart);

mysqli_free_result($stateComments);
?>
