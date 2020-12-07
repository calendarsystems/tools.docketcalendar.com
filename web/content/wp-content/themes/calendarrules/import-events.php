<?php
/*
Template Name: Calendar Events
 */
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
//THIS FILE IS USED AS calendar-events
session_start();
function custom_loop() {
require_once('Connections/docketDataSubscribe.php');
require('globals/global_tools.php');
require('globals/global_courts.php');
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$database_docketData = $GLOBALS['database_docketData'];
$docketUserDataSubscribe = $GLOBALS['docketUserDataSubscribe'];
    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://tools.docketcalendar.com/docket-calculator';</script>";
    }
    if($_GET['case_id'] == '')
    {
      echo "<script>window.location.href='https://tools.docketcalendar.com/docket-calculator';</script>";
    }
	
    $case_id = $_GET['case_id'];
	$importDocketId = $_GET['importDocketId'];
	$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$case_id."";
	$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
	$totalRows_caseQuery = mysqli_num_rows($caseQuery);
	$row_case = mysqli_fetch_assoc($caseQuery);
	
	
	$getAllEmailIdForAssignCase="SELECT user from docket_cases_users 
     WHERE case_id = ".$case_id."";
	$dataEmailIdForAssignCase = mysqli_query($docketDataSubscribe,$getAllEmailIdForAssignCase);
	while($rowUserIdForAssignCase = mysqli_fetch_assoc($dataEmailIdForAssignCase))
		{
			
			$getAllUserIdforCase = "SELECT user_id FROM docket_cases WHERE created_by='".$rowUserIdForAssignCase['user']."' AND case_id=".$case_id."";
			$dataQuery = mysqli_query($docketDataSubscribe,$getAllUserIdforCase);
			while($row_datacase = mysqli_fetch_assoc($dataQuery))
			{
				$arrayForUserIdForAssignCase[]=$row_datacase['user_id'];
			}
			
		}
	
	$arrayForCurrentUserIdForCase[]=$_SESSION['userid'];
	
	$output = array_merge($arrayForCurrentUserIdForCase,$arrayForUserIdForAssignCase);
	$output = array_unique($output);
	
	$inClause = implode(",",$output);
	
	
	$query_importEvents = "SELECT i.access_token,e.authenticator,c.event_date,i.jurisdiction,i.import_docket_id,i.trigger_item,c.short_name,c.eventtype,dc.case_matter,c.case_event_id,dc.created_by,e.event_docket,e.has_child,i.triggerItem FROM docket_cases dc
	INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
	INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
	INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
	WHERE dc.user_id IN (".$inClause.") AND dc.case_id = ".$case_id." AND e.import_docket_id  = ".$importDocketId." AND c.case_event_id NOT IN (select eventid FROM docket_cases_archive WHERE caseid = ".$case_id." AND userid = ".$_SESSION['userid']." AND event_delete = 2) ORDER BY c.import_event_id desc";
	$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
	$totalRows_importEvents = mysqli_num_rows($ImportEvents);
	?>
<!-- JS-->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.core.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-indicator.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.sort-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.field.js"></script>
<script src="https://tools.docketcalendar.com/src/fields/jsgrid.field.text.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
<style>
.jsgrid-grid-body {
  height:500px; <?php echo $style; ?>!important;
}
/* header row */
.jsgrid-header-row>.jsgrid-header-cell {
  background-color: #b7deed;      /* orange */
  font-family: "Roboto Slab";
  font-size: 1.2em;
  color: #1e5799;
  font-weight: normal;
}
#loading-img {
    background: url(https://tools.docketcalendar.com/assets/images/ajax-loader.gif) center center no-repeat;
    height: 100%;
    z-index: 20;
	width: 100%;
}
</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div style="width: 100%;clear:both;">
    <div style="float: left;"><h2>Trigger Event(s)</h2></div>
    <div style="margin-left: 15%;float: right;"><a href="docket-cases">Docket Cases</a>&nbsp;|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></div>
</div>

<span id="ajax_result" style="color:red;padding-left: 220px;"></span>

<?php

?>
<div style="clear:both;"><h4>Case : <?php echo $row_case['case_matter'];?></h4></div>

    <div id="jsGrid" style="padding-bottom: 30px;"></div>
    <?php if(!isset($_SESSION['access_token'])) { ?><span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='https://tools.docketcalendar.com/google-login/?delete_event=<?php echo $case_id;?>'>Please login Google Authentication to update/delete events.</a></span><?php } ?>
    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                 return (!filter.Date || client.Date.indexOf(filter.Date) > -1)
						&& (!filter.Action || client.Action === filter.Action)
                        && (!filter.Trigger || client.Trigger.indexOf(filter.Trigger) > -1)
                        && (!filter.Events || client.Events.indexOf(filter.Events) > -1)
						&& (!filter.Comment || client.Comment.indexOf(filter.Comment) > -1)
						&& (!filter.EventsType || client.EventsType.indexOf(filter.EventsType) > -1)
						&& (!filter.Created || client.Created.indexOf(filter.Created) > -1)
						&& (!filter.Modified || client.Modified.indexOf(filter.Modified) > -1);
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php if($totalRows_importEvents > 0) {
                while ($row_events = mysqli_fetch_assoc($ImportEvents)) {
					$textValue = getEventCustomTextValue($row_events['case_event_id'],$row_events['trigger_item'],$row_events['jurisdiction'],$case_id,$_SESSION['userid']);
					
					$queryGetTriggerEventModifiedDetails="SELECT event_modified_by FROM docket_case_triggerevent_mod where user_id=".$_SESSION['userid']." AND case_id=".$case_id." AND trigger_id='".$row_events['trigger_item']."' AND event_id='".$row_events['case_event_id']."'";	
					$detailsGetEventTriggerModified = mysqli_query($docketDataSubscribe,$queryGetTriggerEventModifiedDetails);
					while ($rowDataDetails = mysqli_fetch_assoc($detailsGetEventTriggerModified))
					{
						$modifiedBy = $rowDataDetails['event_modified_by'];
					}	

				?>
                {
					
                <?php if($_SESSION['author_id'] == $row_events['authenticator']) {      ?>
                "Action": "<button class='myButton' onclick='javascript:edit_event(<?php echo $row_events['case_event_id']; ?>);'><strong>E</strong>dit</button><button class='myButton' onclick='javascript:view_event(<?php echo $row_events['case_event_id']; ?>);' title='View'><strong>V</strong>iew</button>",
                <?php } else { ?>
                "Action": "<button class='myButton' onclick='javascript:edit_event(<?php echo $row_events['case_event_id']; ?>);'><strong>E</strong>dit</button><button class='myButton' onclick='javascript:view_event(<?php echo $row_events['case_event_id']; ?>);' title='View'><strong>V</strong>iew</button>",
               <?php } ?>
			    "Date": "<?php  echo date("m/d/Y, h:i A",strtotime($row_events['event_date'])); ?>",
                "Trigger": "<?php echo $row_events['triggerItem'];?>",
                "Events": "<?php echo $row_events['short_name'];?>",
				"Comment": "<?php echo $textValue;?>",
				"EventsType": "<?php echo $row_events['eventtype'];?>",
				"Created": "<?php echo $row_events['created_by'];?>",
				"Modified": "<?php echo $modifiedBy;?>"
                },
            <?php } }?>
             ];

        }());
		$(function() {
			var originalFilterTemplate = jsGrid.fields.text.prototype.filterTemplate;
			jsGrid.fields.text.prototype.filterTemplate = function() {
					var grid = this._grid;
					var $result = originalFilterTemplate.call(this);
					$result.on("keyup", function(e) {
						  // TODO: add proper condition and optionally throttling to avoid too much requests  
						  grid.search();
					});
					return $result;
				}
		});
        $(function() {
            $("#jsGrid").jsGrid({
                height: "100%",
                width: "100%",
                sorting: true,
                paging: false,
                autoload: true,
				filtering: true,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
					{ name: "Action",  width: 140, sorting: false},
                    { name: "Date", type: "text", width: 150,placeholder:"Date" },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
					{ name: "Comment", type: "text", width: 300 },
					{ name: "EventsType", type: "text", width: 180 },
					{ name: "Created", type: "text", width: 300 },
					{ name: "Modified", type: "text", width: 300 },
                ]
            });

        });
    </script>

<script type="text/javascript">
jQuery(".overlay").show();
	 setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
    function view_event(case_event_id)
    {
		jQuery(".overlay").show();
        <?php if(!isset($_SESSION['access_token'])) { ?>
        var data = '<div style="padding:20px;">Please login into Google Authentication to access view.</div>';
        jQuery.dialogbox({
            type:'msg',
            title:'',
            content:data,
            btn:['Login'],
            call:[
              function(){
                jQuery.dialogbox.prompt({
                    content:'Redirect to Google Authentication Login',
                    time:2000
                });
                window.location.href='<?php echo get_home_url(); ?>/google-login/?view_event='+case_event_id;
             }
            ],
            closeCallback:function(){
                jQuery.dialogbox.prompt({
                    content:'You have closed the message',
                    time:2000
                });
                return false;
            }
        });
        <?php } else { ?>
               window.location.href='<?php echo get_home_url(); ?>/view-calendar-event?id='+case_event_id;
        <?php } ?>
    }

    function edit_event(case_event_id)
    {
		jQuery(".overlay").show();
        <?php if(!isset($_SESSION['access_token'])) { ?>
        var data = '<div style="padding:20px;">Please login into Google Authentication to access edit.</div>';
        jQuery.dialogbox({
            type:'msg',
            title:'',
            content:data,
            btn:['Login'],
            call:[
              function(){
                jQuery.dialogbox.prompt({
                    content:'Redirect to Google Authentication Login',
                    time:2000
                });
                window.location.href='<?php echo get_home_url(); ?>/google-login/?update_event='+case_event_id;
              }
            ],
            closeCallback:function(){
                jQuery.dialogbox.prompt({
                    content:'You have closed the message',
                    time:2000
                });
                return false;
            }
        });
       <?php } else { ?>
               window.location.href='<?php echo get_home_url(); ?>/update-calendar-event?id='+case_event_id;
        <?php } ?>
    }


</script>
<?php
}
function getEventCustomTextValue($caseEventId,$triggerId,$juriId,$caseId,$userId)
{
	$database_docketData = $GLOBALS['database_docketData'];
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	mysqli_select_db($docketDataSubscribe,$database_docketData);
	$sqlSelectEventCustomText="SELECT event_custometext FROM docket_customtext WHERE event_eventid='".$caseEventId."' AND trigger_trigid='".$triggerId."'  AND case_id=".$caseId." AND user_id=".$userId." AND event_custometext IS NOT NULL  order by id desc limit 1";
	$ResultSelectEventCustomText = mysqli_query($docketDataSubscribe,$sqlSelectEventCustomText);
	$totalRowsResultSelectEventCustomText = mysqli_num_rows($ResultSelectEventCustomText);
	if($totalRowsResultSelectEventCustomText > 0)
	{
		while ($rowData = mysqli_fetch_assoc($ResultSelectEventCustomText))
				{
					
					return $textValue = $rowData ['event_custometext'];
				}
	}
	else{
		
		$sqlSelectTriggerCustomText="SELECT trigger_customtext FROM docket_customtext WHERE   trigger_trigid='".$triggerId."' AND trigger_juri='".$juriId."' AND case_id=".$caseId." AND user_id=".$userId." AND trigger_customtext IS NOT NULL  order by id desc limit 1";
	    $ResultSelectTriggerCustomText = mysqli_query($docketDataSubscribe,$sqlSelectTriggerCustomText);
		$totalRowsResultSelectTriggerCustomText = mysqli_num_rows($ResultSelectTriggerCustomText);
		if($totalRowsResultSelectTriggerCustomText > 0)
		{
			while ($rowTriggerData = mysqli_fetch_assoc($ResultSelectTriggerCustomText))
							{
								
								return $textValue = $rowTriggerData ['trigger_customtext'];
							}
		}
		else{
			$sqlSelectCaseCustomText="SELECT case_customtext FROM docket_customtext WHERE   case_id=".$caseId." AND user_id=".$userId." AND case_customtext IS NOT NULL  order by id desc limit 1";
			$ResultSelectCaseCustomText = mysqli_query($docketDataSubscribe,$sqlSelectCaseCustomText);
			$totalRowsResultSelectCaseCustomText = mysqli_num_rows($ResultSelectCaseCustomText);
			if($totalRowsResultSelectCaseCustomText > 0)
			{
				while ($rowCaseData = mysqli_fetch_assoc($ResultSelectCaseCustomText))
				{
					
					return $textValue = $rowCaseData ['case_customtext'];
				}
			}
			else{
				$textValue="";
				return $textValue;
			}
		}
	}
	
	
}
genesis();
?>