<?php 
/**
   * @package paytm-gratification_sample
   * @author Yeshu Agarwal
   * @copyright 2016
   */
   
	include "lib/config_paytm.php";
	include "lib/encdec_paytm.php";
	

	// Transfer Money
	$mobile = '7777777777';
	$merchantOrderId = rand(20,80);
	$amount = 1;
	$details = transfer_money($mobile,$merchantOrderId,$amount);
	print_r($details);
	
	//Check Transaction Status
	$status = check_transaction_status("226893");
	print_r($status);
	
	?>