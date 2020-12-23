<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: Calendar
 */
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
session_start();
function custom_loop() {
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
    require('globals/global_tools.php');
    require('globals/global_courts.php');
	
	?>
<style>
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
<!-- JAVASCRIPT -->
<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<link rel="stylesheet" href="components/bootstrap2/css/bootstrap.css">
<link rel="stylesheet" href="components/bootstrap2/css/bootstrap-responsive.css">
<!-- CSS -->
<link rel="stylesheet" href="components/css/calendar.css">

<style>
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
<div class="overlay">
    <div id="loading-img"></div>
</div>
<div class="container">
	<div class="page-header">

		<div class="form-inline">
			<div class="btn-group">
				<button class="btn btn-primary" data-calendar-nav="prev"><< Prev</button>
				<button class="btn" data-calendar-nav="today">Today</button>
				<button class="btn btn-primary" data-calendar-nav="next">Next >></button>
			</div>
			<div class="btn-group">
				<button class="btn btn-warning" data-calendar-view="year">Year</button>
				<button class="btn btn-warning active" data-calendar-view="month">Month</button>
				<button class="btn btn-warning" data-calendar-view="week">Week</button>
				<button class="btn btn-warning" data-calendar-view="day">Day</button>
			</div>
		</div>

		<h3></h3>
		<label class="checkbox">
					<input type="checkbox" id="include_archive_events"> Include Archive Events
		</label>
		
	</div>

	<div class="row">
		<div class="span9">
			<div id="calendar"></div>
		</div>
		<!--
		<div class="span3">
		
			<div class="row-fluid">
			
				
			
				<label class="checkbox">
					<input type="checkbox" id="format-12-hours" > 12 Hour format
				</label>
				<label class="checkbox">
					<input type="checkbox" id="show_wb"> Show week box
				</label>
				<label class="checkbox">
					<input type="checkbox" id="show_wbn"> Show week box number
				</label>
				
			</div>
			
		
			<h4>Events</h4>
			<small>This list is populated with events dynamically</small>
			<ul id="eventlist" class="nav nav-list"></ul>
			
		</div>
	-->
	</div>

	<div class="clearfix"></div>
	<br><br>
	<div class="modal hide fade" id="events-modal">
	<div class="overlay">
		<div id="loading-img"></div>
	</div>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Event</h3>
		</div>
		<div class="modal-body" style="height: 400px">
		</div>
		<div class="modal-footer">
			<a href="#" data-dismiss="modal" class="btn">Close</a>
		</div>
	</div>
	<script type="text/javascript" src="components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="components/underscore/underscore-min.js"></script>
	<script type="text/javascript" src="components/bootstrap2/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="components/jstimezonedetect/jstz.min.js"></script>
	<script type="text/javascript" src="components/js/calendar.js"></script>
	<script type="text/javascript" src="components/js/events.js"></script>
</div>
<?php
}
genesis();
?>