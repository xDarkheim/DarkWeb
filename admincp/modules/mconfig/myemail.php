<?php
echo '<h2>Change Email Settings</h2>';

function saveChanges(): void {
	global $_POST;
	foreach($_POST as $setting) {
		if(!check_value($setting)) {
			message('error','Missing data (complete all fields).');
			return;
		}
	}
	$xmlPath = __PATH_MODULE_CONFIGS__.'usercp.myemail.xml';
	$xml = simplexml_load_string(file_get_contents($xmlPath));
	
	$xml->active = $_POST['setting_1'];
	$xml->require_verification = $_POST['setting_2'];
	
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

loadModuleConfigs('usercp.myemail');
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the change email module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Email Verification<br/><span>If enabled, the account's email will not be changed until the user clicks the verification link sent to their current email.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('require_verification'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>