<?php require_once('Connections/docketData.php'); ?>
<?php
session_start();
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



mysqli_select_db($docketData,$database_docketData);
$query_cartState = sprintf("SELECT DISTINCT
cart.id,
cart.sessionid,
cart.systemid,
cart.courttype,
courts.courtSystem_Description,
court_pricing.Price
FROM
cart
Inner Join courts ON cart.systemid = courts.courtSystem_SystemID
Inner Join court_pricing ON courts.courtSystem_Description = court_pricing.State
WHERE cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype = 'state'", GetSQLValueString(session_id(), "text"));
$cartState = mysqli_query($docketData,$query_cartState, $docketData) or die(mysqli_error($docketData));
$row_cartState = mysqli_fetch_assoc($cartState);
$totalRows_cartState = mysqli_num_rows($cartState);

mysqli_select_db($docketData,$database_docketData);
$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.sessionid = '". session_id() ."' AND cart.subscribed =  '". $_SESSION['userid'] ."' AND cart.courttype <> 'state'");
$cart = mysqli_query($docketData,$query_cart, $docketData) or die(mysqli_error($docketData));
$cart2 = mysqli_query($docketData,$query_cart, $docketData) or die(mysqli_error($docketData));
$row_cart = mysqli_fetch_assoc($cart);
$row_cart2 = mysqli_fetch_assoc($cart2);
$totalRows_cart = mysqli_num_rows($cart);
$totalRows_cart2 = mysqli_num_rows($cart2);


$colname_attornys_cart = "-1";
if (isset($_SESSION['userid'])) {
  $colname_attornys_cart = $_SESSION['userid'];
}
mysqli_select_db($docketData,$database_docketData);
$query_attornys_cart = sprintf("SELECT * FROM attorneys WHERE user_id = %s and isActive =1 ORDER BY name ASC", GetSQLValueString($colname_attornys_cart, "int"));
$attornys_cart = mysqli_query($docketData,$query_attornys_cart, $docketData) or die(mysqli_error($docketData));
$row_attornys_cart = mysqli_fetch_assoc($attornys_cart);
$totalRows_attornys_cart = mysqli_num_rows($attornys_cart);


function checkCourt($systemID){
			$docketData = $GLOBALS['docketData'];
			$database_docketData = $GLOBALS['database_docketData'];
			mysqli_select_db($docketData,$database_docketData);
			$query_cart = sprintf("SELECT cart.id, cart.sessionid, cart.systemid, cart.courttype, courts.courtid, courts.code, courts.courtSystem_Description, courts.courtSystem_SystemID, courts.courtSystem_Code, courts.description, courts.price, courts.systemID, courts.type_Description, courts.type_SystemID FROM cart Inner Join courts ON cart.systemid = courts.systemID WHERE cart.courttype <> 'state' AND cart.systemid = '". $systemID ."' and cart.sessionid = %s", GetSQLValueString(session_id(), "text"));
			$cart = mysqli_query($docketData,$query_cart, $docketData) or die(mysqli_error($docketData));
			$row_cart = mysqli_fetch_assoc($cart);
    if ($row_cart['id'] != ''){
	return 'yes';
	}else{
	return 'no';
}
}

?>
<?php include('inc_top.php'); ?>
<table><tr><td></td></tr></table>
<h1>Order Completed</h1>
<div class="main">

<div class="wrapper">
  <div class="content">
      <div class="middle_blank"> 
      	<div>
      	  <table width="100%" border="0" cellpadding="18">
      	    <tr>
      	      <td align="center" valign="top">
              <div class="orderForm">
              <form action="process_payment.php" method="post">
              <p class="formTitles">Your Current Subscriptions</p>
              <table width="500" border="0" align="center" cellpadding="3">
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong>Users (<?php echo $totalRows_attornys_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	         <?php if ($totalRows_attornys_cart > 0) { // Show if recordset not empty ?>
					 <?php do { ?> 
                <tr>
                  <td>&nbsp;</td>
      	          <td><?php echo $row_attornys_cart['name']; ?></td>
      	          <td><a href="remove_attorney.php?id=<?php echo $row_attornys_cart['attorneyID']; ?>"></a></td>
    	          </tr>
      	        <tr>
                <?php } while ($row_attornys_cart = mysqli_fetch_assoc($attornys_cart)); ?><?php } // Show if recordset not empty ?>
      	            <tr>
      	              <td>&nbsp;</td>
      	              <td><strong><br />
      	              States  (<?php echo $totalRows_cartState; ?>)</strong></td>
      	          <td width="14">&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cartState > 0) { // Show if recordset not empty ?>
      	        <?php
						$statePrice = 0;
						 do { ?>
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><?php echo $row_cartState['courtSystem_Description']; ?>
                  <?php $stateIDs = $stateIDs .  $row_cartState['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cartState['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cartState = mysqli_fetch_assoc($cartState)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        <?php 
					   $state_courts = $totalRows_cartState;
					   if ($state_courts == 1){
							$state_court_cost = $statePrice;   
					   }elseif ($state_courts == 0){
						   $state_court_cost = 0;
					   }else{
							$state_court_cost = ($state_courts - 1) * 19.95 + $statePrice;   
					   }
					   ?>
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><strong><br />
      	            Courts (<?php echo $totalRows_cart; ?>)</strong></td>
      	          <td>&nbsp;</td>
    	          </tr>
      	        <?php if ($totalRows_cart > 0) { // Show if recordset not empty ?>
      	        <?php do { ?>
      	        <tr>
      	          <td>&nbsp;</td>
      	          <td><?php echo $row_cart['description']; ?>
                  <?php $courtIDs = $courtIDs . $row_cart['systemid']. ','; ?>
                  </td>
      	          <td><a href="remove_court.php?id=<?php echo $row_cart['id']; ?>&state=<?php echo $colname_Courts; ?>"></a></td>
   	            </tr>
      	        <?php } while ($row_cart = mysqli_fetch_assoc($cart)); ?>
      	        <?php } // Show if recordset not empty ?>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
      	          <td align="center">&nbsp;</td>
   	            </tr>

      	        <tr>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td align="right" class="cartTotal">&nbsp;</td>
      	          <td class="smallText">&nbsp;</td>
   	            </tr>
      	        <tr>
      	          <td align="center">&nbsp;</td>
      	          <td align="center"><a href="courts.php">Back to Courts</a></td>
      	          <td align="right">&nbsp;</td>
      	          </tr>
   	          </table>
              
              <p><br />
                <img src="assets/images/horizontal_line.png" width="800" height="1" /><br />
              </p>
              <table width="543" align="center" cellpadding="5">
                <tr>                </tr>
                <tr>                </tr>
                <tr>                </tr>
              </table>
              <table width="543" align="center" cellpadding="5">
                <tr>                  </tr>
                <tr>                  </tr>
                <tr>                  </tr>
                <tr>                  </tr>
                <tr>                  </tr>
                <tr>                  </tr>
                <tr>                  </tr>
              </table>
              <p>&nbsp;</p>
              </form>
              </div></td>
   	        </tr>
   	      </table>
      	<div style="clear:both;"></div>
      	</div>
      </div><!-- end middle -->
	</div> <!-- end content -->
</div> <!-- end wrapper -->
</div> <!-- end main -->
<?php include('inc_footer.php'); ?>
<?php
mysqli_free_result($cart);
?>
