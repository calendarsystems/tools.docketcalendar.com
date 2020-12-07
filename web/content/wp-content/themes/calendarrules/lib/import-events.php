<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Import Events
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

	global $docketDataSubscribe;

    require('globals/global_tools.php');
    require('globals/global_courts.php');

    if($_SESSION['userid'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
    }
    if($_GET['case_id'] == '')
    {
      echo "<script>window.location.href='http://googledocket.com/docket-calculator';</script>";
    }
    unset($_SESSION['delete_event']);
    $case_id = $_GET['case_id'];
	?>

<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">

<link rel="stylesheet" type="text/css" href="jquery/css/jsgrid.css" />
<link rel="stylesheet" type="text/css" href="jquery/css/theme.css" />
<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
<style>
.jsgrid-grid-body {
  height:auto !important;
}
</style>
<script src="jquery/js/jquery-1.8.3.js"></script>

<script src="src/jsgrid.core.js"></script>
<script src="src/jsgrid.load-indicator.js"></script>
<script src="src/jsgrid.load-strategies.js"></script>
<script src="src/jsgrid.sort-strategies.js"></script>
<script src="src/jsgrid.field.js"></script>
<script src="src/fields/jsgrid.field.text.js"></script>
<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
<?php
$query_maincase = "SELECT dc.* FROM docket_cases dc WHERE dc.case_id = ".$case_id."";
$caseQuery = mysqli_query($docketDataSubscribe,$query_maincase);
$totalRows_caseQuery = mysqli_num_rows($caseQuery);
$row_case = mysqli_fetch_assoc($caseQuery);

$query_importEvents = "SELECT i.access_token,e.authenticator,c.event_date,c.short_name,dc.case_matter,c.case_event_id,e.event_docket,e.has_child,i.triggerItem,i.import_docket_id FROM docket_cases dc
INNER JOIN import_docket_calculator as i ON i.case_id = dc.case_id
INNER JOIN import_events as e ON e.import_docket_id = i.import_docket_id
INNER JOIN case_events as c ON c.import_event_id = e.import_event_id

WHERE dc.user_id = ".$_SESSION['userid']." AND dc.case_id = ".$case_id." ORDER BY c.import_event_id desc";
$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
$totalRows_importEvents = mysqli_num_rows($ImportEvents);
?>
<div style="width: 100%;clear:both;">
    <div style="float: left;"><h2>Calendar Events</h2></div>
    <div style="margin-left: 15%;float: right;"><a href="docket-cases">Docket Cases</a>&nbsp;<?php if($totalRows_importEvents> 0) { ?>|&nbsp;<a href="javascript:void(0);" onclick="javascript:PrintResult();">Print</a><?php } ?></div>
</div>
<span id="ajax_result" style="color:red;padding-left: 220px;"></span>
<div style="clear:both;"><h4>Case : <?php echo $row_case['case_matter'];?></h4></div>
<div><div style="float: left;"><input type="checkbox" onchange="javascript:checkAll(this)">Select All</div><div style="float: right;"><a href="javascript:void(0);" id="deleteAll" onclick="javascript:deleteAll_cal_events(<?php echo $case_id;?>)">Delete</a></div></div>

    <div id="jsGrid" style="padding-bottom: 30px;"></div>
    <?php if(!isset($_SESSION['access_token'])) { ?><span style="position:inherit;padding-top:30px;padding-left:15px;font-size:12px;color:green;"><a href='http://googledocket.com/google-login/?delete_event=<?php echo $case_id;?>'>Please login Google Authentication to update/delete events.</a></span><?php } ?>
    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                    return (!filter.Sr || client.Sr.indexOf(filter.Sr) > -1)
                        && (!filter.Date || client.Date.indexOf(filter.Date) > -1)
                        && (!filter.Trigger || client.Trigger.indexOf(filter.Trigger) > -1)
                        && (!filter.Events || client.Events.indexOf(filter.Events) > -1)
                        && (!filter.Action || client.Action === filter.Action);
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php if($totalRows_importEvents > 0) {
                while ($row_events = mysqli_fetch_assoc($ImportEvents)) { ?>
                {
                "Sr": "<input type='checkbox' name='chk' id='<?php echo $row_events['case_event_id']; ?>'>",
                "Date": "<?php  echo date("dS M Y, h:i A",strtotime($row_events['event_date'])); ?>",
                "Trigger": "<?php echo $row_events['triggerItem'];?>",
                "Events": "<?php echo $row_events['short_name'];?>",
                <?php if($_SESSION['author_id'] == $row_events['authenticator']) {      ?>
                "Action": "<input class='jsgrid-button jsgrid-edit-button' type='button' title='Edit' onclick='javascript:edit_event(<?php echo $row_events['case_event_id']; ?>);'><input class='jsgrid-button jsgrid-clear-filter-button' type='button' onclick='javascript:view_event(<?php echo $row_events['case_event_id']; ?>);' title='View'><input class='jsgrid-button jsgrid-delete-button' type='button' title='Delete' onclick='javascript:delete_calendar_events(<?php echo $row_events['case_event_id']; ?>,<?php echo $case_id;?>,<?php echo $row_events['import_docket_id'];?>);'>"
                <?php } else { ?>
                "Action":"<input class='jsgrid-button jsgrid-clear-filter-button' type='button' onclick='javascript:view_event(<?php echo $row_events['case_event_id']; ?>);' title='View'>"
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
                paging: true,
                autoload: true,
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Sr", type: "checkbox", width: 20 },
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
                    { name: "Action", type: "text", width: 80, sorting: false  },
                ]
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
                pageSize: 100,
                pageButtonCount: 5,
                controller: db,
                fields: [
                    { name: "Date", type: "text", width: 150 },
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
                    { name: "Sr", type: "checkbox", width: 20 },
                    { name: "Date", type: "text", width: 150 },
                    { name: "Trigger", type: "text", width: 150 },
                    { name: "Events", type: "text", width: 300 },
                    { name: "Action", type: "text", width: 80, sorting: false  },
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
                window.location.href='http://googledocket.com/google-login/?view_event='+case_event_id;
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
               window.location.href='http://googledocket.com/view-calendar-event?id='+case_event_id;
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
                window.location.href='http://googledocket.com/google-login/?update_event='+case_event_id;
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
               window.location.href='http://googledocket.com/update-calendar-event?id='+case_event_id;
        <?php } ?>
    }

    function delete_calendar_events(case_event_id,case_id,import_docket_id)
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
                window.location.href='http://googledocket.com/google-login/?delete_event='+case_id;
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
                    data: { "case_event_id":case_event_id, "import_docket_id":import_docket_id },
                    success: function (response) {
                       //console.log(response);
                       jQuery("#ajax_result").show();
                       jQuery("#ajax_result").html(response.html);
                       setTimeout(function() {
                            window.location.href = 'http://googledocket.com/calendar-events?case_id='+case_id;
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

  function deleteAll_cal_events(case_id) {
     var caseEventId  = [];
    $("input:checkbox[name=chk]:checked").each(function () {
           // alert("Id: " + $(this).attr("id"));
            caseEventId.push($(this).attr("id"));
        });
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
                    url: "<?php echo get_home_url(); ?>/ajax/deleteall_import_calendar.php",
                    type: "post",
                    dataType: "json",
                    data: { "case_event_id":caseEventId},
                    success: function (response) {
                       //console.log(response);
                       jQuery("#ajax_result").show();
                       jQuery("#ajax_result").html(response.html);
                       setTimeout(function() {
                            window.location.href = 'http://googledocket.com/calendar-events?case_id=62';
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
        
    }
</script>
<?php
}

genesis();
?>