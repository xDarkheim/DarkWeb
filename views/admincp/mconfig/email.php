<h1 class="page-header">Email Settings</h1>
<?php
$emailConfigs = array_replace([
	'active' => 0,
	'send_from' => '',
	'send_name' => '',
	'smtp_active' => 0,
	'smtp_host' => '',
	'smtp_port' => '',
	'smtp_user' => '',
	'smtp_pass' => '',
], is_array($emailConfigs ?? null) ? $emailConfigs : []);
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Email System<br/><span>Enable/disable the email system.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',$emailConfigs['active'],'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Send Email From<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_2" value="<?php echo $emailConfigs['send_from']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Send Email From Name<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_3" value="<?php echo $emailConfigs['send_name']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>SMTP Status<br/><span>Enable/disable the SMTP system.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_4',$emailConfigs['smtp_active'],'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>SMTP Host<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_5" value="<?php echo $emailConfigs['smtp_host']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>SMTP Port<br/><span></span></th>
			<td>
				<label>
					<input type="text" class="input-mini" name="setting_6" value="<?php echo $emailConfigs['smtp_port']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>SMTP User<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_7" value="<?php echo $emailConfigs['smtp_user']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>SMTP Password<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_8" value="<?php echo $emailConfigs['smtp_pass']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>