<?php
/*
Template Name: Archivecasetriggers
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {
	require_once('Connections/docketDataSubscribe.php');
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
    $case_id = $_GET['case_id'];
    ?>
	<!-- JAVASCRIPT -->
	<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="jquery/css/notify.css"> 
<script type="text/javascript" src="jquery/js/notify.js"></script>
	<script src="src/jsgrid.core.js"></script>
	<script src="src/jsgrid.load-indicator.js"></script>
	<script src="src/jsgrid.load-strategies.js"></script>
	<script src="src/jsgrid.sort-strategies.js"></script>
	<script src="src/jsgrid.field.js"></script>
	<script src="src/fields/jsgrid.field.text.js"></script>
	<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
	<!-- CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
	<link rel="stylesheet" type="text/css" href="jquery/css/jsgrid.css" />
	<link rel="stylesheet" type="text/css" href="jquery/css/theme.css" />
	<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
<?php
	$queryvalidateUserCaseEvents = "SELECT dc.created_by,i.created_on FROM docket_cases dc INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id WHERE dcu.user =  '".$_SESSION['author_id']."'";
	$queryvalidateUserCaseEventsResult = mysqli_query($docketDataSubscribe,$queryvalidateUserCaseEvents);

	$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$case_id."";
	$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
	$totalRows_caseQuery = mysqli_num_rows($caseQuery);
	$row_case = mysqli_fetch_assoc($caseQuery);	

	$queryConditionForDisplayTrigger = "SELECT MAX(case_delete) FROM docket_cases_archive WHERE caseid = ".$case_id."";
	$resultConditionForDisplayTrigger = mysqli_query($docketDataSubscribe,$queryConditionForDisplayTrigger);
	$rowConditionForDisplayTrigger = mysqli_fetch_assoc($resultConditionForDisplayTrigger);	
	
	if($rowConditionForDisplayTrigger['case_delete'] == 2)
	{
		$conditionFilter = "";
	}else{
		
		$conditionFilter = "AND i.trigger_item IN (select triggerid FROM docket_cases_archive WHERE caseid = ".$case_id." AND userid = ".$_SESSION['userid']." AND triggerid IS NOT NULL)";
	}
	
	$querytriggerValue = "SELECT DISTINCT( i.import_docket_id),dc.case_id,dc.case_matter,i.jurisdiction,i.trigger_item,i.triggerItem,i.trigger_date,i.trigger_time,i.meridiem,dc.created_by  FROM docket_cases dc 
					INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id 
					INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id 
					WHERE i.case_id = ".$case_id." ".$conditionFilter." GROUP BY i.import_docket_id";
					
					$selectTriggerData = mysqli_query($docketDataSubscribe,$querytriggerValue);
			
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
    background: url(assets/images/ajax-loader.gif) center center no-repeat;
    height: 100%;
    z-index: 20;
	width: 100%;
}


</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div class="FntCls" style="width: 100%;clear:both;">
    <div style="float: left;"><h2>Archive Trigger(s)</h2></div>
    <div style="margin-left: 15%;float: right;"><a href="Archivedocketcases">Back</a>&nbsp;|<a href="javascript:void(0);" onclick="javascript:PrintResult();">Print</a></div>
</div>
<span id="ajax_result" style="color:red;padding-left: 220px;"></span>
<div class="FntCls" style="clear:both;"><h4>Case : <?php echo $row_case['case_matter'];?></h4></div>
    <div class="FntCls" id="jsGrid" style="padding-bottom: 30px;"></div>
     <?php if(!isset($_SESSION['access_token'])) { ?><span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='<?php echo get_home_url(); ?>/google-login/?delete_event=<?php echo $case_id;?>'>Please login Google Authentication to update/delete events.</a></span><?php } ?>

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
			
					
					while($rowDate = mysqli_fetch_assoc($selectTriggerData))
					{
			
					$textValue = getEventCustomTextValue($rowDate['trigger_item'],$rowDate['jurisdiction'],	$case_id,$_SESSION['userid']);				
					$queryGetTriggerModifiedDetails="SELECT trigger_modified_by  FROM docket_case_triggerevent_mod where user_id=".$_SESSION['userid']." AND case_id=".$case_id." AND trigger_id='".$rowDate['trigger_item']."'";	
					$detailsGetTriggerModified = mysqli_query($docketDataSubscribe,$queryGetTriggerModifiedDetails);
					while ($rowDataDetails = mysqli_fetch_assoc($detailsGetTriggerModified))
					{
						$modifiedBy = $rowDataDetails['trigger_modified_by'];
					}		
						
					$querySelectDeletePermissionData = "SELECT trigger_delete FROM docket_cases_archive WHERE caseid=".$case_id." AND triggerid='".$rowDate['trigger_item']."'";
					$resultDeletePermissionData = mysqli_query($docketDataSubscribe,$querySelectDeletePermissionData);
					while ($rowDeletePermissionData = mysqli_fetch_assoc($resultDeletePermissionData))
					{
						$DeletePermissionValue = $rowDeletePermissionData ['trigger_delete'];
					}		
						?>
                {
               "Action": "<?php if($DeletePermissionValue == 2){ ?><button class='myButton' onclick='javascript:restore_trigger(<?php echo $rowDate['case_id'].','.$rowDate['trigger_item']; ?>);'><strong>R</strong>estore</button><?php } ?><button class='myButton' onclick='javascript:view_trigger(<?php echo $rowDate['case_id'].','.$rowDate['import_docket_id']; ?>);'><strong>V</strong>iew</button><?php if($DeletePermissionValue == 2){ ?><button class='myButton red' onclick='javascript:delete_trigger(<?php echo $rowDate['case_id'].','.$rowDate['trigger_item']; ?>);'><strong>D</strong>elete</button><?php } ?>",
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
					{ name: "Action",width: 230, sorting: false  },
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
					{ name: "Action", width: 230, sorting: false  },
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 300 },
					{ name: "Comment", type: "text", width: 300 },
					{ name: "Created", type: "text", width: 300 },
					{ name: "Modified", type: "text", width: 300 },
                ]
            });
            popupWin.document.close();
			
    }
	function restore_trigger(case_id,trigger_item)
	{
			jQuery(".overlay").show();	
                jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_restore_trigger.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_id":case_id,"trigger_item":trigger_item },
                    success: function (response) {
                       console.log(response);
					   jQuery(".overlay").hide();	
                    
					   $.notify("Trigger has been successfully restore", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"bell",
							  delay:5000,
							  blur: 0.8,
							  close: true,
							  background: "#008000",
							  color: "#98FB98",
							  buttonAlign: "center",
							});
							/*
                       setTimeout(function() {
                            window.location.href = '<?php echo get_home_url(); ?>/archivedocketcases';
                       }, 1000);
					   */
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       jQuery("#button_export").hide();
                    }
                });
	}
    function view_trigger(case_id,importDocketId)
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
        <?php } else { ?>
               window.location.href='<?php echo get_home_url(); ?>/archivecaseevents?case_id='+case_id+'&importDocketId='+importDocketId;
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
 
 function delete_trigger(case_id,triggerId)
        {
            <?php if(!isset($_SESSION['access_token'])) { ?>
                alert('Please login into Google Authentication to access delete.');
                window.location.href='<?php echo get_home_url(); ?>/google-login/?docket_case='+case_id;
            <?php } else { ?>
            jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Deleting the Trigger will also delete All Events. Are you sure you want proceed?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
               // jQuery("#ajax_result").show();
                //jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery(".overlay").show();	
                jQuery.ajax({
                    //url: "<?php echo get_home_url(); ?>/ajax/deleteCaseNotification.php",
					url: "<?php echo get_home_url(); ?>/ajax/deleteTriggerNotification.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_id":case_id,"triggerId":triggerId},
                    success: function (response) {
                       console.log(response);
					   jQuery(".overlay").hide();	
                      // jQuery("#ajax_result").show();
                       //jQuery("#ajax_result").html(response.html);
					   $.notify("You will recive an Email Notification for deletion of Trigger", {
							  type:"danger",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"bell",
							  delay:5000,
							  blur: 0.8,
							  close: true,
							  background: "#D44950",
							  color: "#FDFEFE",
							  buttonAlign: "center",
							});
                       setTimeout(function() {
                            window.location.href = '<?php echo get_home_url(); ?>/archivecasetriggers?case_id='+case_id;
                       }, 1000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       jQuery("#button_export").hide();
                    }
                });
                jQuery.dialogbox.close();
            },
            function(){
                jQuery.dialogbox.close();
            }
            ]
        });

            <?php } ?>
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