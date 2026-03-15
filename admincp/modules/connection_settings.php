<?php
use Darkheim\Infrastructure\Database\dB;
use Darkheim\Domain\Validator;
echo '<h1 class="page-header"><i class="bi bi-database-fill me-2"></i>Connection Settings</h1>';

$allowedSettings = array(
	'settings_submit', # the Submit button
	'SQL_DB_HOST',
	'SQL_DB_NAME',
	'SQL_DB_USER',
	'SQL_DB_PASS',
	'SQL_DB_PORT',
	'SQL_PASSWORD_ENCRYPTION',
);

if(isset($_POST['settings_submit'])) {
	try {
		
		// host
		if(!isset($_POST['SQL_DB_HOST'])) {
			throw new RuntimeException('Invalid Host setting.');
		}
		$setting['SQL_DB_HOST'] = $_POST['SQL_DB_HOST'];
		
		// database
		if(!isset($_POST['SQL_DB_NAME'])) {
			throw new RuntimeException('Invalid Database setting.');
		}
		$setting['SQL_DB_NAME'] = $_POST['SQL_DB_NAME'];
		
		// user
		if(!isset($_POST['SQL_DB_USER'])) {
			throw new RuntimeException('Invalid User setting.');
		}
		$setting['SQL_DB_USER'] = $_POST['SQL_DB_USER'];
		
		// password
		if(!isset($_POST['SQL_DB_PASS'])) {
			throw new RuntimeException('Invalid Password setting.');
		}
		$setting['SQL_DB_PASS'] = $_POST['SQL_DB_PASS'];
		
		// port
		if(!isset($_POST['SQL_DB_PORT'])) {
			throw new RuntimeException('Invalid Port setting.');
		}
		if(!Validator::UnsignedNumber($_POST['SQL_DB_PORT'])) {
			throw new RuntimeException('Invalid Port setting.');
		}
		$setting['SQL_DB_PORT'] = $_POST['SQL_DB_PORT'];
		
		// md5
		if(!isset($_POST['SQL_PASSWORD_ENCRYPTION'])) {
			throw new RuntimeException('Invalid password encryption setting.');
		}
		if(!in_array($_POST['SQL_PASSWORD_ENCRYPTION'], array('none', 'wzmd5', 'phpmd5', 'sha256'))) {
			throw new RuntimeException('Invalid password encryption setting.');
		}
		$setting['SQL_PASSWORD_ENCRYPTION'] = $_POST['SQL_PASSWORD_ENCRYPTION'];
		
		// test connection
		$testdB = new dB($setting['SQL_DB_HOST'], $setting['SQL_DB_PORT'], $setting['SQL_DB_NAME'], $setting['SQL_DB_USER'], $setting['SQL_DB_PASS']);
		if($testdB->dead) {
			throw new RuntimeException('The connection to database was unsuccessful, settings not saved.');
		}
		
		// cms configs
		$cmsConfigurations = cmsConfigs();

		// make sure the settings are in the allowlist
		foreach(array_keys($setting) as $settingName) {
			if(!in_array($settingName, $allowedSettings, true)) {
				throw new RuntimeException(
					'One or more submitted setting is not editable.'
				);
			}
			
			$cmsConfigurations[$settingName] = $setting[$settingName];
		}
		
		$newDarkheimConfig = json_encode(
			$cmsConfigurations,
			JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
		);
		$cfgFile = fopen(__PATH_CONFIGS__.'cms.json', 'wb');
		if(!$cfgFile) {
			throw new RuntimeException(
				'There was a problem opening the configuration file.'
			);
		}
		
		fwrite($cfgFile, $newDarkheimConfig);
		fclose($cfgFile);
		
		message('success', 'Settings successfully saved!');
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}

echo '<div class="acp-card">';
	echo '<div class="acp-card-header">Database Configuration</div>';
	echo '<form action="" method="post">';
		echo '<table class="table table-hover module_config_tables" style="table-layout:fixed;">';

			echo '<tr>';
				echo '<td>';
					echo '<strong>Host</strong>';
					echo '<p class="setting-description">Hostname/IP address of your MSSQL server.</p>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" class="form-control" name="SQL_DB_HOST" value="'.config('SQL_DB_HOST',true).'" required>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<strong>Database</strong>';
					echo '<p class="setting-description">Usually "MuOnline".</p>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" class="form-control" name="SQL_DB_NAME" value="'.config('SQL_DB_NAME',true).'" required>';
				echo '</td>';
			echo '</tr>';

			echo '<tr>';
				echo '<td>';
					echo '<strong>User</strong>';
					echo '<p class="setting-description">Usually "sa".</p>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" class="form-control" name="SQL_DB_USER" value="'.config('SQL_DB_USER',true).'" required>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<strong>Password</strong>';
					echo '<p class="setting-description">User\'s password.</p>';
				echo '</td>';
				echo '<td>';
					echo '<input type="text" class="form-control" name="SQL_DB_PASS" value="'.config('SQL_DB_PASS',true).'" required>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<strong>Port</strong>';
					echo '<p class="setting-description">Port number to remotely connect to your MSSQL server. Usually 1433.</p>';
				echo '</td>';
				echo '<td>';
					echo '<input type="number" class="form-control" name="SQL_DB_PORT" value="'.config('SQL_DB_PORT',true).'" required>';
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>';
					echo '<strong>Password Encryption</strong>';
					echo '<p class="setting-description">Select the type of password encryption you are using for your account\'s table.</p>';
				echo '</td>';
				echo '<td>';
					echo '<div class="radio">';
						echo '<label>';
							echo '<input type="radio" name="SQL_PASSWORD_ENCRYPTION" value="none" '.(config('SQL_PASSWORD_ENCRYPTION',true) == 'none' ? 'checked' : null).'>';
							echo 'None';
						echo '</label>';
					echo '</div>';
					echo '<div class="radio">';
						echo '<label>';
							echo '<input type="radio" name="SQL_PASSWORD_ENCRYPTION" value="wzmd5" '.(config('SQL_PASSWORD_ENCRYPTION',true) == 'wzmd5' ? 'checked' : null).'>';
							echo 'MD5 (WZ)';
						echo '</label>';
					echo '<div class="radio">';
						echo '<label>';
							echo '<input type="radio" name="SQL_PASSWORD_ENCRYPTION" value="phpmd5" '.(config('SQL_PASSWORD_ENCRYPTION',true) == 'phpmd5' ? 'checked' : null).'>';
							echo 'MD5 (PHP)';
						echo '</label>';
					echo '<div class="radio">';
						echo '<label>';
							echo '<input type="radio" name="SQL_PASSWORD_ENCRYPTION" value="sha256" '.(config('SQL_PASSWORD_ENCRYPTION',true) == 'sha256' ? 'checked' : null).'>';
							echo 'Sha256';
						echo '</label>';
					echo '</div>';
				echo '</td>';
			echo '</tr>';
			
		echo '</table>';
		echo '<div class="p-3"><button type="submit" name="settings_submit" value="ok" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Settings</button></div>';
	echo '</form>';
echo '</div>';
echo '</div>';
