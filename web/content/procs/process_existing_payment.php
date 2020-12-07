<?php
require_once('../Connections/docketData.php');
session_start();
	ob_start();
// Include AuthnetCIM class. Nothing works without it!
require('../classes/AuthnetCIM.class.php');
require('../classes/register.php');

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



$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


mysqli_select_db($docketData,$database_docketData);
$query_user = sprintf("SELECT * FROM users WHERE sessionID = '". session_id() ."'", GetSQLValueString($colname_user, "text"));
$user =mysqli_query($docketData,$query_user) or die(mysqli_error($docketData));
$row_user = mysqli_fetch_assoc($user);
$totalRows_user = mysqli_num_rows($user);
print_r($row_user);


mysqli_select_db($docketData,$database_docketData);
$query_courts = sprintf("SELECT * FROM cart WHERE courttype <> 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$courts =mysqli_query($docketData,$query_courts) or die(mysqli_error($docketData));
$row_courts = mysqli_fetch_assoc($courts);
$totalRows_courts = mysqli_num_rows($courts);

mysqli_select_db($docketData,$database_docketData);
$query_state = sprintf("SELECT * FROM cart WHERE courttype = 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$state =mysqli_query($docketData,$query_state) or die(mysqli_error($docketData));
$row_state = mysqli_fetch_assoc($state);
$totalRows_state = mysqli_num_rows($state);

// get courts
if ($totalRows_courts > 0){
	do {
		$indCourts = $row_courts['systemid'].','.$indCourts;
	} while ($row_courts = mysqli_fetch_assoc($courts));
}

if ($totalRows_state > 0){
	do {
		$indState = $row_state['systemid'].','.$indState;
	} while ($row_state = mysqli_fetch_assoc($state));
}

echo $indCourts .'<BR>';
echo $indState .'<BR>';
echo 'firm: '. $row_user['firm'] .'<BR>';
echo 'atty count: '. $totalRows_attornys_cart .'<BR><BR>';

// Use try/catch so if an exception is thrown we can catch it and figure out what happened
try
{
	//$random = substr(number_format(time() * rand(),0,'',''),0,10);
		  // register with authorize.net
	//  $expiration =  $_POST['year'].'-'.$_POST['month'];
	  //echo 'ExpirationDate: '. $expiration;
	//  CreateCIM_PaymentProfile($random, $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['firm'], $_POST['billingaddress'], $_POST['city'], $_POST['state'],$_POST['zip'], '', '', $_POST['cardnumber'], $expiration, '', '', '', '', '');
	  //function CreateCIM_PaymentProfile($customer_id, $Email, $FirstName, $LastName, $Company, $Address, $City, $State, $Zip, $Country, $Phone, $CardNumber, $CardExpiration, $ShippingAddress,  $ShippingCity, $ShippingState, $ShippingPostalCode, $ShippingCountry){

	  // send email activation link

    // Create AuthnetCIM object. Set third parameter to "true" for developer account
    // or use the built in constant USE_DEVELOPMENT_SERVER for better readability.
    // $cim = new AuthnetCIM('2SFf97yj', '883Chu6v8T9LAg3t', AuthnetCIM::USE_DEVELOPMENT_SERVER);
	$cim = new AuthnetCIM($Auth_API_Login, $Auth_TransactionKey, AuthnetCIM::USE_PRODUCTION_SERVER);


 	$profile_id 	= $_SESSION['auth_profile_id']; //'5819657';
	$payment_id 	= $_SESSION['auth_payment_id']; //'5084863';
	$shipping_profile_id = $_SESSION['auth_shipping_id']; //'5205665';
	// echo '<BR>profileID: '. $profile_id. '<BR>paymentID:'. $payment_id.'<BR>ShippingID:'.$shipping_profile_id;
    // Create fake transaction information

    $purchase_amount = $_POST['chrgAmount'];

 
    // Process the transaction
    $cim->setParameter('amount', $purchase_amount);
    $cim->setParameter('customerProfileId', $profile_id);
    $cim->setParameter('customerPaymentProfileId', $payment_id);
    $cim->setParameter('customerShippingAddressId', $shipping_profile_id);
//	$cim->setParameter('cardCode', '123');
//	$cim->setParameter('taxAmount','0.00');
//	$cim->setParameter('taxName','Sales Tax');
//	$cim->setParameter('taxDescription','FL Sales Tax');


//  this is where you set the receipt info /////////////////////////////////////////////////////////////////
 
	$cim->setParameter('orderInvoiceNumber', 'CRO');
	$cim->setParameter('orderDescription', 'Add New User/Court');
	$cim->setParameter('orderPurchaseOrderNumber', '12345');
	
	//				  SKU,  Item Name	  , Description, 			  Qty,  Item unit price
    $cim->setLineItem('Calendar Rules', 'Subscription', 'Calendar Rules', '1', $purchase_amount);
    $cim->createCustomerProfileTransaction();

	    // Get the payment profile ID returned from the request
    if ($cim->isSuccessful())
       
	
	
	{
        $approval_code = $cim->getAuthCode();
		$transaction_id = $cim->getTransactionID();
		
		// save user to CRC api
		
					   
///////////////// save sub users to CRC api /////////////////////////////////////////////////////////////////////////
		require '../include/Pest.php';
		$loginToken = $CRCloginToken;
		
		$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
		
		
		echo "<BR><BR>CRCurl: ".$CRCurl;
		echo "<BR><BR>SessionID: ".session_id();
		echo "<BR><BR>password: ".$row_user['userpassword'];
		
		
				
		$userToken = new Pest($CRCurl);
		$userToken = $userToken->get('/users/'.session_id() .'?password='. $row_user['userpassword'] .'&soapREST=SOAP');
		$userToken = new SimpleXMLElement($userToken);
		$ParentLoginToken = $userToken;
		print_r($userToken);
		echo "<BR><BR>ParentLoginToken: ". $ParentLoginToken;
		if ($totalRows_attornys_cart > 0) { // if there are more attorney's
			do {
				
				if (empty($row_attornys_cart['isActive'])){ // if new attorney
				// echo '<BR>NEW: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

					require('../include/inc_add_subuser_to_crc.php'); 
					
					
					include('Mail.php');
        include('Mail/mime.php');
		$host = "smtpout.secureserver.net";
		$username = $Company_Email;
		//$password = $Company_Email_Pass;
 
        // Constructing the email
        $sender = $Company_Email;                              // Your name and email address
        $recipient =  $email;                           // The Recipients name and email address
        $subject = "Welcome to ".$CompanyName;                            // Subject for the email
			//begin of HTML message 
			$html = "Thank you for subscribing to CalendarRules For Outlook, from CalendarRules.com. <BR><BR>
Your user id is:". $login ."<BR><BR>
Your password is: ". $password ."<BR><BR>
 
To download and install the software, please refer to the <a href='http://subscribe.crcrules.com/documentation/calendarrules%20for%20outlook%20installation%20guide.pdf'>Installation Guide</a>.<BR>
Once installed, please read the <a href='http://subscribe.crcrules.com/documentation/calendarrules%20for%20outlook%20user%20guide.pdf'>User Guide</a>.<BR><BR>
 
If you have any additional questions, please send an email to <a href='mailto:support@calendarrules.com'>support@calendarrules.com</a>.<BR><BR>
Thank you,<BR> 
CalendarRules Support"; 
		   //end of message 
        $crlf = "\n";
        $headers = array(
                        'From'          => $sender,
                        'Return-Path'   => $sender,
                        'Subject'       => $subject
                        );
        // Creating the Mime message
        $mime = new Mail_mime($crlf);

        // Setting the body of the email
        $mime->setHTMLBody($html);

        $body = $mime->get();
        $headers = $mime->headers($headers);

        // Sending the email
        $mail =& Mail::factory('smtp',
		  array ('host' => $host,
			'auth' => true,
			'username' => $username,
			'password' => $Company_Email_Pass));
        $mail->send($recipient, $headers, $body);

					
					
					 // update users cart to mark subscribed courts
					  $updateSQL = sprintf("UPDATE cart SET subscribed=%s WHERE sessionid=%s",
								   GetSQLValueString($_SESSION['userid'], "int"),
								   GetSQLValueString(session_id(), "text"));
								   mysqli_select_db($docketData,$database_docketData);
								   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
					// update users attorneys to mark subscribed attorneys
					  $updateSQL = sprintf("UPDATE attorneys SET isActive='1', user_id=%s WHERE sessionid=%s",
								   GetSQLValueString($_SESSION['userid'],"int"),
								   GetSQLValueString(session_id(), "text"));
								   mysqli_select_db($docketData,$database_docketData);
								   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
					
				}else{ // attorney is existing so just update
				
				// echo  ' EXISTING: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

						
				if (empty($row_attornys_cart['username'])) {
					$user = $row_user['username'];
					$pass = $row_user['userpassword'];
				}else{
					$user = $row_attornys_cart['username'];
					$pass = $row_attornys_cart['password'];
				}
						$loginToken = $CRCloginToken;

						$userToken = new Pest($CRCurl);
						$userToken = $userToken->get('/users/'. $user .'?password='. $pass .'&soapREST=SOAP');
						$userToken = new SimpleXMLElement($userToken);
						$userLoginToken = $userToken;
						
						
						// get user
//						$user = new Pest($CRCurl);
//						$user = $user->get('/user?loginToken='.$userLoginToken);
//						$user = new SimpleXMLElement($user);
//						$url = $CRCurl;
						
						
						//set xml <strong class="highlight">request</strong> 35809
						$xml = '
						<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<Comments></Comments>
						<ContactName>'. $row_attornys_cart['name'] .'</ContactName>
						<EMail>'. $row_attornys_cart['email'] .'</EMail>
						<FaxNumber></FaxNumber>
						<Login>'. $row_attornys_cart['username'].'</Login>
						<LoginToken>'. $userLoginToken .'</LoginToken>
						<Name>'. $row_user['firm']  .'</Name>
						<NotifyModCourt>true</NotifyModCourt>
						<NotifyModEvent>false</NotifyModEvent>
						<Password>'.$row_attornys_cart['password'] .'</Password>
						<PhoneNumber></PhoneNumber>
						<Quote>
						<CourtSystems/>
						<CourtTypes/>
						<EndDate>0001-01-01T00:00:00</EndDate>
						<Jurisdictions/>
						<StartDate>0001-01-01T00:00:00</StartDate>
						<Vendor>Test</Vendor>
						<Type/>
						</Quote>
						<SoftwareName></SoftwareName>';
						   
						
						$xml = $xml . '
						  <Subscription>
							<CourtSystems>';
							// add states
							// if just one remove trailing slash
							if ($indState <> ''){
									$states	= $indState;
									if (substr_count($states, ',') < 2){
										$states = str_replace(',','',$states);
									}else{
										// remove trailing comma
										$states = substr($states, 0, strlen($states)-1); 
									}
									$states = explode(",",$states);	
									while(list($key,$value) = each($states)){ 
										$xml = $xml . '<GenericTypeExt><SystemID>'.$value.'</SystemID></GenericTypeExt>';
									} 
							}
						
						$xml = $xml .'</CourtSystems><Jurisdictions>';
							
							// add individual courts // from order
							if ($indCourts <> ''){
									$courts	= $indCourts;
									if (substr_count($courts, ',') < 2){
										$courts = str_replace(',','',$courts);
									}else{
										// remove trailing comma
										$courts = substr($courts, 0, strlen($courts)-1); 
									}
								$courts = explode(",",$courts);	
									while(list($key,$value) = each($courts)){ 
										$xml = $xml . '<Jurisdiction><SystemID>'. $value .'</SystemID></Jurisdiction>';
									} 
								}

							// close out xml
						$xml = $xml . '</Jurisdictions></Subscription>
						<VendorLoginToken>'. $ParentLoginToken .'</VendorLoginToken>
						</User>'; 
						//echo $xml;
						$session = curl_init($CRCurl.'/user');
						curl_setopt ($session, CURLOPT_POST, true);
						curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($session, CURLOPT_HEADER, true);
						curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						
						$response = curl_exec($session);
						//print_r ($response);
						curl_close($session);
						
				} // end if new attorney
			} while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); 
		} // end if more attorney's

		
		  // update users cart to mark subscribed courts
		  $updateSQL = sprintf("UPDATE cart SET subscribed=%s WHERE sessionid=%s",
                       GetSQLValueString($_SESSION['userid'], "int"),
                       GetSQLValueString(session_id(), "text"));
					   mysqli_select_db($docketData,$database_docketData);
  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
    	// update users attorneys to mark subscribed attorneys
		  $updateSQL = sprintf("UPDATE attorneys SET isActive='1', user_id=%s WHERE sessionid=%s",
                       GetSQLValueString($_SESSION['userid'],"int"),
					   GetSQLValueString(session_id(), "text"));
					   mysqli_select_db($docketData,$database_docketData);
  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
		
		
///////////////// end sub users user to CRC api /////////////////////////////////////////////////////////////////////////
		
  		// all set redirect
		header('Location: /order-completed');
    }else{
		
		header('Location: /update-card?cardfailed=1&email='.$_POST['email'].'&firstname='.$_POST['firstname'].'&lastname='.$_POST['lastname'].'&firm='.$_POST['firm'].'&billingaddress='.$_POST['billingaddress'].'&city='.$_POST['city'].'&state='.$_POST['state'].'&zip='.$_POST['zip'].'&username='.$_POST['username'].'&password='.$_POST['password']);
	//echo '<BR>failed';
	}
    // Print the results of the request
   // echo '<b>createCustomerProfileTransactionRequest Response Summary:</b> ' . $cim->getResponseSummary() . '<br />';
//	echo '<b>Approval code:</b>' . $approval_code .'<BR>';
//	echo '<b>Transaction ID:</b>'. $transaction_id;
}
catch (AuthnetCIMException $e)
{
    echo $e;
    echo $cim;
}
?>