<?php
echo '<h2>Clear Skill-Tree Settings</h2>';
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the clear skill tree module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Zen Cost<br/><span>Amount of zen required to clear the master skill tree. Set to 0 to disable zen requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo mconfig('zen_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost<br/><span>Number of credits required to clear the master skill tree. Set to 0 to disable credit requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_4" value="<?php echo mconfig('credit_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $clearskilltreeCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<th>Required Level<br/><span>Minimum level required to clear the master skill tree. It is recommended to keep this setting at the maximum level of 400.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_5" value="<?php echo mconfig('required_level'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Required Master Level<br/><span>Minimum master level required to clear the master skill tree.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_6" value="<?php echo mconfig('required_master_level'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>