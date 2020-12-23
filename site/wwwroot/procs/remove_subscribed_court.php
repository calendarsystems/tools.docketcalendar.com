<?php
require_once('Connections/docketData.php');
session_start();
ob_start();

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

  $deleteSQL = sprintf("DELETE FROM cart WHERE id=%s AND sessionid = %s",
                       GetSQLValueString($_GET['id'], "int"),
					   GetSQLValueString(session_id(), "text"));

  mysqli_select_db($docketData,$database_docketData);
  $Result1 =mysqli_query($docketData,$deleteSQL) or die(mysqli_error($docketData));

$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart =mysqli_query($docketData,$query_attornys_cart) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


mysqli_select_db($docketData,$database_docketData);
$query_user = sprintf("SELECT * FROM users WHERE sessionID = '". session_id() ."'", GetSQLValueString($colname_user, "text"));
$user =mysqli_query($docketData,$query_user) or die(mysqli_error($docketData));
$row_user = mysqli_fetch_assoc($user);
$totalRows_user = mysqli_num_rows($user);



mysqli_select_db($docketData,$database_docketData);
$query_courts = sprintf("SELECT * FROM cart WHERE courttype <> 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$courts =mysqli_query($docketData,$query_courts) or die(mysqli_error($docketData));
$row_courts = mysqli_fetch_assoc($courts);
$totalRows_courts = mysqli_num_rows($courts);

mysqli_select_db($docketData,$database_docketData);
$query_state = sprintf("SELECT * FROM cart WHERE courttype = 'state' and sessionid = '". session_id() ."'", GetSQLValueString($colname_courts, "text"));
$state =mysqli_query($docketData,$query_state) or die(mysqli_error($docketData));
$row_state = mysqli_fetch_assoc($state);
$totalRows_state = mysqli_num_rows($state);




// get courts
if ($totalRows_courts > 0){
	do {
		$indCourts = $row_courts['systemid'].','.$indCourts;
	} while ($row_courts = mysqli_fetch_assoc($courts));
}

if ($totalRows_state > 0){
	do {
		$indState = $row_state['systemid'].','.$indState;
	} while ($row_state = mysqli_fetch_assoc($state));
}

echo $indCourts .'<BR>';
echo $indState .'<BR>';
		
					   
///////////////// save sub users to CRC api /////////////////////////////////////////////////////////////////////////
		require '../include/Pest.php';
		$loginToken = $CRCloginToken;
		
		
		$userToken = new Pest($CRCurl);
		$userToken = $userToken->get('/users/'.session_id() .'?password='.$row_user['userpassword'].'&soapREST=SOAP');
		$userToken = new SimpleXMLElement($userToken);
		$ParentLoginToken = $userToken;
		echo "ParentLoginToken: ". $ParentLoginToken;
		if ($totalRows_attornys_cart > 0) { // if there are more attorney's
			do {
				
				if (empty($row_attornys_cart['isActive'])){ // if new attorney

					
				}else{ // attorney is existing so just update
				
				// echo  ' EXISTING: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

						
				if (empty($row_attornys_cart['username'])) {
					$user = $row_user['username'];
					$pass = $row_user['userpassword'];
				}else{
					$user = $row_attornys_cart['username'];
					$pass = $row_attornys_cart['password'];
				}
						$loginToken = $CRCloginToken;

						$userToken = new Pest($CRCurl);
						$userToken = $userToken->get('/users/'. $user .'?password='. $pass .'&soapREST=SOAP');
						$userToken = new SimpleXMLElement($userToken);
						$userLoginToken = $userToken;
						
						
						// get user
//						$user = new Pest($CRCurl);
//						$user = $user->get('/user?loginToken='.$userLoginToken);
//						$user = new SimpleXMLElement($user);
//						$url = $CRCurl;
						
						
						//set xml <strong class="highlight">request</strong> 35809
						$xml = '
						<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
						<Comments></Comments>
						<ContactName>'. $row_attornys_cart['name'] .'</ContactName>
						<EMail>'. $row_attornys_cart['email'] .'</EMail>
						<FaxNumber></FaxNumber>
						<Login>'. $row_attornys_cart['username'].'</Login>
						<LoginToken>'. $userLoginToken .'</LoginToken>
						<Name>'. $row_user ['firm']  .'</Name>
						<NotifyModCourt>true</NotifyModCourt>
						<NotifyModEvent>false</NotifyModEvent>
						<Password>'.$row_attornys_cart['password'] .'</Password>
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
						   
						
						$xml = $xml . '
						  <Subscription>
							<CourtSystems>';
							// add states
							// if just one remove trailing slash
							if ($indState <> ''){
									$states	= $indState;
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
							
							// add individual courts // from order
							if ($indCourts <> ''){
									$courts	= $indCourts;
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
						$xml = $xml . '</Jurisdictions></Subscription>
						<VendorLoginToken>'. $ParentLoginToken .'</VendorLoginToken>
						</User>'; 
						//echo $xml;
						$session = curl_init($CRCurl.'/user');
						curl_setopt ($session, CURLOPT_POST, true);
						curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($session, CURLOPT_HEADER, true);
						curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						
						$response = curl_exec($session);
						//print_r ($response);
						curl_close($session);
						
				} // end if new attorney
			} while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); 
		} // end if more attorney's

		
		  // update users cart to mark subscribed courts
		  $updateSQL = sprintf("UPDATE cart SET subscribed=%s WHERE sessionid=%s",
                       GetSQLValueString($_SESSION['userid'], "int"),
                       GetSQLValueString(session_id(), "text"));
					   mysqli_select_db($docketData,$database_docketData);
  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
    	// update users attorneys to mark subscribed attorneys
		  $updateSQL = sprintf("UPDATE attorneys SET isActive='1', user_id=%s WHERE sessionid=%s",
                       GetSQLValueString($_SESSION['userid'],"int"),
					   GetSQLValueString(session_id(), "text"));
					   mysqli_select_db($docketData,$database_docketData);
  					   $Result1 =mysqli_query($docketData,$updateSQL) or die(mysqli_error($docketData));
		
		
///////////////// end sub users user to CRC api /////////////////////////////////////////////////////////////////////////
		
  		// all set redirect
  $deleteGoTo = "/courts";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  
  header(sprintf("Location: %s", $deleteGoTo));



?>