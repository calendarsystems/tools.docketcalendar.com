<?php 
 foreach ($_POST['attendee'] as $selectedOption){
	 
	$domainname = substr(strrchr($selectedOption, "@"), 1);
	$domain = explode(".",$domainname);
	  if($domain[0] == 'outlook')
	  {
		  echo "outlook";
	  }else 
	  {
		  echo $domain[0];
	  }
	  
 }
// initiate cURL resource
$ch = curl_init();    
// where you want to post data
$url = "https://login.microsoftonline.com/common/oauth2/v2.0/token";
curl_setopt($ch, CURLOPT_URL,$url);
// tell curl you want to post something
curl_setopt($ch, CURLOPT_POST, true);  
 $scopes = array("openid","offline_access","https://outlook.office.com/calendars.readwrite");
 $redirectUri="http://googledocket.com/docket-calculator";
// define what you want to post
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
      "grant_type" => "refresh_token",
        "refresh_token" => 'asd56454123asdasdadqe564544',
		 "redirect_uri" => $redirectUri,
        "scope" => $scopes,
      'client_id'=> '812c0837-df3a-4a86-9f7f-b2d5c8c4c0c7',
      'client_secret'=> 'kgzjXV=jhnGCNJD67803_]@'
    )));
 
// return the output in string format
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
// execute post
$output = curl_exec ($ch);
 
// close the connectionk;kl;k;k;kl;;kl;kl;kkl;k;kk;kl;k;kl;k
curl_close ($ch);
 

 
print_r($output);
