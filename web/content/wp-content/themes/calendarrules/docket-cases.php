<?php 
require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');

/*
Template Name: Docket Cases
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

	//$_SESSION['author_id'] = "ttestcal@gmail.com";
    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='https://".$_SERVER["HTTP_HOST"]."/docket-calculator';</script>";
    }
    if(!isset($_SESSION['access_token']))
    {
		if($_SESSION['CheckAccess']!="NoGmail")
		{
			$login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
			echo "<script>window.location.href='".$login_url."';</script>";
		}
      
    }
	$getUserInfo = "SELECT COUNT(id) FROM userContactsInfo WHERE user_id = ".$_SESSION['userid']." AND email = '".$_SESSION['author_id']."'";
	$dataGetUserInfo = mysqli_query($docketDataSubscribe,$getUserInfo);
	$totalRowsdataGetUserInfo = mysqli_num_rows($dataGetUserInfo);
	if($totalRowsdataGetUserInfo == 0)
	{
		$insertIntoTable = "INSERT INTO userContactsInfo(user_id,email) VALUES(".$_SESSION['userid'].",'".$_SESSION['author_id']."')";
		$dataInsertIntoTable = mysqli_query($docketDataSubscribe,$insertIntoTable);
	}
	?>
	<!-- JAVASCRIPT -->
	<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
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
	
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jqGrid.bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
	
<style>
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
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
    }
button.mbtn {
  padding:0.6em 2em;
  border-radius: 8px;
  color:#fff;
  background-color:#1976d2;
  font-size:1.1em;
  border:0;
  cursor:pointer;
  margin:1em;
}

button.mbtn.green
{
    background-color:#2e7d32;
}

button.mbtn.red
{
    background-color:#c62828;
}	
#loading-img {
    background: url(https://tools.docketcalendar.com/assets/images/ajax-loader.gif) center center no-repeat;
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
<div style="width: 100%;">
    <div style="float: left;"><h2>Docket Calculator Cases</h2></div>
    <div style="margin-left: 15%;float: right;"><!--<input type="button" id="getContacts" name="getContacts" value="Update Contacts" onclick="getGmailContacts();">--><a href="https://tools.docketcalendar.com/add-custom-contacts"><b>Contacts</b></a>&nbsp;|&nbsp;<a href="https://tools.docketcalendar.com/archivedocketcases"><b>Archive Cases</b></a>&nbsp;|<a href="https://tools.docketcalendar.com/add-case"><b>Add New Case</b></a>&nbsp;|&nbsp;<a href="https://tools.docketcalendar.com/import-case-events">Cases Events</a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="javascript:PrintResult();">Print</a></div>
</div>
<div id="ajax_result" style="margin-left:350px;color:red;padding-top: 25px;"></div>

<?php
	/*
	$query_importEvents = "SELECT * from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents); 
	*/
	$arrayForCaseIdForUserEmail   = array();
	$arrayForCaseIdForAssignEmail = array();
	$arrayForArchiveCase          = array();
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

	$querySelectAllCaseFromArchive = "SELECT caseid FROM docket_cases_archive WHERE userid=".$_SESSION['userid']." AND case_delete = 2";
	$resultAllCaseFromArchive = mysqli_query($docketDataSubscribe,$querySelectAllCaseFromArchive);
    $totalRowsAllCaseFromArchive = mysqli_num_rows($resultAllCaseFromArchive);
	if($totalRowsAllCaseFromArchive > 0)
	{
		 while ($rowDataAllCaseFromArchive = mysqli_fetch_assoc($resultAllCaseFromArchive)) {
			 $arrayForArchiveCase[] = $rowDataAllCaseFromArchive['caseid'];
		 }
		 $result = array_diff($arrayForCaseId,$arrayForArchiveCase);
		 $inClause = implode(",",$result);
		 
	}
	else{
		
		$inClause = implode(",",$arrayForCaseId);
	}
	
	$query_importEvents = "SELECT dc.case_id,dc.case_matter as case_matter,dc.created_by as createdBy,dc.modified_by,dc.created_on as createdOn from docket_cases as dc
    INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
    WHERE dc.case_id IN (".$inClause.") GROUP BY dc.case_id ORDER BY dc.case_id DESC";
    $ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
    $totalRows_importEvents = mysqli_num_rows($ImportEvents);
	

   
?>
    <div class="FntCls" id="jsGrid" style="padding-bottom: 30px;"></div>

	 <?php if(!isset($_SESSION['access_token'])) {
		if($_SESSION['CheckAccess']!="NoGmail")
		{
			?><span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='<?php echo get_home_url(); ?>/google-login/?delete_event=<?php echo $case_id;?>'>Please login Google Authentication to update/delete events.</a></span><?php } 
			}?>
    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                    return (!filter.Case || client.Case.indexOf(filter.Case) > -1)
						&& (!filter.Action || client.Action === filter.Action)
						&& (!filter.Comment || client.Comment.indexOf(filter.Comment) > -1)
                        && (!filter.Created || client.Created.indexOf(filter.Created) > -1)
						&& (!filter.Modified || client.Modified.indexOf(filter.Modified) > -1);
                        
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php if($totalRows_importEvents > 0) {
                while ($row_events = mysqli_fetch_assoc($ImportEvents)) {
					$sqlSelectCaseCustomText = "SELECT case_customtext FROM docket_customtext WHERE case_id =".$row_events['case_id']." AND case_customtextlevel=1";
					$ResultSelectCaseCustomText = mysqli_query($docketDataSubscribe,$sqlSelectCaseCustomText);
					$totalRowsSelectCaseCustomText = mysqli_num_rows($ResultSelectCaseCustomText);
					if($totalRowsSelectCaseCustomText > 0)
					{
						while ($rowData = mysqli_fetch_assoc($ResultSelectCaseCustomText))
						{
							$textValue = $rowData ['case_customtext'];
						}	
					}
					else{
						$textValue ="";
					}
					
                    ?>
                {
              
                <?php  if($_SESSION['author_id'] == $row_events['created_by']) { ?>
                "Action": "<button class='myButton green' onclick='javascript:edit_case(<?php echo $row_events['case_id']; ?>);'><strong>E</strong>dit</button><button class='myButton blue' onclick='javascript:view_case(<?php echo $row_events['case_id']; ?>);'><strong>V</strong>iew</button>",
                <?php } else { ?>
                  "Action": "<button class='myButton' onclick='javascript:edit_case(<?php echo $row_events['case_id']; ?>);'><strong>E</strong>dit</button><button class='myButton' onclick='javascript:view_case(<?php echo $row_events['case_id']; ?>);'><strong>V</strong>iew</button>",
                <?php } ?>
				"Case": "<?php echo $row_events['case_matter']; ?>",
				"Comment": "<?php echo $textValue; ?>",
				"Created By": "<?php echo $row_events['createdBy']; ?>",
				"Modified By": "<?php echo $row_events['modified_by']; ?>",
                "Created": "<?php echo date("m/d/Y, h:i A",strtotime($row_events['createdOn'])); ?>"
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
                height: "70%",
                width: "100%",
                sorting: true,
                paging: true,
                autoload: true,
				filtering: true,
				styleUI : "Bootstrap",
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
					{ name: "Action",  width: 140  },
                    { name: "Case", type: "text", width: 250 },
					{ name: "Comment", type: "text", width: 250 },
                    { name: "Created By",  width: 320 },
					{ name: "Modified By",  width: 320 },
                    { name: "Created",  width: 200 }
                    
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
                pageSize: 1000,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Case", type: "text", width: 250 },
					{ name: "Comment", type: "text", width: 250 },
                    { name: "Created By", type: "text", width: 320 },
					{ name: "Modified By",  width: 320 },
                    { name: "Created", type: "text", width: 200 },
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
					{ name: "Action", type: "text", width: 140, sorting: false  },
                    { name: "Case", type: "text", width: 250 },
					{ name: "Comment", type: "text", width: 250 },
                    { name: "Created By", type: "text", width: 320 },
					{ name: "Modified By",  width: 320 },
                    { name: "Created", type: "text", width: 200 }
                    
                ]
            });
            popupWin.document.close();
    }
        function edit_case(case_id)
        {
		  jQuery(".overlay").show();
          window.location.href='<?php echo get_home_url(); ?>/update-case?case_id='+case_id;
        }
        function view_case(case_id)
        {
		  jQuery(".overlay").show();	
          window.location.href='<?php echo get_home_url(); ?>/casetriggers?case_id='+case_id;
        }
   
		
	
    </script>
<?php

}

genesis();
?>