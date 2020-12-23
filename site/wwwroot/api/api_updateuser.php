<?php require_once('Connections/docketData.php');
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


$userToken = new Pest('http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest');
$userToken = $userToken->get('/users/'.$_GET['username'].'?password='.$_GET['password'].'&soapREST=REST');
$userToken = new SimpleXMLElement($userToken);
$userLoginToken = $userToken;

echo $userLoginToken.'<BR>';

// get user
$user = new Pest('http://www.crcrules.com/UA/CalendarRulesMembershipService.svc/rest');
$user = $user->get('/user?loginToken='.$userLoginToken);

$user = new SimpleXMLElement($user);
echo $user->ContactName.'<BR>';
echo $user->{'SoftwareName'}.'<BR>';
echo $user->Login;
echo '<BR><BR>';
echo 'Individual Court Systems Subscribed to:<BR>';
foreach($user->Subscription->Jurisdictions->Jurisdiction as $Sub) {
	echo 'court: '. $Sub->{'Description'}. '<BR>';
}
echo '<BR>State Systems Subscribed to:<BR>';
foreach($user->Subscription->CourtSystems->GenericTypeExt as $Sub) {
	echo 'court: '. $Sub->{'Description'}. '<BR>';
}

// update user
//set URL
$url = $CRCurl;

//set xml <strong class="highlight">request</strong> 35809
$comments = $_GET['comments'];
$contactName = $_GET['contactName'];
$email = $_GET['email'];
$login = $_GET['username'];
$password = $_GET['password'];
$softwareName = $_GET['firm'];
$firm = $_GET['firm'];





//set xml <strong class="highlight">request</strong> 35809
$xml = '
<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
<Comments>This is a test account. Updated</Comments>
<ContactName>'. $user->ContactName .'</ContactName>
<EMail>'. $user->EMail .'</EMail>
<FaxNumber></FaxNumber>
<Login>'. $user->Login .'</Login>
<LoginToken>'. $user->LoginToken .'</LoginToken>
<Name>'. $user->Name .'</Name>
<NotifyModCourt>true</NotifyModCourt>
<NotifyModEvent>false</NotifyModEvent>
<Password>'. $user->Password .'</Password>
<PhoneNumber></PhoneNumber>
<Quote>
<CourtSystems/>
<CourtTypes/>
<EndDate>0001-01-01T00:00:00</EndDate>
<Jurisdictions/>
<StartDate>0001-01-01T00:00:00</StartDate>
<Type/>
</Quote>
<SoftwareName>Website Software</SoftwareName>';

// if cancelling all subscriptions use this:
//$xml = $xml. '<Subscription><CourtSystems></CourtSystems><Jurisdictions>';

$xml = $xml . '
  <Subscription>
	<CourtSystems>';
	// add states
	// if just one remove trailing slash
	$states	= $_GET['states'];
	if (substr_count($states, ',') < 2){
		$states = str_replace(',','',$states);
	}else{
	// remove trailing comma
	$states = substr($states, 0, strlen($states)-1); 
	}
$states = explode(",",$states);	
	while(list($key,$value) = each($states)){ 
	$xml = $xml . '<GenericTypeExt><SystemID>'.$value.'</SystemID></GenericTypeExt>';
} 

$xml = $xml .'</CourtSystems><Jurisdictions>';
	
	// add individual courts
	$courts	= $_GET['courts'];
	if (substr_count($courts, ',') < 2){
		$courts = str_replace(',','',$courts);
	}else{
	// remove trailing comma
	$courts = substr($courts, 0, strlen($courts)-1); 
	}
$courts = explode(",",$courts);	
	while(list($key,$value) = each($courts)){ 
	$xml = $xml . '<Jurisdiction><SystemID>'. $value .'</SystemID></Jurisdiction>';
} 
	

	// close out xml
$xml = $xml . '</Jurisdictions></Subscription></User>'; 



//$xml = urlencode($xml);

// Get the curl session object
$session = curl_init($url);

// set url to post to 
//curl_setopt($session, CURLOPT_URL,$url);
// Tell curl to use HTTP POST;
curl_setopt ($session, CURLOPT_POST, true);
// Tell curl that this is the body of the POST
curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
// Tell curl not to return headers, but do return the response
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
// allow redirects 
//curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($session);
print_r ($response);
curl_close($session);

?>