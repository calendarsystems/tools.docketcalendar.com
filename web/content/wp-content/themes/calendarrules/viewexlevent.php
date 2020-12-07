	<?php require_once('Connections/docketDataSubscribe.php');
		require_once('googleCalender/settings.php');
		/*
		Template Name: viewexlevent
		*/
		//custom hooks below here...
		remove_action('genesis_loop', 'genesis_do_loop');
		add_action('genesis_loop', 'custom_loop');

		function custom_loop() {
		  $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		  require('globals/global_tools.php');
		  require('globals/global_courts.php');
		  	$Jusri = $_GET['juri'];
			$Trigger = $_GET['trig'];	
	?>
		<!-- JS-->
		<script src="jquery/js/jquery-1.8.3.js"></script>
		<link href="jquery/css/jquery.modal.css" rel="stylesheet" type="text/css">
		<link href="jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
		<script src="jquery/js/jquery.modal.js" type="text/javascript"></script>
		<script src="jquery/js/jquery.datetimepicker.full.min.js"></script>
		<style>
			#divform1 { font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;}
			.xdsoft_datetimepicker .xdsoft_timepicker {
			 width:70px !important;
			}
			.jsgrid-grid-body
			{
			   height:150px !important;
			}
			.reviewdata {
			font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
			border-collapse: collapse;
			width: 100%;
			border-collapse:separate;
			border-radius:6px;
			-moz-border-radius:6px;
			border: 1px solid #E5EBEE;
			}

			.reviewdata td, #reviewdata th {
			border: 1px solid #F6F8F9;
			padding: 8px;
			}

			.reviewdata tr:hover {background-color: #ddd;}

			.reviewdata th {
			padding-top: 12px;
			padding-bottom: 12px;
			text-align: left;
			background-color: #4CAF50;
			color: white;
			}
			.reviewheader
			{
			background-color: #b7deed;
			color:#1e5799;
			}
		</style>
		
		<script>
		 jQuery('#txtTime').datetimepicker({
					 datepicker:false,
					 format:'h:i a',
					 formatTime: 'h:i a',
					 step:30,
					 ampm: true,
					 value:'00:00',      
		  });
		</script>
		<fieldset class="grpBox">
			<table style="width: 100%;clear:both;">
				<tr>
					<td style="float:left"><h3>View Exclude Event(s)</h3></td>
					<td style="float:right"><a href='/excludevents' >Exclude events</a>|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></td>
				</tr>
			</table>
			<span id="ajax_result"></span>
			<div id="eventOutput">
				<table style="width: 100%;clear:both;">
					<tr>
						<td style="float:left;">Court:&nbsp;&nbsp;&nbsp;</td>
						<td style="float:left;font-weight:bold;" id="jurisdicationVal"></td>
					</tr>
					<tr>
						<td style="float:left;">Trigger:</td>
						<td style="float:left;font-weight:bold;" id="triggerValtd"></td>
					</tr>
				</table>
				
				<input type="hidden" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime(@$_POST['txtTime'])); ?>" style="width:60%" />
				<input type="hidden" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($_POST['txtTriggerDate'])) { echo $_POST['txtTriggerDate']; } else { echo date("m/d/Y"); } ?>" /> 
				<div id="output">
				<div>
		
					<script type="text/javascript">
			
			var cmbJurisdictions = '<?php echo $Jusri; ?>';
			var cmbTriggers =  '<?php echo $Trigger; ?>';
			var selectServiceType =  jQuery("#cmbServiceTypes").val();
			var txtTriggerDate =  jQuery("#datepicker").val();
			var txtTime =  jQuery("#txtTime").val();
			var cmbMatter =  49;
			var isTimeRequired =  jQuery("#isTimeRequired").val();
			var sort_date =  jQuery("#sort_date").val();
			var isServed =  jQuery("#isServed").val();

/*		
	alert("cmbJurisdictions ="+cmbJurisdictions);
	alert("cmbTriggers ="+cmbTriggers);
	alert("isTimeRequired ="+isTimeRequired);
	alert("cmbMatter ="+cmbMatter);
	alert("selectServiceType ="+selectServiceType);
	alert("isServed ="+isServed);
	alert("txtTime ="+txtTime);
	alert("txtTriggerDate ="+txtTriggerDate);
	alert("sort_date ="+sort_date);
 */
		jQuery("#eventOutput").hide();
		jQuery("#ajax_result").show();
		jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_exclude_events.php",
                type: "post",
                //dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":sort_date},
                success: function (response) {
                  console.log(response);
				  
                  jQuery("#output").show();
                  jQuery("#output").html(response);
               
                },
                error: function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                  
                }
         });

						jQuery.ajax({
						url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
						type: "post",
						data: {"jurisdicationVal":cmbJurisdictions,"triggerVal":cmbTriggers},
						dataType: "json",
						success: function (response) {
						console.log(response);
							jQuery("#triggerValtd").html(response.tiggersResultVal);
							jQuery("#jurisdicationVal").html(response.jurisdictionResultVal);
							jQuery("#ajax_result").hide(500);
							jQuery("#eventOutput").show();
						},
						error: function(jqXHR, textStatus, errorThrown) {
						   console.log(textStatus, errorThrown);
						}
					  });
			</script>

			</div>	
		</fieldset>
	<?php  
		}
		 genesis(); // <- everything important: make sure to include this.
	?>
