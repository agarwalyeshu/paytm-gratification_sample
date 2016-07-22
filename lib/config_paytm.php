<?php
/**
   * @package paytm-gratification_sample
   */
define('PAYTM_ENVIRONMENT', 'TEST'); // PROD
define('PAYTM_Merchant_key','');
define('PAYTM_Merchant_Guid','');
define('PAYTM_Sales_Guid','');

$PAYTM_DOMAIN = "trust-uat.paytm.in";
if (PAYTM_ENVIRONMENT == 'PROD') {
	$PAYTM_DOMAIN = 'trust.paytm.in';
}

define('PAYTM_STATUS_QUERY_URL', 'https://'.$PAYTM_DOMAIN.'/wallet-web/txnStatusList');
define('PAYTM_TXN_URL', 'https://'.$PAYTM_DOMAIN.'/wallet-web/salesToUserCredit');

?>