<?php
require_once('/Connections/docketData.php');
session_start();
// Include AuthnetCIM class. Nothing works without it!
//require('classes/AuthnetCIM.class.php');
require('/classes/register.php');

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

		//$token = md5(uniqid(microtime()) . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
		$token = session_id();
		// if new user, add user to docketlaw database
		  $insertSQL = sprintf("INSERT INTO users (username, userpassword, firstname, lastname, email, firm, sessionID, TrialAccount) VALUES (%s, %s, %s, %s, %s, %s, %s, '1')",
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['firstname'], "text"),
                       GetSQLValueString($_POST['lastname'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['firm'], "text"),
					   GetSQLValueString($token, "text"));
					  mysqli_select_db($docketData,$database_docketData);
					  $Result1 =mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
					  $_SESSION['userid'] = mysqli_insert_id($docketData);
  
  		// update users cart to mark subscribed courts
//		  $updateSQL = sprintf("INSERT INTO cart (subscribed, systemid, sessionid, courttype) VALUES (%s, '-14768', %s, '-35804'",
//  					   GetSQLValueString($_SESSION['userid'], "int"),
//                       GetSQLValueString(session_id(), "text"));
//					   mysqli_select_db($docketData,$database_docketData);
//  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
//
//		  $updateSQL = sprintf("INSERT INTO cart (subscribed, systemid, sessionid, courttype) VALUES (%s, '-44295', %s, '-35804'",
//  					   GetSQLValueString($_SESSION['userid'], "int"),
//                       GetSQLValueString(session_id(), "text"));
//					   mysqli_select_db($docketData,$database_docketData);
//  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
//
//		  $updateSQL = sprintf("INSERT INTO cart (subscribed, systemid, sessionid, courttype) VALUES (%s, '-14253', %s, '-35834'",
//  					   GetSQLValueString($_SESSION['userid'], "int"),
//                       GetSQLValueString(session_id(), "text"));
//					   mysqli_select_db($docketData,$database_docketData);
//  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
//
//		  $updateSQL = sprintf("INSERT INTO cart (subscribed, systemid, sessionid, courttype) VALUES (%s, '-14328', %s, '-35834'",
//  					   GetSQLValueString($_SESSION['userid'], "int"),
//                       GetSQLValueString(session_id(), "text"));
//					   mysqli_select_db($docketData,$database_docketData);
//  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
  
		
		include('Mail.php');
        include('Mail/mime.php');
		$host = $SMTPServer;
		$username = $Company_Email;
		$password = $Company_Email_Pass;
 
        // Constructing the email
        $sender = $Company_Email;                              // Your name and email address
        $recipient = $_POST['email'];                           // The Recipients name and email address
        $subject = $CompanyName. " Trial Activation";                            // Subject for the email
        		//begin of HTML message 
		$html = "Thank you for your interest in ". $CompanyName .". Click the Activate Account link below to activate your 30 day trial.<br> 
		         Username: ". $_POST['username'] ."<br> 
				<a href=\"". $SSLDomain ."/activate.php?token=". $token ."\">Activate Account</a> 
			  <br><br>"; 
		 //end of messag
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
        $subject = $CompanyName. " Trial Created";                          // Subject for the email
		//begin of HTML message 
		$html = "Trial has been started.<br> 
				 Username: ". $_POST['username'] ."<BR>
		         ". $_POST['firstname']. " ". $_POST['lastname']. "<br>  
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

		
		
		
  	    
  		$_SESSION['firstname'] = $_POST['firstname'];
		$_SESSION['username'] = $_POST['username'];
		$_SESSION['password'] = $_POST['password'];
		//$_SESSION['auth_profile_id'] = $profile_id;
		//$_SESSION['auth_payment_id'] = $payment_id;
		//$_SESSION['auth_shipping_id'] = $shipping_profile_id;
  		// all set redirect
		header('Location: /trial-completed');

    // Print the results of the request
//    echo '<b>createCustomerProfileTransactionRequest Response Summary:</b> ' . $cim->getResponseSummary() . '<br />';
//	echo '<b>Approval code:</b>' . $approval_code .'<BR>';
//	echo '<b>Transaction ID:</b>'. $transaction_id;

?>