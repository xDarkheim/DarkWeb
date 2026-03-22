<?php
echo '<h2>Add Stats Settings</h2>';
?>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the add stats module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Zen Cost<br/><span>Amount of zen required to add stats. Set to 0 to disable zen requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('zen_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost<br/><span>Amount of credit required to add stats. Set to 0 to disable credit requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_4" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('credit_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $addstatsCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<th>Required Level<br/><span>Minimum level required to add stats. Set to 0 to disable level requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_5" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_level'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Required Master Level<br/><span>Minimum master level is required to add stats. Set to 0 to disable master level requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_6" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('required_master_level'); ?>"/>
				</label>
			</td>
		</tr>
		
		<tr>
			<th>Maximum Stats<br/><span>Number of points that each stat may have.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_7" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_stats'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Minimum Points Limit<br/><span>Minimum amount of points the player must add to use the module.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_8" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('minimum_limit'); ?>"/>
				</label>
			</td>
		</tr>
		
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>