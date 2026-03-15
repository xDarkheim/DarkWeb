<?php
use Darkheim\Domain\Validator;
?>
<h1 class="page-header"><i class="bi bi-search me-2"></i>Search Character</h1>
<div class="acp-card mb-4">
	<form class="d-flex gap-2" role="form" method="post">
		<label>
			<input type="text" class="form-control" name="search_request" placeholder="Character name" style="max-width:300px;"/>
		</label>
		<button type="submit" class="btn btn-primary" name="search_character" value="ok"><i class="bi bi-search me-1"></i>Search</button>
	</form>
</div>
<?php
if(isset($_POST['search_character'], $_POST['search_request'])) {
	try {
		if(!Validator::Length($_POST['search_request'], 11, 2)) {
			throw new RuntimeException("The name can be 3 to 10 characters long.");
		}
		$searchRequest = '%'.$_POST['search_request'].'%';
		$searchResults = $dB->query_fetch("SELECT TOP 10 "._CLMN_CHR_NAME_.", "._CLMN_CHR_ACCID_." FROM "._TBL_CHR_." WHERE Name LIKE ?", array($searchRequest));
		if(!$searchResults) {
			throw new RuntimeException("No results found.");
		}
		echo '<div class="acp-card"><div class="acp-card-header">Results for: <strong>'.$_POST['search_request'].'</strong></div>';
		echo '<table class="table table-hover mb-0"><thead><tr><th>Character</th><th></th></tr></thead><tbody>';
		foreach($searchResults as $character) {
			echo '<tr><td>'.$character[_CLMN_CHR_NAME_].'</td><td class="text-end">';
			echo '<a href="'.admincp_base("accountinfo&id=".$common->retrieveUserID($character[_CLMN_CHR_ACCID_])).'" class="btn btn-sm btn-default me-1">Account Info</a>';
			echo '<a href="'.admincp_base("editcharacter&name=".$character[_CLMN_CHR_NAME_]).'" class="btn btn-sm btn-warning">Edit Character</a>';
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
?>