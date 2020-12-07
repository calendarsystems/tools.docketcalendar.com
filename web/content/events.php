<?php
require_once('Connections/docketDataSubscribe.php');
session_start();

$docketDataSubscribe = $GLOBALS['docketDataSubscribe'];
$sqlEvents = "SELECT id, title, start_date, end_date,status,eventColor,description FROM  events WHERE user_id = '".$_SESSION['userid']."' AND emailid='".$_SESSION['author_id']."' AND status !=2";

$resultset = mysqli_query($docketDataSubscribe, $sqlEvents) or die("database error:". mysqli_error($docketDataSubscribe));
$calendar = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {

	$calendar[] = array(
	'title'=>$rows['title'],
	'start'=>$rows['start_date'],
	'end'=>$rows['end_date'],  
	'desc'=>$rows['description'],
	'color'=> $color = CheckColor($rows['eventColor'])
	);

}
echo json_encode($calendar);

function CheckColor($color)
{
			$colorCode="";
			switch ($color) {
				case '1':
					$colorCode = "#7986cb";
					break;
				case '2':
					$colorCode = "#33b679";
					break;
				case '3':
					$colorCode = "#8e24aa";
					break;
				case '4':
					$colorCode = "#e67c73";
					break;
				case '5':
					$colorCode = "#f6bf26";
					break;
				case '6':
					$colorCode = "#F4511E";
					break;
				case '7':
					$colorCode = "#039be5";
					break;
				case '8':
					$colorCode = "#616161";
					break;
				case '9':
					$colorCode = "#3f51b5";
					break;
				case '10':
					$colorCode = "#0b8043";
					break;	
                case '11':
					$colorCode = "#D50000";
					break;
				default:
					  return "default";
					break;					
			}
			return $colorCode;
}
?>