<?php 
require_once('../Connections/docketData.php'); 
require('../globals/global_tools.php');
session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
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

$colname_userInfo = "-1";
if (isset($_SESSION['userid']))
{
  $colname_userInfo = $_SESSION['userid'];
}

$query_userInfo = sprintf("SELECT * FROM attorneys WHERE user_id = %s", GetSQLValueString($docketData,$colname_userInfo, "int"));
$userInfo = mysqli_query($docketData,$query_userInfo);
$row_userInfo = mysqli_fetch_assoc($userInfo);
$totalRows_userInfo = mysqli_num_rows($userInfo);

$newURL="http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";

$selectJurisdiction=0;
$selectTriggerItem=0;
$selectServiceType=0;

if (isset($_POST['cmbJurisdictions'])) {
    $masterCourt=$_POST['cmbJurisdictions'];
    setcookie("masterCookie",$_POST['cmbJurisdictions'],time()+(60*60*24),"/");
} else {
    $masterCourt=$_COOKIE['masterCookie'];
}

if (isset($_SESSION['userid']))
{
    $command="/users/".$_SESSION['username']."?";
    $parameters="password=".$_SESSION['password']."&soapREST=REST";
    $file= $newURL.$command.$parameters;

    $content=file_get_contents($newURL.$command.$parameters,false,$context);
    $xml=$content;
    $array=xml2array($xml);

    $loginToken=$array['string'];
}

if (isset($_POST['cmbJurisdictions'])) {
    $selectJurisdiction=$_POST['cmbJurisdictions'];
}
if ($_POST['cmbCaseMatter']!="") {
		 $cmbCaseMatter = $_POST['cmbCaseMatter'];
}
if ($_POST['tab'] == 'btnSearchText') {

    if (isset($_POST['boxTrigger'])) {
        $trigger="true";
    } else {
        $trigger="false";
    }

    if (isset($_POST['boxEvent'])) {
        $event="true";
    }  else {
        $event="false";
    }

    if (isset($_POST['boxRule'])) {
        $rule="true";
    }  else {
        $rule="false";
    }

    if ($_POST['txtSearch']!="") {
        $searchText=urlencode($_POST['txtSearch']);
        $newURL="http://www.crcrules.com/CalendarRulesMembershipService.svc/rest";
        $command="/search?";
        $parameters="loginToken=$loginToken&jurisdictionSystemID=$_POST[cmbJurisdictions]&searchInTrigger=$trigger&searchInEvent=$event&searchInRule=$rule&searchText=$searchText";

        $file= $newURL.$command.$parameters;
        $content=file_get_contents($file);
        $xml=$content;
        $array=xml2array($xml);

        $theSearchResults=$array['DataSet']['diffgr:diffgram']['NewDataSet']['Table'];
        //print_r($theSearchResults);
    } else {
        $newURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
        $command="/triggers?";
        $parameters="loginToken=$loginToken&jurisdictionSystemID=$_POST[cmbJurisdictions]";
        $file= $newURL.$command.$parameters;
//print_r($file);
        $content=file_get_contents($file,false,$context);

        $xml=$content;
        $array=xml2array($xml);

        $theTriggers=$array['ArrayOfTriggerItem']['TriggerItem'];
        $theSearchResults=$array['ArrayOfTriggerItem']['TriggerItem'];
//        print_r($theTriggers);
    } ?>
<div class="searchTextResults">

<table class="triggers" width="100%"><tr><td><div class="divider"></div><h4>Results</h4></td></tr>
                        <?php echo "<pre>";
                       //print_r($theSearchResults);
                        echo "</pre>";
     if (isset($theSearchResults['tmp_sys_id'])  ) {
                                echo '<tr><td><div> ';
//echo "SINGLE ITEM!";


                                echo '<ul class="triggersnav"><a target="_top" class="docketcalclink" href="docket-calculator?cmbJurisdictions='.$_POST['cmbJurisdictions'].'&cmbTriggers='.$theSearchResults['trg_item_sys_id'].'&cmbCaseMatter='.$cmbCaseMatter.'">use in docket calculator &gt;</a><li><a href="javascript:void(0);"> '.highlight($theSearchResults['trg_item_desc'],$_POST['txtSearch']);

                                echo '<ul ><li><a href="javascript:void(0);">'.highlight($theSearchResults['short_name_desc'],$_POST['txtSearch']).'<br>';
                                echo highlight($theSearchResults['court_rule'],$_POST['txtSearch'])."<br>";
                                echo "Date Rule: ".highlight($theSearchResults['date_rule'],$_POST['txtSearch']);
                                echo '<ul id="courtdesc"><li><a href="javascript:void(0);">'.highlight($theSearchResults['court_rule_desc'],$_POST['txtSearch']);
                                echo "</a></li></ul></li>  </ul></li> </ul></div></td></tr>";
                                ?>

                                <?php


                            } else {
//echo "MULTIPLE ITEMS!".is_array($theSearchResults[0]);

                              $last="";

                           foreach ($theSearchResults as $result) {
                            if (isset($result['tmp_sys_id'])) {
                                if ($result['trg_item_sys_id'] != $last && $last !="") { ?>

                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php }

                            if ($result['trg_item_sys_id'] != $last) { ?>

                            <tr>
                                <td>
                                    <div>
                                       <ul class="triggersnav"><a target="_top" class="docketcalclink" href="docket-calculator?cmbJurisdictions=<?php echo $_POST['cmbJurisdictions'].'&cmbTriggers='.$result['trg_item_sys_id'].'&cmbCaseMatter='.$cmbCaseMatter; ?>">use in docket calculator &gt;</a>

                                            <li><a href="javascript:void(0);"> <?php echo highlight($result['trg_item_desc'],$_POST['txtSearch']); ?>
                                             <?php } ?>
                                                <ul>
                                                    <li>
                                                        <a href="javascript:void(0);"><?php echo highlight($result['short_name_desc'],$_POST['txtSearch']).'<br>';
                                                        echo highlight($result['court_rule'],$_POST['txtSearch'])."<br>";
                                                        echo "Date Rule: ".highlight($result['date_rule'],$_POST['txtSearch']); ?>

                                                        <ul id="courtdesc">
                                                            <li>
                                                                <a href="javascript:void(0);"><?php echo highlight($result['court_rule_desc'],$_POST['txtSearch']); ?></a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>




                                <?php         $last=$result['trg_item_sys_id'];


                            }

                           }

                        }
                        ?>

          </table></div>
<?php
}

if ($_POST['tab'] == 'btnSearchServices') {
//
//// get the Services for Search Services
//

    $newURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
    $command="/jurisdictionservicetypes?";
    $parameters="loginToken=$loginToken&jurisdictionSystemID=$_POST[cmbJurisdictions]";

    $file= $newURL.$command.$parameters;

    $content=file_get_contents($file);
    $xml=$content;
    $array=xml2array($xml);


    //print_r($array);

    $theServices=$array['ArrayOfJurisdictionServiceType']['JurisdictionServiceType'];
    ?>
    <table width="100%">
                                       <tr>
                                         <td><h4>Service Type</h4></td><td><h4>Days Type</h4></td><td><h4>Days Count</h4></td>




                                   <?php
                                   $servList="---";


                                   foreach ($theServices as $service) {

                                      if (strpos($servList,$service['Description']) > 0 ) {



                                      } else {
                                          //echo $servList;
                                          $servList=$servList." ".$service['Description'];
                                      ?>
                                <tr><td><p><?php echo $service['Description']; ?></p></td>

                                      <td><p>
                                        <?php  echo  $service['DaysType'];?>
                                        </p></td>

                                      <td><p>
                                        <?php  echo $service['DaysAdd'];?>
                                        </p></td> </tr>

                        <?php               }
                                   }
                                    ?>

                              </table>

<?php
}
if ($_POST['tab'] == 'btnSearchHoliday') {

    $formDate=$_POST['txtStartDate'];
    $startDate=substr($formDate,6,4)."-".substr($formDate,0,2)."-".substr($formDate,3,2);

    $formDate=$_POST['txtEndDate'];
    $endDate=substr($formDate,6,4)."-".substr($formDate,0,2)."-".substr($formDate,3,2);

//
//// get the Holiday List for Search Holiday
//

    $newURL="http://www.crcrules.com/CalendarRulesService.svc/rest";
    $command="/holidays?";
    $parameters="loginToken=$loginToken&jurisdictionSystemID=$_POST[cmbJurisdictions]&startDate=$startDate&endDate=$endDate";

    $file= $newURL.$command.$parameters;

    //echo $file;

    $content=file_get_contents($file);
    $xml=$content;
    $array=xml2array($xml);


//    print_r($array);

    $theHolidays=$array['ArrayOfHoliday']['Holiday'];
    ?>

                               <div class="searchHolidaysResults">
                                   <table width="100%">
                                   <tr><td colspan="2"><div class="divider"></div></td></tr>
                                       <tr><td width="20%"><h4>Date</h4></td><td><h4>Holiday</h4></td>
                                   <?php foreach ($theHolidays as $holiday) {
                                      ?>
                                     <tr><td><p><?php echo substr($holiday['Date'],0,10); ?></p></td>

                                      <td><p>
                                        <?php  echo $holiday['Description'];?>
                                      </p></td> </tr>

                        <?php
                                   }
                                    ?>
                        </table>
    </div>
<?php
//print_r($theHolidays);

}
?>





<?php
function highlight($text,$search) {
    $words = explode(" ",$search);

    $new=$text;

    foreach ($words as $word) {

            $new=str_replace(strtoupper($word),'<font color="red">'.strtoupper($word).'</font>',strtoupper($new) )    ;
    }

    return $new;

}

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