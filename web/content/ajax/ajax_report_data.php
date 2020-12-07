<?php require_once '../Connections/docketData.php';
require_once '../Connections/docketDataSubscribe.php';
session_start();
//global $docketDataSubscribe;
$context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n')));
require '../globals/global_tools.php';
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

		/*
		echo "<pre>";
		print_r($_POST);
		echo "</pre>";
		*/
		/* Condition for where clause */
		$CONDITION="";
		
		if(!empty($_POST['rtpDateR1Val']!=''))
		{
			$dateRng1 			= 	$_POST['rtpDateR1Val'];
			$dateRng2 			= 	$_POST['rtpDateR2Val'];	
			/* Condition for filter by Date Range in where clause */	
			$CONDITION.= "AND ce.event_date BETWEEN '".$dateRng1."' AND '".$dateRng2."'";
		}
		
		
		if(!empty($_POST['quickPickCriteria']))
		{
			$quickPickCriteria 	= $_POST['quickPickCriteria'];
			$todayDate = new DateTime("now");
			switch($quickPickCriteria)
			{
				case "Yesterday":
				$todayDate->modify('-1 day');
				$dateRange = 	$todayDate->format('Y-m-d');
				$dateRng1  =	$dateRange;	
				$dateRng2  =    $dateRange;
				break;
				case "Today":
				$dateRange = 	$todayDate->format('Y-m-d');
				$dateRng1  =	$dateRange;	
				$dateRng2  =    $dateRange;
				break;
				case "ThisWeek":
				$dateRange 		= 	$todayDate->format('Y-m-d');
				$newDate    	=   new DateTime();
				$newDate->modify('+1 week');
				$dateRangenew  	=   $newDate->format('Y-m-d');
				$dateRng1   	=	$dateRange;	
				$dateRng2   	=	$dateRangenew;
				break;
				case "NextWeek":
				$dateRange 		= 	$todayDate->format('Y-m-d');
				$newDate    	=   new DateTime();
				$newDate->modify('+1 week 7days');
				$dateRangenew  	=   $newDate->format('Y-m-d');
				$dateRng1   	=	$dateRange;	
				$dateRng2   	=	$dateRangenew;
				break;
				case "NextTwoWeeks":
				$dateRange 		= 	$todayDate->format('Y-m-d');
				$newDate    	=   new DateTime();
				$newDate->modify('+1 week 14days');
				$dateRangenew  	=   $newDate->format('Y-m-d');
				$dateRng1   	=	$dateRange;	
				$dateRng2   	=	$dateRangenew;
				break;
				case "ThisMonth":
				$todayDate 		= new DateTime("first day of this month");
				$monthdateRange = 	$todayDate->format('Y-m-d');
				$newDate    	=   new DateTime('last day of this month');
				$newDate->modify('month');
				$monthdateRangenew  	=   $newDate->format('Y-m-d');
				$dateRng1   	=	$monthdateRange;	
				$dateRng2   	=	$monthdateRangenew;
				break;
				case "NextMonth":
				$todayDate 		= new DateTime("first day of next month");
				$monthdateRange = 	$todayDate->format('Y-m-d');
				$newDate    	=   new DateTime('last day of next month');
				$newDate->modify('month');
				$monthdateRangenew  	=   $newDate->format('Y-m-d');
				$dateRng1   	=	$monthdateRange;	
				$dateRng2   	=	$monthdateRangenew;
				break;
			}
			/* Condition for filter by Date Range in where clause */	
			$CONDITION.= "AND ce.event_date BETWEEN '".$dateRng1."' AND '".$dateRng2."'";
		}
	
		if(!empty($_POST['caseIdVal']))
		{
			$caseId = $_POST['caseIdVal'];
			/* Condition for filter by Case id in  where clause */	
			$CONDITION.= " AND dc.case_id =".$caseId."";
		}
		if(!empty($_POST['jurisdictionVal']))
		{
			$jurisdictionValId = $_POST['jurisdictionVal'];
			/* Condition for filter by jurisdiction id in  where clause */	
			$CONDITION.= " AND im.jurisdiction =".$jurisdictionValId."";
		}
		if(!empty($_POST['triggerVal']))
		{
			$triggerVal = $_POST['triggerVal'];
			/* Condition for filter by Trigger value in  where clause */	
			$CONDITION.= " AND im.triggerItem ='".$triggerVal."'";
		}
		if(!empty($_POST['eventtypeVal']))
		{
			$eventtypeVal = $_POST['eventtypeVal'];
			/* Condition for filter by Event Type in  where clause */	
			$CONDITION.= " AND ce.eventtype ='".$eventtypeVal."'";
		}
		if(!empty($_POST['locationVal']))
		{
			$locationVal = $_POST['locationVal'];
			/* Condition for filter by location in  where clause */	
			$CONDITION.= " AND im.location ='".$locationVal."'";
		}
		if(!empty($_POST['customtextVal']))
		{
			$customtextVal = $_POST['customtextVal'];
			/* Condition for filter by Custom Text in  where clause */	
			$CONDITION.= " AND dctxt.case_customtext ='".$customtextVal."'";
		}
		if(!empty($_POST['attendeesVal']))
		{
			
			$arrForAttendees= array();
			foreach($_POST['attendeesVal'] as $attdVal)
			{
				$arrForAttendees[]=$attdVal;
			}
			$inArrforAttendees = "'" . implode ( "','", $arrForAttendees ) . "'"; 
			/* Condition for filter by Attendees in  where clause */	
			$CONDITION.= " AND dca.attendee in (".$inArrforAttendees.")";
		}
		if(!empty($_POST['assignedUsersVal']))
		{
		
			$arrForAssignedUser= array();
			foreach($_POST['attendeesVal'] as $assigVal)
			{
				$arrForAssignedUser[] = $assigVal;
			}
			$inArrforAssignedUser =  "'" . implode ( "','", $arrForAssignedUser ) . "'";
			/* Condition for filter by Assigned User in  where clause */	
			$CONDITION.= " AND dcu.user in (".$inArrforAssignedUser.")";
		}
		if($_POST['includearch'] == "Yes")
		{
			$ConditionForArchive="";
			
		}else{
			$ConditionForArchive=" HAVING MAX(ddca.case_delete)<>2  AND ddca.trigger_delete IS NULL AND ddca.event_delete IS NULL";	
			
		}
		
?>
<?php
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
$masterCourt=0;
if (isset($_SESSION['userid']))
{
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

    if (isset($theTotalJurisdictions['Code'])) {
        $default_court_id =  $theTotalJurisdictions['SystemID'];
    } else {
        foreach($theTotalJurisdictions as $juris)
        {
          $defaultCourt[] = $juris['SystemID'];
        }
        
    }
}

	/*
		TABLE : docket_cases
			<th>Case</th>
			
		-------------------------------------------	
		TABLE :  import_docket_calculator
			<th>Jurisdiction </th> NAME Needed from API
		
		TABLE :  import_docket_calculator
			<th>Trigger</th>
			
		TABLE :  import_docket_calculator
			<th>Location</th>
			<th>Custom Text</th>	
		
		---------------------------------------------
		
		TABLE : case_events	
			<th>Date Time</th>
			<th>Events</th>
			
		TABLE : case_events	
			<th>Event Type</th>
		
		TABLE : case_events
			<th>Court Rule</th>
			<th>Date Rule</th>
		
		-------------------------------------------------
		
		TABLE : docket_cases_attendees
			<th>Attendees</th>
			
		-------------------------------------------------	
		
		TABLE : docket_cases_users
			<th>Assigned User</th>
			
		--------------------------------------------------	
		
	
		$getCaseName = "SELECT case_matter FROM docket_cases WHERE";
		
		$getimportDocketCalculatorDetails="SELECT jurisdiction,triggerItem,	location,custom_text FROM import_docket_calculator WHERE";
		
		$getcaseEventsDetails="SELECT event_date,eventName,eventtype,courtRule,dateRule FROM case_events WHERE";
		
		$getdocketCasesAttendeesDetails="SELECT attendee FROM docket_cases_attendees WHERE";
	
		$getdocketCaseAssignedDetails="SELECT user FROM docket_cases_users WHERE";
		
		
	*/
		
		
		/*$getDataSQLQuery="SELECT dc.case_matter as CASENAME,im.jurisdiction as JURI,im.triggerItem as TRIGGERNAME,im.location as LOCATION,im.custom_text as CUSTOMTEXT,ce.event_date as EVENTDATE,ce.eventName as EVENTNAME,ce.eventtype as EVENTYPE,ce.courtRule as COURTRULE,ce.dateRule as DATERULE,GROUP_CONCAT(dca.attendee) as ATTENDEES,GROUP_CONCAT(dcu.user) as ASSIGNED
		FROM docket_cases dc
		INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id	
		INNER JOIN import_docket_calculator im ON dc.case_id = im.case_id 
		INNER JOIN import_events ie ON im.import_docket_id = ie.import_docket_id
		INNER JOIN case_events ce ON ie.import_event_id = ce.import_event_id
		INNER JOIN docket_cases_attendees dca ON im.case_id  = dca.case_id
		WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."'".$CONDITION." LIMIT 10";
		
		$resultSQLQueryDetials = mysqli_query($docketDataSubscribe,$getDataSQLQuery);
		*/
		
	?>
     <style>
	 /*table.dataTable.display tbody td {
		max-width: 355px;
		text-overflow: ellipsis;
		overflow: hidden;
	 }*/
    </style>
	<table id="example" class="stripe cell-border" cellspacing="0" style="width:auto;">
    <thead>
	<tr>
		<th>Case</th>
		<th>Date Time</th>
		<th>Events</th>
		<?php	
		$SELECTSTATEMENT="";
			if(in_array("juriHdr",$_POST['headerArrData']))
			{
				echo "<th>Jurisdiction</th>";
				$SELECTSTATEMENT.=",im.jurisdiction as JURI";
			}
			if(in_array("trigHdr",$_POST['headerArrData']))
			{
				echo "<th>Trigger</th>";
				$SELECTSTATEMENT.=",im.triggerItem as TRIGGERNAME";
			}
			if(in_array("eventypeHdr",$_POST['headerArrData']))
			{
				echo "<th>Event Type</th>";
				$SELECTSTATEMENT.=",ce.eventtype as EVENTYPE";
			}
			if(in_array("locHdr",$_POST['headerArrData']))
			{
				echo "<th>Location</th>";
				$SELECTSTATEMENT.=",im.location as LOCATION";
			}
			if(in_array("custtxtHdr",$_POST['headerArrData']))
			{
				echo "<th>Custom Text</th>";
				$SELECTSTATEMENT.=",im.custom_text as CUSTOMTEXT";
			}
			if(in_array("courtruleHdr",$_POST['headerArrData']))
			{
				echo "<th>Court Rule</th>";
				$SELECTSTATEMENT.=",ce.courtRule as COURTRULE";
			}
			if(in_array("dateruleHdr",$_POST['headerArrData']))
			{
				echo "<th>Date Rule</th>";
				$SELECTSTATEMENT.=",ce.dateRule as DATERULE";
			}
			if(in_array("attHdr",$_POST['headerArrData']))
			{
				echo "<th>Attendees</th>";
				$SELECTSTATEMENT.=",GROUP_CONCAT(dca.attendee) as ATTENDEES";
			}
			if(in_array("assignHdr",$_POST['headerArrData']))
			{
				echo "<th>Assigned User</th>";
				$SELECTSTATEMENT.=",GROUP_CONCAT(dcu.user) as ASSIGNED";
			}
		?>
		
	</tr>
	</thead>
    <tbody>

	<?php
		$getDataSQLQuery="SET SQL_BIG_SELECTS = 1;";
		
		$resultSQLQueryDetials = mysqli_query($docketDataSubscribe,$getDataSQLQuery);
		if($_SESSION['CheckAccess']=="NoGmail"){
			$getDataSQLQuery="SELECT ddca.event_delete,ddca.trigger_delete ,dc.case_matter as CASENAME,ce.event_date as EVENTDATE,ce.eventName as EVENTNAME".$SELECTSTATEMENT."
			FROM docket_cases dc
			LEFT JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id	
			LEFT JOIN import_docket_calculator im ON dc.case_id = im.case_id 
			LEFT JOIN import_events ie ON im.import_docket_id = ie.import_docket_id
			LEFT JOIN case_events ce ON ie.import_event_id = ce.import_event_id
			LEFT JOIN docket_cases_attendees dca ON im.case_id  = dca.case_id
			LEFT JOIN docket_cases_archive ddca ON dc.case_id = ddca.caseid
			LEFT JOIN docket_customtext dctxt ON dc.case_id  = dctxt.case_id
			WHERE dc.user_id = '".$_SESSION['userid']."'".$CONDITION."  GROUP BY ce.eventName ".$ConditionForArchive."";
		}else{
			$getDataSQLQuery="SELECT ddca.event_delete,ddca.trigger_delete ,dc.case_matter as CASENAME,ce.event_date as EVENTDATE,ce.eventName as EVENTNAME".$SELECTSTATEMENT."
			FROM docket_cases dc
			LEFT JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id	
			LEFT JOIN import_docket_calculator im ON dc.case_id = im.case_id 
			LEFT JOIN import_events ie ON im.import_docket_id = ie.import_docket_id
			LEFT JOIN case_events ce ON ie.import_event_id = ce.import_event_id
			LEFT JOIN docket_cases_attendees dca ON im.case_id  = dca.case_id
			LEFT JOIN docket_cases_archive ddca ON dc.case_id = ddca.caseid
			LEFT JOIN docket_customtext dctxt ON dc.case_id  = dctxt.case_id
			WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."'".$CONDITION."  GROUP BY ce.eventName ".$ConditionForArchive."";
		}
		$resultSQLQueryDetials = mysqli_query($docketDataSubscribe,$getDataSQLQuery);
		
			while ($rowDetailsData = mysqli_fetch_assoc($resultSQLQueryDetials)) 
			{
				$newDate =  new DateTime($rowDetailsData['EVENTDATE']);
				echo "<tr>";
				echo "<td>".$rowDetailsData['CASENAME']."</td>";
				echo "<td>".$newDate->format('m/d/Y')."</td>";
				echo "<td>".$rowDetailsData['EVENTNAME']."</td>";
				
				if(in_array("juriHdr",$_POST['headerArrData']))
				{
					$JURID = juriName($rowDetailsData['JURI'],$theTotalJurisdictions);
					echo "<td>".$JURID."</td>";
				}
				if(in_array("trigHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['TRIGGERNAME']."</td>";
				}
				if(in_array("eventypeHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['EVENTYPE']."</td>";
				}
				if(in_array("locHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['LOCATION']."</td>";
				}
				if(in_array("custtxtHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['CUSTOMTEXT']."</td>";
				}
				if(in_array("courtruleHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['COURTRULE']."</td>";
				}
				if(in_array("dateruleHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['DATERULE']."</td>";
				}
				if(in_array("attHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['ATTENDEES']."</td>";
				}
				 if(in_array("assignHdr",$_POST['headerArrData']))
				{
					echo "<td>".$rowDetailsData['ASSIGNED']."</td>";
				}
					echo "</tr>";
			}	
		?>
    </tbody>
    </table>
<script>
$(document).ready(function() {
	$.fn.dataTable.moment( 'm/d/y' );
    $('#example').DataTable( {
		responsive: true,
		deferRender:    true,
        scrollY:        400,
        scrollX:        true,
        scrollCollapse: true,
        scroller:       true,
		ordering:       true,
		autoWidth: false,
		 fixedHeader: {
            header: true, 
        },
		//"columnDefs" : [{"targets":1, "type":"date-uk"}],
		"bPaginate": false,
        dom: 'Bfrtip',
        buttons: [
			'copyHtml5',
            'csvHtml5',
            'excelHtml5',
            {
                extend: 'print',
                exportOptions: {
                    columns: ':visible'
                },	customize: function ( win ) {
                    $(win.document.body)
                        .css( 'font-size', '12pt' );
 
                    $(win.document.body).find( 'table' )
                         .addClass( 'responsive' )
	                        .css( 'font-size', 'inherit' );
							

					$(win.document.body).find('table').css('table-layout','auto');
                },
				exportOptions: {
	                    columns: ':visible',
                  		stripHtml: false
	                }
            },
           
        ],
        columnDefs: [ 
		{"width": "150px", "targets": 0 },
		{"responsivePriority": 1,"width": "150px", "targets": 1 ,"type":"date-us"},
		{"width": "350px", "targets": 2 },
		<?php	
				if(in_array("juriHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "150px" },
			<?php
				}
				if(in_array("trigHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "150px"},
			<?php
				}
				if(in_array("eventypeHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "80px"  }, 
			<?php
				}
				if(in_array("locHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "350px"},
			<?php
				}
				if(in_array("custtxtHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "350px"},
			<?php
				}
				if(in_array("courtruleHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "550px" },
			<?php
				}
				if(in_array("dateruleHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "550px" },
			<?php
				}
				if(in_array("attHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "350px" },
			<?php
				}
				if(in_array("assignHdr",$_POST['headerArrData']))
				{		
	    ?>
					{"width": "350px" },
			<?php
				}
			?>
		]
    } );
	
} );


</script>
<?php
function juriName($juri,$theTotalJurisdictions)
{
	foreach($theTotalJurisdictions as $juris)
	{
		if ($juri == $juris['SystemID'])
		{
			return $juris['Description'];
		}
		else{
			if ($juri == $juris['SystemID'])
			{
				return $juris['Description'];
			}
		}
	}
	
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

?>


