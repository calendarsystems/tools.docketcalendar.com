<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Update Event Option
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

    require('globals/global_tools.php');
    require('globals/global_courts.php');

    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    //session_start();
    $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

    $colname_userInfo = "-1";
    if (isset($_SESSION['userid']))
    {
      $colname_userInfo = $_SESSION['userid'];
    }			
	$query_authInfo = "SELECT * FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' and user_id=".$_SESSION['userid']." ";
	$authInfo = mysqli_query($docketDataSubscribe,$query_authInfo);
	$totalRows_authInfo = mysqli_num_rows($authInfo);
	$row_authInfo = mysqli_fetch_assoc($authInfo);
	$default_court_system_id = $row_authInfo['default_jurisdication'];
	$googleContactlist=array();
	if($row_authInfo['googlecontactprefrence'] == "Yes")
	{
		function cmp($a, $b){
            if ($a == $b)
            return 0;
            return ($a['name'] < $b['name']) ? -1 : 1;
            }
            $googleContactlist = $_SESSION['google_contacts']; usort($contact_list, "cmp");
	}
	
		
	$query_case_customContact = "SELECT userContactName as name,userContactEmail as email FROM userContactUpdate  WHERE  userid = ".$_SESSION['userid']."";
    $customContact = mysqli_query($docketDataSubscribe,$query_case_customContact);
    $totalRows_customContact = mysqli_num_rows($customContact);
	
	if($totalRows_customContact > 0)
	{
		while($row_customContact = mysqli_fetch_assoc($customContact))
       {
		 $customCustomerarray[]= array('name'=>$row_customContact['name'], 'email'=>$row_customContact['email']);
       }
	   $contact_list = array_merge($googleContactlist, $customCustomerarray);
	}else{
		$contact_list =$googleContactlist;
	}
	
	$authInfo1 = mysqli_query($docketDataSubscribe,$query_authInfo);
	$totalRows_authInfo1 = mysqli_num_rows($authInfo1);
	$assignees = array();
	if($totalRows_authInfo1 > 0)
	{
		while ($row = mysqli_fetch_array($authInfo1)) {
			if($row['assignees']!=NULL)
			{
				$assignees = explode(",",$row['assignees']);
			}
			
		}
	}


$newURL="http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

$selectJurisdiction=0;
$selectTriggerItem=0;
$selectServiceType=0;

if (isset($_SESSION['userid']))
{
        function xml2array($contents, $get_attributes=1, $priority = 'tag') {
            if(!$contents) return array();

            if(!function_exists('xml_parser_create')) {
                //print "'xml_parser_create()' function not found!";
                return array();
            }

            //Get the XML parser of PHP - PHP must have this module for the parser to work
            $parser = xml_parser_create('');
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($parser, trim($contents), $xml_values);
            xml_parser_free($parser);

            if(!$xml_values) return;//Hmm...

            //Initializations
            $xml_array = array();
            $parents = array();
            $opened_tags = array();
            $arr = array();

            $current = &$xml_array; //Refference

            //Go through the tags.
            $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
            foreach($xml_values as $data) {
                unset($attributes,$value);//Remove existing values, or there will be trouble

                //This command will extract these variables into the foreach scope
                // tag(string), type(string), level(int), attributes(array).
                extract($data);//We could use the array by itself, but this cooler.

                $result = array();
                $attributes_data = array();

                if(isset($value)) {
                    if($priority == 'tag') $result = $value;
                    else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
                }

                //Set the attributes too.
                if(isset($attributes) and $get_attributes) {
                    foreach($attributes as $attr => $val) {
                        if($priority == 'tag') $attributes_data[$attr] = $val;
                        else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }

                //See tag status and do the needed.
                if($type == "open") {//The starting of the tag '<tag>'
                    $parent[$level-1] = &$current;
                    if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                        $current[$tag] = $result;
                        if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                        $repeated_tag_index[$tag.'_'.$level] = 1;

                        $current = &$current[$tag];

                    } else { //There was another element with the same tag name

                        if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                            $repeated_tag_index[$tag.'_'.$level]++;
                        } else {//This section will make the value an array if multiple tags with the same name appear together
                            $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                            $repeated_tag_index[$tag.'_'.$level] = 2;

                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                        }
                        $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                        $current = &$current[$tag][$last_item_index];
                    }

                } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
                    //See if the key is already taken.
                    if(!isset($current[$tag])) { //New Key
                        $current[$tag] = $result;
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;


                    } else { //If taken, put all things inside a list(array)
                        if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                            // ...push the new element into that array.
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                            if($priority == 'tag' and $get_attributes and $attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                            $repeated_tag_index[$tag.'_'.$level]++;

                        } else { //If it is not an array...
                            $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                            $repeated_tag_index[$tag.'_'.$level] = 1;
                            if($priority == 'tag' and $get_attributes) {
                                if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                                    $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                    unset($current[$tag.'_attr']);
                                }

                                if($attributes_data) {
                                    $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                                }
                            }
                            $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                        }
                    }

                } elseif($type == 'close') { //End of tag '</tag>'
                    $current = &$parent[$level-1];
                }
            }

            return($xml_array);
        }

    $command="/users/".$_SESSION['username']."?";
    $parameters="password=".$_SESSION['password']."&soapREST=REST";
    $file= $newURL.$command.$parameters;

    $content=file_get_contents($newURL.$command.$parameters,false,$context);
    $xml=$content;
    $array=xml2array($xml);

    $loginToken=$array['string'];


    $newURL="http://www.crcrules.com/CalendarRulesService.svc/rest";

    $command="/jurisdictions/my?";
    $parameters="loginToken=$loginToken";

    $file= $newURL.$command.$parameters;


    $content=file_get_contents($file,false,$context);
    $xml=$content;
    $array=xml2array($xml);

    $theTotalJurisdictions = $array['ArrayOfJurisdiction']['Jurisdiction'];
    $numJuris=sizeof($theTotalJurisdictions);

}
?>
<style>
 #divform1 { font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;}
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
    }
#loading-img {
    background: url((https://tools.docketcalendar.com/assets/images/ajax-loader.gif) center center no-repeat;
    height: 100%;
    z-index: 20;
	width: 100%;
}

.overlay {
	display: none;
	width: 100%;
    height: 100%;
	background: #e9e9e9;
    position: fixed;
    top: 0;
    left: 0;
	opacity: 0.5;
    z-index: 100; /* Just to keep it at the very top */
}
.switch {
  position: relative;
  display: inline-block;
  width: 90px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
   background-color: grey;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: grey;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(55px);
  -ms-transform: translateX(55px);
  transform: translateX(55px);
}

/*------ ADDED CSS ---------*/
.on
{
  display: none;
}

.on, .off
{
  color: white;
  position: absolute;
  transform: translate(-50%,-50%);
  top: 50%;
  left: 50%;
  font-size: 14px;
  font-family: Verdana, sans-serif;
}

input:checked+ .slider .on
{display: block;}

input:checked + .slider .off
{display: none;}

/*--------- END --------*/

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;}

</style>
<!-- CSS-->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
	<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
	<!-- CSS -->
	<link rel = "stylesheet" type = "text/css"    href = "https://tools.docketcalendar.com/jquery/css/standalone.css">
	<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/fastselect.css">

<div class="overlay">
    <div id="loading-img"></div>
</div>
<div id="divform1">
	<div style="width: 80%;">
		<div style="float: left;width: 70%;"><h2>User Preferences</h2></div>
		<div style="float: right;"><a href="https://tools.docketcalendar.com/excludevents">Exclude Events</a></div>
	</div>
		<div class="widget">
		<form id="form1" method="POST" action="<?php echo get_home_url(); ?>/procs/update_events_option.php">
			<table class="table table-striped" width="100%">
				<tr>
				   <td style="width:25%">Default Jurisdiction:</td>
				   <td>
				   <select name="cmbJurisdictions" id="cmbJurisdictions" style="width:400px;">
				   <option value="0">---Select Court---</option>

			<?php if (isset($theTotalJurisdictions['Code'])) { ?>
					<option value="<?php echo $theTotalJurisdictions['SystemID']; ?>"
					<?php if ($selectJurisdiction==$theTotalJurisdictions['SystemID'] || $theTotalJurisdictions['SystemID'] == $default_court_system_id) {
						echo 'selected = "selected"';}
						?>><?php     echo $theTotalJurisdictions['Description']; ?></option>
		   <?php } else {
				  foreach($theTotalJurisdictions as $juris)
				  {
			?>
				   <option value="<?php echo $juris['SystemID']; ?>" <?php if ($selectJurisdiction==$juris['SystemID'] || ($default_court_system_id == $juris['SystemID']) ) { echo 'selected = "selected"'; }
					 if ( ($default_court_system_id == $juris['SystemID'])){ echo 'selected = "selected"'; } ?>>
					<?php     echo $juris['Description']; ?></option>
			<?php
				  }
				 }
	  ?>
		 </select></td>
				</tr>

				<tr>
				   <td style="width:25%">Add court rule to body:</td>
				   <td>
				   <select name="add_court_rule_body" id="add_court_rule_body" style="width:300px;">
					  <option value="Don't add" <?php if($row_authInfo['add_court_rule_body'] == "Don't add") { ?> selected="selected" <?php } ?>>Don't add</option>
					  <option value="Rule Text" <?php if($row_authInfo['add_court_rule_body'] == "Rule Text") { ?> selected="selected" <?php } ?>>Rule Text</option>
					  <option value="Rule ID only" <?php if($row_authInfo['add_court_rule_body'] == "Rule ID only") { ?> selected="selected" <?php } ?>>Rule ID only</option>
				   </select>
				   </td>
				</tr>

				<tr>
				   <td style="width:25%">Add date rule to body:</td>
				   <td>
				   <select name="add_date_rule_body" id="add_date_rule_body" style="width:300px;">
					  <option value="yes" <?php if($row_authInfo['add_date_rule_body'] == "yes") { ?> selected="selected" <?php } ?>>yes</option>
					  <option value="no" <?php if($row_authInfo['add_date_rule_body'] == "no") { ?> selected="selected" <?php } ?>>no</option>
				   </select>
				   </td>
				</tr>

				<tr>
				   <td style="width:25%">Case name location:</td>
				   <td>
				   <select name="case_name_location" id="case_name_location" style="width:300px;">
					  <option value="don't add" <?php if($row_authInfo['case_name_location'] == "don't add") { ?> selected="selected" <?php } ?>>don't add</option>
					  <option value="prepend to subject" <?php if($row_authInfo['case_name_location'] == "prepend to subject") { ?> selected="selected" <?php } ?>>prepend to subject</option>
					  <option value="append to subject" <?php if($row_authInfo['case_name_location'] == "append to subject") { ?> selected="selected" <?php } ?>>append to subject</option>
					  <option value="prepand to body" <?php if($row_authInfo['case_name_location'] == "prepand to body") { ?> selected="selected" <?php } ?>>prepend to body</option>
					  <option value="append to body" <?php if($row_authInfo['case_name_location'] == "append to body") { ?> selected="selected" <?php } ?>>append to body</option>
				   </select>
				   </td>
				</tr>

				 <tr>
				   <td style="width:25%">Custom text location:</td>
				   <td>
				   <select name="custom_text_location" id="custom_text_location" style="width:300px;">
					  <option value="don't add" <?php if($row_authInfo['custom_text_location'] == "don't add") { ?> selected="selected" <?php } ?>>don't add</option>
					  <option value="prepend to subject" <?php if($row_authInfo['custom_text_location'] == "prepend to subject") { ?> selected="selected" <?php } ?>>prepend to subject</option>
					  <option value="append to subject" <?php if($row_authInfo['custom_text_location'] == "append to subject") { ?> selected="selected" <?php } ?>>append to subject</option>
					  <option value="prepand to body" <?php if($row_authInfo['custom_text_location'] == "prepand to body") { ?> selected="selected" <?php } ?>>prepend to body</option>
					  <option value="append to body" <?php if($row_authInfo['custom_text_location'] == "append to body") { ?> selected="selected" <?php } ?>>append to body</option>
				   </select>
				   </td>
				</tr>
				<tr>
				   <td style="width:25%">Reminder for Email:</td>
				   <td>
				   <select name="reminder_minutes" id="reminder_minutes" style="width:300px;">
				   <option value="5" <?php if($row_authInfo['reminder_minutes'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_authInfo['reminder_minutes'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_authInfo['reminder_minutes'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_authInfo['reminder_minutes'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_authInfo['reminder_minutes'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_authInfo['reminder_minutes'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_authInfo['reminder_minutes'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_authInfo['reminder_minutes'] == 240) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="300" <?php if($row_authInfo['reminder_minutes'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_authInfo['reminder_minutes'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_authInfo['reminder_minutes'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_authInfo['reminder_minutes'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_authInfo['reminder_minutes'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_authInfo['reminder_minutes'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_authInfo['reminder_minutes'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_authInfo['reminder_minutes'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_authInfo['reminder_minutes'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_authInfo['reminder_minutes'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_authInfo['reminder_minutes'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_authInfo['reminder_minutes'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_authInfo['reminder_minutes'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_authInfo['reminder_minutes'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_authInfo['reminder_minutes'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
					</td>
				</tr>
				<tr>
				   <td style="width:25%">Reminder for PopUp:</td>
				   <td>
				   <select name="reminder_minutes_popup" id="reminder_minutes_popup" style="width:300px;">
				   <option value="5" <?php if($row_authInfo['reminder_minutes_popup'] == 5) { ?> selected="selected" <?php } ?>>5 Minutes</option>
				   <option value="10" <?php if($row_authInfo['reminder_minutes_popup'] == 10) { ?> selected="selected" <?php } ?>>10 Minutes</option>
				   <option value="15" <?php if($row_authInfo['reminder_minutes_popup'] == 15) { ?> selected="selected" <?php } ?>>15 Minutes</option>
				   <option value="30" <?php if($row_authInfo['reminder_minutes_popup'] == 30) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="60" <?php if($row_authInfo['reminder_minutes_popup'] == 60) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="120" <?php if($row_authInfo['reminder_minutes_popup'] == 120) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="180" <?php if($row_authInfo['reminder_minutes_popup'] == 180) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="240" <?php if($row_authInfo['reminder_minutes_popup'] == 240) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="300" <?php if($row_authInfo['reminder_minutes_popup'] == 300) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="360" <?php if($row_authInfo['reminder_minutes_popup'] == 360) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="420" <?php if($row_authInfo['reminder_minutes_popup'] == 420) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="480" <?php if($row_authInfo['reminder_minutes_popup'] == 480) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="540" <?php if($row_authInfo['reminder_minutes_popup'] == 540) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="600" <?php if($row_authInfo['reminder_minutes_popup'] == 600) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="660" <?php if($row_authInfo['reminder_minutes_popup'] == 660) { ?> selected="selected" <?php } ?>>11 hours</option>
				   <option value="720" <?php if($row_authInfo['reminder_minutes_popup'] == 720) { ?> selected="selected" <?php } ?>>0.5 Day</option>
				   <option value="1080" <?php if($row_authInfo['reminder_minutes_popup'] == 1080) { ?> selected="selected" <?php } ?>>18 hours</option>
				   <option value="1440" <?php if($row_authInfo['reminder_minutes_popup'] == 1440) { ?> selected="selected" <?php } ?>>1 Day</option>
				   <option value="2880" <?php if($row_authInfo['reminder_minutes_popup'] == 2880) { ?> selected="selected" <?php } ?>>2 Days</option>
				   <option value="4320" <?php if($row_authInfo['reminder_minutes_popup'] == 4320) { ?> selected="selected" <?php } ?>>3 Days</option>
				   <option value="5760" <?php if($row_authInfo['reminder_minutes_popup'] == 5760) { ?> selected="selected" <?php } ?>>4 Days</option>
				   <option value="10080" <?php if($row_authInfo['reminder_minutes_popup'] == 10080) { ?> selected="selected" <?php } ?>>1 Week</option>
				   <option value="20160" <?php if($row_authInfo['reminder_minutes_popup'] == 20160) { ?> selected="selected" <?php } ?>>2 Weeks</option>
				   </select>
					</td>
				</tr>
				<tr>
				   <td style="width:35%">Show trigger title on calendar body:</td>
				   <td>
				   <select name="show_trigger" id="show_trigger" style="width:100px;">
					  <option value="yes" <?php if($row_authInfo['show_trigger'] == "yes") { ?> selected="selected" <?php } ?>>yes</option>
					  <option value="no" <?php if($row_authInfo['show_trigger'] == "no") { ?> selected="selected" <?php } ?>>no</option>
				   </select>
				   </td>
				</tr>
				<tr>
				   <td style="width:25%">Add Assignee's :</td>
				   <td>
				    <?php 
					if(isset($_SESSION['google_contacts'])) { ?>
                    <select multiple id="attendee" class="multipleSelect" name="addassignee[]" style="width:410px">
                     <?php
					
					 foreach($contact_list as $contact) {

						if($_SESSION['author_id'] == $contact['email'])
						{
							 unset($contact['email']); 
							 unset($contact['name']); 
						} 					 
					?>	
					<option value="<?php echo $contact['email'];?>" <?php if(in_array($contact['email'],$assignees)) {  ?> selected="selected" <?php } ?>><?php echo $contact['name'];?></option>

                     <?php } ?>
                    </select>
                    <?php } ?>
				   </td>
				</tr>
				<tr>
				   <td style="width:25%">All day appointments status:</td>
				   <td>
				   <select name="all_day_appointments" id="all_day_appointments" style="width:100px;">
					  <option value="free" <?php if($row_authInfo['all_day_appointments'] == "free") { ?> selected="selected" <?php } ?>>free</option>
					  <option value="busy" <?php if($row_authInfo['all_day_appointments'] == "busy") { ?> selected="selected" <?php } ?>>busy</option>
				   </select>
				   </td>
				</tr>
				<tr>
				   <td style="width:25%">Appointments w\time status:</td>
				   <td>
				   <select name="appointments_status" id="appointments_status" style="width:100px;">
					  <option value="free" <?php if($row_authInfo['appointments_status'] == "free") { ?> selected="selected" <?php } ?>>free</option>
					  <option value="busy" <?php if($row_authInfo['appointments_status'] == "busy") { ?> selected="selected" <?php } ?>>busy</option>
				   </select>
				   </td>
				</tr>
				 <tr>
				   <td style="width:25%">Appointment length:</td>
				   <td>
				   <select name="appointment_length" id="appointment_length" style="width:100px;">
				   <option value="1800" <?php if($row_authInfo['appointment_length'] == 1800) { ?> selected="selected" <?php } ?>>30 Minutes</option>
				   <option value="3600" <?php if($row_authInfo['appointment_length'] == 3600) { ?> selected="selected" <?php } ?>>1 hour</option>
				   <option value="7200" <?php if($row_authInfo['appointment_length'] == 7200) { ?> selected="selected" <?php } ?>>2 hours</option>
				   <option value="10800" <?php if($row_authInfo['appointment_length'] == 10800) { ?> selected="selected" <?php } ?>>3 hours</option>
				   <option value="14400" <?php if($row_authInfo['appointment_length'] == 14400) { ?> selected="selected" <?php } ?>>4 hours</option>
				   <option value="18000" <?php if($row_authInfo['appointment_length'] == 18000) { ?> selected="selected" <?php } ?>>5 hours</option>
				   <option value="21600" <?php if($row_authInfo['appointment_length'] == 21600) { ?> selected="selected" <?php } ?>>6 hours</option>
				   <option value="25200" <?php if($row_authInfo['appointment_length'] == 25200) { ?> selected="selected" <?php } ?>>7 hours</option>
				   <option value="28800" <?php if($row_authInfo['appointment_length'] == 28800) { ?> selected="selected" <?php } ?>>8 hours</option>
				   <option value="32400" <?php if($row_authInfo['appointment_length'] == 32400) { ?> selected="selected" <?php } ?>>9 hours</option>
				   <option value="36000" <?php if($row_authInfo['appointment_length'] == 36000) { ?> selected="selected" <?php } ?>>10 hours</option>
				   <option value="39600" <?php if($row_authInfo['appointment_length'] == 39600) { ?> selected="selected" <?php } ?>>11 hours</option>
				  <option value="43200" <?php if($row_authInfo['appointment_length'] == 43200) { ?> selected="selected" <?php } ?>>12 hours</option>
				   </select>
				   </td>
				</tr>
				<!--
				<tr>
				   <td style="width:25%">Recalculated events:</td>
				   <td>
				   <select name="recalculated_events" id="recalculated_events" style="width:300px;">
					  <option value="replace events" <?php //if($row_authInfo['recalculated_events'] == "replace events") { ?> selected="selected" <?php //} ?>>replace events</option>
				   </select>
				   </td>
				</tr>
				-->
				<!--
				<tr>
				   <td style="width:25%">"Do Not Recalculate" events:</td>
				   <td>
				   <select name="do_not_recalculate_events" id="do_not_recalculate_events" style="width:300px;">
					  <option value="use original date" <?php //if($row_authInfo['do_not_recalculate_events'] == "use original date") { ?> selected="selected" <?php //} ?>>use original date</option>
					  <option value="use new date" <?php //if($row_authInfo['do_not_recalculate_events'] == "use new date") { ?> selected="selected" <?php // } ?>>use new date</option>
				   </select>
				   </td>
				</tr>
				-->
				<tr>
					<td style="width:25%">Prefrence for Google Contacts:</td>
					<td>
						<label class="switch">
							<input type="checkbox" id="togBtn" name="togBtn" <?php if($row_authInfo['googlecontactprefrence'] == "Yes") { ?> checked <?php } ?>>
							<div class="slider round"><!--ADDED HTML -->
							<span class="on">Yes</span>
							<span class="off">No</span><!--END-->
							</div>
							<input type="hidden" name="hiddenGooglePrefrencesValue" id="hiddenGooglePrefrencesValue"/>
						</label>
					</td>
				</tr>
				<tr>
				   <td style="width:25%">Add "CalendarRulesEvent" Tag:</td>
				   <td>
				   <select name="calendar_rules_events_tag" id="calendar_rules_events_tag" style="width:300px;">
					  <option value="Yes" <?php if($row_authInfo['calendar_rules_events_tag'] == "Yes") { ?> selected="selected" <?php } ?>>Yes</option>
					  <option value="No" <?php if($row_authInfo['calendar_rules_events_tag'] == "No") { ?> selected="selected" <?php } ?>>No</option>
				   </select>
				   </td>
				</tr>
				<tr>
				   <td style="width:25%">Event Color:</td>
				   <td>
				  <select name="eventColor" id="eventColor" style="width:300px;">
				   <option value="0" <?php if($row_authInfo['eventColor'] == "0") { ?> selected="selected" <?php } ?>>No Color</option>
				   <option value="11" <?php if($row_authInfo['eventColor'] == "11") { ?> selected="selected" <?php } ?>>Tomato</option><div style="background-color:red;width:20px;height:5px;"></div>
				   <option value="6" <?php if($row_authInfo['eventColor'] == "6") { ?> selected="selected" <?php } ?>>Tangerine</option>
				   <option value="2" <?php if($row_authInfo['eventColor'] == "2") { ?> selected="selected" <?php } ?>>Sage</option>
				   <option value="7" <?php if($row_authInfo['eventColor'] == "7") { ?> selected="selected" <?php } ?>>Peacock</option>
				   <option value="1" <?php if($row_authInfo['eventColor'] == "1") { ?> selected="selected" <?php } ?>>Lavender</option>
				   <option value="8" <?php if($row_authInfo['eventColor'] == "8") { ?> selected="selected" <?php } ?>>Graphite</option>
				   <option value="4" <?php if($row_authInfo['eventColor'] == "4") { ?> selected="selected" <?php } ?>>Flamingo</option>
				   <option value="5" <?php if($row_authInfo['eventColor'] == "5") { ?> selected="selected" <?php } ?>>Banana</option>
				   <option value="10" <?php if($row_authInfo['eventColor'] == "10") { ?> selected="selected" <?php } ?>>Basil</option>
				   <option value="9" <?php if($row_authInfo['eventColor'] == "9") { ?> selected="selected" <?php } ?>>Blueberry</option>
				   <option value="3" <?php if($row_authInfo['eventColor'] == "3") { ?> selected="selected" <?php } ?>>Grape</option>
				   </select>&nbsp;&nbsp;<div id="colorIdentifier" style="width:20px;height:10px;"></div>
				   </td>
				   
				</tr>	
				<tr>
					<td colspan="2" align="center"><input type="button" id="eventOption" value="Update">
					&nbsp;</td>
				</tr>
			</table>
			</form>
		</div>
	</div>
<script type="text/javascript">
jQuery(".overlay").show();
jQuery('.multipleSelect').fastselect();
jQuery('.tagsInput').fastselect();
	setTimeout(function() {
	jQuery(".overlay").hide();
	}, 2000);
					   
document.addEventListener('DOMContentLoaded', function () {
  var checkbox = document.querySelector('input[type="checkbox"]');
  checkbox.addEventListener('change', function () {
    if (checkbox.checked) {
      // do this
      console.log('Checked');
	  var googlePrefrenceValue = "Yes";
	   jQuery("#hiddenGooglePrefrencesValue").val(googlePrefrenceValue);
	  
    } else {
      // do that
      console.log('Not checked');
	  
	 // jQuery(".overlay").show();
	  var googlePrefrenceValue = "No";
	  jQuery("#hiddenGooglePrefrencesValue").val(googlePrefrenceValue);
	
    }
  });
});					   
	jQuery("#eventOption").click(function(){
			 $.notify("Preferences Sucessfully Addded!", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"check",
							  delay:5000,
							  blur: 0.8,
							 close: true,
							  buttonAlign: "center",
							});
						
					if(jQuery("#hiddenGooglePrefrencesValue").val() == "")
					  {
						<?php 
						  if($row_authInfo['googlecontactprefrence'] != "")
						  {
						?>	  
						   jQuery("#hiddenGooglePrefrencesValue").val('<?php echo $row_authInfo['googlecontactprefrence']; ?>');
                        <?php 						   
						  }							  
						?>
					  }
					   jQuery("#form1").submit();
			
			
			});
		
</script>					   
<?php
}
genesis();
?>
