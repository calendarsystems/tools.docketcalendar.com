<?php 
/*
Template Name: Add Custom Contact
 */
 
remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');
function custom_loop() {
set_time_limit(0);
ini_set('memory_limit', '3000M');
ini_set('post_max_size', '2000M');		
ini_set('max_execution_time', 0);
ini_set('max_input_time', 1800);
ini_set('upload_max_filesize', '1000M');
	session_start();
	require_once('Connections/docketDataSubscribe.php');
    $docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
    global $calendarData;
    require('globals/global_tools.php');
    require('globals/global_courts.php');
    ?>
	<!-- JAVASCRIPT -->
	<!-- <script src="jquery/js/jquery-1.8.3.js"></script>  -->
<script src="https://code.jquery.com/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet"/>
 

<link rel="stylesheet" href="https://tools.docketcalendar.com/jquery/css/notify.css"> 
<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/notify.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.core.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.load-indicator.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.load-strategies.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.sort-strategies.js"></script>
	<script src="https://tools.docketcalendar.com/src/jsgrid.field.js"></script>
	<script src="https://tools.docketcalendar.com/src/fields/jsgrid.field.text.js"></script>
	<script type="text/javascript" src="https://tools.docketcalendar.com/jquery/js/dialogbox.js"></script>
	
	<!-- CSS-->
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/main.css">
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jsgrid.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/jqGrid.bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/theme.css" />
	<link rel="stylesheet" type="text/css" href="https://tools.docketcalendar.com/jquery/css/dialogbox.css">
    
<style type="text/css">
.jsgrid-grid-body {
  height:auto !important;
}
.button-white
{
	height:31px !important;
}
.button-default
{
	height:31px !important;
}
/* header row */
.jsgrid-header-row>.jsgrid-header-cell {
  background-color: #b7deed;      /* orange */
  font-family: "Roboto Slab";
  font-size: 1.2em;
  color: #1e5799;
  font-weight: normal;
}
.FntCls {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;}
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

label.switch-toggle {
    background: url('images/switch.png') repeat-y;
    display: block !important;
    height: 12px;
    padding-left: 26px;
    cursor: pointer;
    display: none;
}
label.switch-toggle.on {
    background-position: 0 12px;
}
label.switch-toggle.off {
    background-position: 0 0;
}
label.switch-toggle.hidden {
    display: none;
}
.required:before {
    color: red;
    content: '*';
}

</style>
<div class="overlay">
    <div id="loading-img"></div>
</div>
    <div style="width: 80%;">
        <div style="float: left;width: 70%;"><h2>Add Custom Contacts</h2></div>
        <div style="margin-left: 15%;float: right;"><a href="https://tools.docketcalendar.com/docket-cases">Docket Cases</a></div>
    </div>

	
    <div class="widget">
       <table class="table table-striped" width="100%" cellpadding="10" cellspacing="10">
			<?php
		$query_authInfo = "SELECT googlecontactprefrence FROM users_tool_option WHERE authenticator = '".$_SESSION['author_id']."' and user_id=".$_SESSION['userid']." ";
		$authInfo2 = mysqli_query($docketDataSubscribe,$query_authInfo);
		$totalRows_authInfo = mysqli_num_rows($authInfo);
		
		$googleContactlist=array();
		$row_authInfo = mysqli_fetch_assoc($authInfo2);
		if($row_authInfo['googlecontactprefrence'] == "Yes")
		{
		?>	
			<tr id="googleContactAdd">
			   <td style="width:25%">Click to Update Google Contacts</td>
				<td>
					<input type="button" id="getContacts" name="getContacts" value="Retrieve Contacts" onclick="getGmailContacts();">
				</td>
				
			</tr>
		<?php
		} 
		?>
            <tr>
               <td style="width:25%"><strong><label class="required">Name</label>:</strong></td>
				<td>
					<input type="text" id="userNameContact" name="userNameContact" style="width:410px;height: 35px;" value=""></td>
				</td>
            </tr>
			
			<tr>
               <td style="width:25%"><strong><label class="required">Email</label>:</strong></td>
               <td>
					<input type="text" id="userEmailContact" name="userEmailContact" style="width:410px;height: 35px;" value=""></td>
				</td>
            </tr>
			
			<tr>
               <td style="width:25%">Phone:</td>
               <td>
					<input type="text" id="userPhoneContact" name="userPhoneContact" style="width:410px;height: 35px;" value=""></td>
				</td>
				
            </tr>
			<tr>
               <td style="width:25%"></td>
               <td>
					<input type="button" id="updateAttendees" name="updateAttendees" value="Add" onclick="ajaxCallForContactUpdateStatus();">
				</td>
				
            </tr>
			
		</table>

    </div>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">Update Contact Information</h4>
      </div>
      <div class="modal-body">
	  <table width="100%" class="table table-striped" cellpadding="5" cellspacing="5">
		<tr>
			<td>
				<label>Name</label>
			</td>
			<td>
				<input type="text" id="contactNameId" name="contactNameId"/>
			</td>
		</tr>
		<tr>
			<td>
				<label>Email</label>
			</td>
			<td>
				<input type="text" id="contactEmailId" name="contactEmailId"/>
			</td>
		</tr>
		<tr>
			<td>
				<label>Phone</label>
			</td>
			<td>
				<input type="text" id="contactNumberId" name="contactNumberId"/>
			</td>
		</tr>
	   </table>
	  <input type="hidden" id="hiddenContactId" name="hiddenContactId"/>
      </div>
      <div class="modal-footer">
        <!-- 
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
		<button type="button" class="btn btn-default" data-dismiss="modal" onclick="updateCustomContacts();">Submit</button>
      </div>
    </div>
  </div>
</div>
	<?php
	$query_case_customContact = "SELECT id,userContactName,userContactEmail,userContactPhone FROM userContactUpdate  WHERE  userid = ".$_SESSION['userid']."";
	$customContact = mysqli_query($docketDataSubscribe,$query_case_customContact);
	?>
	<div class="FntCls" id="jsGrid" style="padding-bottom: 30px;"></div>
	    <script>
        (function() {

            var db = {
                loadData: function(filter) {
                    return $.grep(this.clients, function(client) {
                    return (!filter.Name || client.Name.indexOf(filter.Name) > -1)
						&& (!filter.Email || client.Email.indexOf(filter.Email) > -1)
						&& (!filter.Phone || client.Phone.indexOf(filter.Phone) > -1);
                    });
                    }
            };

            window.db = db;
            db.clients = [
            <?php 
                while ($row_events = mysqli_fetch_assoc($customContact)) {
			
                    ?>
                {
				"Action": "<button class='myButton' onclick='javascript:deleteCustomContacts(<?php echo $row_events['id']; ?>);'><strong>D</strong>elete</button><button class='myButton' onclick='javascript:viewCustomContacts(<?php echo $row_events['id']; ?>);'><strong>E</strong>dit</button>",
                "Name": "<?php echo $row_events['userContactName']; ?>",
				"Email": "<?php echo $row_events['userContactEmail']; ?>",
				"Phone": "<?php echo $row_events['userContactPhone']; ?>"
                },
            <?php } ?>
             ];

        }());
		$(function() {
			var originalFilterTemplate = jsGrid.fields.text.prototype.filterTemplate;
			jsGrid.fields.text.prototype.filterTemplate = function() {
					var grid = this._grid;
					var $result = originalFilterTemplate.call(this);
					$result.on("keyup", function(e) {
						  // TODO: add proper condition and optionally throttling to avoid too much requests  
						  grid.search();
					});
					return $result;
				}
		});
        $(function() {
            $("#jsGrid").jsGrid({
                height: "70%",
                width: "100%",
                sorting: true,
                paging: true,
                autoload: true,
				filtering: true,
				styleUI : "Bootstrap",
                pageSize: 10,
                pageButtonCount: 5,
                controller: db,
                fields: [
					{ name: "Action",  width: 140  },
                    { name: "Name", type: "text", width: 250 },
					{ name: "Email", type: "text", width: 250 },
                    { name: "Phone",  type: "text", width: 250 }
                ]
            });
        });
    </script>


<script type="text/javascript">
		function getGmailContacts()
		{
			jQuery(".overlay").show();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_importGoogleContacts.php",
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                       console.log(response);
                      jQuery(".overlay").hide();
                       $.notify("Google Contacts Updated", {
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
                            window.location.href = '<?php echo get_home_url(); ?>/add-custom-contacts';
                       }, 500);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
					   jQuery(".overlay").hide();
                       
                    }
                });
			
		}
		function ajaxCallForContactUpdateStatus()
		{
			var userNameContact =  jQuery("#userNameContact").val();
			var userEmailContact = jQuery("#userEmailContact").val();
			var userPhoneContact = jQuery("#userPhoneContact").val();
			var userID = '<?php echo $_SESSION['userid']; ?>';
			var autheticatorEmailID = '<?php echo $_SESSION['author_id']; ?>'; 
			reWhiteSpace = new RegExp(/^\s+$/);		
			if(userNameContact.length < 1)
			{
				$.notify("Please provide User Name", {
							  type:"danger",
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
							
				return false;
			}
			if (reWhiteSpace.test(userNameContact)) {
				 $.notify("Please provide User Name", {
							  type:"danger",
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
				  return false;
			}
			if(userEmailContact=="")
			{
				$.notify("Please provide Email", {
							  type:"danger",
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
							
				return false;
			}
			
			
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			if(!emailReg.test(userEmailContact)) {
				jQuery("#userEmailContact").focus();
				$.notify("Please provide proper Email Addres", {
							  type:"danger",
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
							
				return false;
			}
		
			jQuery(".overlay").show();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updateContactList.php",
                    type: "post",
					data: { "userName":userNameContact,"userEmail":userEmailContact,"userPhone":userPhoneContact,"userID":userID,"autheticatorEmailID":autheticatorEmailID},
                    success: function (response) {
                       console.log(response);
                      jQuery(".overlay").hide();
                       $.notify("Update Contact List", {
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
                            window.location.href = '<?php echo get_home_url(); ?>/add-custom-contacts';
                       }, 500);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       
                    }
                });
		}
		function viewCustomContacts(contactid)
		{
			
			jQuery(".overlay").show();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_viewCustomContactsinfo.php",
                    type: "post",
                    dataType: "json",
					data: { "id":contactid},
                    success: function (response) {
                      console.log(response);
                      jQuery(".overlay").hide();
					  jQuery('#myModal').modal('show');
					  jQuery("#contactNameId").val(response.userContactName);
					  jQuery("#contactEmailId").val(response.userContactEmail);
					  jQuery("#contactNumberId").val(response.userContactPhone);
					  jQuery("#hiddenContactId").val(contactid);
					  
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       
                    }
                });
		}
		function updateCustomContacts()
		{
			jQuery(".overlay").show();
			var contactNameId = jQuery("#contactNameId").val();
			var contactEmailId = jQuery("#contactEmailId").val();
			var contactNumberId = jQuery("#contactNumberId").val();
			var contactid = jQuery("#hiddenContactId").val();
			jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_updateCustomContactsinfo.php",
                    type: "post",
					data: { "id":contactid,"contactNameId":contactNameId,"contactEmailId":contactEmailId,"contactNumberId":contactNumberId},
                    success: function (response) {
                      
					   jQuery(".overlay").hide();
                       $.notify("Updated Contact List", {
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
							
							console.log(response);
							 setTimeout(function() {
                            window.location.href = 'http://gtools.docketcalendar.com/add-custom-contacts';
                       }, 500);
							
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                       
                    }
                });
		}
		
		function deleteCustomContacts(contactid)
		{
		    jQuery.dialogbox({
            type:'msg',
            title:'',
            content:'Are you sure you want to delete Contact Information?',
            closeBtn:true,
            btn:['Confirm','Cancel'],
            call:[
            function(){
                jQuery.ajax({
					url: "<?php echo get_home_url(); ?>/ajax/ajax_deleteCustomContactsinfo.php",
                    type: "post",
                    data: { "id":contactid },
                    success: function (response) {
                       console.log(response);
                        $.notify("Deleted Contact List", {
							  type:"danger",
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
							
							console.log(response);
							 setTimeout(function() {
                            window.location.href = 'http://gtools.docketcalendar.com/add-custom-contacts';
                       }, 500);
                   
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
</script>
<?php
}

genesis();
?>