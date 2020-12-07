<?php
global $docketData;
$hostname_docketData = "mariadb-132.wc1.phx1.stabletransit.com";
$database_docketData = "375786_google";
$username_docketData = "375786_google";
$password_docketData = "Gosharks123";
$docketData = mysqli_connect($hostname_docketData, $username_docketData, $password_docketData,$database_docketData) or trigger_error(mysqli_connect_error(), E_USER_ERROR);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
mysqli_select_db( $docketData,$database_docketData);

$GLOBALS['docketData'] = $docketData;
$GLOBALS['database_docketData'] = $database_docketData;

$CompanyName = "Calendar Rules";
//$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
//$CRCloginToken = 'YBI7srFzDWsE8NZ96Kc42tnnSMF%2BjA0hsZ27ueDsNKzwXRacPPuU7EF6J9CIAU%2BTumqvxShXhFanlg4XiWO47L%2Bsp5XD4gCc9WGm8I5LDFv1b%2BoSuKGbz2pxtb0kTuiNxZZ4DLnMoBl7zuPVkz1Hl3SqCze8DM9%2FpUrdZNsoqiyg%2FSKFUeeOyhKzrn9aO4I%2FEDW4iH4BUUovSa47Pi7Ci398Z7QgRk%2FG4M0UH7NoKzRTfnsYwvwfBZoaOkT3rgPu';
$CRCloginToken = "HyxjUrnVsNxaC7oErpIHFP1XXjXs6u1HSbN0nmemi5ZLiGLF%2B%2FTS0ZNf%2FU%2F1zoAAeKkdmhIeQzTtxGBS4VsoTTncuHIRkC3dcQc1QM%2BXyQZmpuVmQ07zhjxvTPWZnBN2uYwfPkrLJlYdhipwHQynR6OXDDMsxBaW5vKlL%2BAdtPGfUZkn4TFVFTrtBY9CeZo6Pa7idqhi3lW%2F34zLcGZdwjW7SmJUqSJtJeKkrgCSccmIYVVitcXZAk3fpuIhx6Xq";
//$CRCloginToken = 'J3G6xPee6w7Y72BZvNB/CGCmXzupUKWK/eK+6N4GfLDyFXXtezy+yebVlrItdPC8gnQyDLOXYB4qUKa6XBJSt/0ApSj4ugfsManyS7KDEx2JJMfRcfWJwMcnQizcrNck3djttu+GvY4rrx0jV3Rh76w9w8+4o5JUsOJDJro4tflxtST8tvnUF5pLCuB4ThHpvPG0r6TxmjMUuUJJrSSmrXCa36ubTbXqi5WbjL+Fl3DdUJhNQk4GdAyPfmgwjubL';
$Auth_API_Login = '56V7gKjw'; // 66apTY6BWs4S
$Auth_TransactionKey = '5da758Kb6V94ShNX'; // 75bBfuD7RgM8899q

// Mailgun INFO_ALL
$domain = "mg.courtrulescompany.com";
$mg_API_key = "key-10f97fdl376g-1jzm7fqg9lu4sjzab-1";

// Development = USE_DEVELOPMENT_SERVER
// Production = USE_PRODUCTION_SERVER
$Auth_Mode = 'AuthnetCIM::USE_DEVELOPMENT_SERVER';
$Company_Email = "subscribe@calendarrules.com";
$Company_Email_Pass = "crc123";
$SMTPServer = "smtpout.secureserver.net";
$SSLDomain = "https://www.calendarrulesforoutlook.com";
$userguideURL = "http://www.calendarrulesforoutlook.com/docs/UserGuide.pdf";
$installguideURL = "http://www.calendarrulesforoutlook.com/docs/InstallGuide.pdf";
?>