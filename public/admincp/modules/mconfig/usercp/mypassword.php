<?php
?>
<h2>Change Password Settings</h2>
<?php
function saveChanges(): void {
	global $_POST;
	foreach($_POST as $setting) {
		if(!check_value($setting)) {
			message('error','Missing data (complete all fields).');
			return;
		}
	}
	$xmlPath = __PATH_MODULE_CONFIGS_USERCP__.'my-password.xml';
	$xml = simplexml_load_string(file_get_contents($xmlPath));
	
	$xml->active = $_POST['setting_1'];
	$xml->change_password_email_verification = $_POST['setting_2'];
	$xml->change_password_request_timeout = $_POST['setting_3'];
	
	$save = $xml->asXML($xmlPath);
	if($save) {
		message('success','Settings successfully saved.');
	} else {
		message('error','There has been an error while saving changes.');
	}
}

if(isset($_POST['submit_changes'])) {
	saveChanges();
}

loadModuleConfigs('my-password');
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the change password module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Email Verification<br/><span>If enabled, the account's password will not be changed until the user clicks the verification link sent via email.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('change_password_email_verification'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Change Password Time Limit<br/><span>If email verification is enabled, set the time that the verification link will stay valid.</span></th>
			<td>
				<label>
					<input class="input-small" type="text" name="setting_3" value="<?php echo mconfig('change_password_request_timeout'); ?>"/>
				</label> hour(s)
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>