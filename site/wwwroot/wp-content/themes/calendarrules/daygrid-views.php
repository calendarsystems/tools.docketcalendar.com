<?php require_once('Connections/docketDataSubscribe.php');
/*
Template Name: daygrid-views
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
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/main.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/timegridmain.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/listmain.css' rel='stylesheet' />
<link href='https://tools.docketcalendar.com/jquery/js/CalendarJs/daygridmain.css' rel='stylesheet' />
<!-- JAVASCRIPT -->
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/main.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/timegridmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/listmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/interactionmain.js'></script>
<script src='https://tools.docketcalendar.com/jquery/js/CalendarJs/daygridmain.js'></script>


<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid' ],
      header: {
        left: 'prevYear,prev,next,nextYear today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay'
      },
	defaultDate: '<?php echo date('Y-m-d');?>',
      editable: true,
      navLinks: true, // can click day/week names to navigate views
      eventLimit: true, // allow "more" link when too many events
      events: {
         url: 'events.php',
        failure: function() {
          //document.getElementById('script-warning').style.display = 'block'
        }
      },
    });

    calendar.render();
  });

</script>
<style>

  body {
    margin: 40px 10px;
    padding: 0;
    font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    font-size: 14px;
  }

  #calendar {
    max-width: 900px;
    margin: 0 auto;
  }

</style>
</head>
<body>

  <div id='calendar'></div>

</body>
</html>
<?php
}
genesis();
?>