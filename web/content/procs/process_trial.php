<?php
require_once('Connections/docketData.php');
ini_set('display_errors',1); 

session_start();
// Include AuthnetCIM class. Nothing works without it!
//require('classes/AuthnetCIM.class.php');
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
					  $_SESSION['userid'] = mysql_insert_id();
  
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
  



	// Constructing the email
    $from_add = $Company_Email;                              
    $to_add = $_POST['email'];      //.",benjamin.seeman@gmail.com";  
    $subject = $CompanyName. " Trial Activation";            

	//begin of HTML message 
	$message = "Thank you for your interest in ". $CompanyName .". Click the Activate Account link below to activate your 14 day trial.<br> 
		         Username: ". $_POST['username'] ."<br> 
				<a href=\"". $SSLDomain ."/activate?token=". $token ."\">Activate Account</a> 
			  <br><br>"; 
		
	$headers =  "From: $from_add \r\n";
	$headers .= "Reply-To: $from_add \r\n";
	$headers .= "Return-Path: $from_add \r\n";
	$headers .= "X-Mailer: PHP \r\n";
	$headers .= "Content-type: text/html";
	
	if(mail($to_add,$subject,$message,$headers)) 
	{
//		$msg = "Mail sent to customer OK";
	} 
	else 
	{
// 	   $msg = "Error sending email to customer";
	}


	// Constructing the email
	$from_add = $_POST['email'];                              
    $to_add = $Company_Email;  //.",benjamin.seeman@gmail.com";    
    $subject = $CompanyName. " Trial Created";                
	
	$message = "Trial has been started.<br> 
				 Username: ". $_POST['username'] ."<BR>
		         ". $_POST['firstname']. " ". $_POST['lastname']. "<br>  
				 Firm: ". $_POST['firm'] . "
			  <br><br>"; 

	$headers =  "From: $from_add \r\n";
	$headers .= "Reply-To: $from_add \r\n";
	$headers .= "Return-Path: $from_add \r\n";
	$headers .= "X-Mailer: PHP \r\n";
	$headers .= "Content-type: text/html";

	if(mail($to_add,$subject,$message,$headers)) 
	{
//		$msg = "Mail sent to admin OK";
	} 
	else 
	{
// 	   $msg = "Error sending email to admin";
	}

		
		
		
  	    
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