<h2>Downloads Settings</h2>
<?php
$downloadTypes = array (
	1 => 'Client',
	2 => 'Patch',
	3 => 'Tool'
);

function downloadTypesSelect($downloadTypes,$selected=null): void {
	foreach($downloadTypes as $key => $typeOPTION) {
		$optionValue = downloadEsc($key);
		$optionLabel = downloadEsc($typeOPTION);
		if(check_value($selected) && (string)$key === (string)$selected) {
			echo '<option value="'.$optionValue.'" selected="selected">'.$optionLabel.'</option>';
		} else {
			echo '<option value="'.$optionValue.'">'.$optionLabel.'</option>';
		}
	}
}

function downloadEsc($value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Status<br/><span>Enable/disable the downloads module.</span></th>
			<td>
				<?php enabledisableCheckboxes('setting_1',mconfig('active'),'Enabled','Disabled'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Client Downloads<br/></th>
			<td>
				<?php enabledisableCheckboxes('setting_2',mconfig('show_client_downloads'),'Yes','No'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Patches Downloads<br/></th>
			<td>
				<?php enabledisableCheckboxes('setting_3',mconfig('show_patch_downloads'),'Yes','No'); ?>
			</td>
		</tr>
		<tr>
			<th>Show Tools Downloads<br/></th>
			<td>
				<?php enabledisableCheckboxes('setting_4',mconfig('show_tool_downloads'),'Yes','No'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
		</tr>
	</table>
</form>

<hr>
<h3>Manage Downloads</h3>
<?php
$downloads = $downloadsList ?? null;
if(is_array($downloads)) {
$downloadsConfigUrl = downloadEsc(admincp_base('modules_manager&config=downloads'));
echo '
<table class="table table-striped table-bordered table-hover">
	<tr>
		<th>Title</th>
		<th>Description</th>
		<th>Link</th>
		<th>Size (MB)</th>
		<th>Type</th>
		<th></th>
	</tr>';
	
	foreach($downloads as $thisDownload) {
	$downloadId = (int)$thisDownload['download_id'];
	$downloadTitle = downloadEsc($thisDownload['download_title'] ?? '');
	$downloadDescription = downloadEsc($thisDownload['download_description'] ?? '');
	$downloadLink = downloadEsc($thisDownload['download_link'] ?? '');
	$downloadSize = downloadEsc(round((float)($thisDownload['download_size'] ?? 0), 2));
	$deleteLink = downloadEsc(admincp_base('modules_manager&config=downloads&deletelink='.$downloadId));
	echo '
	<form action="'.$downloadsConfigUrl.'" method="post">
	<input type="hidden" name="downloads_edit_id" value="'.$downloadId.'"/>
	<tr>
		<td><input type="text" name="downloads_edit_title" class="form-control" value="'.$downloadTitle.'"/></td>
		<td><input type="text" name="downloads_edit_desc" class="form-control" value="'.$downloadDescription.'"/></td>
		<td><input type="text" name="downloads_edit_link" class="form-control" value="'.$downloadLink.'"/></td>
		<td><input type="text" name="downloads_edit_size" class="form-control" value="'.$downloadSize.'"/></td>
		<td>
			<select name="downloads_edit_type" class="form-control">';
				downloadTypesSelect($downloadTypes, $thisDownload['download_type']);
		echo '
			</select>
		</td>
		<td>
		<input type="submit" class="btn btn-success" name="downloads_edit_submit" value="Save"/>
		<a href="'.$deleteLink.'" class="btn btn-danger">Remove</a>
		</td>
	</tr>
	</form>';
	}
	
echo '</table>';
} else {
	message('error','You have not added any download link.');
}
?>

<hr>
<h3>Add Download</h3>
<form action="<?php echo downloadEsc(admincp_base('modules_manager&config=downloads')); ?>" method="post">
<table class="table table-striped table-bordered table-hover">
	<tr>
		<th>Title</th>
		<th>Description</th>
		<th>Link</th>
		<th>Size (MB)</th>
		<th>Type</th>
	</tr>
	<tr>
		<td><label>
				<input type="text" name="downloads_add_title" class="form-control"/>
			</label></td>
		<td><label>
				<input type="text" name="downloads_add_desc" class="form-control"/>
			</label></td>
		<td><label>
				<input type="text" name="downloads_add_link" class="form-control"/>
			</label></td>
		<td><label>
				<input type="text" name="downloads_add_size" class="form-control"/>
			</label></td>
		<td>
			<label>
				<select name="downloads_add_type" class="form-control">
					<?php downloadTypesSelect($downloadTypes); ?>
				</select>
			</label>
		</td>
	</tr>
	<tr>
		<td colspan="5"><input type="submit" name="downloads_add_submit" class="btn btn-success" value="Add Download"/></td>
	</tr>
</table>
</form>