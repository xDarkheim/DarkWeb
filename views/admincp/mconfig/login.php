<h2>Login Settings</h2>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the login module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Session Timeout<br/><span>Enable/disable sessions timeout.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_2',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('enable_session_timeout'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Session Timeout Limit<br/><span>If session timeout is enabled, define the time (in seconds) after which the inactive session should be logged out automatically.</span></th>
			<td>
				<label>
					<input class="input-mini" type="text" name="setting_3" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('session_timeout'); ?>"/>
				</label> seconds
			</td>
		</tr>
		<tr>
			<th>Maximum Failed Login Attempts<br/><span>Define the maximum failed login attempts before the client's IP address should be temporarily blocked.</span></th>
			<td>
				<label>
					<input class="input-mini" type="text" name="setting_4" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_login_attempts'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Failed Login Attempts IP Block Duration<br/><span>Time in minutes of failed login attempts IP block duration.</span></th>
			<td>
				<label>
					<input class="input-mini" type="text" name="setting_5" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('failed_login_timeout'); ?>"/>
				</label> minutes
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>