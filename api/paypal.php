<?php
use Darkheim\Infrastructure\Payment\PaypalIPN;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Account\Account;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Domain\Validator;
// Access
define('access', 'api');

// Load CMS
if(!@include(rtrim(str_replace('\\','/', dirname(__DIR__)), '/') . '/includes/cms.php')) {
	throw new RuntimeException('Could not load CMS.');
}

// Load PayPal Configurations
$cfg = loadConfigurations('donation.paypal');
if(!is_array($cfg)) {
	header("HTTP/1.1 500 Internal Server Error");
	die();
}

// PayPal Sandbox
$enable_sandbox = $cfg['paypal_enable_sandbox'];

// PayPal Seller Email
$seller_email = $cfg['paypal_email'];

// Instance
$ipn = new PaypalIPN();
if($enable_sandbox == 1) {
	$ipn->useSandbox();
}

// Verification
$verified = $ipn->verifyIPN();

// IPN
$paypal_ipn_status = "VERIFICATION FAILED";
if($verified) {
	try {
		
		// Check receiver email
		if(strtolower($_POST["receiver_email"]) != strtolower($seller_email)) {
			throw new RuntimeException('RECEIVER EMAIL MISMATCH');
		}
		
		// common class
		$common = new Common();
		
		// data
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$txn_type = $_POST['txn_type'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$user_id = $_POST['custom'];
		
		// Process payment
		try {
			
			if($_POST['payment_status'] == 'Completed') {
				
				// donation amount
				$add_credits = floor($payment_amount*$cfg['paypal_conversion_rate']);
				
				// account
				if(!Validator::UnsignedNumber($user_id)) {
					throw new RuntimeException("invalid userid");
				}
				
				// account info
				$Account = new Account();
				$accountInfo = $Account->accountInformation($user_id);
				if(!is_array($accountInfo)) {
					throw new RuntimeException("invalid account");
				}
				
				$creditSystem = new CreditSystem();
				$creditSystem->setConfigId($cfg['credit_config']);
				$configSettings = $creditSystem->showConfigs(true);
				switch($configSettings['config_user_col_id']) {
					case 'userid':
						$creditSystem->setIdentifier($accountInfo[_CLMN_MEMBID_]);
						break;
					case 'username':
						$creditSystem->setIdentifier($accountInfo[_CLMN_USERNM_]);
						break;
					case 'email':
						$creditSystem->setIdentifier($accountInfo[_CLMN_EMAIL_]);
						break;
					default:
						throw new RuntimeException("invalid identifier");
				}
				
				$_GET['page'] = 'api';
				$_GET['subpage'] = 'paypal';
				
				$creditSystem->addCredits($add_credits);
				
				$paypal_ipn_status = "Completed Successfully";
				
				// paypal log
				$common->paypal_transaction($txn_id,$user_id,$payment_amount,$payer_email,$item_number);
			
			} else {
				
				/* block account */
				$common->blockAccount($user_id);
				
				/* update transaction */
				$common->paypal_transaction_reversed_updatestatus($item_number);
				
			}
			
		} catch(Exception $ex) {
			$paypal_ipn_status = $ex->getMessage();
		}
		
	} catch(Exception $ex) {
		$paypal_ipn_status = $ex->getMessage();
	}
} elseif($enable_sandbox) {
	if($_POST["test_ipn"] != 1) {
		$paypal_ipn_status = "RECEIVED FROM LIVE WHILE SANDBOXED";
	}
} elseif($_POST["test_ipn"] == 1) {
	$paypal_ipn_status = "RECEIVED FROM SANDBOX WHILE LIVE";
}

// OK
header("HTTP/1.1 200 OK");