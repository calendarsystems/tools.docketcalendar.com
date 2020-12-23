<?php require_once('Connections/docketData.php'); 
session_start();
		$userToken = new Pest($CRCurl);
		$userToken = $userToken->get('/users/'. $row_user['username'] .'?password='. $row_user['userpassword'] .'&soapREST=REST');
		$userToken = new SimpleXMLElement($userToken);
		$ParentLoginToken = $userToken;
		
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
						
						echo '<BR> userLoginToken: '. $userLoginToken;
						
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
						<Name>'. $firm  .'</Name>
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
							echo 'states: '. $indState.'<BR><BR>';
							echo 'courts: '. $indCourts.'<BR><BR>';
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
						
						// save xml for debugging:
//		$myFile = "Convert_". $row_attornys_cart['username'].".txt";
//		$fh = fopen($myFile, 'w') or die("can't open file");
//		$stringData = $xml;
//		fwrite($fh, $stringData);
//		fclose($fh);
						
						$session = curl_init($CRCurl.'/user');
						curl_setopt ($session, CURLOPT_POST, true);
						curl_setopt ($session, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($session, CURLOPT_HEADER, true);
						curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
						curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
						
						
						$response = curl_exec($session);
						print_r ($response);
						curl_close($session);
?>