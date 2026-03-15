<?php
use Darkheim\Application\Credits\CreditSystem;
echo '<h1 class="page-header"><i class="bi bi-cash-coin me-2"></i>Credit Manager</h1>';
$creditSystem = new CreditSystem();

if(isset($_POST['creditsconfig'], $_POST['identifier'], $_POST['credits'], $_POST['transaction'])) {
	try {
		$creditSystem->setConfigId($_POST['creditsconfig']);
		$creditSystem->setIdentifier($_POST['identifier']);
		switch($_POST['transaction']) {
			case 'add':      $creditSystem->addCredits($_POST['credits']); message('success','Credits added.'); break;
			case 'subtract': $creditSystem->subtractCredits($_POST['credits']); message('success','Credits subtracted.'); break;
			default: throw new RuntimeException("Invalid transaction.");
		}
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}

echo '<div class="row g-3">';

// Form
echo '<div class="col-lg-4"><div class="acp-card"><div class="acp-card-header">Add / Subtract Credits</div><div class="p-3">';
echo '<form role="form" method="post">';
echo '<div class="form-group"><label>Configuration</label>'.$creditSystem->buildSelectInput("creditsconfig",1,"form-control").'</div>';
echo '<div class="form-group"><label>Identifier</label><input type="text" class="form-control" name="identifier" placeholder="username / email / character"><p style="font-size:11px;color:#666;margin-top:4px;">Depends on the selected configuration.</p></div>';
echo '<div class="form-group"><label>Credits</label><input type="number" class="form-control" name="credits" value="0" min="0"></div>';
echo '<div class="form-group"><label>Transaction</label>';
echo '<div class="d-flex gap-3"><label style="color:#aaa;"><input type="radio" name="transaction" value="add" checked> Add</label>';
echo '<label style="color:#aaa;"><input type="radio" name="transaction" value="subtract"> Subtract</label></div></div>';
echo '<button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-left-right me-1"></i>Execute</button>';
echo '</form></div></div></div>';

// Logs
echo '<div class="col-lg-8"><div class="acp-card"><div class="acp-card-header">Transaction Logs</div>';
$creditsLogs = $creditSystem->getLogs();
if(is_array($creditsLogs)) {
	echo '<table id="credits_logs" class="table table-hover mb-0">';
	echo '<thead><tr><th>Config</th><th>Identifier</th><th>Credits</th><th>Transaction</th><th>Date</th><th>Module</th><th>AdminCP</th></tr></thead><tbody>';
	foreach($creditsLogs as $d) {
		$inAcp  = $d['log_inadmincp'] == 1 ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">No</span>';
		$trans  = $d['log_transaction'] == 'add' ? '<span class="badge-status on">Add</span>' : '<span class="badge-status off">Sub</span>';
		echo '<tr><td>'.$d['log_config'].'</td><td>'.$d['log_identifier'].'</td><td>'.$d['log_credits'].'</td><td>'.$trans.'</td>';
		echo '<td>'.date("Y-m-d H:i",$d['log_date']).'</td><td>'.$d['log_module'].'</td><td>'.$inAcp.'</td></tr>';
	}
	echo '</tbody></table>';
} else { echo '<div class="p-3">'; inline_message('info', 'No logs found.'); echo '</div>'; }
echo '</div></div>';

echo '</div>';