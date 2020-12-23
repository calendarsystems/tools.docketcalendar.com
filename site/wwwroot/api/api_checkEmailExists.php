<?php require_once('../Connections/docketData.php'); 
$docketData = $GLOBALS['docketData'];
$database_docketData = $GLOBALS['database_docketData'];


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

$colname_checkEmail = "-1";
if (isset($_POST['email'])) {
  $colname_checkEmail = $_POST['email'];
}
mysqli_select_db($docketData,$database_docketData);
$query_checkEmail = sprintf("SELECT * FROM users WHERE email = %s", GetSQLValueString($colname_checkEmail, "text"));
$checkEmail = mysqli_query($docketData,$query_checkEmail) or die(mysqli_error($docketData));
$row_checkEmail = mysqli_fetch_assoc($checkEmail);
$totalRows_checkEmail = mysqli_num_rows($checkEmail);
 if ($totalRows_checkEmail == 0) { 
echo "yes";

 }else{
echo "no"; 
 }
  
  mysqli_free_result($checkEmail);// Show if recordset empty ?>
