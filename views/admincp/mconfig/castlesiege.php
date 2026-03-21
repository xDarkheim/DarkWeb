<?php
echo '<h2>Castle Siege Settings</h2>';
$cfg = $castleSiegeConfig ?? null;
if(!is_array($cfg)) {
	message('error', 'Error loading config file.');
	return;
}
?>
<form action="" method="post">
	
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Active<br/><span>Enables or disabled the castle siege module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1', $cfg['active'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Hide Idle<br/><span>If enabled, the idle stages of castle siege will not be displayed.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2', $cfg['hide_idle'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Live Data<br/><span>If enabled, castle siege data will be loaded directly from the database and will bypass the cache system.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_3', $cfg['live_data'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Castle Owner<br/><span>Displays the castle owner.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_4', $cfg['show_castle_owner'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Castle Owner Alliance<br/><span>Displays the castle owner alliance guilds.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_5', $cfg['show_castle_owner_alliance'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Battle Countdown<br/><span>Displays the castle siege battle countdown.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_6', $cfg['show_battle_countdown'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Castle Information<br/><span>Displays the castle information.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_7', $cfg['show_castle_information'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Current Stage<br/><span>Displays the current castle siege stage.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_8', $cfg['show_current_stage'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Next Stage<br/><span>Displays the next castle siege stage.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_9', $cfg['show_next_stage'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Battle Duration<br/><span>Displays the castle siege battle duration. Duration of battle is calculated according to your castle siege schedule configurations.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_10', $cfg['show_battle_duration'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Registered Guilds<br/><span>Displays the registered guilds and alliances.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_11', $cfg['show_registered_guilds'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Schedule<br/><span>Displays the full castle siege schedule.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_12', $cfg['show_schedule'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Schedule PHP Date Format<br/><span>Documentation:<br /><a href="https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters" target="_blank">https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters</a></span></th>
			<td>
				<label>
					<input class="form-control" type="text" name="setting_13" value="<?php echo $cfg['schedule_date_format']; ?>"/>
				</label>
			</td>
		</tr>
		<tr>
			<th>Show Widget<br/><span>Displays the castle siege information in your theme's sidebar/header.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_14', $cfg['show_widget'], 'Enabled', 'Disabled'); ?>
			</td>
		</tr>
	</table>
	
	<h3>Schedule</h3>
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>Stage</th>
				<th>Start Day</th>
				<th>Start Time</th>
				<th>End Day</th>
				<th>End Time</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($cfg['stages'] as $stageIndex => $stageData) {
			echo '<tr>';
				echo '<td>'.lang($stageData['title']).'</td>';
				echo '<td><select name="setting_stage_startday[]" class="form-control">'.weekDaySelectOptions($stageData['start_day']).'</select></td>';
				echo '<td><input class="form-control" type="text" name="setting_stage_starttime[]" value="'.$stageData['start_time'].'"/></td>';
				echo '<td><select name="setting_stage_endday[]" class="form-control">'.weekDaySelectOptions($stageData['end_day']).'</select></td>';
				echo '<td><input class="form-control" type="text" name="setting_stage_endtime[]" value="'.$stageData['end_time'].'"/></td>';
			echo '</tr>';
		}
		?>
		</tbody>
	</table>
	
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>