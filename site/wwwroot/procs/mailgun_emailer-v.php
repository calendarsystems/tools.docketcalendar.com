<?php
///// Send an email using mailgun and session variables.
require_once '../Connections/docketData.php';
require '../mailgun-php/vendor/autoload.php';
use Mailgun\Mailgun;

session_start();

# First, instantiate the SDK with your API credentials and define your domain.
$mg = new Mailgun($mg_API_key);

send_the_mail("vinithkumar.m@clariontechnologies.co.in", "admin@calendarrules.com", "Test courts", "hi vinith<br>hai hai", "../logs/get-a-quote-11132017011446.csv", $mg, $domain, $database_docketData);

function send_the_mail($to, $from, $subject, $html, $attachment, $mg, $domain, $database_docketData) {
	$mg->sendMessage($domain, array('from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html, 'attachment' => $attachment));
}

?>