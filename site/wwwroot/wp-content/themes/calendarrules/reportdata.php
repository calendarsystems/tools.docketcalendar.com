	<?php require_once('Connections/docketDataSubscribe.php');
		require_once('googleCalender/settings.php');
		/*
		Template Name: reportdata
		*/
		//custom hooks below here...
		remove_action('genesis_loop', 'genesis_do_loop');
		add_action('genesis_loop', 'custom_loop');
		function custom_loop() {
		  session_start();	
		  $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		  require('globals/global_tools.php');
		  require('globals/global_courts.php');
		  
	if (!isset($_SESSION['userid'])) {
      echo "<script>alert('Your browser session has expired, please login into Site.');window.location.href='/login';</script>";
    }
		
	function cmp($a, $b){
	if ($a == $b)
	return 0;
	return ($a['name'] < $b['name']) ? -1 : 1;
	}
	$contact_list = $_SESSION['google_contacts']; usort($contact_list, "cmp");
          
	?>
	<!-- JAVASCRIPT -->

	<script src="https://tools.docketcalendar.com/jquery/js/jquery-1.8.3.js"></script>
	<script src="https://tools.docketcalendar.com/jquery/js/typeahead.js"></script>
	<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.colVis.min.js"></script>
	<!-- 
	<script src="https://cdn.datatables.net/plug-ins/1.10.11/sorting/date-uk.js"></script>
	-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
	<script src="https://cdn.datatables.net/plug-ins/1.10.20/sorting/datetime-moment.js"></script>
	
	<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
	<script src="https://tools.docketcalendar.com/jquery/js/jquery.datetimepicker.full.min.js"></script>
	<!-- CSS -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css" rel="stylesheet">
	<link rel = "stylesheet" type = "text/css"    href = "https://tools.docketcalendar.com/jquery/css/standalone.css">
	<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/fastselect.css">
	<link href="https://tools.docketcalendar.com/jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
	<style>
	.typeahead { border: 2px solid #FFF;border-radius: 4px;padding: 8px 12px;max-width: 470px;min-width: 290px;background: rgb(213, 233, 234);color: #203334;}
	.tt-menu { width:300px; }
	ul.typeahead{margin:0px;padding:10px 0px;}
	ul.typeahead.dropdown-menu li a {padding: 10px !important;	border-bottom:#CCC 1px solid;color:#203334;}
	ul.typeahead.dropdown-menu li:last-child a { border-bottom:0px !important; }
	.bgcolor {max-width: 550px;min-width: 290px;max-height:340px;center;padding: 100px 10px 130px;border-radius:4px;text-align:center;margin:10px;}
	.demo-label {font-size:1.5em;color: #686868;font-weight: 500;color:#203334;}
	.dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, .dropdown-menu>.active>a:hover {
		text-decoration: none;
		background-color: rgb(105, 188, 192);
		outline: 0;
	}
	table.dataTable tbody td {
	  word-break: break-word;
	  vertical-align: top;
	}
</style>
    <div style="width: 80%;">
        <div style="float: left;width: 70%;"><h2>Report Tool</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="https://tools.docketcalendar.com/docket-cases">Docket Cases</a></div>
    </div>
	<div id="ReportCaseid" class="widget FntCls">
	<form name="reportData" id="reportData" method="POST">
		<table class="table table-striped" width="100%" cellpadding="2" cellspacing="2" id="tb1">
		<tr>
		   <td style="width:25%"><b>Date Range:</b></td>
		   <td>
				From ->&nbsp;<input type="text" name="rptDate1" id="datepicker1" class="datepicker" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				To->&nbsp;<input type="text" name="rptDate2" id="datepicker2" class="datepicker" /> 
				<span style="color:red;" id="messageSpan"></span>
		   </td>  
        </tr>
			<tr>
		   <td style="width:25%"><b>Quick Pick:</b></td>
		   <td>
				<div>
					<input type="radio" name="quickDateRange" value="Yesterday">Yesterday /
					<input type="radio" name="quickDateRange" value="Today">Today /
					<input type="radio" name="quickDateRange" value="ThisWeek">This Week / 
					<input type="radio" name="quickDateRange" value="NextWeek">Next Week / <br/>
					<input type="radio" name="quickDateRange" value="NextTwoWeeks">Next Two Weeks /
					<input type="radio" name="quickDateRange" value="ThisMonth">This Month /
					<input type="radio" name="quickDateRange" value="NextMonth">Next Month /
				</div>
				</td>  
			</tr>
		</table>
		<table class="table table-striped" width="100%" cellpadding="2" cellspacing="2">
		<tr>
		   <td style="width:25%"><b>Case:</b></td>
		   <td>
			<?php
			if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
			{
				$queryGetCaseDetials = "SELECT * from docket_cases as dc
				INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
				WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id DESC";
				$resultGetCaseDetials = mysqli_query($docketDataSubscribe,$queryGetCaseDetials);
				$totalRowsCaseDetials = mysqli_num_rows($resultGetCaseDetials);

			}
			?>
			<?php if($totalRowsCaseDetials > 0 && $_SESSION['author_id'] != '') { ?>
				<select style="width:450px" id="selectCaseDetail">
						<option value="">---Select Case---</option>
			<?php 
				while ($rowCaseData = mysqli_fetch_assoc($resultGetCaseDetials)) 
				{
					/*Array of all Case Id of User*/
					$CaseId[] = $rowCaseData['case_id'];
			?>
						<option value="<?php echo $rowCaseData['case_id']; ?>"><?php echo $rowCaseData['case_matter']; ?></option>
			<?php } ?>
				</select>&nbsp;&nbsp;
			<?php } else if($totalRowsCaseDetials == 0 && $_SESSION['author_id'] != '') { ?>
				   <a href="/add-case">Please add case</a>
			<?php } else { ?>
				   <a href="<?php echo $login_url;?>">Please login Google Authentication to access cases.</a>
			<?php } ?>
		   </td>  
        </tr>
		<tr>
			<td>
				<b>Jurisdiction</b>
			</td>
			<td>
				<span id="ajax_jurisdiction"></span>
			</td>
		</tr>
		<tr>
			<td>
				<b>Trigger</b>
			</td>
			<td>
				<input type="text" id="triggerText" name="triggerText" style="width:450px;" class="typeahead">
			</td>
		</tr>
		<tr>
			<td>
				<b>Event Type</b>
			</td>
			<td>
				<input type="text" id="eventtype" name="eventtype" style="width:450px;" class="typeahead">
			</td>
		</tr>
		<tr>
			<td>
				<b>Location</b>
			</td>
			<td>
				<input type="text" id="location" name="location" style="width:450px;" class="typeahead">
			</td>
		</tr>
		<tr>
			<td>
				<b>Custom Text</b>
			</td>
			<td>
				<input type="text" id="customtext" name="customtext" style="width:450px;" class="typeahead">
			</td>
		</tr>
		<tr>
			<td>
				<b>Attendees</b>
			</td>
			<td>
			<?php
			/* InArrayData of all Case Id of User*/
			$inArrforCaseId = implode(",",$CaseId);
			$getAllCaseAttendees = "SELECT distinct(attendee) from docket_cases_attendees WHERE case_id IN (".$inArrforCaseId.")";
			$resultAllCaseAttendees = mysqli_query($docketDataSubscribe,$getAllCaseAttendees);
			while($rowData = mysqli_fetch_assoc($resultAllCaseAttendees))
			{
				$attendessArr[] = $rowData['attendee'];
			}
			?>
				<?php if(isset($_SESSION['google_contacts'])) { ?>
                    <select multiple="multiple" id="attendees" class="multipleSelect" name="attendees[]" style="width:490px;height: 35px;">
                <?php 
				
					foreach($contact_list as $contact) 
						{ 
						
						
						if($_SESSION['author_id'] == $contact['email'])
							{
								 unset($contact['email']); 
								 unset($contact['name']); 
							}
						if(in_array($contact['email'],$attendessArr))
							{		
															
					?>
						<option value="<?php echo $contact['email'];?>"><?php echo $contact['name'];?></option>	
					<?php 
							}
							
						} 
				?>
				
                    </select>
                <?php } ?>
			</td>
		</tr>
		<tr>
			<td>
				<b>Assigned Users</b>
			</td>
			<td>
			<?php 
			$getAllCaseAssignedUsers = "SELECT distinct(user) from docket_cases_users WHERE case_id IN (".$inArrforCaseId.")";
			$resultAllCaseAssignedUsers = mysqli_query($docketDataSubscribe,$getAllCaseAssignedUsers);
			while($rowAssignedUsersData = mysqli_fetch_assoc($resultAllCaseAssignedUsers))
			{
				$CaseAssignedUsersArr[] = $rowAssignedUsersData['user'];	
			}
			?>
				
				<?php if(isset($_SESSION['google_contacts'])) { ?>
                    <select multiple="multiple" id="assignedUsers" class="multipleSelect" name="assignedUsers[]" style="width:470px;height: 35px;">
                <?php 
					
					foreach($contact_list as $contact) 
						{ 
							if($_SESSION['author_id'] == $contact['email'])
							{
								 unset($contact['email']); 
								 unset($contact['name']); 
							}
							if(in_array($contact['email'],$CaseAssignedUsersArr))
							{							
				?>
                        <option value="<?php echo $contact['email'];?>"><?php echo $contact['name'];?></option>
                <?php 		
							}
						}		
				?>
                    </select>
                <?php } ?>
			</td>
		</tr>
		</table>
		<table class="table table-striped" width="100%" cellpadding="2" cellspacing="2">
		<tr>
		   <td style="width:25%"><b>Add Columns:</b></td>
		   <td>
				<div>
				<input type="checkbox" class="ids" name="tabheadrs[]" value="juriHdr">Jurisdiction /	
				<input type="checkbox" class="ids" name="tabheadrs[]" value="trigHdr">Trigger /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="eventypeHdr">Event Type /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="locHdr">Location /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="custtxtHdr">Custom Text /<br/>
				<input type="checkbox" class="ids" name="tabheadrs[]" value="courtruleHdr">Court Rule /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="dateruleHdr">Date Rule /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="attHdr">Attendees /
				<input type="checkbox" class="ids" name="tabheadrs[]" value="assignHdr">Assigned User
				</div>
		   </td>  
        </tr>
		<tr>
			<td>
				<b>Include Archived Case data</b>
			</td>
			<td>
				<label><input type="radio" name="includeArchValue" value="Yes">Yes</label> 
				<label><input type="radio" name="includeArchValue" value="No" checked>No</label>
			</td>
		</tr>	
		<tr>
		   <td ><input id="btnView" name="btnView" type="button" value="View"  /></td>
		    <td><input style="float:right;" id="btnReset" name="btnReset" type="button" value="Reset"  /></td>
        </tr>
		</table>
	</form>		
	<hr>
	<hr>
	<div id="dataContainer">
	</div>
	<hr>
	</div>
	<script>
		$(document).ready(function () {
			$('.multipleSelect').fastselect();
			if ($.fn.DataTable.isDataTable("#example")) {
				$('#example').DataTable().clear().destroy();
			}
			$('#example').DataTable({
				destroy: true,
				responsive: true,
				deferRender:    true,
				scrollY:        400,
				scrollX:        true,
				scrollCollapse: true,
				scroller:       true,
				 fixedHeader: {
					header: true, 
				},
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
					}
				},
				
				],
				columnDefs: [ {
				visible: false
				} ]
			});
				//$("#datepicker1,#datepicker2").datepicker({ dateFormat: 'yy-mm-dd' });
			$("#datepicker1").datepicker({
					dateFormat: 'yy-mm-dd',
					showButtonPanel: true,
					closeText: 'Clear', // Text to show for "close" button
					onSelect: function (selected) {
					var dt = new Date(selected);
					dt.setDate(dt.getDate() + 1);
					$("#datepicker2").datepicker("option", "minDate", dt);
					$("#t").datepicker("option", "minDate", dt);
					$('#tb1 input[type=radio]').attr('disabled','true');
					},onClose: function () {
					var event = arguments.callee.caller.caller.arguments[0];
					// If "Clear" gets clicked, then really clear it
					if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
						$(this).val('');
						$("#tb1 input[type=radio]").removeAttr('disabled');
					}
				}
			});
			$("#datepicker2").datepicker({
					dateFormat: 'yy-mm-dd',
					showButtonPanel: true,
					closeText: 'Clear', // Text to show for "close" button
					onSelect: function (selected) {
						var dt = new Date(selected);
						dt.setDate(dt.getDate() - 1);
						$("#datepicker1").datepicker("option", "maxDate", dt);
						$('#tb1 input[type=radio]').attr('disabled','true');
					},onClose: function () {
					var event = arguments.callee.caller.caller.arguments[0];
					// If "Clear" gets clicked, then really clear it
					if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
						$(this).val('');
						$("#tb1 input[type=radio]").removeAttr('disabled');
					}
				}
			});
			
			$("#ajax_jurisdiction").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			$.ajax({
				url: "<?php echo get_home_url(); ?>/ajax/ajax_userjurisdictionvalue.php",
				type: "post",
				success: function (response) {
				   jQuery("#ajax_jurisdiction").html(response);
				},
				error: function(jqXHR, textStatus, errorThrown) {
				   console.log(textStatus, errorThrown);
					}
			});
			$("#btnReset").click(function () {
					$("#tb1 input[type=radio]").removeAttr('disabled');
					$('#reportData').trigger("reset");
			});
			$("#btnView").click(function () {
					$("#dataContainer").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
					$("#dataContainer").show();
					var HeaderArray=[];
					$("input:checkbox[class=ids]:checked").each(function () {
						HeaderArray.push($(this).val());
					});
					var caseIdVal       	= $("#selectCaseDetail").val();
					var rtpDateR1Val 		= $("#datepicker1").val();
					var rtpDateR2Val 		= $("#datepicker2").val();
					var jurisdictionVal 	= $("#case_jurisdiction").val();;
					var triggerVal 			= $("#triggerText").val();
					var eventtypeVal 		= $("#eventtype").val();
					var locationVal 		= $("#location").val();
					var customtextVal 		= $("#customtext").val();
					var attendeesVal 		= $("#attendees").val();
					var assignedUsersVal 	= $("#assignedUsers").val();
					var assignedUsersVal 	= $("#assignedUsers").val();
					var quickPickCriteria   = $("input:radio[name='quickDateRange']:checked").val();
					var includearch   = $("input:radio[name='includeArchValue']:checked").val();
					$.ajax({
						url: "<?php echo get_home_url(); ?>/ajax/ajax_report_data.php",
						type: "post",
						data: {"headerArrData":HeaderArray,"caseIdVal":caseIdVal,"rtpDateR1Val":rtpDateR1Val,"rtpDateR2Val":rtpDateR2Val,"jurisdictionVal":jurisdictionVal,"triggerVal":triggerVal,"eventtypeVal":eventtypeVal,"locationVal":locationVal,"customtextVal":customtextVal,"attendeesVal":attendeesVal,"assignedUsersVal":assignedUsersVal,"quickPickCriteria":quickPickCriteria,"includearch":includearch},
						success: function (response) {
							$("#dataContainer").html('');
							//$('#example').DataTable().ajax.reload();
							$("#dataContainer").html(response);
						},
						error: function(jqXHR, textStatus, errorThrown) {
						   console.log(textStatus, errorThrown);
						}
					});
			});
		});		
		$('#eventtype').typeahead({
            source: function (query, result) {
                $.ajax({
                    url: "<?php echo get_home_url(); ?>/ajax/ajax_event_autocomplete.php",
					data: 'query=' + query,            
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
						result($.map(data, function (item) {
							return item;
                        }));
                    }
                });
            }
        });
    
		$('#location').typeahead({
            source: function (query, result) {
                $.ajax({
                    url: "<?php echo get_home_url(); ?>/ajax/ajax_customtextandlocation_autocomplete.php",
					data: 'location=' + query,            
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
						result($.map(data, function (item) {
							return item;
                        }));
                    }
                });
            }
        });
		$('#customtext').typeahead({
            source: function (query, result) {
                $.ajax({
                    url: "<?php echo get_home_url(); ?>/ajax/ajax_customtextandlocation_autocomplete.php",
					data: 'customtext=' + query,            
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
						result($.map(data, function (item) {
							return item;
                        }));
                    }
                });
            }
        });
		
		var jurisdiction;
		$("#case_jurisdiction").live("change", function(event) {	
				jurisdiction = $("#case_jurisdiction").val();
		});
		if(jurisdiction === 'undefined')
		{
			jurisdiction = 0;
		}
		$('#triggerText').typeahead({
            source: function (query, result) {
                $.ajax({
                    url: "<?php echo get_home_url(); ?>/ajax/ajax_trigger_autocomplete.php",
					data: 'trigger=' + query + '&jurisdiction=' + jurisdiction,       
                    dataType: "json",
                    type: "POST",
                    success: function (data) {
						result($.map(data, function (item) {
							return item;
                        }));
                    }
                });
            }
        });
		
	</script>
	<?php 
		}
		 genesis(); // <- everything important: make sure to include this.
	?>
