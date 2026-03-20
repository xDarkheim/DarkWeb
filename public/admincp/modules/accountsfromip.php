<?php
use Darkheim\Domain\Validator;
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Find Accounts from IP</h1>
<div class="acp-card mb-4">
	<form class="d-flex gap-2" role="form" method="post">
		<label>
			<input type="text" class="form-control" name="ip_address" placeholder="0.0.0.0" style="max-width:300px;"/>
		</label>
		<button type="submit" class="btn btn-primary" name="search_ip" value="ok"><i class="bi bi-search me-1"></i>Search</button>
	</form>
</div>
<?php
if(isset($_POST['ip_address'])) {
	try {
		if(!Validator::Ip($_POST['ip_address'])) {
			throw new RuntimeException("You have entered an invalid IP address.");
		}
		$searchdb = $dB;
		$membStatData = $searchdb->query_fetch(
				"SELECT "._CLMN_MS_MEMBID_." FROM "._TBL_MS_." WHERE "._CLMN_MS_IP_." = ? GROUP BY "._CLMN_MS_MEMBID_, array($_POST['ip_address']));
		echo '<div class="acp-card"><div class="acp-card-header">Results for IP: <strong>'.$_POST['ip_address'].'</strong></div>';
		if(is_array($membStatData)) {
			echo '<table class="table table-hover mb-0"><thead><tr><th>Account</th><th></th></tr></thead><tbody>';
			foreach($membStatData as $u) {
				echo '<tr><td>'.$u[_CLMN_MS_MEMBID_].'</td><td class="text-end"><a href="'.admincp_base("accountinfo&id=".$common->retrieveUserID($u[_CLMN_MS_MEMBID_])).'" class="btn btn-sm btn-default">Account Info</a></td></tr>';
			}
			echo '</tbody></table>';
		} else {
			echo '<div class="p-3">'; inline_message('info', 'No accounts found linked to this IP.'); echo '</div>';
		}
		echo '</div>';
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
?>