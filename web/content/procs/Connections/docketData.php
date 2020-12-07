<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_docketData = "mysql51-008.wc1.dfw1.stabletransit.com";
$database_docketData = "375786_crules";
$username_docketData = "375786_crules";
$password_docketData = "Crules123";
$docketData = mysqli_connect($hostname_docketData, $username_docketData, $password_docketData,$database_docketData);
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$CompanyName = "Calendar Rules";
$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
//$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
//$CRCloginToken = 'YBI7srFzDWsE8NZ96Kc42tnnSMF%2BjA0hsZ27ueDsNKzwXRacPPuU7EF6J9CIAU%2BTumqvxShXhFanlg4XiWO47L%2Bsp5XD4gCc9WGm8I5LDFv1b%2BoSuKGbz2pxtb0kTuiNxZZ4DLnMoBl7zuPVkz1Hl3SqCze8DM9%2FpUrdZNsoqiyg%2FSKFUeeOyhKzrn9aO4I%2FEDW4iH4BUUovSa47Pi7Ci398Z7QgRk%2FG4M0UH7NoKzRTfnsYwvwfBZoaOkT3rgPu';
$CRCloginToken = 'M6yl5smeqfYf3zKpu9CDypx4LW6hERE9yaKH35RjgBElMDVDNE4G08bUoMtcA6K2rMbWk2qqv5myraxATvfeLfvcUaz7Dgur0cYXgtutyKWszesmnYPjTbHPI3dhbP5DgFelMg/8KRblYQ93hAMJa//AnT7kbK2PrICIlmn6/d+hVwj4hQMAnftmC4OIG8DmZpX6ZxoI0EmzsbcR/6vwphTXQiHZw3e+Ph39gnaryvtDhtlOqjThZc10B1zyshbt';
//$CRCloginToken = 'J3G6xPee6w7Y72BZvNB/CGCmXzupUKWK/eK+6N4GfLDyFXXtezy+yebVlrItdPC8gnQyDLOXYB4qUKa6XBJSt/0ApSj4ugfsManyS7KDEx2JJMfRcfWJwMcnQizcrNck3djttu+GvY4rrx0jV3Rh76w9w8+4o5JUsOJDJro4tflxtST8tvnUF5pLCuB4ThHpvPG0r6TxmjMUuUJJrSSmrXCa36ubTbXqi5WbjL+Fl3DdUJhNQk4GdAyPfmgwjubL';
$Auth_API_Login = '56V7gKjw'; // 66apTY6BWs4S  
$Auth_TransactionKey = '5da758Kb6V94ShNX'; // 75bBfuD7RgM8899q  
// Development = USE_DEVELOPMENT_SERVER
// Production = USE_PRODUCTION_SERVER
$Auth_Mode = 'AuthnetCIM::USE_DEVELOPMENT_SERVER';
$Company_Email = "subscribe@calendarrules.com";
$Company_Email_Pass = "crc123";
$SMTPServer = "smtpout.secureserver.net";
$SSLDomain = "http://calendarrulesforoutlook.com";
?>