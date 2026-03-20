<?php
use Darkheim\Domain\Validator;
?>
<h1 class="page-header"><i class="bi bi-person-fill-slash me-2"></i>Latest Bans</h1>
<?php
$database = $dB;
if(isset($_GET['liftban'])) {
	try {
		if(!Validator::UnsignedNumber($_GET['liftban'])) {
			throw new RuntimeException("Invalid ban id.");
		}
		$banInfo = $database->query_fetch_single("SELECT * FROM ".Ban_Log." WHERE id = ?", array($_GET['liftban']));
		if(!is_array($banInfo)) {
			throw new RuntimeException("Ban ID does not exist.");
		}
		$unban = $database->query("UPDATE "._TBL_MI_." SET "._CLMN_BLOCCODE_." = 0 WHERE "._CLMN_USERNM_." = ?", array($banInfo['account_id']));
		if(!$unban) {
			throw new RuntimeException("Could not unban account.");
		}
		$database->query("DELETE FROM ".Ban_Log." WHERE account_id = ?", array($banInfo['account_id']));
		$database->query("DELETE FROM ".Bans." WHERE account_id = ?", array($banInfo['account_id']));
		message('success', 'Account ban lifted');
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}

// Temporal bans
$tBans = $database->query_fetch("SELECT TOP 25 * FROM ".Ban_Log." WHERE ban_type = ? ORDER BY id DESC", array("temporal"));
// Permanent bans
$pBans = $database->query_fetch("SELECT TOP 25 * FROM ".Ban_Log." WHERE ban_type = ? ORDER BY id DESC", array("permanent"));

echo '<div class="acp-tabs-wrap">';
echo '<div class="acp-tabs">';
echo '<button class="acp-tab active" onclick="switchTab(\'tab-temp\',this)">Temporal Bans</button>';
echo '<button class="acp-tab" onclick="switchTab(\'tab-perm\',this)">Permanent Bans</button>';
echo '</div>';

// Temporal
echo '<div class="acp-tab-content active" id="tab-temp">';
if(is_array($tBans)) {
	echo '<table class="table table-hover mb-0"><thead><tr><th>Account</th><th>Banned By</th><th>Date</th><th>Days</th><th>Reason</th><th></th></tr></thead><tbody>';
	foreach($tBans as $ban) {
		echo '<tr><td>'.$ban['account_id'].'</td><td>'.$ban['banned_by'].'</td><td>'.date("Y-m-d H:i",$ban['ban_date']).'</td><td>'.$ban['ban_days'].'</td><td>'.htmlspecialchars($ban['ban_reason']).'</td>';
		echo '<td class="text-end"><a href="'.admincp_base($_REQUEST['module']."&liftban=".$ban['id']).'" class="btn btn-sm btn-danger">Lift Ban</a></td></tr>';
	}
	echo '</tbody></table>';
} else { echo '<div class="p-3">'; inline_message('info', 'No temporal bans logged.'); echo '</div>'; }
echo '</div>';

// Permanent
echo '<div class="acp-tab-content" id="tab-perm" style="display:none">';
if(is_array($pBans)) {
	echo '<table class="table table-hover mb-0"><thead><tr><th>Account</th><th>Banned By</th><th>Date</th><th>Reason</th><th></th></tr></thead><tbody>';
	foreach($pBans as $ban) {
		echo '<tr><td>'.$ban['account_id'].'</td><td>'.$ban['banned_by'].'</td><td>'.date("Y-m-d H:i",$ban['ban_date']).'</td><td>'.htmlspecialchars($ban['ban_reason']).'</td>';
		echo '<td class="text-end"><a href="'.admincp_base($_REQUEST['module']."&liftban=".$ban['id']).'" class="btn btn-sm btn-danger">Lift Ban</a></td></tr>';
	}
	echo '</tbody></table>';
} else { echo '<div class="p-3">'; inline_message('info', 'No permanent bans logged.'); echo '</div>'; }
echo '</div>';
echo '</div>';
?>
