<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_docketData = "mysql50-91.wc1.dfw1.stabletransit.com";
$database_docketData = "375786_subscribers";
$username_docketData = "375786_npavone";
$password_docketData = "weBdev123";
//$docketData = mysql_pconnect($hostname_docketData, $username_docketData, $password_docketData) or trigger_error(mysql_error(),E_USER_ERROR); 
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$docketData = mysqli_connect($hostname_docketData, $username_docketData, $password_docketData,$database_docketData);
if (!$docketData) {
  die("Database connection failed: " . mysqli_connect_error($docketDataSubscribe));
}
$CompanyName = "Calendar Rules";
$CRCurl = "http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest";
$CRCloginToken = 'YBI7srFzDWsE8NZ96Kc42tnnSMF%2BjA0hsZ27ueDsNKzwXRacPPuU7EF6J9CIAU%2BTumqvxShXhFanlg4XiWO47L%2Bsp5XD4gCc9WGm8I5LDFv1b%2BoSuKGbz2pxtb0kTuiNxZZ4DLnMoBl7zuPVkz1Hl3SqCze8DM9%2FpUrdZNsoqiyg%2FSKFUeeOyhKzrn9aO4I%2FEDW4iH4BUUovSa47Pi7Ci398Z7QgRk%2FG4M0UH7NoKzRTfnsYwvwfBZoaOkT3rgPu';
$Auth_API_Login = '56V7gKjw';
$Auth_TransactionKey = '5da758Kb6V94ShNX';
// Development = USE_DEVELOPMENT_SERVER
// Production = USE_PRODUCTION_SERVER
$Auth_Mode = 'AuthnetCIM::USE_PRODUCTION_SERVER';
$Company_Email = "subscribe@calendarrules.com";
$Company_Email_Pass = "weBdev123";
$SMTPServer = "smtpout.secureserver.net";
$SSLDomain = "http://subscribe.crcrules.com";
?>