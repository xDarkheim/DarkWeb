<?php
echo '<h2>Reset Settings</h2>';
?>
<form action="" method="post">
	
	<h3>General</h3>
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the reset module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Maximum Resets<br/><span>Maximum allowed number of resets each character may have.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_6" value="<?php echo mconfig('maximum_resets'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Keep Stats<br/><span>If enabled, the character's stats will not be reverted to its base stats.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_7',mconfig('keep_stats'),'Yes (keep stats)','No (reset stats)'); ?>
			</td>
		</tr>
		<tr>
			<th>Clear Inventory<br/><span>Clears the character's inventory.<br /><br /><span style="color:red;">* Enabling this setting will also clear the character's equipment</span></span></th>
			<td>
				<?php enabledisableCheckboxes('setting_10',mconfig('clear_inventory'),'Yes','No'); ?>
			</td>
		</tr>
		<tr>
			<th>Revert Class Evolution<br/><span>Example: If the character is a Soul Master, after performing the reset, the character's class will become Dark Wizard.<br /><br /><span style="color:red;">* Enabling this setting will also clear the character's quests</span></span></th>
			<td>
				<?php enabledisableCheckboxes('setting_11',mconfig('revert_class_evolution'),'Yes','No'); ?>
			</td>
		</tr>
	</table>
	
	<h3>Requirements</h3>
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Required Level<br/><span>Minimum level required to perform a reset.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_5" value="<?php echo mconfig('required_level'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Zen Cost<br/><span>Amount of zen required to reset the character. Set to 0 to disable zen requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_2" value="<?php echo mconfig('zen_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost<br/><span>Amount of credit required to reset the character. Set to 0 to disable credit requirement.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_4" value="<?php echo mconfig('credit_cost'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Cost Configuration<br/><span></span></th>
			<td>
				<?php echo $resetCostCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
	</table>
	
	<h3>Reward</h3>
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Level Up Points Reward<br/><span>Number of level-up points to be given to the character after the reset. Set to 0 to disable.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_8" value="<?php echo mconfig('points_reward'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Multiply Level Up Points by Resets<br/><span>If enabled, the amount of level up points reward will be multiplied by the number of resets.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_9',mconfig('multiply_points_by_resets'),'Yes','No'); ?>
			</td>
		</tr>
		<tr>
			<th>Credit Reward<br/><span>Amount of credit to be rewarded on each reset to the character. Set to 0 to disable credit reward.</span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_12" value="<?php echo mconfig('credit_reward'); ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Credit Reward Configuration<br/><span></span></th>
			<td>
				<?php echo $resetRewardCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
	</table>
	
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>