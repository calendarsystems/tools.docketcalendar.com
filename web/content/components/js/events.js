(function($) {

	"use strict";
	
	var d = new Date();

	var month = d.getMonth()+1;
	var day = d.getDate();

	var output = d.getFullYear() + '-' +
		((''+month).length<2 ? '0' : '') + month + '-' +
		((''+day).length<2 ? '0' : '') + day;
	var options = {
		events_source: 'events.php',
		view: 'month',
		tmpl_path: 'tmpls/',
		tmpl_cache: false,
		day: output,
		onAfterEventsLoad: function(events) {
			if(!events) {
				return;
			}
			var list = $('#eventlist');
			list.html('');
			
			$.each(events, function(key, val) {
				$(document.createElement('li'))
					.html('<a href="' + val.url + '">' + val.title + '</a>')
					.appendTo(list);
			});
		},
		onAfterViewLoad: function(view) {
			$('.page-header h3').text(this.getTitle());
			$('.btn-group button').removeClass('active');
			$('button[data-calendar-view="' + view + '"]').addClass('active');
		},
		classes: {
			months: {
				general: 'label'
			}
		}
	};

	var calendar = $('#calendar').calendar(options);
		calendar.setOptions({modal: "#events-modal"});
		calendar.setOptions({weekbox: "false"});
		calendar.view();
		
	$('.btn-group button[data-calendar-nav]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.navigate($this.data('calendar-nav'));
		});
	});

	$('.btn-group button[data-calendar-view]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.view($this.data('calendar-view'));
		});
	});

	$('#first_day').change(function(){
		var value = $(this).val();
		value = value.length ? parseInt(value) : null;
		calendar.setOptions({first_day: value});
		calendar.view();
	});

	$('#language').change(function(){
		calendar.setLanguage($(this).val());
		calendar.view();
	});

	$('#events-in-modal').change(function(){
		
		var val = $(this).is(':checked') ? $(this).val() : null;
		//calendar.setOptions({modal: "#events-modal"});
	});
	$('#format-12-hours').change(function(){
		var val = $(this).is(':checked') ? true : false;
		calendar.setOptions({format12: val});
		calendar.view();
	});
	$('#show_wbn').change(function(){
		var val = $(this).is(':checked') ? true : false;
		calendar.setOptions({display_week_numbers: val});
		calendar.view();
	});
	$('#show_wb').change(function(){
		var val = $(this).is(':checked') ? true : false;
			
		calendar.setOptions({weekbox: val});
		calendar.view();
	});
	//$('#include_archive_events').change(function(){
	$('#include_archive_events').on('change',function(){
		var valNEW = $(this).is(':checked') ? "show" : "hide";	
		if(valNEW == "show")
		{
			
			$(".overlay").show();
			setTimeout(function() {
				$(".overlay").hide();
			}, 2000);	
			var options = {
			events_source: 'events.php?showArchiveValue='+valNEW,
			view: 'month',
			tmpl_path: 'tmpls/',
			tmpl_cache: false,
			day: output,
			onAfterEventsLoad: function(events) {
			if(!events) {
				return;
			}
					var list = $('#eventlist');
					list.html('');
					
					$.each(events, function(key, val) {
						$(document.createElement('li'))
							.html('<a href="' + val.url + '">' + val.title + '</a>')
							.appendTo(list);
					});
				},
				onAfterViewLoad: function(view) {
					$('.page-header h3').text(this.getTitle());
					$('.btn-group button').removeClass('active');
					$('button[data-calendar-view="' + view + '"]').addClass('active');
				},
				classes: {
					months: {
						general: 'label'
					}
				}
			};

			var calendar = $('#calendar').calendar(options);
			calendar.view();
		}else if(valNEW == "hide"){
			
			$("#calendar").load('https://www.google.calendarrules.com/calendar');
			$(".overlay").show();
			setTimeout(function() {
				
				$(".overlay").hide();
			}, 5000);	
		}
		
	});
	
	$('#events-modal .modal-header, #events-modal .modal-footer').click(function(e){
		
		//e.preventDefault();
		//e.stopPropagation();
	});
}(jQuery));