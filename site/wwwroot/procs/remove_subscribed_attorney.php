<?php require_once('Connections/docketData.php'); 
ini_set('display_errors', 1); 
error_reporting(E_ALL);

session_start();
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

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

require ('../include/Pest.php');	

if ((isset($_GET['id'])) && ($_GET['id'] != "")) {


mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE attorneyID = %s", GetSQLValueString($_GET['id'], "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);	
	



$userToken = new Pest($CRCurl);
$userToken = $userToken->get('/users/'.$row_attornys_cart['username'].'?password='.$row_attornys_cart['password'].'&soapREST=REST');
$userToken = new SimpleXMLElement($userToken);
$userLoginToken = $userToken;

//echo $userLoginToken;				
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
//
//// update user
////set URL

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
$xml = $xml . '<Subscription><CourtSystems>';
$xml = $xml .'</CourtSystems><Jurisdictions>';
$xml = $xml . '</Jurisdictions></Subscription></User>'; 
//echo $xml;
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


  $deleteSQL = sprintf("DELETE FROM attorneys WHERE attorneyID=%s AND user_id = %s",
                       GetSQLValueString($_GET['id'], "int"),
					   GetSQLValueString($_SESSION['userid'], "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$deleteSQL) or die(mysqli_error($docketData));


  $deleteGoTo = "/courts";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}
?>