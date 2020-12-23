<?php require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
/*
Template Name: Viewchildevents
 */

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

		session_start();
		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		 $event_date = $_REQUEST['event_date'];
		 $case_event_id = $_REQUEST['case_event_id'];
		 $attendees = $_REQUEST['attendees'];
		 $caselab = $_REQUEST['caselab'];
		 $eventCustomText = $_REQUEST['eventCustomText'];
		 $DateToChange = (explode(" ",$event_date));	
?>
<style>
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;
    }
	#loading-img {
    background: url(assets/images/ajax-loader.gif) center center no-repeat;
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
</style>

<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="jquery/css/notify.css"> 
<script type="text/javascript" src="jquery/js/notify.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div class="FntCls">
	
	<table style="margin-bottom:5px;">
		<tr>
			<td style="padding-right:5px"><b>Parent New Event Date:</b></td><td><?php  echo date("dS M Y",strtotime($DateToChange[0])).' '. date("H:i:s",strtotime($DateToChange[1])).' '. $DateToChange[2]; ?></td>
		</tr>
	</table>
	<table>
		<tr>
			<td><h3>Children Event(s)</h3></td>
		</tr>
	</table>
</div>

<?php		
		 $query_searchInfo = "SELECT idc.*, ie.system_id, ie.import_event_id as import_event_id, ie.system_id as system_id, ie.event_id as event_id,ie.recalculate_flag FROM case_events as ce
			 inner join import_events as ie ON ie.import_event_id = ce.import_event_id
			 inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
			 WHERE ce.case_event_id = '".$case_event_id."' ";
			$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
			$row_searchInfo = mysqli_fetch_assoc($searchInfo);
			$totalRows_searchInfo = mysqli_num_rows($searchInfo);
			$case_id = $row_searchInfo['case_id'];
			$system_id = $row_searchInfo['system_id'];
			$case_system = array();
			$parent_system = array();
			$has_child = 0;
			
			 $query_parentInfo = "SELECT ie.system_id as system_id, ie.event_id as event_id,idc.import_docket_id
             FROM case_events as ce
             inner join import_events as ie ON ie.import_event_id = ce.import_event_id
             inner join import_docket_calculator as idc ON idc.import_docket_id = ie.import_docket_id
             WHERE ie.parent_system_id = '".$row_searchInfo['system_id']."' AND idc.case_id = '".$case_id."' ";
             $parentInfo = mysqli_query($docketDataSubscribe,$query_parentInfo);

             $totalRows_parentInfo = mysqli_num_rows($parentInfo);

             if($totalRows_parentInfo > 0)
             {
                 $has_child = 1;
                 while($row_parentInfo = mysqli_fetch_assoc($parentInfo))
                 {
                   $parent_system[] = $row_parentInfo['system_id'];
				   $import_docket_id = $row_parentInfo['import_docket_id'];
                 }
             }
			 //print_r($parent_system);
			
			echo  $tablestart="<div class='FntCls'><table class='table table-striped' width='100%'>
			<tr><td width='25%' style='font-weight: bold;'>Event Date</td><td style='font-weight: bold;'>Event(s)</td></tr>";
			 foreach($parent_system as $val)
			 {
				 
				 $query_ChildInfo = "SELECT import_event_id FROM import_events WHERE system_id= '".$val."' AND authenticator='".$_SESSION['author_id']."' and import_docket_id = '".$import_docket_id."'";
				 $ChildimporteventInfo = mysqli_query($docketDataSubscribe,$query_ChildInfo);
				 while($rowImportEventid = mysqli_fetch_assoc($ChildimporteventInfo))
				 {
					 $shortNameChildInfo = "SELECT event_date,short_name FROM case_events WHERE import_event_id= '".$rowImportEventid['import_event_id']."'";
					 $shortNameChildInfoInfo = mysqli_query($docketDataSubscribe,$shortNameChildInfo);
					
					 while($rowshortName = mysqli_fetch_assoc($shortNameChildInfoInfo))
					 {
						echo  $body="<tr><td width='25%'>".$rowshortName['event_date']."</td>"."<td>".$rowshortName['short_name']."</td></tr>";
					 }
				 }
			 }
			echo $tableclose="</table></div>";
			  
?>
	<div id="ajax_result"></div>
	<div id="output"></div>
	<table>
		<tr>
		<td  style="padding-right:5px">
			<input type="button" value="Back" onclick="window.history.go(-1); return false;"/>
		</td>
		<td>
			<input type="button" value="Update" onclick="updateEvents(<?php echo $case_id; ?>,<?php echo $import_docket_id; ?>)"/>
		</td>
		</tr>
	</table>
	<script type="text/javascript">
	jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
	function updateEvents(case_id,import_docket_id)
	{					
		//jQuery("#ajax_result").show();
		//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
		jQuery(".overlay").show();
		var case_event_id='<?php echo $case_event_id; ?>';
		var attendees='<?php echo $attendees; ?>';
		var event_date='<?php echo $event_date; ?>';
		var caselab='<?php echo $caselab; ?>';
		var eventCustomText='<?php echo $eventCustomText; ?>';
		jQuery.ajax({
		url: "<?php echo get_home_url(); ?>/ajax/update_calendar_event.php",
		type: "post",
		dataType: "json",
		data: { "case_event_id":case_event_id, "attendees":attendees, "event_date":event_date, "caselab":caselab,"eventCustomText":eventCustomText },
		success: function (response) {
		   //console.log("YID"+response);
		   //console.log(response);
		   //jQuery("#ajax_result").show(4500);
		  // jQuery("#ajax_result").html(response.html);
		  jQuery(".overlay").hide();
		  $.notify("Events sucessfully updated!", {
				  type:"success",
				  align:"center", 
				  verticalAlign:"middle",
				  animation:true,
				  animationType:"scale",
				  icon:"check",
				  delay:2500,
				  blur: 0.8,
				 close: true,
				  buttonAlign: "center",
				});
		   setTimeout(function() {
				//jQuery('#ajax_result').hide(1000);
				//jQuery("#output").html(response.html);
				
				//window.location.href = '<?php echo get_home_url(); ?>/casetriggers?case_id='+case_id;
		   }, 500);
		},
		error: function(jqXHR, textStatus, errorThrown) {
		   console.log(textStatus, errorThrown);
		   jQuery("#button_export").hide();
		   if(errorThrown)
		   {
			    $.notify("Events sucessfully updated!", {
				  type:"success",
				  align:"center", 
				  verticalAlign:"middle",
				  animation:true,
				  animationType:"scale",
				  icon:"check",
				  delay:2500,
				  blur: 0.8,
				 close: true,
				  buttonAlign: "center",
				});
				 setTimeout(function() {
				 window.location.href = '<?php echo get_home_url(); ?>/calendar-events?case_id='+case_id+'&importDocketId='+import_docket_id;
		   }, 500);
		   }
		}
	 });
	}
	</script>
<?php
}
genesis();
?>