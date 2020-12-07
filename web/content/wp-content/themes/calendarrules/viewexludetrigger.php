	<?php require_once('Connections/docketDataSubscribe.php');
		require_once('googleCalender/settings.php');
		/*
		Template Name: viewexcludetrigger
		*/
		//custom hooks below here...
		remove_action('genesis_loop', 'genesis_do_loop');
		add_action('genesis_loop', 'custom_loop');

		function custom_loop() {
		 $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		  require('globals/global_tools.php');
		  require('globals/global_courts.php');
		  $Jusri = $_GET['juris'];	
		  $userName = $_SESSION['username'];
	?>
		<!-- JS-->
		<script src="jquery/js/jquery-1.8.3.js"></script>
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
		var a = 1;
		</script>
		<fieldset class="grpBox">
			<table style="width: 100%;clear:both;">
				<tr>
					<td style="float:left"><h3>View Exclude Trigger(s)</h3></td>
					<td style="float:right"><a href='/excludevents' >Exclude events</a>|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></td>
				</tr>
			</table>
			<span id="ajax_result"></span>
			<div id="outputExcludeevent">
				<table style="width: 100%;clear:both;">
					<tr>
						<td style="float:left;">Court:</td><td style="float:left;font-weight:bold;" id="jurisdicationVal"></td>
					</tr>
				</table>
				<div class="output">
				</div>
				
											<script>
											jQuery("#outputExcludeevent").hide();
											jQuery("#ajax_result").show();
											jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
											var jurisdicationVal = <?php echo $Jusri;?>;
											jQuery.ajax({
											url: "<?php echo get_home_url(); ?>/ajax/ajax_showJurisdictionValue.php",
											type: "post",
											data: {"jurisdicationVal":jurisdicationVal},
											dataType: "json",
											success: function (response) {
											console.log(response);
												jQuery("#jurisdicationVal").html(response.jurisdictionResultVal);
												//jQuery("#ajax_result").hide(500);
												
											},
											error: function(jqXHR, textStatus, errorThrown) {
											   console.log(textStatus, errorThrown);
											}
										  });
										 
										  //jQuery("#outputExcludeevent").hide();
											jQuery("#ajax_result").show();
											jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
											var jurisdicationVal = <?php echo $Jusri;?>;
											jQuery.ajax({
											url: "<?php echo get_home_url(); ?>/ajax/ajax_showExcludeEventTrigger.php",
											type: "post",
											data: {"jurisdicationVal":jurisdicationVal},
											success: function (response) {
											console.log(response);
												jQuery("#ajax_result").hide(500);
												jQuery(".output").html(response);
												jQuery("#outputExcludeevent").show(500);
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
