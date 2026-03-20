<?php
use Darkheim\Domain\Validator;
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Search Account</h1>
<div class="acp-card mb-4">
	<form class="d-flex gap-2" role="form" method="post">
		<label>
			<input type="text" class="form-control" name="search_request" placeholder="Account username" style="max-width:300px;"/>
		</label>
		<button type="submit" class="btn btn-primary" name="search_account" value="ok"><i class="bi bi-search me-1"></i>Search</button>
	</form>
</div>
<?php
if(isset($_POST['search_account'], $_POST['search_request'])) {
	try {
		if(!Validator::Length($_POST['search_request'], 11, 2)) {
			throw new RuntimeException("The username can be 3 to 10 characters long.");
		}
		$searchdb = $dB;
		$searchRequest = '%'.$_POST['search_request'].'%';
		$searchResults = $searchdb->query_fetch("SELECT "._CLMN_MEMBID_.", "._CLMN_USERNM_." FROM "._TBL_MI_." WHERE "._CLMN_USERNM_." LIKE ?", array($searchRequest));
		if(!$searchResults) {
			throw new RuntimeException("No results found.");
		}
		echo '<div class="acp-card"><div class="acp-card-header">Results for: <strong>'.$_POST['search_request'].'</strong></div>';
		echo '<table class="table table-hover mb-0"><thead><tr><th>Username</th><th></th></tr></thead><tbody>';
		foreach($searchResults as $account) {
			echo '<tr><td>'.$account[_CLMN_USERNM_].'</td><td class="text-end"><a href="'.admincp_base("accountinfo&id=".$account[_CLMN_MEMBID_]).'" class="btn btn-sm btn-default">Account Info</a></td></tr>';
		}
		echo '</tbody></table></div>';
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
?>