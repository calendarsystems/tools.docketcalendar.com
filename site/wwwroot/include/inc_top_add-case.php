<?php 
require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
require_once('Connections/docketDataSubscribe.php');
global $docketDataSubscribe;
session_start();

//ini_set('display_errors',1);
//error_reporting(E_ALL);

    if (!isset($_SESSION['userid'])) {
      echo "<script>alert('Your browser session has expired, please login into Site.');window.location.href='/login';</script>";
    }

    if(!isset($_SESSION['access_token']))
    {
		if($_SESSION['CheckAccess']!="NoGmail")
		{
			echo "<script>window.location.href='/docket-calculator';</script>";
		}
      
    }

    $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
    //echo "s=".$_SESSION['access_token'];
    if(isset($_SESSION['access_token'])) {

        try {
            global $calendarData;
            $capi = new GoogleCalendarApi();

            // Get the access token
            $calendarData = $capi->GetCalendarsList($_SESSION['access_token']);
        }
        catch(Exception $e) {
            unset($_SESSION['access_token']);
			if($_SESSION['CheckAccess']!="NoGmail")
			{
				echo "<script>alert('Your browser session has expired, please login into Google.');window.location.href='".$login_url."';</script>";
			}
            
        }
    }
?>
<script src="jquery/js/jquery-1.8.3.js"></script>
<script language="javascript">
jQuery(document).ready(function()
{
		jQuery("#case_matter").focusout(function()
		{
		if (jQuery("#case_matter").val())
		{
			jQuery("#messageSpan").text('Checking...').fadeIn("slow");
			jQuery.post("/api/api_checkCaseExists.php",{ case_name:jQuery("#case_matter").val(),createdby:'<?php echo $_SESSION['author_id'];?>' } ,function(data)
			{
				
				if(data=='no') //if case name not avaiable
				{
					jQuery("#messageSpan").fadeTo(200,0.1,function() //start fading the messagebox
					{ 
					  //add message and change the class of the box and start fading
					  jQuery(this).html('Case already exists').fadeTo(900,1);
					  //jQuery("#case_matter").css({"background-color":"#FF9F9F"});
					  jQuery("#case_matter").attr("value", "");
					}); 
									
				}
				else
				{
						jQuery("#messageSpan").fadeTo(200,0.1,function()  //start fading the messagebox
						{ 
						  //add message and change the class of the box and start fading
							//jQuery("#case_matter").css({"background-color":"#B8F5B1"});
							jQuery(this).html('Case name available').fadeTo(900	,1);    
						});
						jQuery("#AddCaseid").submit();
						
				}
		
			});
		}
    });
	
	if (jQuery("#caseLeveleventColor").val())
		{
		
			var colorValueId =  jQuery("#caseLeveleventColor").val();
			if(colorValueId != '0')
			{
				var PresentcolorValue = getColorCode(colorValueId);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
		}
		jQuery('#caseLeveleventColor').on('change', function() {
			var colorValue =  this.value ;
			if(colorValue != '0')
			{
				var PresentcolorValue = getColorCode(colorValue);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
			else
			{
				jQuery('#colorIdentifier').css('background-color','#ffffff');
			};
		});
}); function getColorCode(ID)
		{
			var colorCode="";
			switch (ID) {
				case '1':
					colorCode = "#7986cb";
					break;
				case '2':
					colorCode = "#33b679";
					break;
				case '3':
					colorCode = "#8e24aa";
					break;
				case '4':
					colorCode = "#e67c73";
					break;
				case '5':
					colorCode = "#f6bf26";
					break;
				case '6':
					colorCode = "#F4511E";
					break;
				case '7':
					colorCode = "#039be5";
					break;
				case '8':
					colorCode = "#616161";
					break;
				case '9':
					colorCode = "#3f51b5";
					break;
				case '10':
					colorCode = "#0b8043";
					break;	
                case '11':
					colorCode = "#D50000";
					break;
				default:
					  return "default";
					break;					
			}
			return colorCode;
			
		}
</script>

