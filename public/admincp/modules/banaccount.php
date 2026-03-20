<?php
use Darkheim\Domain\Validator;
?>
<h1 class="page-header"><i class="bi bi-slash-circle-fill me-2"></i>Ban Account</h1>
<?php
	$database = $dB;

	// Add ban system cron if it doesn't exist
	$banCron = "INSERT INTO ".Cron." (cron_name, cron_description, cron_file_run, cron_run_time, cron_status, cron_protected, cron_file_md5) VALUES ('Ban System', 'Scheduled task to lift temporal bans', 'temporal_bans.php', '3600', 1, 1, '1a3787c5179afddd1bfb09befda3d1c7')";
	$checkBanCron = $database->query_fetch_single("SELECT * FROM ".Cron." WHERE cron_file_run = ?", array("temporal_bans.php"));
	if(!is_array($checkBanCron)) {
		$database->query($banCron);
	}
	
	if(isset($_POST['submit_ban'])) {
		try {
			if(!isset($_POST['ban_account'])) {
				throw new RuntimeException("Please enter the account username.");
			}
			if(!$common->userExists($_POST['ban_account'])) {
				throw new RuntimeException("Invalid account username.");
			}
			if(!isset($_POST['ban_days'])) {
				throw new RuntimeException("Please enter the amount of days.");
			}
			if(!Validator::UnsignedNumber($_POST['ban_days'])) {
				throw new RuntimeException("Invalid ban days.");
			}
			if(isset($_POST['ban_reason'])
					&& !Validator::Length(
							$_POST['ban_reason'],
							100,
							1
					)
			) {
				throw new RuntimeException("Invalid ban reason.");
			}
			
			// Check Online Status
			if($common->accountOnline($_POST['ban_account'])) {
				throw new RuntimeException("The account is currently online.");
			}
			
			// Account Information
			$userID = $common->retrieveUserID($_POST['ban_account']);
			$accountData = $common->accountInformation($userID);
			
			// Check if already banned
			if($accountData[_CLMN_BLOCCODE_] == 1) {
				throw new RuntimeException("This account is already banned.");
			}
			
			// Ban Type
			$banType = ($_POST['ban_days'] >= 1 ? "temporal" : "permanent");
			
			// Log Ban
			$banLogData = array(
				'acc' => $_POST['ban_account'],
				'by' => $_SESSION['username'],
				'type' => $banType,
				'date' => time(),
				'days' => $_POST['ban_days'],
				'reason' => ($_POST['ban_reason'] ?? "")
			);
			
			$logBan = $database->query("INSERT INTO ".Ban_Log." (account_id, banned_by, ban_type, ban_date, ban_days, ban_reason) VALUES (:acc, :by, :type, :date, :days, :reason)", $banLogData);
			if(!$logBan) {
				throw new RuntimeException("Could not log ban (check tables)[1].");
			}
			
			// Add a temporal ban
			if($banType == "temporal") {
				$tempBanData = array(
					'acc' => $_POST['ban_account'],
					'by' => $_SESSION['username'],
					'date' => time(),
					'days' => $_POST['ban_days'],
					'reason' => ($_POST['ban_reason'] ?? "")
				);
				$tempBan = $database->query("INSERT INTO ".Bans." (account_id, banned_by, ban_date, ban_days, ban_reason) VALUES (:acc, :by, :date, :days, :reason)", $tempBanData);
				if(!$tempBan) {
					throw new RuntimeException(
							"Could not add temporal ban (check tables)[2]. - "
							.$database->error
					);
				}
			}
			
			// Ban Account
			$banAccount = $database->query("UPDATE "._TBL_MI_." SET "._CLMN_BLOCCODE_." = ? WHERE "._CLMN_USERNM_." = ?", array(1, $_POST['ban_account']));
			if(!$banAccount) {
				throw new RuntimeException("Could not ban account.");
			}
			
			message('success', 'Account Banned');
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
?>
<div class="acp-card" style="max-width:520px;">
	<div class="acp-card-header"><i class="bi bi-slash-circle me-2"></i>Ban Account</div>
	<form action="" method="post" class="p-3">
		<div class="form-group">
			<label>Account Username</label>
			<label>
				<input type="text" name="ban_account" class="form-control" placeholder="username" required>
			</label>
		</div>
		<div class="form-group">
			<label>Days <small style="color:#666;">(0 = permanent)</small></label>
			<label>
				<input type="number" name="ban_days" class="form-control" value="0" min="0" required>
			</label>
		</div>
		<div class="form-group">
			<label>Reason <small style="color:#666;">(optional)</small></label>
			<label>
				<input type="text" name="ban_reason" class="form-control" placeholder="Reason for ban">
			</label>
		</div>
		<button type="submit" name="submit_ban" class="btn btn-danger w-100"><i class="bi bi-slash-circle me-1"></i>Ban Account</button>
	</form>
</div>