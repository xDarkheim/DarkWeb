<?php
echo '<h2>Clear PK Settings</h2>';
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the clear pk module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Zen Cost<br/><span>Amount of zen required to clear the character pk status. Set to 0 to disable zen requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo mconfig('zen_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost<br/><span>Number of credits required to clear the character pk status. Set to 0 to disable credit requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_4" value="<?php echo mconfig('credit_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $clearpkCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>