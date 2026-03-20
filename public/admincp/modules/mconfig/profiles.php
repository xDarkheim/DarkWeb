<?php
?>
<h2>Profiles Settings</h2>
<?php
function saveChanges(): void {
	global $_POST;
	foreach($_POST as $setting) {
		if(!check_value($setting)) {
			message('error','Missing data (complete all fields).');
			return;
		}
	}
	$xmlPath = __PATH_MODULE_CONFIGS__.'profiles.xml';
	$xml = simplexml_load_string(file_get_contents($xmlPath));
	
	$xml->active = $_POST['setting_1'];
	$xml->encode = $_POST['setting_2'];
	
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

loadModuleConfigs('profiles');
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the profile modules.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Base64 Encoding<br/><span>Enable/disable the usage of Base64 encoding in profiles URLs.<br>If you are having issues with players or guilds with special characters in their names, enabling this option might help.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('encode'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>