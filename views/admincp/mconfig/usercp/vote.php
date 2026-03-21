<?php
echo '<h2>Vote and Reward Settings</h2>';
$voteConfigUrl = admincp_base('modules_manager&config=vote');
?>
<form action="<?php echo $voteConfigUrl; ?>" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the vote module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Save Vote Logs<br/><span>If enabled, every vote will be permanently logged in a database table.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('vote_save_logs'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Credit Configuration<br/><span></span></th>
			<td>
				<?php echo $voteCreditConfigSelect ?? ''; ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>

<hr>
<h3>Manage Vote Sites</h3>
<?php
$votesiteList = $voteSiteList ?? null;
if(is_array($votesiteList)) {
	echo '<table class="table table-striped table-bordered table-hover">';
	echo '<tr>';
	echo '<th>Title</th>';
	echo '<th>Link (full url including http)</th>';
	echo '<th>Reward</th>';
	echo '<th>Vote Every</th>';
	echo '<th></th>';
	echo '</tr>';
	
	foreach($votesiteList as $thisVoteSite) {
		echo '<tr>';
		echo '<td>'.$thisVoteSite['votesite_title'].'</td>';
		echo '<td>'.$thisVoteSite['votesite_link'].'</td>';
		echo '<td>'.$thisVoteSite['votesite_reward'].' credit(s)</td>';
		echo '<td>'.$thisVoteSite['votesite_time'].' hour(s)</td>';
		echo '<td><a href="'.admincp_base('modules_manager&config=vote&deletesite='.$thisVoteSite['votesite_id']).'" class="btn btn-block"><i class="fa fa-remove"></i></a></td>';
		echo '</tr>';
	}
} else {
	echo '<h4>Add Voting Site</h4>';
	echo '<table class="table table-striped table-bordered table-hover">';
	echo '<tr>';
	echo '<th>Title</th>';
	echo '<th>Link (full url including http)</th>';
	echo '<th>Reward</th>';
	echo '<th>Vote Every</th>';
	echo '<th></th>';
	echo '</tr>';
}
echo '<form action="'.$voteConfigUrl.'" method="post">';
echo '<tr>';
echo '<td><input name="votesite_add_title" class="form-control" type="text"/></td>';
echo '<td><input name="votesite_add_link" class="form-control" type="text"/></td>';
echo '<td><input name="votesite_add_reward" class="form-control" type="text"/> credit(s)</td>';
echo '<td><input name="votesite_add_time" class="form-control" type="text"/> hour(s)</td>';
echo '<td><input type="submit" name="votesite_add_submit" class="btn btn-success" value="Add!"/></td>';
echo '</tr>';
echo '</form>';
echo '</table>';

?>