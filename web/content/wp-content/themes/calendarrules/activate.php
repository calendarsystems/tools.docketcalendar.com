<?php
/* 
Template Name: activate
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
global $docketData;
include ("Connections/docketData.php");
//}

//require_once('Connections/docketData.php');


?>

      	<h1 class="entry-title">Trial Activated</h1>

              <p class="formTitles">&nbsp;</p>

             <p>   
                Your trial account has been created and will expire in 14 days.  You will receive an email shortly with your account details and links to the <a href="<?php echo $installguideURL; ?>">Install</a> and <a href="<?php echo $userguideURL; ?>">User</a> Guides.  <Br /><br />Also, the password for the documentation section below is <b>crcrules</b>. </p>
                
                
                <?php
					//	echo $_SESSION['userid'] .'<BR>' . $_SESSION['firstname'] .'<BR>'. $_SESSION['username'] .'<BR>'. $_SESSION['password'];
		?>

      
<?php

mysql_free_result($user);

mysql_free_result($courts);

}

genesis();

?>
