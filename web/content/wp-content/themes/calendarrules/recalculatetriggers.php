<?php require_once('Connections/docketDataSubscribe.php');
require_once('googleCalender/settings.php');
/*
Template Name: Recalculatetrigger
*/
//custom hooks below here...
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
function custom_loop() {
  require('globals/global_tools.php');
  require('globals/global_courts.php');
  
	$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
	global $docketData;
	session_start();
	$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

if (!function_exists("GetSQLValueString"))
{
    function GetSQLValueString($docketData,$theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
    {
      if (PHP_VERSION < 6) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
      }

      $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketData,$theValue) : mysql_escape_string($theValue);

      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
        case "double":
          $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
          break;
        case "date":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
        case "defined":
          $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
          break;
      }
      return $theValue;
    }
}
if($_SESSION['userid'] == '')
		{
		echo "<script>window.location.href='https://" . $_SERVER["HTTP_HOST"] . "/docket-calculator';</script>";
		}
				 $importDocketId = $_REQUEST['importDocketId'];
				 $TriggerDate    = $_REQUEST['TriggerDate'];
				 $TriggerTime    = $_REQUEST['TriggerTime'];
			     $meridiem       = $_REQUEST['meridiem'];
				 $attendees 	 = $_REQUEST['hiddenattenddes'];
?>
<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="//code.jquery.com/jquery.min.js"></script>
<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
<!-- JS-->
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/fastselect.standalone.js"></script>
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
<div class="overlay">
    <div id="loading-img"></div>
</div>	
	<div id="output"></div>
		<table width="100%">
			<tr>
                <td colspan="2">
					<input type="button" onclick="resendtoprev(<?php echo $importDocketId; ?>)" value="Back"/><div class="divider"></div>
						<form id="triggerDatafrom" method="post" action="/recalculatetrigger">
									<input type="hidden" name="TriggerDate" id="TriggerDate" value="<?php if(isset($TriggerDate)) {echo $TriggerDate; }?>"/>
									<input type="hidden" name="TriggerTime" id="TriggerTime" value="<?php if(isset($TriggerTime)) {echo $TriggerTime; }?>"/>
									<input type="hidden" name="meridiem" id="meridiem" value="<?php if(isset($meridiem)) {echo $meridiem; }?>"/>
									<input type="hidden" name="importDocketId" value="<?php if(isset($importDocketId)) {echo $importDocketId; }?>"/>
									<input name="docket_search_id" type="hidden" id="docket_search_id" value="<?php echo $_SESSION['docket_search_id']; ?>">
									<span id="ajax_result"></span>
		</form>
				</td>
            </tr>
        </table>
	<div>
		<table>
			<tr>
				<td>
					
					<input type="button"  value="Update Calendar" class='importCalendar'/>
				</td>
			</tr>
		</table>
		
	</div>
	<div style="display:none"> 
<form id="exportToExcelForm" method="POST" action="<?php echo get_home_url(); ?>/ajax/docket_result_export.php">
<input type="hidden" id="hidden_cmbJurisdictions_val" name="hidden_cmbJurisdictions_val">
<input type="hidden" id="hidden_JurisdictionsText_val" name="hidden_JurisdictionsText_val">
<input type="hidden" id="hidden_cmbTriggers_val" name="hidden_cmbTriggers_val">
<input type="hidden" id="hidden_TriggersText_val" name="hidden_TriggersText_val">
<input type="hidden" id="hidden_selectServiceType_val" name="hidden_selectServiceType_val">
<input type="hidden" id="hidden_selectServiceText_val" name="hidden_selectServiceText_val">
<input type="hidden" id="hidden_txtTriggerDate_val" name="hidden_txtTriggerDate_val">
<input type="hidden" id="hidden_txtTime_val" name="hidden_txtTime_val">
<input type="hidden" id="hidden_cmbMatter_val" name="hidden_cmbMatter_val">
<input type="hidden" id="hidden_cmbMatterText_val" name="hidden_cmbMatterText_val">
<input type="hidden" id="hidden_isTimeRequired_val" name="hidden_isTimeRequired_val">
<input type="hidden" id="hidden_sort_date_val" name="hidden_sort_date_val">
<input type="hidden" id="hidden_isServed_val" name="hidden_isServed_val">
<input type="hidden" id="hidden_eventarray_val" name="hidden_eventarray_val">
<input type="hidden" id="hidden_eventSpecificTime" name="hidden_eventSpecificTime">
<!-- Set Excel,iCal,Outlook Export -->
<input type="hidden" id="hidden_excelData" name="hidden_excelData">
<input type="hidden" id="hidden_iCalData" name="hidden_iCalData">
<input type="hidden" id="hidden_outllookData" name="hidden_outllookData">
</form>
</div>
<?php
	 
	if($importDocketId)
    {
		$query_searchInfo = "SELECT * FROM import_docket_calculator WHERE import_docket_id = '".$importDocketId."' ";
		$searchInfo = mysqli_query($docketDataSubscribe,$query_searchInfo);
	    while($row_searchInfo = mysqli_fetch_assoc($searchInfo))
		{
			$cmbJurisdictions = $row_searchInfo['jurisdiction'];
			$cmbTriggers = $row_searchInfo['trigger_item'];
			$isTimeRequired = '';
			$selectServiceType = $row_searchInfo['service_type'];
			//$txtTime=$row_searchInfo['trigger_time'];
			//$txtTriggerDate=$row_searchInfo['trigger_date'];
			$txtTime=$TriggerTime;
			$txtTriggerDate=$TriggerDate;
			$cmbMatter=$row_searchInfo['case_id'];
			$sort_date=$row_searchInfo['sort_date'];
			$access_token = $row_searchInfo['access_token'];
			?>
			<script type="text/javascript">
			jQuery(".overlay").show();
	setTimeout(function() {
						   jQuery(".overlay").hide();
					   }, 2000);
         var cmbJurisdictions = '<?php echo $cmbJurisdictions; ?>';
         var cmbTriggers =  '<?php echo $cmbTriggers; ?>';
         var selectServiceType =  '<?php if($selectServiceType==''){$selectServiceType =0;} echo$selectServiceType;  ?>';
         var txtTriggerDate =  '<?php echo $txtTriggerDate; ?>';
         var txtTime =  '<?php echo $txtTime; ?>';
         var cmbMatter =  '<?php echo $cmbMatter; ?>';
         var isTimeRequired =  '<?php echo $isTimeRequired; ?>';
         var sort_date =  '<?php echo $sort_date; ?>';
         var isServed = '<?php if($selectServiceType == 0) {$isServed = '';} else { $isServed = 'Y';} echo $isServed; ?>';
		 var auth_token = '<?php echo $access_token; ?>';
		 var importdocketId = '<?php echo $importDocketId; ?>';
		<?php } ?>
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
 
 	   function checkAll(ele) {

     var checkboxes = document.getElementsByTagName("input");
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == "checkbox") {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
 function checkResult(val, parentElem)
 {
    var queryString = "input[data=\'"+val+"\']";
    var parent = document.querySelectorAll(queryString);
    parent.forEach(function(inputElem){
        inputElem.checked = parentElem.checked
    });
 }
 jQuery('.multipleSelect').fastselect();
 
		jQuery("#output").fadeIn();
		//jQuery("#output").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_recalculate_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":sort_date,"importdocketId":importdocketId},
                success: function (response) {
                   console.log(response);
				  jQuery("#output").hide(500);
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
		 function redirect()
		 {
			 window.location.href='<?php echo get_home_url(); ?>/import-calendar';
		 }
		 
		//<![CDATA[
		var theForm = document.forms['form2'];
		if (!theForm) {
			theForm = document.form1;
		}
		function __doPostBack(eventTarget, eventArgument) {
			if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
				theForm.__EVENTTARGET.value = eventTarget;
				theForm.__EVENTARGUMENT.value = eventArgument;
				theForm.submit();
			}
		}
		    function CheckEvents(){
        var checked=false;
        var elements = document.getElementsByName("events[]");
        for(var i=0; i < elements.length; i++){
            if(elements[i].checked) {
                checked = true;
            }
        }
        if (!checked) {
            alert("Please check at least one checkbox.");
            return false;
        }
        return true;
      }
	  
		function tree_view()
		{
		    jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Switching back to Normal View will check all un-checked events. Do you wish to proceed?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
               jQuery(".overlay").show();
			 var cmbJurisdictions = '<?php echo $cmbJurisdictions; ?>';
			 var cmbTriggers =  '<?php echo $cmbTriggers; ?>';
			 var selectServiceType =  '<?php if($selectServiceType==''){$selectServiceType =0;} echo$selectServiceType;  ?>';
			 var txtTriggerDate =  '<?php echo $txtTriggerDate; ?>';
			 var txtTime =  '<?php echo $txtTime; ?>';
			 var cmbMatter =  '<?php echo $cmbMatter; ?>';
			 var isTimeRequired =  '<?php echo $isTimeRequired; ?>';
			 var sort_date =  '<?php echo $sort_date; ?>';
			 var isServed = '<?php if($selectServiceType == 0) {$isServed = '';} else { $isServed = 'Y';} echo $isServed; ?>';
			 var auth_token = '<?php echo $access_token; ?>';
			 var importdocketId = '<?php echo $importDocketId; ?>';
			 
			 
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_tree_recalculate_result.php",
					type: "post",
					dataType: "json",
					data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":1,"importdocketId":importdocketId },
					success: function (response) {
					   //console.log(response);
					   jQuery(".overlay").hide();
						$.notify("Tree View", {
						  type:"info",
						  align:"center", 
						  verticalAlign:"middle",
						  animation:true,
						  animationType:"scale",
						  icon:"check",
						  delay:2500,
						  blur: 0.8,
						  close: true,
						  color: "#4B7EE0",
						  buttonAlign: "center",
						});
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
					   
						jQuery('ul.triggersnav').find('ul').hide();
					},
					error: function(jqXHR, textStatus, errorThrown) {
					   console.log(textStatus, errorThrown);
					   jQuery("#button_export").hide();
					}
				});
					jQuery.dialogbox.close();
				},
				function(){
					jQuery.dialogbox.close();
				}
				]
			});
	
		}
	function normal_view()
	{  
		 var normalViewMsg = confirm("Switching back to Normal View will check all un-checked events. Do you wish to proceed?");
		if(normalViewMsg==true)
		{
			jQuery("#triggerDatafrom").submit(); 
		}	
	 
		jQuery.dialogbox({
		type:'msg',
		title:'',
		content:'Deleting a case will delete all events associated with this case. Are you sure you want to delete the case and all events?',
		closeBtn:true,
		btn:['Confirm','Cancel'],
		call:[
			function(){
			   // jQuery("#ajax_result").show();
				//jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery(".overlay").show();	
		   
					$.notify("Normal View", {
									  type:"info",
									  align:"center", 
									  verticalAlign:"middle",
									  animation:true,
									  animationType:"scale",
									  icon:"check",
									  delay:2500,
									  blur: 0.8,
									  close: true,
									  color: "#4B7EE0",
									  buttonAlign: "center",
									});
					jQuery("#triggerDatafrom").submit(); 
					jQuery.dialogbox.close();
			},
			function(){
				jQuery.dialogbox.close();
			}
			]
		});
	}

	  jQuery('.multipleSelect').fastselect();
	  
	jQuery(".importCalendar").click(function(){	
			
			var EventsArray= [];
			jQuery("input:checkbox[class=evenetClass]:checked").each(function () {
				EventsArray.push(jQuery(this).val());
			});
			jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Do you want to update the Calendar ?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
				jQuery(".overlay").show();
				 var docket_search_id =  jQuery("#docket_search_id").val();
				 var attendees 		  =  '<?php echo $attendees; ?>';
				 var importDocketId   =  <?php  echo $importDocketId; ?>;
				 var TriggerDate      =  '<?php echo $TriggerDate; ?>';
				 var TriggerTime      =  '<?php echo $TriggerTime; ?>';
				 var meridiem         =  '<?php echo $meridiem; ?>';
			
				 jQuery.ajax({
						url: "<?php echo get_home_url(); ?>/ajax/ajax_import_recalculated_events.php",
						type: "post",
						dataType: "json",
						data: { "docket_search_id":docket_search_id,"events":EventsArray,"importDocketId":importDocketId,"TriggerDate":TriggerDate,"TriggerTime":TriggerTime,"meridiem":meridiem,"attendees":attendees},
						success: function (response) {
						   console.log(response);
						   jQuery(".overlay").hide();
							 $.notify("Recalculated Events successfully", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"check",
							  delay:3500,
							  blur: 0.8,
							 close: true,
							  buttonAlign: "center",
							});
						  
						   setTimeout(function() {
								
								 window.location.href = "<?php echo get_home_url(); ?>/uservalidate?importDocketId="+importDocketId;
							}, 2500);
						},
						error: function(jqXHR, textStatus, errorThrown) {
						   console.log(textStatus, errorThrown);
						   if(errorThrown == 'Internal Server Error')
					 {
						/*
						 //This code is just a bugfix for 500 error
							jQuery("#button_export").hide();
						    //This code is just a bugfix for 500 error
							jQuery("#ajax_result").show();
							var resVal = "<span style='color:green'>";
							var ResponseText= '<?php echo "Successfully imported to your Google Calendar."; ?>';
							var ResponseVal = resVal+ResponseText+'</span>';
							jQuery("#ajax_result").html(ResponseVal);
							jQuery('#ajax_result').hide(5000);
							setTimeout(function() {
								jQuery('#ajax_result').hide(5000);
								 window.location.href = "<?php echo get_home_url(); ?>/uservalidate?importDocketId="+importDocketId;
							}, 5000);
							*/
							  jQuery(".overlay").hide();
							$.notify("Recalculated Events successfully", {
							  type:"success",
							  align:"center", 
							  verticalAlign:"middle",
							  animation:true,
							  animationType:"scale",
							  icon:"check",
							  delay:3500,
							  blur: 0.8,
							 close: true,
							  buttonAlign: "center",
							});
							 window.location.href = "<?php echo get_home_url(); ?>/uservalidate?importDocketId="+importDocketId;
						  
					 }
						  
						}
				 });
                jQuery.dialogbox.close();
            },
            function(){
                jQuery.dialogbox.close();
            }
            ]
        });
		  
	  });
	  
	
	  
	  function resendtoprev(importDocketId)
	  {
		  jQuery(".overlay").show();
		   window.location.href = "<?php echo get_home_url(); ?>/update-case-triggers?importdocketid="+importDocketId+"&flag=updateFlag";
	  }
	
		function hiddenSetValues()
		{
		
					var array = [];
					var uncheckedarray = [];
					
					var checkboxes = document.querySelectorAll('input[type=checkbox]:checked');

					for (var i = 0; i < checkboxes.length; i++) {
						array.push(checkboxes[i].value)
					} 
				 var cmbJurisdictions 		=  jQuery("#cmbJurisdictions").val();
				 var JurisdictionsText      = jQuery('#cmbJurisdictions :selected').text();
				 var cmbTriggers 			=  jQuery("#cmbTriggers").val();
				 var TriggersText           = jQuery('#cmbTriggers :selected').text();
				 var selectServiceType 		=  jQuery("#cmbServiceTypes").val();
				 var selectServiceText      = jQuery('#cmbServiceTypes :selected').text();
				 var txtTriggerDate 		=  jQuery("#datepicker").val();
				 var txtTime 				=  jQuery("#txtTime").val();
				 var cmbMatter 				=  jQuery("#cmbMatter").val();
				 var cmbMatterText          = jQuery('#cmbMatter :selected').text();
				 var isTimeRequired 		=  jQuery("#isTimeRequired").val();
				 var sort_date 				=  jQuery("#sort_date").val();
				 var isServed 				=  jQuery("#isServed").val();

				
				var cmbJurisdictions = '<?php echo $cmbJurisdictions; ?>';
				var cmbTriggers =  '<?php echo $cmbTriggers; ?>';
				var selectServiceType =  '<?php if($selectServiceType==''){$selectServiceType =0;} echo$selectServiceType;  ?>';
				var txtTriggerDate =  '<?php echo $txtTriggerDate; ?>';
				var txtTime =  '<?php echo $txtTime; ?>';
				var cmbMatter =  '<?php echo $cmbMatter; ?>';
				var isTimeRequired =  '<?php echo $isTimeRequired; ?>';
				var sort_date =  '<?php echo $sort_date; ?>';
				var isServed = '<?php if($selectServiceType == 0) {$isServed = '';} else { $isServed = 'Y';} echo $isServed; ?>';
				var auth_token = '<?php echo $access_token; ?>';
				var importdocketId = '<?php echo $importDocketId; ?>';
				
				 jQuery("#hidden_cmbJurisdictions_val").val(cmbJurisdictions);
				 jQuery("#hidden_cmbTriggers_val").val(cmbTriggers);
				 jQuery("#hidden_selectServiceType_val").val(selectServiceType);
				 jQuery("#hidden_txtTriggerDate_val").val(txtTriggerDate);
				 
				 jQuery("#hidden_cmbMatter_val").val(cmbMatter);
				 jQuery("#hidden_isTimeRequired_val").val(isTimeRequired);
				 jQuery("#hidden_sort_date_val").val(sort_date);
				 jQuery("#hidden_isServed_val").val(isServed);
				 if(txtTime != '') {
					 txtTime_arr = txtTime.split(':');
					 txtTime_arr_2 = txtTime_arr[1].split(' ');
					 txtTime = txtTime_arr[0]+':'+txtTime_arr_2[0]+':00';
				 }
				 jQuery("#hidden_txtTime_val").val(txtTime);
				 
				 jQuery("#hidden_JurisdictionsText_val").val('<?php echo $_POST['JurisdictionsValText']; ?>');
				 jQuery("#hidden_TriggersText_val").val('<?php echo $_POST['TriggersText']; ?>');
				 jQuery("#hidden_selectServiceText_val").val('<?php echo $_POST['hidden_selectServiceText_val']; ?>');
				 jQuery("#hidden_cmbMatterText_val").val('<?php echo $_POST['hidden_cmbMatterText_val']; ?>');
				 jQuery("#hidden_eventarray_val").val(array);
		}		
		function Exlexport()
		   {
			  				
				$.notify("Data Export to Excel", {
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
				var setDataForExcelExport = "Excel";
				jQuery("#hidden_excelData").val(setDataForExcelExport);
				jQuery("#hidden_iCalData").val("");
				jQuery("#hidden_outllookData").val("");
				hiddenSetValues();   
				jQuery("#exportToExcelForm").submit();
				 
		   }
		   
		function Icalexport()
			{
				$.notify("Data Export to ICAL", {
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
				var setDataForIcalExport = "iCal";
				jQuery("#hidden_iCalData").val(setDataForIcalExport);
				jQuery("#hidden_excelData").val("");
				jQuery("#hidden_outllookData").val("");
				hiddenSetValues();   
				jQuery("#exportToExcelForm").submit();
			}
		function Outlookexport()
			{
				
				$.notify("Data Export to OUTLOOK", {
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
				var setDataForOutlookExport = "outlook";
				jQuery("#hidden_outllookData").val(setDataForOutlookExport);
				jQuery("#hidden_iCalData").val("");
				jQuery("#hidden_excelData").val("");
				hiddenSetValues();   
				jQuery("#exportToExcelForm").submit();
			   
			}	
			</script>
			
			<?php
	}
}
 genesis(); // <- everything important: make sure to include this.

 ?>
