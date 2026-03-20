<?php
use Darkheim\Application\Credits\CreditSystem;
echo '<h1 class="page-header"><i class="bi bi-sliders me-2"></i>Credit Configurations</h1>';
$creditSystem = new CreditSystem();

// NEW CONFIG
if(isset($_POST['new_submit'])) {
	try {
		$creditSystem->setConfigTitle($_POST['new_title']);
		$creditSystem->setConfigDatabase($_POST['new_database']);
		$creditSystem->setConfigTable($_POST['new_table']);
		$creditSystem->setConfigCreditsColumn($_POST['new_credits_column']);
		$creditSystem->setConfigUserColumn($_POST['new_user_column']);
		$creditSystem->setConfigUserColumnId($_POST['new_user_column_id']);
		$creditSystem->_configCheckOnline = $_POST['new_checkonline'];
		$creditSystem->_configDisplay = $_POST['new_display'];
		$creditSystem->saveConfig();
		message('success','Configuration saved.');
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
// EDIT CONFIG
if(isset($_POST['edit_submit'])) {
	try {
		$creditSystem->setConfigId($_POST['edit_id']);
		$creditSystem->setConfigTitle($_POST['edit_title']);
		$creditSystem->setConfigDatabase($_POST['edit_database']);
		$creditSystem->setConfigTable($_POST['edit_table']);
		$creditSystem->setConfigCreditsColumn($_POST['edit_credits_column']);
		$creditSystem->setConfigUserColumn($_POST['edit_user_column']);
		$creditSystem->setConfigUserColumnId($_POST['edit_user_column_id']);
		$creditSystem->_configCheckOnline = $_POST['edit_checkonline'];
		$creditSystem->_configDisplay = $_POST['edit_display'];
		$creditSystem->editConfig();
		message('success','Configuration updated.');
	} catch(Exception $ex) { message('error', $ex->getMessage()); }
}
// DELETE CONFIG
if(isset($_GET['delete'])) {
	try { $creditSystem->setConfigId($_GET['delete']); $creditSystem->deleteConfig(); }
	catch(Exception $ex) { message('error', $ex->getMessage()); }
}

// Helper to render a radio group
function _radios($name, $options, $checked): void {
	echo '<div class="d-flex flex-wrap gap-3 mt-1">';
	foreach($options as $val => $lbl) {
		echo '<label style="color:#aaa;font-size:13px;"><input type="radio" name="'.$name.'" value="'.$val.'" '.($checked==$val?'checked':'').'> '.$lbl.'</label>';
	}
	echo '</div>';
}

echo '<div class="row g-3">';

// ── LEFT: Form ──────────────────────────────────────────────────
echo '<div class="col-lg-4"><div class="acp-card">';

if(!isset($_GET['edit'])) {
	echo '<div class="acp-card-header">New Configuration</div><div class="p-3">';
	echo '<form method="post">';
	echo '<div class="form-group"><label>Title</label><input type="text" class="form-control" name="new_title" required></div>';
	echo '<div class="form-group"><label>Database</label>';
	_radios('new_database', [
		'MuOnline' => config('SQL_DB_NAME',true),
	], 'MuOnline');
	echo '</div>';
	echo '<div class="form-group"><label>Table</label><input type="text" class="form-control" name="new_table" required></div>';
	echo '<div class="form-group"><label>Credits Column</label><input type="text" class="form-control" name="new_credits_column" required></div>';
	echo '<div class="form-group"><label>User Column</label><input type="text" class="form-control" name="new_user_column" required></div>';
	echo '<div class="form-group"><label>User Identifier</label>';
	_radios('new_user_column_id', ['userid'=>'User ID','username'=>'Username','email'=>'Email','character'=>'Character'], 'userid');
	echo '</div>';
	echo '<div class="form-group"><label>Check Online Status</label>';
	_radios('new_checkonline', ['1'=>'Yes','0'=>'No'], '1');
	echo '</div>';
	echo '<div class="form-group"><label>Display in My Account</label>';
	_radios('new_display', ['1'=>'Yes','0'=>'No'], '1');
	echo '</div>';
	echo '<button type="submit" name="new_submit" value="1" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Save</button>';
} else {
	$creditSystem->setConfigId($_GET['edit']);
	$cd = $creditSystem->showConfigs(true);
	echo '<div class="acp-card-header">Edit Configuration</div><div class="p-3">';
	echo '<form method="post">';
	echo '<input type="hidden" name="edit_id" value="'.$cd['config_id'].'">';
	echo '<div class="form-group"><label>Title</label><input type="text" class="form-control" name="edit_title" value="'.htmlspecialchars($cd['config_title']).'" required></div>';
	echo '<div class="form-group"><label>Database</label>';
	_radios('edit_database', ['MuOnline'=>config('SQL_DB_NAME',true)], $cd['config_database']);
	echo '</div>';
	echo '<div class="form-group"><label>Table</label><input type="text" class="form-control" name="edit_table" value="'.htmlspecialchars($cd['config_table']).'" required></div>';
	echo '<div class="form-group"><label>Credits Column</label><input type="text" class="form-control" name="edit_credits_column" value="'.htmlspecialchars($cd['config_credits_col']).'" required></div>';
	echo '<div class="form-group"><label>User Column</label><input type="text" class="form-control" name="edit_user_column" value="'.htmlspecialchars($cd['config_user_col']).'" required></div>';
	echo '<div class="form-group"><label>User Identifier</label>';
	_radios('edit_user_column_id', ['userid'=>'User ID','username'=>'Username','email'=>'Email','character'=>'Character'], $cd['config_user_col_id']);
	echo '</div>';
	echo '<div class="form-group"><label>Check Online Status</label>';
	_radios('edit_checkonline', ['1'=>'Yes','0'=>'No'], $cd['config_checkonline']);
	echo '</div>';
	echo '<div class="form-group"><label>Display in My Account</label>';
	_radios('edit_display', ['1'=>'Yes','0'=>'No'], $cd['config_display']);
	echo '</div>';
	echo '<button type="submit" name="edit_submit" value="1" class="btn btn-warning w-100"><i class="bi bi-save me-1"></i>Update</button>';
}
echo '</form>';
echo '</div></div></div>';

// ── RIGHT: Config List ──────────────────────────────────────────
echo '<div class="col-lg-8">';
$cofigsList = $creditSystem->showConfigs();
if(is_array($cofigsList)) {
	foreach($cofigsList as $data) {
		$dbDisplay  = config('SQL_DB_NAME',true);
		$online     = $data['config_checkonline'] ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">No</span>';
		$display    = $data['config_display']     ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">No</span>';
		echo '<div class="acp-card mb-3">';
		echo '<div class="acp-card-header">';
		echo '<span>'.$data['config_title'].'</span>';
		echo '<div class="d-flex gap-1">';
		echo '<a href="'.admincp_base("creditsconfigs&edit=".$data['config_id']).'" class="btn btn-sm btn-default"><i class="bi bi-pencil"></i></a>';
		echo '<a href="'.admincp_base("creditsconfigs&delete=".$data['config_id']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete?\')"><i class="bi bi-trash"></i></a>';
		echo '</div></div>';
		echo '<table class="dash-table">';
		echo '<tr><td>Config ID</td><td>'.$data['config_id'].'</td></tr>';
		echo '<tr><td>Database</td><td>'.$dbDisplay.'</td></tr>';
		echo '<tr><td>Table</td><td><code>'.$data['config_table'].'</code></td></tr>';
		echo '<tr><td>Credits Column</td><td><code>'.$data['config_credits_col'].'</code></td></tr>';
		echo '<tr><td>User Column</td><td><code>'.$data['config_user_col'].'</code></td></tr>';
		echo '<tr><td>User Identifier</td><td>'.$data['config_user_col_id'].'</td></tr>';
		echo '<tr><td>Online Check</td><td>'.$online.'</td></tr>';
		echo '<tr><td>Display in Account</td><td>'.$display.'</td></tr>';
		echo '</table></div>';
	}
} else { echo '<div class="p-3">'; inline_message('info', 'No configurations created yet.'); echo '</div>'; }
echo '</div>';
echo '</div>';