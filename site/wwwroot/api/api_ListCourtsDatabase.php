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