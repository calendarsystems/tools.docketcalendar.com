<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Import Case Events
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

    unset($_SESSION['delete_event']);
	?>

<!-- CSS-->
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
<style>
.jsgrid-grid-body {
  height:auto !important;
}
</style>
<script src="https://tools.docketcalendar.com/jquery/js/jquery-1.8.3.js"></script>

<script src="https://tools.docketcalendar.com/src/jsgrid.core.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-indicator.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.load-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.sort-strategies.js"></script>
<script src="https://tools.docketcalendar.com/src/jsgrid.field.js"></script>
<script src="https://tools.docketcalendar.com/src/fields/jsgrid.field.text.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
<?php
	$arrayForCaseIdForUserEmail   = array();
	$arrayForCaseIdForAssignEmail = array();
	$arrayForCaseId = array();
	
	$getAllCaseIdForUserEmail="SELECT dcu.case_id from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
	$dataCaseIdForUserEmail = mysqli_query($docketDataSubscribe,$getAllCaseIdForUserEmail);
    $totalCaseIdForUserEmail = mysqli_num_rows($dataCaseIdForUserEmail); 
	if($totalCaseIdForUserEmail > 0)
	{
		while($rowCaseIdForUserEmail = mysqli_fetch_assoc($dataCaseIdForUserEmail))
		{
			$arrayForCaseIdForUserEmail[] = $rowCaseIdForUserEmail["case_id"];
		}
		
	}

	$getAllCaseIdForAssignEmail="SELECT dc.case_id from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id != '".$_SESSION['userid']."'  AND dc.created_by != '".$_SESSION['author_id']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
	$dataCaseIdForAssignEmail = mysqli_query($docketDataSubscribe,$getAllCaseIdForAssignEmail);
    $totalCaseIdForAssignEmail = mysqli_num_rows($dataCaseIdForAssignEmail); 
	if($totalCaseIdForAssignEmail > 0)
	{
		while($rowCaseIdForAssignEmail = mysqli_fetch_assoc($dataCaseIdForAssignEmail))
		{
			//$arrayForCaseIdForAssignEmail[] = $rowCaseIdForAssignEmail["case_id"];\
			$arrayForCaseIdForAssignEmailValidate[] = $rowCaseIdForAssignEmail["case_id"];
		}
		
	}
	
	/* NEW CODE FOR ASSIGN CASE START */
	foreach($arrayForCaseIdForAssignEmailValidate as $caseValidateId)
	{
		$getCreatedByEmail = "SELECT created_by from docket_cases WHERE case_id = ".$caseValidateId."";
		$datagetCreatedByEmail = mysqli_query($docketDataSubscribe,$getCreatedByEmail);
		while($rowgetCreatedByEmail = mysqli_fetch_assoc($datagetCreatedByEmail))
		{
			$emailIdoFUser = $rowgetCreatedByEmail["created_by"];
			
			$getUserNameFromSubscribeDocketlaw = "SELECT username from users WHERE email = '".$emailIdoFUser."'";
			$datagetUserNameFromSubscribeDocketlaw = mysqli_query($docketDataSubscribe,$getUserNameFromSubscribeDocketlaw);
			while($rowgetUserNameFromSubscribeDocketlaw = mysqli_fetch_assoc($datagetUserNameFromSubscribeDocketlaw))
			{
					if($rowgetUserNameFromSubscribeDocketlaw['username'] == $_SESSION['username'])
					{
						$arrayForCaseIdForAssignEmail[] = $caseValidateId;
					}
			}
		}
	}
	
	/* NEW CODE FOR ASSIGN CASE END */
	if (!empty($arrayForCaseIdForUserEmail)) {
		if (!empty($arrayForCaseIdForAssignEmail)) 
		{
			$output = array_merge($arrayForCaseIdForUserEmail,$arrayForCaseIdForAssignEmail);
			$output = array_unique($output);
		}else
		{
			$output = array_unique($arrayForCaseIdForUserEmail);
		}
	}
	
	//print_r($output);
	foreach($output as $caseId)
	{
		$arrayForCaseId[] = $caseId;
	}
	$inClause = implode(",",$arrayForCaseId);
	
	
	$queryCaseIdEvents = "SELECT DISTINCT(c.import_event_id),i.access_token,e.authenticator,c.event_date,c.short_name,dc.case_matter,c.case_event_id,e.event_docket,dc.case_id,dc.case_matter,i.triggerItem FROM docket_cases dc
	INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
	INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
	INNER JOIN case_events as c ON c.import_event_id = e.import_event_id
	INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id

	WHERE dc.case_id IN (".$inClause.")  ORDER BY c.import_event_id desc";
	$ImportEvents = mysqli_query($docketDataSubscribe,$queryCaseIdEvents);
	$totalRows_importEvents = mysqli_num_rows($ImportEvents);
?>
<div style="width: 100%;clear:both;">
    <div style="float: left;"><h2>Calendar Events</h2></div>
    <div style="margin-left: 15%;float: right;"><a href="https://tools.docketcalendar.com/docket-cases">Docket Cases</a>&nbsp;<?php if($totalRows_importEvents> 0) { ?>|&nbsp;<a href="javascript:void(0);" onclick="javascript:PrintResult();">Print</a><?php } ?></div>
</div>

<style>
    .sort-panel {
        padding: 10px;
        margin: 30px 0 20px 100px;
        background: #fcfcfc;
        border: 1px solid #e9e9e9;
        display: inline-block;
    }
</style>


    <div class="sort-panel">
        <label>Sorting Field:
            <select id="sortingField">
                <option>Case</option>
                <option>Date</option>
                <option>Trigger</option>
                <option>Events</option>
            </select>
            <button type="button" id="sort">Sort</button>
        </label>
    </div>
    <div id="ajax_result" style="color:red;padding-left: 260px;"></div>
    <div id="jsGrid" style="padding-bottom: 30px;"></div>
    <?php if(!isset($_SESSION['access_token'])) { ?><span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='<?php echo get_home_url(); ?>/google-login/?delete_case_event=1'>Please login Google Authentication to update/delete events.</a></span><?php } ?>
    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                    return (!filter.Case || client.Case.indexOf(filter.Case) > -1)
                        && (!filter.Date || client.Date.indexOf(filter.Date) > -1)
                        && (!filter.Trigger || client.Trigger.indexOf(filter.Trigger) > -1)
                        && (!filter.Events || client.Events.indexOf(filter.Events) > -1);
                        
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php if($totalRows_importEvents > 0) {
                while ($row_events = mysqli_fetch_assoc($ImportEvents)) { ?>
                {
                "Case": "<?php echo $row_events['case_matter'];?>",
                "Date": "<?php  echo date("m/d/Y, h:i A",strtotime($row_events['event_date'])); ?>",
                "Trigger": "<?php echo $row_events['triggerItem'];?>",
                "Events": "<?php echo $row_events['short_name'];?>",
                <?php if($_SESSION['author_id'] == $row_events['authenticator']) {      ?>
                
                <?php } ?>
                },
            <?php } }?>
             ];

        }());

        $(function() {
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: true,
				filtering: true,
                paging: true,
                autoload: true,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Case", type: "text", width: 130 },
                    { name: "Date", type: "text", width: 180 },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
                   
                ]
            });
            $("#sort").click(function() {
                var field = $("#sortingField").val();
                $("#jsGrid").jsGrid("sort", field);
            });
        });
    </script>

<script type="text/javascript">

    function PrintResult() {
        var divToPrint = document.getElementById("jsGrid");
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: false,
                paging: false,
                autoload: true,
                pageSize: 1000,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Case", type: "text", width: 130 },
                    { name: "Date", type: "text", width: 180 },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
                ]
            });
            var popupWin = window.open('', '_blank', 'width=300,height=300');
            popupWin.document.open();
            popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: true,
                paging: true,
                autoload: true,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Case", type: "text", width: 130 },
                    { name: "Date", type: "text", width: 180 },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
                  
                ]
            });
            popupWin.document.close();
    }

    function view_event(case_event_id)
    {
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

    function delete_calendar_events(case_event_id,case_id)
    {
        <?php if(!isset($_SESSION['access_token'])) { ?>
        var data = '<div style="padding:20px;">Please login into Google Authentication to access delete.</div>';
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
                window.location.href='<?php echo get_home_url(); ?>/google-login/?delete_event='+case_id;
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
        //alert('Please login into Google Authentication to access delete.');

        <?php } else { ?>
        jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Are you sure you want to delete?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
                jQuery("#ajax_result").show();
                jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");

                jQuery.ajax({
                    url: "<?php echo get_home_url(); ?>/ajax/delete_import_calendar.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_event_id":case_event_id },
                    success: function (response) {
                       //console.log(response);
                       jQuery("#ajax_result").show();
                       jQuery("#ajax_result").html(response.html);
                       setTimeout(function() {
                            window.location.href = '<?php echo get_home_url(); ?>/calendar-events?case_id='+case_id;
                       }, 2000);
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

genesis();
?>