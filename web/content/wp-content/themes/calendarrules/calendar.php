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
</style>
<script src="//code.jquery.com/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/main.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/daygridmain.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/timegridmain.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/listmain.css' rel='stylesheet' />

<!-- JAVASCRIPT -->
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/main.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/daygridmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/timegridmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/listmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/interactionmain.js'></script>



<style>
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


</style>

<div id='script-warning'>
	<div id='loading'>
		 <div class="overlay">
			<div id="loading-img"></div>
		</div>
	</div>
</div>
<div id='calendar'></div>
	<div id="fullCalModal" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
						<h4 id="modalTitle" class="modal-title"></h4>
					</div>
					<div id="modalBody" class="modal-body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
	  plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
		//right: 'dayGridMonth,listWeek'
      },
      defaultDate: '<?php echo date('Y-m-d');?>',
      editable: true,
      navLinks: true, // can click day/week names to navigate views
      eventLimit: true, // allow "more" link when too many events
      events: {
         url: 'https://tools.docketcalendar.com/events.php',
        failure: function() {
          document.getElementById('script-warning').style.display = 'block'
        }
      },
      loading: function(bool) {
        document.getElementById('loading').style.display =
          bool ? 'block' : 'none';
      },
	  eventClick:  function(event) {
			$('#modalTitle').html(event.event.title);
			$('#modalBody').html(event.event.extendedProps.desc);
			$('#fullCalModal').modal('show');
			
        }
    });

    calendar.render();
  });
</script>
<?php
}
genesis();
?>