<h2>Contact Us Settings</h2>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the contact module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Email Subject<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_2" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('subject'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Send Emails To<br/><span></span></th>
			<td>
				<label>
					<input type="text" name="setting_3" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('sendto'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>



