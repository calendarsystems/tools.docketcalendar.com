<?php require_once('Connections/docketDataSubscribe.php');
		require_once('googleCalender/settings.php');
		/*
		Template Name: Excludeevents
		*/
		//custom hooks below here...
		remove_action('genesis_loop', 'genesis_do_loop');
		add_action('genesis_loop', 'custom_loop');
		session_start();
		function custom_loop() {
		$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
		require('globals/global_tools.php');
		require('globals/global_courts.php');

		if(isset($_SESSION['userid']) && $_SESSION['userid'] != '')
		{
		$query_importEvents = "SELECT * from docket_cases as dc
		INNER JOIN docket_cases_users as dcu ON dcu.case_id = dc.case_id
		WHERE dcu.user = '".$_SESSION['author_id']."' AND dc.user_id = '".$_SESSION['userid']."' GROUP BY dc.case_id ORDER BY dc.case_id ";
		$ImportEvents = mysqli_query($docketDataSubscribe,$query_importEvents);
		$totalRows_importEvents = mysqli_num_rows($ImportEvents);

		}
		?>
		<!-- JAVASCRIPT -->
		<script src="//code.jquery.com/jquery.min.js"></script>
		<script src="https://tools.docketcalendar.com/jquery/js/select2.min.js"></script>
		<link href="https://tools.docketcalendar.com/jquery/css/select2.min.css" rel="stylesheet" />
		<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
		<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
	
		<script src="https://tools.docketcalendar.com/jquery/js/jquery.modal.js" type="text/javascript"></script>
		<script src="https://tools.docketcalendar.com/jquery/js/jquery.datetimepicker.full.min.js"></script>
		<!-- CSS -->
		<link href="https://tools.docketcalendar.com/jquery/css/jquery.modal.css" rel="stylesheet" type="text/css">
		<link href="https://tools.docketcalendar.com/jquery/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
		.modal {
			display: none;
		}
		.modal a.close-modal[class*="icon-"] {
		top: -10px;
		right: -10px;
		width: 20px;
		height: 20px;
		color: #fff;
		line-height: 1.25;
		text-align: center;
		text-decoration: none;
		text-indent: 0;
		background: #900;
		border: 2px solid #fff;
		-webkit-border-radius: 26px;
		-moz-border-radius: 26px;
		-o-border-radius: 26px;
		-ms-border-radius: 26px;
		-moz-box-shadow:    1px 1px 5px rgba(0,0,0,0.5);
		-webkit-box-shadow: 1px 1px 5px rgba(0,0,0,0.5);
		box-shadow:         1px 1px 5px rgba(0,0,0,0.5);
		}

		#divform1 { font-family: "Trebuchet MS", Arial, Helvetica, sans-serif !important;}
		.xdsoft_datetimepicker .xdsoft_timepicker {
		 width:70px !important;
		}
		ul{
			
		}

		/* The wider this li is, the fewer columns there will be */
			ul.multiple_columns li{
				text-align: left;
				float: left;
				margin-left:25px;
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
		</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
		<fieldset class="grpBox">
		<table style="width: 100%;clear:both;">
			<tr>
				<td style="float:left"><h3>Exclude events</h3></td>
				<td style="float:right"><a href="https://tools.docketcalendar.com/viewexcludeevents">View Exclude events</a>&nbsp;|&nbsp;<a href='#' onclick="window.history.go(-1); return false;">Back</a></td>
			</tr>
		</table>
		<table>
				<tr>
					<td>
						<p>Jurisdiction:</p>
					</td>
					<td>
						<p>
							<span id="ajax_jurisdiction"><select name="cmbJurisdictions" id="cmbJurisdictions" class="chosen">
							<option value="0">---Select Court---</option></select>
							</span>
						</p>
					</td>
				</tr>	
				<tr>
					<td>
						<p>Trigger Item:</p>
					</td>
					<td>
						<p><span id="ajax_jurisdiction_trigger">Must select Jurisdiction first.</span></p>
							<div id="ex3" class="modal">
							</div>
							<input type="hidden" id="hidden_trigger_item" name="hidden_trigger_item">
					</td>
				</tr>	
		</table>	
		<table style="margin-bottom:30px;">
			<tr>
				<td>
					<input id="btnCalculate" name="btnCalculate" type="button"  value="Preview Events">		
				</td>
			</tr>
		</table>
		<table width="100%">
			<tr>
				<td colspan="2">
					<div class="divider"></div>
					<span id="ajax_result"></span>
				</td>
			</tr>		
		</table>
		<table>
			<tr>
				<td>
					<input type="radio" name="applyjuri"  id="applyjuri" value="1"> Apply To Selected Jurisdiction<br>
					<input type="radio" name="applyjuri" id="applyjuri" value="2"> Apply To All Jurisdiction<br>
					<input type="hidden" name="txtTriggerDate" id="datepicker" class="datepicker" value="<?php if (isset($_POST['txtTriggerDate'])) { echo $_POST['txtTriggerDate']; } else { echo date("m/d/Y"); } ?>" /> 
					<input type="hidden" name="txtTime" id="txtTime" value="<?php echo date("H:i:s",strtotime(@$_POST['txtTime'])); ?>" style="width:60%" />
				</td>
			</tr>
		</table>

		<table>
			<tr>		
				<td>
					 <input id="btnImport" name="btnImport" type="submit" value="SAVE" onclick="javascript:saveExcludeEvents();"  />	 
				</td>
			</tr>
		</table>
		
		<div id="exclude_result"></div>

		<script type="text/javascript">
		var jq = jQuery.noConflict();
		  jQuery("#ajax_jurisdiction").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");

		  <?php if(@$_GET['cmbJurisdictions'] != '') { ?>
			  jQuery.ajax({
				url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
				type: "post",
				data: { "page":"docket_calculator","cmbJurisdictions":<?php echo $_GET['cmbJurisdictions'];?> },
				success: function (response) {
				   jQuery("#ajax_jurisdiction").html(response);
				},
				error: function(jqXHR, textStatus, errorThrown) {
				   console.log(textStatus, errorThrown);
				}
			  });
		  <?php } else { ?>
			 jQuery.ajax({
				url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction.php",
				type: "post",
				data: { "page":"docket_calculator" },
				success: function (response) {
				   jQuery("#ajax_jurisdiction").html(response);
				},
				error: function(jqXHR, textStatus, errorThrown) {
				   console.log(textStatus, errorThrown);
				}
			  });
		  <?php } ?>

		  function get_jurisdictions_trigger(val)
		  {

			 jQuery("#button_calc").hide();
			 jQuery("#button_export").hide();
			 jQuery("#ajax_result").hide();
			 jQuery("#exclude_result").hide();
			 var cmbJurisdictions = jQuery("#cmbJurisdictions").find("option:selected").text();

			   jQuery("#ajax_jurisdiction_trigger").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			   jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_jurisdiction_trigger.php",
					type: "post",
					data: { "cmbJurisdictions":val },
					success: function (response) {
					   if(response.trim() != '')
					   {
						 jQuery("#ajax_jurisdiction_trigger").html(response);
						 <?php if( isset($_GET['cmbTriggers']) && isset($_GET['cmbJurisdictions'])) { ?> jQuery("#cmbTriggers").val(<?php echo $_GET['cmbTriggers'];?>); get_trigger_service(<?php echo $_GET['cmbTriggers'];?>,<?php echo $_GET['cmbJurisdictions'];?>); <?php } ?>
					   } else {
						 jQuery("#ajax_jurisdiction_trigger").html("Must select Jurisdiction first.");
					   }
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					}
			   });


			   jQuery("#ex3").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			   jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_get_all_triggers.php",
					type: "post",
					data: { "cmbJurisdictions":val,"cmbJurisdictions_name":cmbJurisdictions },
					success: function (response) {
					   if(response.trim() != '')
					   {
						 jQuery("#ex3").html(response);
					   } else {
						 jQuery("#ex3").html("");
					   }
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					}
			   });

		  }

		  function get_trigger_service(val,val_parent)
		  {

			 jQuery("#button_calc").hide();
			 jQuery("#button_export").hide();
			 jQuery("#ajax_result").hide();
			 jQuery("#exclude_result").hide();

			 var triggerItem = jQuery.trim(jQuery('#cmbTriggers').find('option:selected').text());
			 jQuery("#hidden_trigger_item").val(triggerItem);
			 jQuery("#ajax_trigger_service").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			   jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_trigger_service.php",
					type: "post",
					data: { "cmbJurisdictions":val_parent,"cmbTriggers":val },
					success: function (response) {
					   jQuery("#ajax_trigger_service").html(response);
					   jQuery("#button_calc").show();
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					}
			   });

		  }

			
		  jQuery("#btnCalculate").click(function(){
	
			 <?php if($_SESSION['access_token'] == '') { ?>
				   alert("Session has expired in your browser, Please once again login.");
				   window.location.href='<?php echo $login_url;?>';
			 <?php } ?>
			 var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
			 var cmbTriggers =  jQuery("#cmbTriggers").val();
			
			if(cmbJurisdictions==0)
			{
					jq.notify("Please select Jurisdiction", {
						  type:"danger",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"bell",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				return false;
			}
			if(cmbTriggers==0)
			{
					jq.notify("Please select Trigger", {
						  type:"danger",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"bell",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				return false;
			}
			 jQuery("#ajax_result").show();
			 jQuery("#ajax_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			 jQuery("#exclude_result").show();
			 jQuery("#exclude_result").html("<img src='https://tools.docketcalendar.com/assets/images/ajax-loader.gif' style='height:30px;'>");
			 jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_exclude_event_result.php",
					type: "post",
					dataType: "json",
					data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers },
					success: function (response) {
					   //console.log(response);
					   setTimeout(function(){ 
						jQuery("#ajax_result").show();
						jQuery("#ajax_result").html(response.html);
						//jQuery("#excludeSpan").hide();
					   }, 500);
					   
					   jQuery("#show_jurisdiction_print").html(jQuery("#cmbJurisdictions option:selected").text());
					   jQuery("#show_trigger_print").html(jQuery("#cmbTriggers option:selected").text());
					   if(jQuery("#cmbServiceTypes option:selected").text() != '')
					   {
						 jQuery("#show_service_print").html("Service Type: <b>"+jQuery("#cmbServiceTypes option:selected").text()+"</b>");
					   }
					   if(response.count > 0) {
						   jQuery("#button_export").show();
						   if(sort_date == 1)
						   {
							   jQuery("#asc_link").hide();
							   jQuery("#desc_link").show();
						   } else if(sort_date == 2)
						   {
							   jQuery("#asc_link").show();
							   jQuery("#desc_link").hide();
						   }
					   } else {
						   jQuery("#button_export").hide();
					   }
						jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
						jQuery(this).parent().children('ul').slideToggle(250);
						jQuery(this).parent().children('a').toggleClass("arrowdown");
						})
						jQuery('ul.triggersnav').find('ul').hide();
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   jQuery("#button_export").hide();
					}
			 });
			 
			 /* AJAX call for display events excluded */
			  jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_show_exclude_event.php",
					type: "post",
					dataType: "json",
					data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers },
					success: function (response) {
					   console.log(response);
					   jQuery("#exclude_result").html(response.html);
					   jQuery("#exclude_result").show();
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					}
			 });

		  });

		function getServiceType()
		{
		 var ServiceTypes = jQuery.trim(jQuery('#cmbServiceTypes').find('option:selected').text());
		 jQuery("#hidden_service_type").val(ServiceTypes);
		}
		

		function tree_view()
		{
			//jQuery("#ajax_result").show();
			//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			jQuery(".overlay").show();
			 var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
			 var cmbTriggers =  jQuery("#cmbTriggers").val();
			jq.notify("Tree View", {
						  type:"info",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"alert",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});

			 jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_tree_docket_result.php",
					type: "post",
					dataType: "json",
					data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers},
					success: function (response) {
					   //console.log(response);
					   jQuery(".overlay").hide();
					   jQuery("#ajax_result").show();
					   jQuery("#ajax_result").html(response.html);
					   jQuery("#show_jurisdiction_print").html(jQuery("#cmbJurisdictions option:selected").text());
					   jQuery("#show_trigger_print").html(jQuery("#cmbTriggers option:selected").text());
					   if(jQuery("#cmbServiceTypes option:selected").text() != '')
					   {
						 jQuery("#show_service_print").html("Service Type: <b>"+jQuery("#cmbServiceTypes option:selected").text()+"</b>");
					   }
					   if(response.count > 0) {
						   jQuery("#button_export").show();

						   jQuery("#asc_link").hide();
						   jQuery("#desc_link").show();

					   } else {
						   jQuery("#button_export").hide();
					   }
						jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
						jQuery(this).parent().children('ul').slideToggle(250);
						jQuery(this).parent().children('a').toggleClass("arrowdown");
						})
						var checkboxes = document.getElementsByTagName("input");
						for (var i = 0; i < checkboxes.length; i++) {
							if (checkboxes[i].type == "checkbox") {
								checkboxes[i].checked = false;
							}
						}
						jQuery('ul.triggersnav').find('ul').hide();
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   jQuery("#button_export").hide();
					}
			 });
		}

		function saveExcludeEvents()
		{
			$(".overlay").show();
			var array = [];
			var uncheckedarray = [];
			
			var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')

			for (var i = 0; i < checkboxes.length; i++) {
				array.push(checkboxes[i].value)
			}
			
			var uncheckedcheckboxes = jQuery("input:checkbox:not(:checked)");

			for (var i = 0; i < uncheckedcheckboxes.length; i++) {
				uncheckedarray.push(uncheckedcheckboxes[i].value)
			}
			if(array == null)
			{
				jq.notify("Please check an option for Events", {
						  type:"danger",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"bell",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				return false;
			}
			var cmbJurisdictions =  jQuery("#cmbJurisdictions").val();
			var cmbTriggers =  jQuery("#cmbTriggers").val();
			var applyjuri =  jQuery('input[name=applyjuri]:checked').val();
			if(typeof applyjuri == 'undefined')
			{
				$(".overlay").hide();
				jq.notify("Please check an option for Jurisdiction", {
						  type:"danger",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"bell",
						  delay:2500,
						  blur: 0.8,
						 close: true,
						  buttonAlign: "center",
						});
				//alert("Please check an option for Jurisdiction.");
				return false;
			}


			//jQuery("#ajax_result").show();
			//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
			 jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_save_exclude_events.php",
					type: "post",
					dataType: "json",
					data: { "cmbJurisdictions":cmbJurisdictions,"events":array,"cmbTriggers":cmbTriggers,"applyjuri":applyjuri,"uncheckevents":uncheckedarray },
					success: function (response) {
					   console.log(response);
					   $(".overlay").hide();
					    jq.notify("Event(s) updated as excluded", {
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
					   //jQuery("#ajax_result").html(response.html);
					   setTimeout(function() {
							jQuery('#ajax_result').hide(1000);
							 window.location.href = "<?php echo get_home_url(); ?>/excludevents";
						}, 1000);
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   jQuery("#button_export").hide();
					}
			 });
			
		}
		jQuery('#txtTime').datetimepicker({
				 datepicker:false,
				 format:'h:i a',
				 formatTime: 'h:i a',
				 step:30,
				 ampm: true,
				 value:'00:00',      
		});
		jQuery('#txtTime').click(function() {
		 jQuery('#txtTime').datetimepicker({
				 value:'08:00', 
				 datepicker:false,
				 format:'h:i a',
				 formatTime: 'h:i a',
				 step:30,
				 ampm: true,     
			});
		}); 


		</script>
<?php 
}genesis(); // <- everything important: make sure to include this.
?>
