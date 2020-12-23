<?php
require_once '../Connections/docketData.php';
require '../mailgun-php/vendor/autoload.php';

use Mailgun\Mailgun;
$path = $_SERVER['DOCUMENT_ROOT'];
session_start();

# First, instantiate the SDK with your API credentials and define your domain.
$mg = new Mailgun($mg_API_key);

send_the_mail($_SESSION['to1'], $_SESSION['from1'], $_SESSION['subject1'], $_SESSION['html1'], $path . "/logs/" . $_SESSION['courts_csv'], $mg, $domain, $database_docketData);

$mg->sendMessage($domain, array('from' => "stacy@calendarrules.com", 'to' => $_SESSION['from1'], 'subject' => "Thank you for request - Calendarrules", 'html' => "Hi, <br><br>Thank You for Your <b>Quote Request!</b> We have received your request successfully. Our team will be in touch shortly."));

switch ($_SESSION['proc']) {

case "get_a_quote":
	header("Location: /get-a-quote?msg=1");
	exit();
	break;
}

function send_the_mail($to, $from, $subject, $html, $attachment, $mg, $domain, $database_docketData) {
	$mg->sendMessage($domain, array('from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html), array('attachment' => array($attachment)));
}

?>