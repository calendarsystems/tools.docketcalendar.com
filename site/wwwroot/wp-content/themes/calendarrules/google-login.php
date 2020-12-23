<?php
/*
Template Name: Google Calendar Login
*/
//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() { 
    require('globals/global_tools.php');
    require('globals/global_courts.php');
	session_start();
?>

<?php

$login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online'; 
 
?>

<div style="padding-left: 195px;padding-top: 20px;">
   <div>
    <a id="logo" title="Click here to login" href="<?php echo $login_url ?>">Please login with Google Authentication to Import/Edit/Delete Events in the Google Calendar.</a>
   </div>
   <div style="padding-left: 165px;padding-top: 28px;">
      <a id="logo" href="<?php echo $login_url ?>"><img title="Click here to login" style="width:200px;height:200px;" src="https://tools.docketcalendar.com/google-logo-300x300.png"></a>
   </div>
</div>
<?php
}
genesis();
?>

