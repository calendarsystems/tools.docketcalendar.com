<?php
$loginToken = $CRCloginToken;

//set URL
$url = $CRCurl;

$comments = $_GET['comments'];
$contactName = $_GET['contactName'];
$email = $_GET['email'];
$login = $_GET['username'];
$password = $_GET['password'];
$softwareName = $_GET['firm'];
$firm = $_GET['firm'];


$xml = '
<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects">
  <Comments></Comments>
  <ContactName>'. $contactName .'</ContactName>
  <EMail>'. $email .'</EMail>
  <FaxNumber></FaxNumber>
  <Login>'. $login .'</Login>
  <Name>'. $firm .'</Name>
  <Password>'. $password .'</Password>
  <PhoneNumber></PhoneNumber>
  <SoftwareName></SoftwareName>';
   

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

$session = curl_init($url);

curl_setopt ($session, CURLOPT_POST, true);
curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($session);
print_r ($response);
curl_close($session);
?>