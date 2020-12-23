<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Casetriggers
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	ini_set('session.gc_maxlifetime', 10800);	
	session_set_cookie_params(10800);
	session_start();
	
   $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

    require('globals/global_tools.php');
    require('globals/global_courts.php');

    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    if($_GET['case_id'] == '')
    {
      echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
    }
    unset($_SESSION['delete_event']);
    $case_id = $_GET['case_id'];
	/* DELETE ALL INVALID CASES */
	$sqlGetAllImportDocketIdToDelete = "SELECT import_docket_id FROM import_docket_calculator where case_id =".$case_id." and calendar_id IS NULL";
	$queryGetAllCaseIdToDelete = mysqli_query($docketDataSubscribe,$sqlGetAllImportDocketIdToDelete);
	while($rowImportDocketIdData = mysqli_fetch_assoc($queryGetAllCaseIdToDelete))
	{

		$sqlDeleteImportDocketId = "DELETE from import_docket_calculator where case_id=".$case_id." AND import_docket_id = ".$rowImportDocketIdData['import_docket_id']."";
		$resultDeleteImportDocketId = mysqli_query($docketDataSubscribe,$sqlDeleteImportDocketId);
	}
	
    ?>
	<!-- JAVASCRIPT -->
	<script src="//code.jquery.com/jquery.min.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.core.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.load-indicator.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.load-strategies.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.sort-strategies.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.field.js"></script>
	<script src="https://tools.docketcalendar.com/src/fields/jsgrid.field.text.js"></script>
	<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
	<!-- CSS-->
	
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jqGrid.bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
	
<?php
$queryvalidateUserCaseEvents = "SELECT dc.created_by,i.created_on FROM docket_cases dc INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id WHERE dcu.user =  '".$_SESSION['author_id']."'";
$queryvalidateUserCaseEventsResult = mysqli_query($docketDataSubscribe,$queryvalidateUserCaseEvents);

$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$case_id."";
$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
$totalRows_caseQuery = mysqli_num_rows($caseQuery);
$row_case = mysqli_fetch_assoc($caseQuery);		
?>
  <style type="text/css">
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
	.jsgrid-grid-body {
	  height:auto !important;
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
.myButton {
	background-color:#D3D3D3D3;
	display:inline-block;
	cursor:pointer;
	font-family:Times New Roman;
	font-size:13px;
	padding:3px 12px;
	text-decoration:none;
	margin-right:5px;
}
 
.myButton:active {
	position:relative;
	top:1px;
}


</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div class="FntCls" style="width: 100%;clear:both;">
    <div style="float: left;"><h2>Case Trigger(s)</h2></div>
    <div style="margin-left: 15%;float: right;"><a href="docket-cases">Back</a>&nbsp;|<a href="javascript:void(0);" onclick="javascript:PrintResult();">Print</a></div>
</div>
<span id="ajax_result" style="color:red;padding-left: 220px;"></span>
<div class="FntCls" style="clear:both;"><h4>Case : <?php echo $row_case['case_matter'];?></h4></div>
    <div class="FntCls" id="jsGrid" style="padding-bottom: 30px;"></div>
    <?php 
	if(!isset($_SESSION['access_token'])) 
	{ 
	if($_SESSION['CheckAccess']!="NoGmail")
		{
	?>	
	<span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='<?php echo get_home_url(); ?>/google-login/?delete_event=<?php echo $case_id;?>'>Please login Google Authentication to update/delete events.</a></span>
	<?php
		}
	} 
	?>

    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                    return (!filter.Action || client.Action === filter.Action)
                        && (!filter.Date || client.Date.indexOf(filter.Date) > -1)
                        && (!filter.Trigger || client.Trigger.indexOf(filter.Trigger) > -1)
						&& (!filter.Comment || client.Comment.indexOf(filter.Comment) > -1)
						&& (!filter.Created || client.Created.indexOf(filter.Created) > -1)
						&& (!filter.Modified || client.Modified.indexOf(filter.Modified) > -1);
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php
				$querytriggerValue = "SELECT DISTINCT( i.import_docket_id),dc.case_id,dc.case_matter,i.jurisdiction,i.trigger_item,i.triggerItem,i.trigger_date,i.trigger_time,i.meridiem,dc.created_by  FROM docket_cases dc 
					INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id 
					INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id 
					WHERE i.case_id = ".$case_id." AND i.trigger_item NOT IN (select triggerid FROM docket_cases_archive WHERE caseid = ".$case_id." AND userid = ".$_SESSION['userid']." AND trigger_delete = 2) GROUP BY i.import_docket_id";
					
					$selectTriggerData = mysqli_query($docketDataSubscribe,$querytriggerValue);
					
					while($rowDate = mysqli_fetch_assoc($selectTriggerData))
					{
				/*
					$sqlSelectTriggerCustomText = "SELECT 
					CASE 
					WHEN trigger_customtextlevel IS NOT NULL AND trigger_customtextlevel = 1 AND trigger_trigid='".$rowDate['trigger_item']."' THEN trigger_customtext
					WHEN case_customtext  IS NOT NULL  THEN case_customtext
					ELSE 'THIS TEXT'
					END 'text'
					FROM docket_customtext WHERE case_id = ".$case_id."";
					$ResultSelectTriggerCustomText = mysqli_query($docketDataSubscribe,$sqlSelectTriggerCustomText);
					$totalRowsSelectTriggerCustomText = mysqli_num_rows($ResultSelectTriggerCustomText);
					while ($rowData = mysqli_fetch_assoc($ResultSelectTriggerCustomText))
						{
							$textValue = $rowData ['text'];
						}
				*/
					$textValue = getEventCustomTextValue($rowDate['trigger_item'],$rowDate['jurisdiction'],	$case_id,$_SESSION['userid']);				
					$queryGetTriggerModifiedDetails="SELECT trigger_modified_by  FROM docket_case_triggerevent_mod where user_id=".$_SESSION['userid']." AND case_id=".$case_id." AND trigger_id='".$rowDate['trigger_item']."'";	
					$detailsGetTriggerModified = mysqli_query($docketDataSubscribe,$queryGetTriggerModifiedDetails);
					while ($rowDataDetails = mysqli_fetch_assoc($detailsGetTriggerModified))
					{
						$modifiedBy = $rowDataDetails['trigger_modified_by'];
					}		
						
						?>
                {
              
                 <?php if($_SESSION['author_id'] == $rowDate['created_by']) {      ?>
                "Action": "<button class='myButton' onclick='javascript:updateTriggerDate(<?php echo $rowDate['import_docket_id']; ?>);'><strong>E</strong>dit</button><button class='myButton' onclick='javascript:view_trigger(<?php echo $rowDate['case_id'].','.$rowDate['import_docket_id']; ?>);'><strong>V</strong>iew</button>",
				 <?php } else { ?>
               "Action": "<button class='myButton' onclick='javascript:updateTriggerDate(<?php echo $rowDate['import_docket_id']; ?>);'><strong>E</strong>dit</button><button class='myButton' onclick='javascript:view_trigger(<?php echo $rowDate['case_id'].','.$rowDate['import_docket_id']; ?>);'><strong>V</strong>iew</button>",
               <?php } ?>
			    "Date": "<?php  echo date("m/d/Y",strtotime($rowDate['trigger_date'])).' '. date("h:i",strtotime($rowDate['trigger_time'])).' '. $rowDate['meridiem']; ?>",
                "Trigger": "<?php echo $rowDate['triggerItem'];?>",
				"Comment": "<?php echo $textValue;?>",
				"Created": "<?php echo $rowDate['created_by'];?>",
				"Modified": "<?php echo $modifiedBy;?>"
                },
            <?php  	    
			   }		
			?>
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
                height: "70%",
                width: "100%",
                sorting: true,
                paging: true,
                autoload: true,
				filtering: true,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
					{ name: "Action",width: 140, sorting: false  },
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 300 },
					{ name: "Comment", type: "text", width: 300 },
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
    function PrintResult() {
        var divToPrint = document.getElementById("jsGrid");
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: false,
                paging: false,
                autoload: true,
                pageSize: 100,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 300 },
					{ name: "Comment", type: "text", width: 300 },
					{ name: "Created", type: "text", width: 300 },
					{ name: "Modified", type: "text", width: 300 },

                ]
            });
            var popupWin = window.open('', '_blank', 'width=300,height=300');
            popupWin.document.open();
            popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: false,
                paging: false,
                autoload: false,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
					{ name: "Action", type: "text", width: 140, sorting: false  },
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 300 },
					{ name: "Comment", type: "text", width: 300 },
					{ name: "Created", type: "text", width: 300 },
					{ name: "Modified", type: "text", width: 300 },
                ]
            });
            popupWin.document.close();
			
    }

    function view_trigger(case_id,importDocketId)
    {
		
		jQuery(".overlay").show();
        <?php 
				
					if(!isset($_SESSION['access_token'])) {
			?>
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
                window.location.href='<?php echo get_home_url(); ?>/docket-cases';
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
        <?php 
				
			} else { ?>
               window.location.href='<?php echo get_home_url(); ?>/calendar-events?case_id='+case_id+'&importDocketId='+importDocketId;
        <?php } ?>
    }

	function updateTriggerDate(importid)
    {
		jQuery(".overlay").show();
        <?php 
		
			if(!isset($_SESSION['access_token'])) { 
		?>
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
                window.location.href='<?php echo get_home_url(); ?>'
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
       <?php 
			
	    } 
	   else { ?>
                var checkFlag = 'updateFlag';
                window.location.href='<?php echo get_home_url(); ?>/update-case-triggers?importdocketid='+importid+'&flag='+checkFlag;
        <?php } ?>
    }

      

      function checkAll(ele) {
     var checkboxes = document.getElementsByTagName("input");
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
</script>
<?php
}


function getEventCustomTextValue($triggerId,$juriId,$caseId,$userId)
{
	$database_docketData = $GLOBALS['database_docketData'];
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	mysqli_select_db($docketDataSubscribe,$database_docketData);
	
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
genesis();
?>