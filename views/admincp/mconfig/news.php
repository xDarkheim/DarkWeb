<h2>News Settings</h2>
<form action="<?php echo htmlspecialchars((string) ($selectedConfigFormAction ?? ""), ENT_QUOTES, "UTF-8"); ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the news module.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_1',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Expanded News<br/><span>Amount of news you want to display expanded. If less than the display news limit configuration, then the rest of the news will not display expanded.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('news_expanded'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Shown News Limit<br/><span>Amount of news to display in the news page.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_3" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('news_list_limit'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Short News<br/><span>Enable/disable the short news feature.</span></th>
			<td>
				<?php \Darkheim\Application\View\FormFieldRenderer::enabledisableCheckboxes('setting_6',\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('news_short'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Short News Character Limit<br/><span>Number of characters to show in the short version of news.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_7" value="<?php echo \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('news_short_char_limit'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>