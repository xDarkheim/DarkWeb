<?php
use Darkheim\Domain\Validator;
echo '<h1 class="page-header"><i class="bi bi-shield-fill-check me-2"></i>AdminCP Access</h1>';
echo '<p class="mb-3" style="color:#777;">Set the access level to 0 to remove an admin.</p>';

if(isset($_POST['settings_submit'])) {
	try {
		$cmsConfigurations = cmsConfigs();
		$newAdminUser  = $_POST['new_admin'];
		$newAdminLevel = $_POST['new_access'];
		unset($_POST['settings_submit'], $_POST['new_admin'], $_POST['new_access']);
		foreach($_POST as $adminUsername => $accessLevel) {
			if(!Validator::AlphaNumeric($adminUsername)) {
				throw new RuntimeException('Invalid username.');
			}
			if(!Validator::UsernameLength($adminUsername)) {
				throw new RuntimeException('Invalid username.');
			}
			if(!array_key_exists($adminUsername, config('admins',true))) {
				continue;
			}
			if(!Validator::UnsignedNumber($accessLevel)) {
				throw new RuntimeException('Access level must be 0–100.');
			}
			if(!Validator::Number($accessLevel, 100)) {
				throw new RuntimeException('Access level must be 0–100.');
			}
			if($accessLevel == 0) { if($adminUsername == $_SESSION['username']) {
				throw new RuntimeException('You cannot remove yourself.');
			}
				continue; }
			$adminAccounts[$adminUsername] = (int)$accessLevel;
		}
		if(check_value($newAdminUser)) {
			if(array_key_exists($newAdminUser, config('admins',true))) {
				throw new RuntimeException('Admin already exists.');
			}
			if(!Validator::UnsignedNumber($newAdminLevel)) {
				throw new RuntimeException('Access level must be 1–100.');
			}
			$adminAccounts[$newAdminUser] = (int)$newAdminLevel;
		}
		$cmsConfigurations['admins'] = $adminAccounts;
		$cfgFile = fopen(__PATH_CONFIGS__.'cms.json', 'wb');
		if(!$cfgFile) {
			throw new RuntimeException('Could not open configuration file.');
		}
		fwrite($cfgFile,
			json_encode(
				$cmsConfigurations,
				JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
			)
		);
		fclose($cfgFile);
		message('success','Settings saved!');
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}

$admins = config('admins',true);
if(is_array($admins)) {
	echo '<div class="acp-card" style="max-width:600px;">';
	echo '<div class="acp-card-header">Administrators</div>';
	echo '<form action="" method="post"><table class="table table-hover mb-0">';
	echo '<thead><tr><th>Account</th><th>Access Level</th></tr></thead><tbody>';
	foreach($admins as $admin => $level) {
		echo '<tr><td><strong>'.$admin.'</strong></td><td><input type="number" class="form-control" style="width:100px;" min="0" max="100" name="'.$admin.'" value="'.$level.'" required></td></tr>';
	}
	echo '<tr><td><input type="text" class="form-control" name="new_admin" placeholder="New admin username"></td>';
	echo '<td><input type="number" class="form-control" style="width:100px;" min="1" max="100" name="new_access" placeholder="Level"></td></tr>';
	echo '</tbody></table>';
	echo '<div class="p-3"><button type="submit" name="settings_submit" value="ok" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button></div>';
	echo '</form></div>';
} else {
	message('error','Admins list is empty.');
}