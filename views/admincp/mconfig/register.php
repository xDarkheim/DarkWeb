<h2>Registration Settings</h2>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the registration module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Recaptcha v2<br/><span>Enable/disable Recaptcha validation. <br/><br/> <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a></span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('register_enable_recaptcha'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Recaptcha Site Key<br/></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_3" value="<?php echo mconfig('register_recaptcha_site_key'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Recaptcha Secret Key<br/></th>
			<td>
				<label>
					<input class="input-xxlarge" type="text" name="setting_4" value="<?php echo mconfig('register_recaptcha_secret_key'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Email Verification<br/><span>If enabled, the user will receive an email with a verification link. The accout will not be created if the email is not verified.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_5',mconfig('verify_email'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Send Welcome Email<br/><span>Sends a welcome email after registering a new account.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_6',mconfig('send_welcome_email'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Verification Time Limit<br/><span>If <strong>Email Verification</strong> is Enabled. Set the amount of time the user has to verify the account. After the verification time limit passed, the user will have to repeat the registration process.</span></th>
			<td>
				<label>
					<input class="input-mini" type="text" name="setting_7" value="<?php echo mconfig('verification_timelimit'); ?>"/>
				</label> Hour(s)
			</td>
		</tr>
		<tr>
			<th>Automatic Log-In<br/><span>Automatic account log-in after registering. This feature only works when email verification is disabled.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_8', mconfig('automatic_login'), 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>