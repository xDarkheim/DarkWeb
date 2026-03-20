<?php
?>
<h2>Contact Us Settings</h2>
<?php

function saveChanges(): void {
	global $_POST;
	foreach($_POST as $setting) {
		if(!check_value($setting)) {
			message('error','Missing data (complete all fields).');
			return;
		}
	}
	$xmlPath = __PATH_MODULE_CONFIGS__.'contact.xml';
	$xml = simplexml_load_string(file_get_contents($xmlPath));
	
	$xml->active = $_POST['setting_1'];
	$xml->subject = $_POST['setting_2'];
	$xml->sendto = $_POST['setting_3'];
	
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

loadModuleConfigs('contact');
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the contact module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Email Subject<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_2" value="<?php echo mconfig('subject'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Send Emails To<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_3" value="<?php echo mconfig('sendto'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>



