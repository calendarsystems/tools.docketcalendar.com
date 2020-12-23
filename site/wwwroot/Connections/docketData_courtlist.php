<?php  // ini_set('display_errors',1);
//error_reporting(E_ALL);
global $docketDataSubscribe;
$hostname_docketDataSubscribe = "mariadb-066.wc1.dfw3.stabletransit.com";
$database_docketDataSubscribe = "375786_dlsub";
$username_docketDataSubscribe = "375786_dlsub";
$password_docketDataSubscribe = "D0cketLaw123";

$docketDataSubscribe = mysqli_connect($hostname_docketDataSubscribe, $username_docketDataSubscribe, $password_docketDataSubscribe);
if (!$docketDataSubscribe) {
  die("Database connection failed: " . mysqli_connect_error($docketDataSubscribe));
}
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$db_select = mysqli_select_db($docketDataSubscribe, $database_docketDataSubscribe);
if (!$db_select) {
  die("Database selection failed: " . mysqli_connect_error($docketDataSubscribe));
}

$CompanyName = "Calendar Rules";
//$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
//$CRCloginToken = 'YBI7srFzDWsE8NZ96Kc42tnnSMF%2BjA0hsZ27ueDsNKzwXRacPPuU7EF6J9CIAU%2BTumqvxShXhFanlg4XiWO47L%2Bsp5XD4gCc9WGm8I5LDFv1b%2BoSuKGbz2pxtb0kTuiNxZZ4DLnMoBl7zuPVkz1Hl3SqCze8DM9%2FpUrdZNsoqiyg%2FSKFUeeOyhKzrn9aO4I%2FEDW4iH4BUUovSa47Pi7Ci398Z7QgRk%2FG4M0UH7NoKzRTfnsYwvwfBZoaOkT3rgPu';
$CRCloginToken = "HyxjUrnVsNxaC7oErpIHFP1XXjXs6u1HSbN0nmemi5ZLiGLF%2B%2FTS0ZNf%2FU%2F1zoAAeKkdmhIeQzTtxGBS4VsoTTncuHIRkC3dcQc1QM%2BXyQZmpuVmQ07zhjxvTPWZnBN2uYwfPkrLJlYdhipwHQynR6OXDDMsxBaW5vKlL%2BAdtPGfUZkn4TFVFTrtBY9CeZo6Pa7idqhi3lW%2F34zLcGZdwjW7SmJUqSJtJeKkrgCSccmIYVVitcXZAk3fpuIhx6Xq";
//$CRCloginToken = 'J3G6xPee6w7Y72BZvNB/CGCmXzupUKWK/eK+6N4GfLDyFXXtezy+yebVlrItdPC8gnQyDLOXYB4qUKa6XBJSt/0ApSj4ugfsManyS7KDEx2JJMfRcfWJwMcnQizcrNck3djttu+GvY4rrx0jV3Rh76w9w8+4o5JUsOJDJro4tflxtST8tvnUF5pLCuB4ThHpvPG0r6TxmjMUuUJJrSSmrXCa36ubTbXqi5WbjL+Fl3DdUJhNQk4GdAyPfmgwjubL';
$Auth_API_Login = '56V7gKjw'; // 66apTY6BWs4S  
$Auth_TransactionKey = '5da758Kb6V94ShNX'; // 75bBfuD7RgM8899q  
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