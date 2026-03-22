<?php
echo '<h2>Buy Zen Settings</h2>';
?>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the buy zen module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Max Zen<br/><span>Maximum zen a character can have</span></th>
			<td>
				<label>
					<input class="input-small" type="text" name="setting_2" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('max_zen'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Exchange Rate<br/><span>How much zen does 1 CREDIT equal to.</span></th>
			<td>
				<label>
					<input class="input-small" type="text" name="setting_3" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('exchange_ratio'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Increment Rate<br/><span>The larger the value, the fewer options there will be in the dropdown menu.</span></th>
			<td>
				<label>
					<input class="input-small" type="text" name="setting_5" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('increment_rate'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $buyzenCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>