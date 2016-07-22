<?php
/**
   * @package paytm-gratification_sample
   */
function encrypt_e($input, $ky) {
	$key = $ky;
	$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
	$input = pkcs5_pad_e($input, $size);
	$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
	$iv = "@@@@&&&&####$$$$";
	mcrypt_generic_init($td, $key, $iv);
	$data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	$data = base64_encode($data);
	return $data;
}

function decrypt_e($crypt, $ky) {

	$crypt = base64_decode($crypt);
	$key = $ky;
	$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
	$iv = "@@@@&&&&####$$$$";
	mcrypt_generic_init($td, $key, $iv);
	$decrypted_data = mdecrypt_generic($td, $crypt);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	$decrypted_data = pkcs5_unpad_e($decrypted_data);
	$decrypted_data = rtrim($decrypted_data);
	return $decrypted_data;
}

function pkcs5_pad_e($text, $blocksize) {
	$pad = $blocksize - (strlen($text) % $blocksize);
	return $text . str_repeat(chr($pad), $pad);
}

function pkcs5_unpad_e($text) {
	$pad = ord($text{strlen($text) - 1});
	if ($pad > strlen($text))
		return false;
	return substr($text, 0, -1 * $pad);
}

function generateSalt_e($length) {
	$random = "";
	srand((double) microtime() * 1000000);

	$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
	$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
	$data .= "0FGH45OP89";

	for ($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand() % (strlen($data))), 1);
	}

	return $random;
}

function checkString_e($value) {
	$myvalue = ltrim($value);
	$myvalue = rtrim($myvalue);
	if ($myvalue == 'null')
		$myvalue = '';
	return $myvalue;
}

function getChecksumFromArray($arrayList, $key, $sort=1) {
	if ($sort != 0) {
		ksort($arrayList);
	}
	$str = getArray2Str($arrayList);
	$salt = generateSalt_e(4);
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = encrypt_e($hashString, $key);
	return $checksum;
}
function getChecksumFromString($str, $key) {
	
	$salt = generateSalt_e(4);
	//$salt = '1234';
	$finalString = $str . "|" . $salt;
	$hash = hash("sha256", $finalString);
	$hashString = $hash . $salt;
	$checksum = encrypt_e($hashString, $key);
	return $checksum;
}

function verifychecksum_e($arrayList, $key, $checksumvalue) {
	$arrayList = removeCheckSumParam($arrayList);
	ksort($arrayList);
	$str = getArray2Str($arrayList);
	$paytm_hash = decrypt_e($checksumvalue, $key);
	$salt = substr($paytm_hash, -4);

	$finalString = $str . "|" . $salt;

	$website_hash = hash("sha256", $finalString);
	$website_hash .= $salt;

	$validFlag = "FALSE";
	if ($website_hash == $paytm_hash) {
		$validFlag = "TRUE";
	} else {
		$validFlag = "FALSE";
	}
	return $validFlag;
}

function verifychecksumFromstr_e($str, $key, $checksumvalue) {
	/*$arrayList = removeCheckSumParam($arrayList);
	ksort($arrayList);
	$str = getArray2Str($arrayList);*/
	$paytm_hash = decrypt_e($checksumvalue, $key);
	$salt = substr($paytm_hash, -4);

	$finalString = $str . "|" . $salt;

	$website_hash = hash("sha256", $finalString);
	$website_hash .= $salt;

	$validFlag = "FALSE";
	if ($website_hash == $paytm_hash) {
		$validFlag = "TRUE";
	} else {
		$validFlag = "FALSE";
	}
	return $validFlag;
}

function getArray2Str($arrayList) {
	$paramStr = "";
	$flag = 1;
	foreach ($arrayList as $key => $value) {
		if ($flag) {
			$paramStr .= checkString_e($value);
			$flag = 0;
		} else {
			$paramStr .= "|" . checkString_e($value);
		}
	}
	return $paramStr;
}

function redirect2PG($paramList, $key) {
	$hashString = getchecksumFromArray($paramList);
	$checksum = encrypt_e($hashString, $key);
}

function removeCheckSumParam($arrayList) {
	if (isset($arrayList["CHECKSUMHASH"])) {
		unset($arrayList["CHECKSUMHASH"]);
	}
	return $arrayList;
}

function callAPI($apiURL, $paramList) {
	$data_string = json_encode($paramList);
	//Here checksum string will return by getChecksumFromArray() function.
	$checkSum = getChecksumFromString($data_string,PAYTM_Merchant_key);
	$ch = curl_init();
	// initiate curl
	$headers = array('Content-Type:application/json','mid:'.PAYTM_Merchant_Guid,'checksumhash:'.$checkSum);
	$ch = curl_init();
	// initiate curl
	curl_setopt($ch, CURLOPT_URL,$apiURL);
	curl_setopt($ch, CURLOPT_POST, 1);
	// tell curl you want to post something
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
	// define what you want to post
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// return the output in string format
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$output = curl_exec ($ch);
	// execute
	$info = curl_getinfo($ch);
    $responseParamList = json_decode($output,true);
	return $responseParamList;
}

function check_transaction_status($txnId){
		$paramList = array();
		$paramList['request'] = array( 
		'requestType' =>'walletTxnId',
		'txnType' => "null",
		'txnId'=> $txnId);
		$paramList['ipAddress'] = $_SERVER['REMOTE_ADDR'];
		$paramList['operationType'] = 'CHECK_TXN_STATUS';
		$paramList['platformName'] = 'PayTM';
		$result = callAPI(PAYTM_STATUS_QUERY_URL, $paramList);
		return $result;
	}
	
function transfer_money($mobile,	$merchantOrderId,$amount){
		$paramList = array();
		$paramList['request'] = array( 
		'requestType' =>'null',
		'merchantGuid' => PAYTM_Merchant_Guid,
		'merchantOrderId' => $merchantOrderId,     
		'salesWalletGuid'=>PAYTM_Sales_Guid,    
		'payeePhoneNumber'=>$mobile,
		'payeeSsoId'=>'',
		'appliedToNewUsers'=>'N',
		'amount'=>$amount,
		'currencyCode'=>'INR');
		$paramList['metadata'] = 'Mcash App';
		$paramList['ipAddress'] = $_SERVER['REMOTE_ADDR'];
		$paramList['operationType'] = 'SALES_TO_USER_CREDIT';
		$paramList['platformName'] = 'PayTM';
		
		$result = callAPI(PAYTM_TXN_URL, $paramList);
		return $result;
	
	}
