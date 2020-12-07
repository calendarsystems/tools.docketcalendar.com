<?php 
require_once '../Connections/docketData.php';
session_start();
ob_start();
//error_reporting(E_ALL);
global $payment_profile_id;
//ini_set("display_errors", "1");
  error_reporting(E_ALL);
$docketDataNew = $GLOBALS['docketDataNew'];
$database_docketData = $GLOBALS['databaseNew'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	  $docketDataNew = $GLOBALS['docketDataNew'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketDataNew,$theValue) : mysqli_escape_string($docketDataNew,$theValue);

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
echo "STEP1";
$colname_userinfo = "-1";
if (isset($_SESSION['userid'])) {
	$colname_userinfo = $_SESSION['userid'];
}
mysqli_select_db($docketDataNew,$database_docketData);
$query_userinfo = sprintf("SELECT * FROM users WHERE id = %s", GetSQLValueString($colname_userinfo, "int"));
$userinfo = mysqli_query($docketDataNew,$query_userinfo) or die(mysqli_error($docketDataNew));
$row_userinfo = mysqli_fetch_assoc($userinfo);
$totalRows_userinfo = mysqli_num_rows($userinfo);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// update Authorize.net info if CC has changed
if ($_POST['oldcardnumber'] == 'XXXXXXXXXXX') {
	echo "STEP2";
	require '../classes/register.php';
	$random = substr(number_format(time() * rand(), 0, '', ''), 0, 10);
	$expiration = $_POST['year'] . '-' . $_POST['month'];

	CreateCIM_PaymentProfile($random, $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['firm'], $_POST['billingfirstname'], $_POST['billinglastname'], $_POST['billingaddress'], $_POST['city'], $_POST['state'], $_POST['zip'], '', '', $_POST['cardnumber'], $expiration, '', '', '', '', '');

} else {
	require '../classes/update_auth.php';
	echo "STEP3";
	if ($_POST['oldcardnumber'] != $_POST['cardnumber']) //If user has change the credit card number.
	{
		$carNumber = $_POST['cardnumber'];
		$changeCard = 'yes';
	} else {
		$carNumber = "XXXX" . substr($_POST['cardnumber'], -4);
		
		$changeCard = 'no';
	}

	//echo "card number :" . $carNumber;
	//echo "card status :" . $changeCard;exit();
	if ($row_userinfo['auth_profile_id'] != '') {
echo "STEP4";

		UpdateCIM_PaymentProfile($_SESSION['userid'], $row_userinfo['auth_profile_id'], $row_userinfo['auth_payment_id'], $row_userinfo['auth_shipping_id'], $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['firm'], $_POST['billingfirstname'], $_POST['billinglastname'], $_POST['billingaddress'], $_POST['city'], $_POST['state'], $_POST['zip'], '', '', $carNumber, $_POST['month'] . '-' . $_POST['year'], $_POST['billingaddress'], $_POST['city'], $_POST['state'], $_POST['zip'], '', $changeCard);
		$payment_profile_id = $_SESSION['auth_payment_id'];
		echo "Payment Profile ID" . $payment_profile_id;
		if($payment_profile_id){
			
			if ($payment_profile_id > 0) {
				$payment_id = $payment_profile_id;
			} else {
				$payment_id = $row_userinfo['auth_payment_id'];
			}

			if ($shipping_profile_id > 0) {
				$shipping_profile_id = $shipping_profile_id;
			} else {
				$shipping_profile_id = $row_userinfo['auth_shipping_id'];
			}
	
			$updateSQL = sprintf("UPDATE users SET  firstname=%s, lastname=%s, email=%s, phone=%s, firm=%s, billingfirstname=%s, billinglastname=%s, billingaddress=%s, billingcity=%s, billingstate=%s, billingzip=%s, auth_payment_id=%s, auth_shipping_id=%s, CardLastFour=%s, Month=%s, Year=%s WHERE id=%s",

			GetSQLValueString($_POST['firstname'], "text"),
			GetSQLValueString($_POST['lastname'], "text"),
			GetSQLValueString($_POST['email'], "text"),
			GetSQLValueString($_POST['phone'], "text"),
			GetSQLValueString($_POST['firm'], "text"),
			GetSQLValueString($_POST['billingfirstname'], "text"),
			GetSQLValueString($_POST['billinglastname'], "text"),
			GetSQLValueString($_POST['billingaddress'], "text"),
			GetSQLValueString($_POST['city'], "text"),
			GetSQLValueString($_POST['state'], "text"),
			GetSQLValueString($_POST['zip'], "text"),
			GetSQLValueString($payment_id, "text"),
			GetSQLValueString($shipping_profile_id, "text"),
			GetSQLValueString(substr($_POST['cardnumber'], -4), "text"),
			GetSQLValueString($_POST['month'], "text"),
			GetSQLValueString($_POST['year'], "text"),
			GetSQLValueString($_SESSION['userid'], "int"));
			
			
		}
			mysqli_select_db($docketDataNew,$database_docketData);
	$Result1 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

	$updateSQL = sprintf("UPDATE attorneys SET email=%s,  name=%s WHERE attorneyID=%s",
		GetSQLValueString($_POST['email'], "text"),
		GetSQLValueString($_POST['firstname'] . " " . $_POST['lastname'], "text"),
		GetSQLValueString($_POST['attorneyid'], "int"));

	mysqli_select_db($docketDataNew,$database_docketData);
	$Result2 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

	// end update user on CRC side
	// update session variables
	$_SESSION['firstname'] = $_POST['firstname'];
	$_SESSION['fullname'] = $_POST['firstname'] . " " . $_POST['lastname'];
	$_SESSION['password'] = $_POST['password'];
		header(sprintf("Location: %s", "/update-card?updated=1&msg=success"));
		exit();
			

	}
}


if ($payment_profile_id > 0 || ($payment_profile_id == 0 && $_POST['oldcardnumber'] == $_POST['cardnumber'])) {

	// let's create a .01 authorization transaction

	$cim = new AuthnetCIM($Auth_API_Login, $Auth_TransactionKey, AuthnetCIM::USE_PRODUCTION_SERVER);

	if ($_SESSION['auth_profile_id'] != '') {
		$profile_id = $_SESSION['auth_profile_id'];
	} else {
		$profile_id = $row_userinfo['auth_profile_id'];
		$_SESSION['auth_profile_id'] = $profile_id;
	}

	if ($payment_profile_id > 0) {
		$payment_id = $payment_profile_id;
	} else {
		$payment_id = $row_userinfo['auth_payment_id'];
	}

	if ($shipping_profile_id > 0) {
		$shipping_profile_id = $shipping_profile_id;
	} else {
		$shipping_profile_id = $row_userinfo['auth_shipping_id'];
	}

	echo "Profile ID: " . $profile_id . "<br>Payment ID: " . $payment_id . "<br>Shipping Profile ID: " . $shipping_profile_id;

	$purchase_amount = .01;
echo "STEP5";
	// Process the transaction
	$cim->setParameter('amount', $purchase_amount);
	$cim->setParameter('customerProfileId', $profile_id);

	$cim->setParameter('customerPaymentProfileId', $payment_id);

	$cim->setParameter('customerShippingAddressId', $shipping_profile_id);

//  this is where you set the receipt info /////////////////////////////////////////////////////////////////

	echo "Vendor: " . $_SESSION['site'];
	$cim->setParameter('orderInvoiceNumber', $_SESSION['site']);
	$cim->setParameter('orderDescription', 'Verify New Card');
	$cim->setParameter('orderPurchaseOrderNumber', '12345');

	//				  SKU,  Item Name	  , Description, 			  Qty,  Item unit price
	$cim->setLineItem('Calendar Rules', 'Card Auth Verify', 'Calendar Rules', '1', $purchase_amount);

	$cim->createCustomerProfileTransaction('profileTransAuthOnly');

	// Get the payment profile ID returned from the request
	if ($cim->isSuccessful()) {
		echo "createCustomerProfileTransaction successful<br>";
		 $approval_code = $cim->getAuthCode();
		 $transaction_id = $cim->getTransactionID();
		
		//exit();
	} else {
		echo "First Instance";
		echo "createCustomerProfileTransaction failed<br>";
		echo '<b>createCustomerProfileRequest Response Summary:</b> ' . $cim->getResponseSummary() . '<br />';
		
		echo "<br>" . $cim;
		echo "<br>approval_code = " . $cim->getAuthCode();
		echo "<br>transaction_id = " . $cim->getTransactionID();
		header('Location: /update-card?cardfailed=1&email=' . $_POST['email'] . '&firstname=' . $_POST['firstname'] . '&lastname=' . $_POST['lastname'] . '&firm=' . $_POST['firm'] . '&billingfirstname=' . $_POST['billingfirstname'] . '&billinglastname=' . $_POST['billinglastname'] . '&billingaddress=' . $_POST['billingaddress'] . '&city=' . $_POST['city'] . '&state=' . $_POST['state'] . '&zip=' . $_POST['zip'] . '&username=' . $_POST['username'] . '&password=' . $_POST['password']);
		exit();
	}

	// all is good, update the web db
	echo "all is good";
	if ($changeCard == 'yes') {
	
	} else {
		$updateSQL = sprintf("UPDATE users SET  firstname=%s, lastname=%s, email=%s, phone=%s, firm=%s, billingfirstname=%s, billinglastname=%s, billingaddress=%s, billingcity=%s, billingstate=%s, billingzip=%s, CardLastFour=%s, Month=%s, Year=%s WHERE id=%s",

			GetSQLValueString($_POST['firstname'], "text"),
			GetSQLValueString($_POST['lastname'], "text"),
			GetSQLValueString($_POST['email'], "text"),
			GetSQLValueString($_POST['phone'], "text"),
			GetSQLValueString($_POST['firm'], "text"),
			GetSQLValueString($_POST['billingfirstname'], "text"),
			GetSQLValueString($_POST['billinglastname'], "text"),
			GetSQLValueString($_POST['billingaddress'], "text"),
			GetSQLValueString($_POST['city'], "text"),
			GetSQLValueString($_POST['state'], "text"),
			GetSQLValueString($_POST['zip'], "text"),
			GetSQLValueString(substr($_POST['cardnumber'], -4), "text"),
			GetSQLValueString($_POST['month'], "text"),
			GetSQLValueString($_POST['year'], "text"),
			GetSQLValueString($_SESSION['userid'], "int"));
	}

	mysqli_select_db($docketDataNew,$database_docketData);
	$Result1 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

	$updateSQL = sprintf("UPDATE attorneys SET email=%s,  name=%s WHERE attorneyID=%s",
		GetSQLValueString($_POST['email'], "text"),
		GetSQLValueString($_POST['firstname'] . " " . $_POST['lastname'], "text"),
		GetSQLValueString($_POST['attorneyid'], "int"));

	mysqli_select_db($docketDataNew,$database_docketData);
	$Result2 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

	// end update user on CRC side
	// update session variables
	$_SESSION['firstname'] = $_POST['firstname'];
	$_SESSION['fullname'] = $_POST['firstname'] . " " . $_POST['lastname'];
	$_SESSION['password'] = $_POST['password'];

} else {
	if ($_POST['cardnumber'] != $_POST['oldcardnumber']) {
		echo "Second Instance";
		//print_r($_POST);
		echo "<br>" . $cim->getResponseSummary();
		header(sprintf("Location: %s", "/update-card?cardfailed=1"));
		
		exit();
	} else {
		$updateSQL = sprintf("UPDATE users SET  firstname=%s, lastname=%s, email=%s, phone=%s, firm=%s, billingfirstname=%s, billinglastname=%s, billingaddress=%s, billingcity=%s, billingstate=%s, billingzip=%s WHERE id=%s",

			GetSQLValueString($_POST['firstname'], "text"),
			GetSQLValueString($_POST['lastname'], "text"),
			GetSQLValueString($_POST['email'], "text"),
			GetSQLValueString($_POST['phone'], "text"),
			GetSQLValueString($_POST['firm'], "text"),
			GetSQLValueString($_POST['billingfirstname'], "text"),
			GetSQLValueString($_POST['billinglastname'], "text"),
			GetSQLValueString($_POST['billingaddress'], "text"),
			GetSQLValueString($_POST['city'], "text"),
			GetSQLValueString($_POST['state'], "text"),
			GetSQLValueString($_POST['zip'], "text"),
			GetSQLValueString($_SESSION['userid'], "int"));

		mysqli_select_db($docketDataNew,$database_docketData);
		$Result1 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

		$updateSQL = sprintf("UPDATE attorneys SET email=%s,  name=%s WHERE attorneyID=%s",
			GetSQLValueString($_POST['email'], "text"),
			GetSQLValueString($_POST['firstname'] . " " . $_POST['lastname'], "text"),
			GetSQLValueString($_POST['attorneyid'], "int"));

		mysqli_select_db($docketDataNew,$database_docketData);
		$Result2 = mysqli_query($docketDataNew,$updateSQL) or die(mysqli_error($docketDataNew));

		// end update user on CRC side
		// update session variables
		$_SESSION['firstname'] = $_POST['firstname'];
		$_SESSION['fullname'] = $_POST['firstname'] . " " . $_POST['lastname'];
		$_SESSION['password'] = $_POST['password'];
		//header(sprintf("Location: %s", "/update-card?updated=1&msg=success"));
		//exit();
	}

}

mysqli_select_db($docketDataNew,$database_docketData);
$query_userinfo = sprintf("SELECT * FROM users WHERE id = %s", GetSQLValueString($_SESSION['userid'], "int"));
$user = mysqli_query($docketDataNew,$query_userinfo) or die(mysqli_error($docketDataNew));
$row_user = mysqli_fetch_assoc($user);
$totalRows_user = mysqli_num_rows($user);

mysqli_select_db($docketDataNew,$database_docketData);
$query_att = sprintf("SELECT * FROM attorneys WHERE sessionid = %s", GetSQLValueString($row_user['sessionID'], "text"));
$att = mysqli_query($docketDataNew,$query_att) or die(mysqli_error($docketDataNew));
$row_att = mysqli_fetch_assoc($att);
$totalRows_att = mysqli_num_rows($att);

// get courts in cart
mysqli_select_db($docketDataNew,$database_docketData);
$query_courts = "SELECT * FROM cart WHERE courttype <> 'state' and courttype <> 'package' and  subscribed = ".$row_user['id']." and sessionid = '" . $row_user['sessionID'] . "'";
$courts = mysqli_query($docketDataNew,$query_courts) or die(mysqli_error($docketDataNew));
$row_courts = mysqli_fetch_assoc($courts);
$totalRows_courts = mysqli_num_rows($courts);

// get states in start
mysqli_select_db($docketDataNew,$database_docketData);
$query_state = "SELECT * FROM cart WHERE courttype = 'state' and sessionid = '" . $row_user['sessionID'] . "'";
$state = mysqli_query($docketDataNew,$query_state) or die(mysqli_error($docketDataNew));
$row_state = mysqli_fetch_assoc($state);
$totalRows_state = mysqli_num_rows($state);

mysqli_select_db($docketDataNew,$database_docketData);
$query_Packages = sprintf("SELECT DISTINCT cart.id, cart.sessionid, cart.systemid, cart.courttype, package_courts.systemid FROM cart Inner Join package_courts ON cart.systemid = package_courts.packageid WHERE cart.courttype = 'package' and cart.sessionid = %s", GetSQLValueString($row_user['sessionID'], "text"));
$Packages = mysqli_query($docketDataNew,$query_Packages) or die(mysqli_error($docketDataNew));
$row_Packages = mysqli_fetch_assoc($Packages);
$totalRows_Packages = mysqli_num_rows($Packages);

mysqli_select_db($docketDataNew,$database_docketData);
$query_attornys_cart = "SELECT * FROM attorneys WHERE user_id = '" . $_SESSION['userid'] . "' ORDER BY name ASC";
$attornys_cart = mysqli_query($docketDataNew,$query_attornys_cart) or die(mysqli_error($docketDataNew));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);

$colname_attornys_cart = $row_user['id'];

mysqli_select_db($docketDataNew,$database_docketData);
$query_attornys_sub = sprintf("SELECT * FROM attorneys WHERE user_id = %s  and isActive=1  or sessionid = '" . $row_user['sessionID'] . "'  and isActive=1  ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_sub = mysqli_query($docketDataNew,$query_attornys_sub) or die(mysqli_error($docketDataNew));
$row_attornys_sub = mysqli_fetch_assoc($attornys_sub);
$totalRows_attornys_sub = mysqli_num_rows($attornys_sub);

// get courts
if ($totalRows_courts > 0) {
	do {
		$indCourts = $row_courts['systemid'] . ',' . $indCourts;
	} while ($row_courts = mysqli_fetch_assoc($courts));
}

if ($totalRows_state > 0) {
	do {
		$indState = $row_state['systemid'] . ',' . $indState;
	} while ($row_state = mysqli_fetch_assoc($state));
}

// add package courts to individual court list
if ($totalRows_Packages > 0) {
	do {
		//echo "<pre>"; print_r($row_Packages);
		if (!strpos($indCourts, $row_Pacakges['systemid'])) {
			// if this package court is not in the individual court list
			$indCourts = $row_Packages['systemid'] . ',' . $indCourts;
		}
	} while ($row_Packages = mysqli_fetch_assoc($Packages));
}

// save user to CRC api
// save main firm account first //

$url = $CRCurl;

$contactName = $row_user['firstname'] . " " . $row_user['lastname'];
$login = $row_user['sessionID'];
$softwareName = '';
$firm = $row_user['firm'];
if ($vendor == '') {$vendor = $row_user['site'];}
$password = $row_user['userpassword'];
$email = $row_user['email'];

require '../include/Pest.php';

$CRCurl = "http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
$url = $CRCurl;

$method = '/users/' . $row_user['username'] . '?password=' . rawurlencode($row_user['userpassword']) . '&soapREST=REST';

$userToken = new Pest($CRCurl);
$userToken = $userToken->get($method);
$userToken = new SimpleXMLElement($userToken);

$xml = '<User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Comments/>
    <ContactName>' . $contactName . '</ContactName>
    <EMail>' . $email . '</EMail>
    <FaxNumber/>
    <Login>' . $login . '</Login>
    <LoginToken>' . $xmlFirmArray->LoginToken . '</LoginToken>
    <Name>' . $firm . '</Name>
    <NotifyModCourt>false</NotifyModCourt>
    <NotifyModEvent>false</NotifyModEvent>
    <Password>' . $password . '</Password>
    <PhoneNumber>' . $row_user['phone'] . '</PhoneNumber>
    <Quote>
        <CourtSystems/>
        <CourtTypes/>
        <EndDate>0001-01-01T00:00:00</EndDate>
        <Jurisdictions/>
        <StartDate>0001-01-01T00:00:00</StartDate>
        <Type/>
    </Quote>
    <SoftwareName/>';

$xml = $xml . '<Subscription>
                            <CourtSystems>';

// add states
// if just one remove trailing slash
if ($indState != '') {
	$states = $indState;
	if (substr_count($states, ',') < 2) {
		$states = str_replace(',', '', $states);
	} else {
		// remove trailing comma
		$states = substr($states, 0, strlen($states) - 1);
	}
	$states = explode(",", $states);
	while (list($key, $value) = each($states)) {
		$xml = $xml . '<GenericTypeExt><SystemID>' . $value . '</SystemID></GenericTypeExt>';
	}
}

$xml = $xml . '</CourtSystems><Jurisdictions>';

// add individual courts // from order
if ($indCourts != '') {
	$courts = $indCourts;
	if (substr_count($courts, ',') < 2) {
		$courts = str_replace(',', '', $courts);
	} else {
		// remove trailing comma
		$courts = substr($courts, 0, strlen($courts) - 1);
	}
	$courts = explode(",", $courts);
	while (list($key, $value) = each($courts)) {
		$xml = $xml . '<Jurisdiction><SystemID>' . $value . '</SystemID></Jurisdiction>';
	}
}

// close out xml
$xml = $xml . '</Jurisdictions></Subscription>

    <Vendor>' . $vendor . '</Vendor>
    <VendorLoginToken>' . $xmlFirmArray->VendorLoginToken . '</VendorLoginToken>
</User>';

echo "XML=" . $xml;

$session = curl_init($url . '/user');
curl_setopt($session, CURLOPT_POST, true);
curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
curl_setopt($session, CURLOPT_HEADER, true);
curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($session);
curl_close($session);
//echo "made it past add firm";
//echo "<pre>"; print_r($response);

echo $method = '/users/' . str_replace(" ", "%20", $row_user['username']) . '?password=' . rawurlencode($row_user['userpassword']) . '&soapREST=REST';

$userToken = new Pest($CRCurl);
$userToken = $userToken->get($method);
$userToken = new SimpleXMLElement($userToken);

echo $method = '/user?loginToken=' . $userToken;

$userToken = new Pest($CRCurl);
$xml = $userToken->get($method);
$xmlUserArray = new SimpleXMLElement($xml);

//echo "here too";

echo "Test=" . $totalRows_attornys_sub;

if ($totalRows_attornys_sub > 0) {
	// if there are more attorney's
	do {

		if (empty($row_attornys_sub['isActive'])) {
			// if new attorney

		} else {
			// attorney is existing so just update

			// echo  ' EXISTING: '. $row_attornys_cart['name'] .' - '.$row_attornys_cart['username'].' - '.$row_attornys_cart['password'].' - '.$_POST['firm'].'<BR>';

			if (empty($row_attornys_sub['username'])) {
				$user = $row_user['username'];
				$pass = $row_user['userpassword'];
			} else {
				$user = $row_attornys_sub['username'];
				$pass = $row_attornys_sub['password'];
			}

			$loginToken = $CRCloginToken;
			$userToken = new Pest($CRCurl);
			$userToken = $userToken->get('/users/' . str_replace(" ", "%20", $user) . '?password=' . rawurlencode($pass) . '&soapREST=SOAP');
			$userToken = new SimpleXMLElement($userToken);
			$userLoginToken = $userToken;

			//set xml <strong class="highlight">request</strong> 35809
			$xml = '
                        <User xmlns="http://schemas.datacontract.org/2004/07/CRC.MembershipService.Objects" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                        <Comments></Comments>
                        <ContactName>' . $contactName . '</ContactName>
                        <EMail>' . $email . '</EMail>
                        <FaxNumber></FaxNumber>
                        <Login>' . $row_attornys_sub['username'] . '</Login>
                        <LoginToken>' . $userLoginToken . '</LoginToken>
                        <Name>' . $row_user['firm'] . '</Name>
                        <NotifyModCourt>true</NotifyModCourt>
                        <NotifyModEvent>false</NotifyModEvent>
                        <Password>' . $password . '</Password>
                        <PhoneNumber>' . $row_user['phone'] . '</PhoneNumber>
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
			if ($indState != '') {
				$states = $indState;
				if (substr_count($states, ',') < 2) {
					$states = str_replace(',', '', $states);
				} else {
					// remove trailing comma
					$states = substr($states, 0, strlen($states) - 1);
				}
				$states = explode(",", $states);
				while (list($key, $value) = each($states)) {
					$xml = $xml . '<GenericTypeExt><SystemID>' . $value . '</SystemID></GenericTypeExt>';
				}
			}

			$xml = $xml . '</CourtSystems><Jurisdictions>';

			// add individual courts // from order
			if ($indCourts != '') {
				$courts = $indCourts;
				if (substr_count($courts, ',') < 2) {
					$courts = str_replace(',', '', $courts);
				} else {
					// remove trailing comma
					$courts = substr($courts, 0, strlen($courts) - 1);
				}
				$courts = explode(",", $courts);
				while (list($key, $value) = each($courts)) {
					$xml = $xml . '<Jurisdiction><SystemID>' . $value . '</SystemID></Jurisdiction>';
				}
			}

			// close out xml
			$xml = $xml . '</Jurisdictions></Subscription>
                            <Vendor>' . $vendor . '</Vendor>
                        <VendorLoginToken>' . $xmlUserArray->VendorLoginToken . '</VendorLoginToken>
                        </User>';
			//echo $xml;
			$session = curl_init($CRCurl . '/user');
			curl_setopt($session, CURLOPT_POST, true);
			curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($session, CURLOPT_HEADER, true);
			curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($session);
			print_r($response);
			curl_close($session);

		} // end if new attorney
	} while ($row_attornys_sub = mysqli_fetch_assoc($attornys_sub));
} // end if more attorney's

if ($row_user['site'] == 'TTA') {
	header("Location: http://www.ttabrules.com/?msg=update");
	exit();
} else {
	header("Location: /courts?state=" . $_SESSION['state'] . '&msg=success');
	exit();
}

mysqli_free_result($userinfo);

?>
