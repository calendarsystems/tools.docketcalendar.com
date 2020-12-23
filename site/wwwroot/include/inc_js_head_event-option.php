<?php require_once('googleCalender/google-calendar-api.php');
require_once('googleCalender/settings.php');
require_once('Connections/docketDataSubscribe.php');
global $docketDataSubscribe;
session_start();
    if (!isset($_SESSION['userid'])) {
      echo "<script>alert('Your browser session has expired, please login into Site.');window.location.href='/login';</script>";
    }

    if(!isset($_SESSION['access_token']))
    {
      echo "<script>window.location.href='/docket-calculator';</script>";
    }

    $login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/contacts.readonly') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
    //echo "s=".$_SESSION['access_token'];

?>
<script src="https://code.jquery.com/jquery.min.js"></script>
<script language="javascript">
jQuery(document).ready(function()
{

	if (jQuery("#eventColor").val())
		{
			var colorValueId =  jQuery("#eventColor").val();
			if(colorValueId != '0')
			{	
				var PresentcolorValue = getColorCode(colorValueId);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
			
		}
		jQuery('#eventColor').on('change', function() {
			var colorValue =  this.value ;
			if(colorValue != '0')
			{
				var PresentcolorValue = getColorCode(colorValue);
				jQuery('#colorIdentifier').css('background-color', PresentcolorValue);
			}
			else
			{
				jQuery('#colorIdentifier').css('background-color','#ffffff');
			}
		});
		
		function getColorCode(ID)
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
});
</script>

