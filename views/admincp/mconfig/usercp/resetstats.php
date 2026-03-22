<?php
echo '<h2>Reset Stats Settings</h2>';
?>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the reset stats module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Zen Cost<br/><span>Amount of zen required to reset the character stats. Set to 0 to disable zen requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost<br/><span>Amount of credit required to reset the character stats. Set to 0 to disable credit requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_4" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $resetstatsCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>