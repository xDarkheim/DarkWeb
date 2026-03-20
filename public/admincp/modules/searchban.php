<?php
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Search Ban</h1>
<div class="acp-card mb-4">
	<form class="d-flex gap-2" role="form" method="post">
		<label>
			<input type="text" class="form-control" name="search_request" placeholder="Account username" style="max-width:300px;"/>
		</label>
		<button type="submit" class="btn btn-primary" name="search_ban" value="ok"><i class="bi bi-search me-1"></i>Search</button>
	</form>
</div>
<?php
$database = $dB;
if(isset($_POST['search_request'])) {
	try {
		$searchRequest = '%'.$_POST['search_request'].'%';
		$search = $database->query_fetch("SELECT TOP 25 * FROM ".Ban_Log." WHERE account_id LIKE ?", array($searchRequest));
		if(!is_array($search)) {
			throw new RuntimeException("No results found.");
		}
		echo '<div class="acp-card"><div class="acp-card-header">Results for: <strong>'.$_POST['search_request'].'</strong></div>';
		echo '<table class="table table-hover mb-0"><thead><tr><th>Account</th><th>Banned By</th><th>Type</th><th>Date</th><th>Days</th><th></th></tr></thead><tbody>';
		foreach($search as $ban) {
			$banType = ($ban['ban_type'] == "temporal" ? '<span class="label label-warning">Temporal</span>' : '<span class="label label-danger">Permanent</span>');
			echo '<tr>';
			echo '<td><a href="'.admincp_base("accountinfo&id=".$common->retrieveUserID($ban['account_id'])).'">'.$ban['account_id'].'</a></td>';
			echo '<td>'.$ban['banned_by'].'</td>';
			echo '<td>'.$banType.'</td>';
			echo '<td>'.date("Y-m-d H:i", $ban['ban_date']).'</td>';
			echo '<td>'.$ban['ban_days'].'</td>';
			echo '<td class="text-end"><a href="'.admincp_base("latestbans&liftban=".$ban['id']).'" class="btn btn-sm btn-danger">Lift Ban</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table></div>';
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
?>
