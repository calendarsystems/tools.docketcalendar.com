<?php
require_once('Connections/docketData.php');
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

//
//// if the user info is entered only in the checkuot screen then we need to add that user to the attorney db
//

if ($_POST['addattorneyfirst']=="Y") {

  $insertSQL = sprintf("INSERT INTO attorneys (sessionid, user_id, name, email, username, password) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString(session_id(), "text"),
					   GetSQLValueString($_SESSION['userid'], "int"),
                       GetSQLValueString($_POST['firstname']." ".$_POST['lastname'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}








$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s  and isActive is null  or sessionid = '". session_id() ."'  and isActive is null  ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);

try
{

	  $random = substr(number_format(time() * rand(),0,'',''),0,10);
		  // register with authorize.net
	  $expiration =  $_POST['year'].'-'.$_POST['month'];
	  //echo 'ExpirationDate: '. $expiration;
	  CreateCIM_PaymentProfile($random, $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['firm'], $_POST['billingaddress'], $_POST['city'], $_POST['state'],$_POST['zip'], '', '', $_POST['cardnumber'], $expiration, '', '', '', '', '');
	  //function CreateCIM_PaymentProfile($customer_id, $Email, $FirstName, $LastName, $Company, $Address, $City, $State, $Zip, $Country, $Phone, $CardNumber, $CardExpiration, $ShippingAddress,  $ShippingCity, $ShippingState, $ShippingPostalCode, $ShippingCountry){

	  // send email activation link

    // Create AuthnetCIM object. Set third parameter to "true" for developer account
    // or use the built in constant USE_DEVELOPMENT_SERVER for better readability.
    // $cim = new AuthnetCIM('2SFf97yj', '883Chu6v8T9LAg3t', AuthnetCIM::USE_DEVELOPMENT_SERVER);
	$cim = new AuthnetCIM($Auth_API_Login, $Auth_TransactionKey, AuthnetCIM::USE_PRODUCTION_SERVER);


 	$profile_id 	= $profile_id; //'5819657';
	$payment_id 	= $payment_profile_id; //'5084863';
	$shipping_profile_id = $shipping_profile_id; //'5205665';
	//echo '<BR>profileID: '. $profile_id. '<BR>paymentID:'. $payment_id.'<BR>ShippingID:'.$shipping_profile_id;
    // Create fake transaction information

    $purchase_amount = $_POST['chrgAmount'];

 
    // Process the transaction
    $cim->setParameter('amount', $purchase_amount);
    $cim->setParameter('customerProfileId', $profile_id);
    $cim->setParameter('customerPaymentProfileId', $payment_id);
    $cim->setParameter('customerShippingAddressId', $shipping_profile_id);
	$cim->setParameter('cardCode', $_POST['cvv']);
//	$cim->setParameter('taxAmount','0.00');
//	$cim->setParameter('taxName','Sales Tax');
//	$cim->setParameter('taxDescription','FL Sales Tax');

//  this is where you set the receipt info /////////////////////////////////////////////////////////////////
 
	$cim->setParameter('orderInvoiceNumber', 'CRO');
	$cim->setParameter('orderDescription', 'New Paid Account');
	$cim->setParameter('orderPurchaseOrderNumber', '12345');
//	$cim->setParameter('approvalCode', '12345');
	
	//				  SKU,  Item Name	  , Description, 			  Qty,  Item unit price
    $cim->setLineItem('Calendars Rules', 'Subscription', 'Calendar Rules', '1', $purchase_amount);
    $cim->createCustomerProfileTransaction();

	    // Get the payment profile ID returned from the request
    if ($cim->isSuccessful())
    {
        $approval_code = $cim->getAuthCode();
		$transaction_id = $cim->getTransactionID();
		
///////////////// save master user to CRC api /////////////////////////////////////////////////////////////////////////
 include('../include/inc_add_to_crc.php'); 
///////////////// end save master user to CRC api /////////////////////////////////////////////////////////////////////////

///////////////// save sub users to CRC api /////////////////////////////////////////////////////////////////////////
		if ($totalRows_attornys_cart > 0) { // if there are more attorney's
			do {
				include('../include/inc_add_subuser_to_crc.php'); 
			} while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); 
		} // end if more attorney's
			
///////////////// end sub users user to CRC api /////////////////////////////////////////////////////////////////////////


		// if new user, add user to docketlaw database
		  $insertSQL = sprintf("INSERT INTO users (username, userpassword, firstname, lastname, email, firm, billingaddress, billingcity, billingstate, billingzip, auth_profile_id, auth_payment_id, auth_shipping_id, CardLastFour, Month, Year, sessionID, CurrentChargeAmount) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['firstname'], "text"),
                       GetSQLValueString($_POST['lastname'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['firm'], "text"),
                       GetSQLValueString($_POST['billingaddress'], "text"),
                       GetSQLValueString($_POST['city'], "text"),
                       GetSQLValueString($_POST['state'], "text"),
                       GetSQLValueString($_POST['zip'], "text"),
                       GetSQLValueString($profile_id, "text"),
                       GetSQLValueString($payment_id, "text"),
                       GetSQLValueString($shipping_profile_id, "text"),
                       GetSQLValueString(substr($_POST['cardnumber'],-4), "text"),
					   GetSQLValueString($_POST['month'], "text"),
					   GetSQLValueString($_POST['year'], "text"),
					   GetSQLValueString(session_id(), "text"),
					   GetSQLValueString($_POST['CurrentChargeAmount'], "text"));
					  mysqli_select_db($docketData,$database_docketData);
					  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
					  $_SESSION['userid'] = mysql_insert_id();
  
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
  
  
  		$_SESSION['fullname'] = $parent_contactName;
		$_SESSION['parent'] = "Y";
  		$_SESSION['firstname'] = $_POST['firstname'];
		$_SESSION['username'] = $_POST['username'];
		$_SESSION['password'] = $_POST['password'];
		$_SESSION['auth_profile_id'] = $profile_id;
		$_SESSION['auth_payment_id'] = $payment_id;
		$_SESSION['auth_shipping_id'] = $shipping_profile_id;
		// send user an email
	
		
		
		include('Mail.php');
        include('Mail/mime.php');
		$host = "smtpout.secureserver.net";
		$username = $Company_Email;
		$password = $Company_Email_Pass;
 
        // Constructing the email
        $sender = $Company_Email;                              // Your name and email address
        $recipient =  $_POST['email'];                           // The Recipients name and email address
        $subject = "Welcome to ".$CompanyName;                            // Subject for the email
			//begin of HTML message 
			$html = "Thank you for subscribing to CalendarRules For Outlook, from CalendarRules.com. <BR><BR>
Your user id is:". $_POST['username'] ."<BR><BR>
Your password is: ". $_POST['password'] ."<BR><BR>
 
To download and install the software, please refer to the <a href='http://www.calendarrulesforoutlook.com/docs/InstallGuide.pdf'>Installation Guide</a>.<BR>
Once installed, please read the <a href='http://www.calendarrulesforoutlook.com/docs/UserGuide.pdf'>User Guide</a>.<BR><BR>
 
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
			'password' => $password));
        $mail->send($recipient, $headers, $body);
		
		
		
		
		//
		// Constructing the email
        $sender = $_POST['email'];                              // Your name and email address
        $recipient = $Company_Email;                          // The Recipients name and email address
        $subject = $CompanyName. " Paid Account Created";                          // Subject for the email
		//begin of HTML message 
		$html = "Paid account has been started.<br> 
				 Username: ". $_POST['username'] ."<BR>
		         ". $_POST['firstname']. " ". $_POST['lastname']. "<br>  
				 Email: ". $_POST['email'] . "
				 Firm: ". $_POST['firm'] . "
			  <br><br>"; 
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
			'password' => $password));
        $mail->send($recipient, $headers, $body);


		
  		// all set redirect
		echo 'end';
		header('Location: /order-completed');
    }else{
		header('Location: /card-declined?email='.$_POST['email'].'&firstname='.$_POST['firstname'].'&lastname='.$_POST['lastname'].'&firm='.$_POST['firm'].'&billingaddress='.$_POST['billingaddress'].'&city='.$_POST['city'].'&state='.$_POST['state'].'&zip='.$_POST['zip'].'&username='.$_POST['username'].'&password='.$_POST['password']);
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