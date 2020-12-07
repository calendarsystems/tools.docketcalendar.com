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
session_start();
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];
$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];

if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	   $docketData = $GLOBALS['docketData'];
	  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($docketData,$theValue) : mysqli_escape_string($docketData,$theValue);

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

?>
<!-- CSS-->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
<link rel="stylesheet" type="text/css" href="jquery/css/dialogbox.css">
<!-- JS-->
<script src="jquery/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="jquery/js/dialogbox.js"></script>
<script type="text/javascript" src="jquery/js/fastselect.standalone.js"></script>
			<div id="output"></div>
			<table width="100%">
			<tr>
                      <td colspan="2"><div class="divider"></div><span id="ajax_result"></span>
						</td>
            </tr>
            </table>
			<div>
			<table>
			<!--
			<form id="form2" method="POST" action="<?php// echo get_home_url(); ?>/ajax/docket_result_import.php" onsubmit="return CheckEvents();">
			<input name="userid" type="hidden" value="<?php //echo $_SESSION['userid']; ?>" />
			<input name="auth_token" type="hidden" value="<?php //echo @$_SESSION['access_token']; ?>" />
			<input type="hidden" value="" name="cmbJurisdictions">
			<input type="hidden" value="" name="cmbTriggers">
			<input type="hidden" value="" name="txtTriggerDate">
			<input type="hidden" value="" name="cmbServiceTypes">
			<input type="hidden" value="" name="isServed">
			<input type="hidden" value="" name="isTimeRequired">
			<input type="hidden" value="" name="cmbMatter">
			<input type="hidden" value="" name="location">
			<input type="hidden" value="" name="custom_text">
			<input type="hidden" value="" name="hidden_trigger_item">
			<input type="hidden" value="" name="hidden_service_type">
			-->
			<tr>
			<td>
				<input type="button" onclick="importCalendar();" value="Import to Calendar"/>
			</td>
			</tr>
			</table>
			</div>
<?php
	$importDocketId = $_REQUEST['importDocketId'];
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
			$txtTime=$row_searchInfo['trigger_time'];
			$txtTriggerDate=$row_searchInfo['trigger_date'];
			$cmbMatter=$row_searchInfo['case_id'];
			$sort_date=$row_searchInfo['sort_date'];
			$access_token = $row_searchInfo['access_token'];
			?>
			<script type="text/javascript">
			
         var cmbJurisdictions = '<?php echo $cmbJurisdictions; ?>';
         var cmbTriggers =  '<?php echo $cmbTriggers; ?>';
         var selectServiceType =  '<?php if($selectServiceType==''){$selectServiceType =0;} echo$selectServiceType;  ?>';
         var txtTriggerDate =  '<?php echo $txtTriggerDate; ?>';
         var txtTime =  '<?php echo $txtTime; ?>';
         var cmbMatter =  '<?php echo $cmbMatter; ?>';
         var isTimeRequired =  '<?php echo $isTimeRequired; ?>';
         var sort_date =  '<?php echo $sort_date; ?>';
         var isServed = '<?php if($selectServiceType == 0) {$isServed = '';} else { $isServed = 'Y';} echo $isServed; ?>';
		 var auth_token = '<?php echo $access_token ?>';
		<?php } ?>
		/*
	alert(cmbJurisdictions);
	alert(cmbTriggers);
	alert(isTimeRequired);
	alert(cmbMatter);
	alert(selectServiceType);
	alert(isServed);
	alert(txtTime);
	alert(txtTriggerDate);
	alert(sort_date);
 */
		jQuery("#output").show();
		jQuery("#output").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
         jQuery.ajax({
                url: "<?php echo get_home_url(); ?>/ajax/ajax_recalculate_result.php",
                type: "post",
                dataType: "json",
                data: { "cmbJurisdictions":cmbJurisdictions,"cmbTriggers":cmbTriggers,"isTimeRequired":isTimeRequired,"selectServiceType":selectServiceType,"isServed":isServed,"txtTime":txtTime,"txtTriggerDate":txtTriggerDate,"cmbMatter":cmbMatter,"sort":sort_date },
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
		 function redirect()
		 {
			 window.location.href='http://googledocket.com/import-calendar';
		 }
		 
				 //<![CDATA[
		var theForm = document.forms['form2'];
		if (!theForm) {
			theForm = document.form1;
		}
		//alert("here!")

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
	  
	  function importCalendar()
	  {
		  jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Do you want to update the Calendar ?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
                jQuery("#ajax_result").show();
                jQuery("#ajax_result").html("<img src='images/ajax-loader.gif' style='height:30px;'>");
				jQuery("#ajax_result").show();
                       setTimeout(function() {
                            window.location.href = 'http://googledocket.com/ajax/ajax_recalculate_result?importDocketId='+importDocketId;
                       }, 1000);
          
                jQuery.dialogbox.close();
            },
            function(){
                jQuery.dialogbox.close();
            }
            ]
        });
	  }
			</script>
			
			<?php
	}
}
 genesis(); // <- everything important: make sure to include this.

 ?>
