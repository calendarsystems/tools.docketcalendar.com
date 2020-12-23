<?php require_once('Connections/docketData.php'); 
session_start();
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
mysqli_select_db($docketData,$database_docketData);
$query_cartStateSub = sprintf("SELECT DISTINCT
cart.id,
cart.sessionid,
cart.systemid,
cart.courttype,
courts.courtSystem_Description,
court_pricing.Price
FROM
cart
Inner Join courts ON cart.systemid = courts.courtSystem_SystemID
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE  cart.sessionid = '". session_id() ."'  AND cart.courttype = 'state'");
$cartStateSub = mysqli_query($docketData,$query_cartStateSub) or die(mysqli_error($docketData));
$row_cartStateSub = mysqli_fetch_assoc($cartStateSub);
$totalRows_cartStateSub = mysqli_num_rows($cartStateSub);


mysqli_select_db($docketData,$database_docketData);
$query_cartSub = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.sessionid = '". session_id() ."' AND cart.courttype <> 'state'");
$cartSub = mysqli_query($docketData,$query_cartSub) or die(mysqli_error($docketData));
$row_cartSub = mysqli_fetch_assoc($cartSub);
$totalRows_cartSub = mysqli_num_rows($cartSub);



// get SubscribedStates


		do { 
			$stateIDs = $stateIDs . $row_cartStateSub['systemid']. ',';
		} while ($row_cartStateSub = mysqli_fetch_assoc($cartStateSub));
		
// get SubscribedCourts
		do { 
			$courtIDs = $courtIDs . $row_cartSub['systemid']. ',';
		} while ($row_cartSub = mysqli_fetch_assoc($cartSub));


//$courtIDs = $courtIDs."-14768,-44295,-14253,-14328,";
//echo '<BR><BR>'.$courtIDs;

require 'Pest.php';
$loginToken = $CRCloginToken;



$userToken = new Pest($CRCurl);
$userToken = $userToken->get('/users/'.$_SESSION['username'].'?password='.$_SESSION['password'].'&soapREST=REST');
$userToken = new SimpleXMLElement($userToken);
$userLoginToken = $userToken;


// get user
$user = new Pest($CRCurl);
$user = $user->get('/user?loginToken='.$userLoginToken);

$user = new SimpleXMLElement($user);


//foreach($user->Subscription->Jurisdictions->Jurisdiction as $Sub) {
//	echo 'court: '. $Sub->{'Description'}. '<BR>';
//}
//echo '<BR>State Systems Subscribed to:<BR>';
//foreach($user->Subscription->CourtSystems->GenericTypeExt as $Sub) {
//	echo 'court: '. $Sub->{'Description'}. '<BR>';
//}

// update user
//set URL
$url = $CRCurl;


//set xml <strong class="highlight">request</strong> 35809
$xml = '
<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
<Comments></Comments>
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
<SoftwareName></SoftwareName>';

// if cancelling all subscriptions use this:
//$xml = $xml. '<Subscription><CourtSystems></CourtSystems><Jurisdictions>';

$xml = $xml . '
  <Subscription>
	<CourtSystems>';
	// add states
	// if just one remove trailing slash
if ($totalRows_cartStateSub > 0){
		$states	= $stateIDs;
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
}
$xml = $xml .'</CourtSystems><Jurisdictions>';
	
	// add individual courts
if ($totalRows_cartSub > 0){
		$courts	= $courtIDs;
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
}
	

	// close out xml
$xml = $xml . '</Jurisdictions></Subscription></User>'; 

//echo '<BR><BR>'. $xml;
// Get the curl session object
						$session = curl_init($url.'/user');
						curl_setopt ($session, CURLOPT_POST, true);
						curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($session, CURLOPT_HEADER, true);
						curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						
						$response = curl_exec($session);
						//print_r ($response);
						curl_close($session);

?>