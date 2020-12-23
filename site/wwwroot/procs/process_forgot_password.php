<?php require_once('Connections/docketData.php'); 
session_start();
ob_start();
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

$colname_rs = "-1";
if (isset($_POST['Email'])) {
  $colname_rs = $_POST['Email'];
}
mysqli_select_db($docketData,$database_docketData);
$query_rs = sprintf("SELECT * FROM attorneys WHERE email = %s", GetSQLValueString($colname_rs, "text"));
echo $query_rs;
$rs =mysqli_query($docketData,$query_rs) or die(mysqli_error($docketData));
$row_rs = mysqli_fetch_assoc($rs);
$totalRows_rs = mysqli_num_rows($rs);
 echo $totalRows_rs;  if ($totalRows_rs > 0) { // Show if recordset not empty 

		require('Mail.php');
        require('Mail/mime.php');
		$host = $SMTPServer;
		$username = $Company_Email;
		$password = $Company_Email_Pass;
 
        // Constructing the email
        $sender = $Company_Email;                              // Your name and email address
        $recipient = $row_rs['email'];                           // The Recipients name and email address
        $subject = $CompanyName. " Login Information";                            // Subject for the email
		//begin of HTML message 
		$html = "Your login information to ". $CompanyName ." is below.<br> 
		         Username: ". $row_rs['username'] ."<br> 
				 Password: ".$row_rs['password'] ."<br>
			  <br><br>Thank you,<BR> ". $CompanyName; 
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
        $mail =& Mail::factory('smtp');
        $mail->send($recipient, $headers, $body);

		header('Location: /login?e=88&to='.$recipient);
?>
  <?php }else{ // Show if recordset not empty
  		header('Location: /forgot-password?e=77');
     } ?>
<?php
mysqli_free_result($rs);
?>
