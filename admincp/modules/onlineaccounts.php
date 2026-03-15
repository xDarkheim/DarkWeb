<?php
use Darkheim\Application\Account\Account;
echo '<h1 class="page-header"><i class="bi bi-broadcast-pin me-2"></i>Online Accounts</h1>';

$Account = new Account();
$serverList = $Account->getServerList();

if(is_array($serverList)) {
	echo '<div class="acp-stat-row mb-4">';
	foreach($serverList as $server) {
		echo '<div class="acp-stat-box"><div class="acp-stat-val">'.number_format($Account->getOnlineAccountCount($server)).'</div><div class="acp-stat-lbl">'.$server.'</div></div>';
	}
	echo '<div class="acp-stat-box accent"><div class="acp-stat-val">'.number_format($Account->getOnlineAccountCount()).'</div><div class="acp-stat-lbl">Total Online</div></div>';
	echo '</div>';
}

$onlineAccounts = $Account->getOnlineAccountList();
echo '<div class="acp-card">';
echo '<div class="acp-card-header">Account List</div>';
if(is_array($onlineAccounts)) {
	echo '<table class="table table-hover mb-0"><thead><tr><th>Account</th><th>IP Address</th><th>Server</th></tr></thead><tbody>';
	foreach($onlineAccounts as $row) {
		echo '<tr>';
		echo '<td><a href="'.admincp_base('accountinfo&u='.$row[_CLMN_MS_MEMBID_]).'">'.$row[_CLMN_MS_MEMBID_].'</a></td>';
		echo '<td><code>'.$row[_CLMN_MS_IP_].'</code></td>';
		echo '<td>'.$row[_CLMN_MS_GS_].'</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<div class="p-3">'; inline_message('info', 'There are no online accounts.'); echo '</div>';
}
echo '</div>';

