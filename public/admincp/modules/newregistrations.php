<?php
echo '<h1 class="page-header"><i class="bi bi-person-plus-fill me-2"></i>New Registrations</h1>';
$db = $dB;
$newRegs = $db->query_fetch("SELECT TOP 200 "._CLMN_MEMBID_.", "._CLMN_USERNM_.", "._CLMN_EMAIL_." FROM "._TBL_MI_." ORDER BY "._CLMN_MEMBID_." DESC");
if(is_array($newRegs)) {
	echo '<div class="acp-card"><div class="acp-card-header">Latest 200 Registrations</div>';
	echo '<table id="new_registrations" class="table table-hover mb-0">';
	echo '<thead><tr><th>ID</th><th>Username</th><th>Email</th><th></th></tr></thead><tbody>';
	foreach($newRegs as $r) {
		echo '<tr>';
		echo '<td>'.$r[_CLMN_MEMBID_].'</td>';
		echo '<td>'.$r[_CLMN_USERNM_].'</td>';
		echo '<td>'.$r[_CLMN_EMAIL_].'</td>';
		echo '<td class="text-end"><a href="'.admincp_base("accountinfo&id=".$r[_CLMN_MEMBID_]).'" class="btn btn-sm btn-default">Account Info</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
} else {
	echo '<div class="p-3">'; inline_message('info', 'No registrations found.'); echo '</div>';
}

