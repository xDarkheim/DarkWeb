<?php
?>
<h1 class="page-header"><i class="bi bi-trophy me-2"></i>Top Voters</h1>
<?php
$database = $dB;
$ts1 = strtotime(date("m/01/Y 00:00"));
$ts2 = strtotime(date((date('m')+1)."/01/Y 00:00"));
$voteLogs = $database->query_fetch("SELECT TOP 100 user_id, COUNT(*) as totalvotes FROM ".Vote_Logs." WHERE timestamp BETWEEN ? AND ? GROUP BY user_id ORDER BY totalvotes DESC", array($ts1,$ts2));
if($voteLogs && is_array($voteLogs)) {
	echo '<div class="acp-card"><div class="acp-card-header">Top Voters — '.date('F Y').'</div>';
	echo '<table class="table table-hover mb-0"><thead><tr><th>#</th><th>Account</th><th>Votes</th></tr></thead><tbody>';
	foreach($voteLogs as $key => $v) {
		$acc = $common->accountInformation($v['user_id']);
		echo '<tr><td>'.($key+1).'</td><td>'.$acc[_CLMN_USERNM_].'</td><td><strong>'.$v['totalvotes'].'</strong></td></tr>';
	}
	echo '</tbody></table></div>';
} else { message('error','No vote logs found.'); }
?>