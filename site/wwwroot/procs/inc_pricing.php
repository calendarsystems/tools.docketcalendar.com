<?php
			$new_mo_cost = 0;
 			$ind_courts = $totalRows_cart;
			$attorney_count = $totalRows_attornys_cart;
			$additionalAttyCost = 0;
			$additionalCourtsCost = 0;
			
		if ($_SESSION['userid'] <> ''){
		
			// user costs
			if ($attorney_count > 0 && $attorney_count < 2){
				$additionalAttyCost = 15;
			}
			if ($attorney_count > 1 && $attorney_count <=10){
				$additionalAttyCost = 	$attorney_count * 15 ;
			}
			if ($attorney_count >= 11 && $attorney_count <= 20){
				$attorney_count = $attorney_count - 10;
				$additionalAttyCost = 	$attorney_count * 10 + 150;
			}
			if ($attorney_count > 20){
				$attorney_count = $attorney_count - 20;
				$additionalAttyCost = 	$attorney_count * 5 + 250;
			}
			
			// court costs
			if ($ind_courts > 0 && $ind_courts < 2){
				$additionalCourtsCost = 11;
			}
			if ($ind_courts > 1 &&  $ind_courts <=10){
				$additionalCourtsCost = 	$ind_courts * 11;
			}
			if ($ind_courts >= 11 && $ind_courts <= 20){
				$ind_courts = $ind_courts - 10;
				$additionalCourtsCost = 	$ind_courts * 9 + 110;
			}
			if ($ind_courts > 20 && $ind_courts <= 30){
				$ind_courts = $ind_courts - 20;
				$additionalCourtsCost = 	$ind_courts * 7 + 200;
			}
			if ($ind_courts > 30){
				$ind_courts = $ind_courts - 30;
				$additionalCourtsCost = 	$ind_courts * 5 + 270;
			}
	
			if ($attorney_count < 2 && $ind_courts > 1){
			$additionalAttyCost = 15;	
			}
			
		
		
		}else{			   
		// user costs
		if ($attorney_count < 2){
			$additionalAttyCost = 0;
		}
		if ($attorney_count > 1 && $attorney_count <=10){
			$additionalAttyCost = 	$attorney_count * 15 - 15;
		}
		if ($attorney_count >= 11 && $attorney_count <= 20){
			$attorney_count = $attorney_count - 10;
			$additionalAttyCost = 	$attorney_count * 10 + 150 - 15;
		}
		if ($attorney_count > 20){
			$attorney_count = $attorney_count - 20;
			$additionalAttyCost = 	$attorney_count * 5 + 250 - 15;
		}
		
		// court costs
		if ($ind_courts < 2){
			$additionalCourtsCost = 0;
		}
		if ($ind_courts > 1 &&  $ind_courts <=10){
			$additionalCourtsCost = 	$ind_courts * 11 - 11;
		}
		if ($ind_courts >= 11 && $ind_courts <= 20){
			$ind_courts = $ind_courts - 10;
			$additionalCourtsCost = 	$ind_courts * 9 + 110 -11;
		}
		if ($ind_courts > 20 && $ind_courts <= 30){
			$ind_courts = $ind_courts - 20;
			$additionalCourtsCost = 	$ind_courts * 7 + 200 - 11;
		}
		if ($ind_courts > 30){
			$ind_courts = $ind_courts - 30;
			$additionalCourtsCost = 	$ind_courts * 5 + 270 - 11;
		}

		if ($attorney_count < 2 && $ind_courts > 1){
		$additionalAttyCost = 15;	
		}
		// sub total
	}

	$new_mo_cost = $additionalAttyCost + $additionalCourtsCost + $statePrice;


		// check discounts
//		if ($totalRows_cart >= 5 && $totalRows_cart < 10 || $totalRows_attornys_cart >= 5 && $totalRows_attornys_cart < 10){
//			$discount = $mo_cost * .10;
//			$mo_cost = $mo_cost - $discount;
//		}
//		if ($totalRows_cart >= 10 || $totalRows_attornys_cart >= 10){
//			$discount = $mo_cost * .15;
//			$mo_cost = $mo_cost - $discount;
//		}
		
?>