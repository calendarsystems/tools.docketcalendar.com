<?php require_once('Connections/docketData.php');
//https://github.com/educoder/pest
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];

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
require 'Pest.php';
$loginToken = $CRCloginToken;
$user = new Pest('http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest');
$userLoginToken = 'bOLm3BrGaEwpB3XM2Cb4Srst%2B1nXHYlpHUdZwDfZ31vGp7RC9vr0w7ahYvf4voaOq8us1DE3i7LzjtNrWl8BoZpLVHm0YJHYPRcH3dn1q1CTus01Bww%2BrHgXXeVDmwqki7mBHJSThdTS8BQgQaImHwsU4ft5A3gYk9p70YyQ690CVjvN9k6Sh7FFO%2FpZaGkNo2b%2BTLz%2FCq0XIB%2Bwosp2qXOycRgP8XI8RlV%2B0mFPz%2BA4sj%2BqF4J55KVPn%2BEOdKBs';
$user = $user->get('/user?loginToken='.$userLoginToken);

$user = new SimpleXMLElement($user);
echo $user->ContactName.'<BR>';
echo $user->{'SoftwareName'}.'<BR>';
echo $user->Login.'<BR>Systems Subscribed to:<BR>';
foreach($user->Subscription->Jurisdictions->Jurisdiction as $Sub) {
	echo 'court: '. $Sub->{'Description'}. '<BR>';
}

// update user
// Update the newly created User's attributes
//$data = array(
//  'User' => array(
//    'Comments' => "Update Test"
//  ) 
//);
//
//$user->put('/user?loginToken='.$userLoginToken, $data);
//
//



echo '<BR><BR><BR><B>COURTS</B><BR>';
// get Jurisdictions
$Courts = new Pest('http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest');
$Courts = $Courts->get('/jurisdictions/all?loginToken='.$loginToken);
$Courts = new SimpleXMLElement($Courts);

foreach($Courts->Jurisdiction as $Court) {
    echo "Code: ". $Court->{'Code'} ."<BR>CourtSystem Description: ".  $Court->CourtSystem->{'Description'} ."<BR>CourtSystemID: ". $Court->CourtSystem->{'SystemID'}  ."<BR>CourtSystemCode: ". $Court->CourtSystem->{'Code'}  ."<BR>Description: ". $Court->{'Description'}."<BR>SystemID: ". $Court->{'SystemID'} ."<BR>TypeDescription: ". $Court->Type->{'Description'}  ."<BR>TypeSystemID: ".  $Court->Type->{'SystemID'}  ."<BR><BR><BR>";
	// insert to courts table
	  $insertSQL = sprintf("INSERT INTO courts (code, courtSystem_Description, courtSystem_SystemID, courtSystem_Code, `description`, price, systemID, type_Description, type_SystemID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($Court->{'Code'}, "text"),
                       GetSQLValueString($Court->CourtSystem->{'Description'}, "text"),
                       GetSQLValueString($Court->CourtSystem->{'SystemID'}, "text"),
                       GetSQLValueString($Court->CourtSystem->{'Code'}, "text"),
					   GetSQLValueString($Court->{'Description'}, "text"),
                       GetSQLValueString($Court->{'Price'}, "text"),
                       GetSQLValueString($Court->{'SystemID'} , "text"),
                       GetSQLValueString($Court->Type->{'Description'} , "text"),
                       GetSQLValueString($Court->Type->{'SystemID'}, "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 = mysqli_query($docketData,$insertSQL) or die(mysqli_error($docketData));
}

echo '<BR><BR><BR><B>States</B><BR>';
// get Jurisdictions
$Courts = new Pest('http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest');
$Courts = $Courts->get('/courtsystems?loginToken='.$loginToken);
$Courts = new SimpleXMLElement($Courts);

foreach($Courts->GenericTypeExt as $Court) {
    echo $Court->{'Description'}." (". $Court->{'SystemID'} .")<BR>";
}


?>