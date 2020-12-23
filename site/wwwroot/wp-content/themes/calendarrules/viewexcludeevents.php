<?php require_once('Connections/docketDataSubscribe.php');
		require_once('googleCalender/settings.php');
		/*
		Template Name: viewexcludeevents
		*/
		//custom hooks below here...
		remove_action('genesis_loop', 'genesis_do_loop');
		add_action('genesis_loop', 'custom_loop');
		function custom_loop() {
		  $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		  require('globals/global_tools.php');
		  require('globals/global_courts.php');
	?>
		<!-- JS-->
<script src="https://tools.docketcalendar.com/jquery/js/jquery-1.8.3.js"></script>
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


#MI6 td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
	<div style="width: 80%;" class="FntCls">
        <div style="float: left;width: 70%;"><h2>View Exclude Events</h2></div>
        <div style="margin-left: 15%;float: right;"><a href='#' onclick="window.history.go(-1); return false;">Back</a></div>
    </div>
			
		<span id="ajax_result"></span>
			
		<table  class='reviewdata'>
			<thead>
			<tr>
				<td class='reviewheader' width='300px'>Jurisdiction</td>
				<td class='reviewheader' width='300px'>Trigger Name</td>
				<td class='reviewheader' width='300px'>Event</td>
				
			</tr>
			<tr>
				<td class='reviewheader' width='300px'><input type="text" id="courtdesc" class="search-key" size="35" placeholder="Jurisdiction"></td>
				<td class='reviewheader' width='300px'><input type="text" id="triggerdesc" size="35" class="search-key" placeholder="Trigger"></td>
				<td class='reviewheader' width='300px'><input type="text" id="eventdesc" class="search-key" size="35" placeholder="Event"></td>
			</tr>
			</thead>
		</table>
	<div class="output"></div>
	<script>
	$(document).ready(function(){
		$(".overlay").show();
	setTimeout(function() {
	   $(".overlay").hide();
   }, 2000);
		$(".search-key").live("keyup", function(event) {
		var $filterableRows = $('#MI6').find('tr');
		 $filterableRows.hide().filter(function() {
		return $(this).find('td').filter(function() {
		  var tdText = $(this).text().toLowerCase(),
		  inputValue = $('#' + $(this).data('input')).val().toLowerCase();
			return tdText.indexOf(inputValue) != -1;
		}).length == $(this).find('td').length;
		}).show(); 
		});
	});
	</script>
	<script>
	jQuery(".output").hide();
	//jQuery("#ajax_result").show();
	//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
	jQuery.ajax({
		url: "<?php echo get_home_url(); ?>/ajax/ajax_showExcludeEvents.php",
		type: "post",
		success: function (response) {
		  console.log(response);
		   //jQuery("#ajax_result").hide(500);
		   jQuery(".output").show();
		   jQuery(".output").html(response);
		},
		error: function(jqXHR, textStatus, errorThrown) {
		   console.log(textStatus, errorThrown);
		}
	  });
	</script>

	<?php 
		}
		 genesis(); // <- everything important: make sure to include this.
	?>
