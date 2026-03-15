<?php
?>
<h1 class="page-header"><i class="bi bi-paypal me-2"></i>PayPal Donations</h1>
<?php
try {
	$database = $dB;
	$paypalDonations = $database->query_fetch("SELECT * FROM ".PayPal_Transactions." ORDER BY id DESC");
	if(!is_array($paypalDonations)) {
		throw new RuntimeException("No PayPal transactions found.");
	}
	echo '<div class="acp-card"><div class="acp-card-header">Transactions</div>';
	echo '<table id="paypal_donations" class="table table-hover mb-0">';
	echo '<thead><tr><th>Transaction ID</th><th>Account</th><th>Amount</th><th>PayPal Email</th><th>Date</th><th>Status</th></tr></thead><tbody>';
	foreach($paypalDonations as $data) {
		$userData = $common->accountInformation($data['user_id']);
		$status = $data['transaction_status'] == 1 ? '<span class="badge-status on">OK</span>' : '<span class="badge-status off">Reversed</span>';
		echo '<tr>';
		echo '<td><code>'.$data['transaction_id'].'</code></td>';
		echo '<td><a href="'.admincp_base("accountinfo&id=".$data['user_id']).'">'.$userData[_CLMN_USERNM_].'</a></td>';
		echo '<td>$'.$data['payment_amount'].'</td>';
		echo '<td>'.$data['paypal_email'].'</td>';
		echo '<td>'.date("Y-m-d H:i",$data['transaction_date']).'</td>';
		echo '<td>'.$status.'</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
} catch(Exception $ex) { message('error', $ex->getMessage()); }
?>