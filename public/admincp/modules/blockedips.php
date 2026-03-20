<?php
?>
<h1 class="page-header"><i class="bi bi-slash-circle me-2"></i>Block IP Address <small style="font-size:13px;color:#666;">(web)</small></h1>
<div class="acp-card mb-4">
	<form class="d-flex gap-2" role="form" method="post">
		<label>
			<input type="text" class="form-control" name="ip_address" placeholder="0.0.0.0" style="max-width:300px;"/>
		</label>
		<button type="submit" class="btn btn-danger" name="submit_block" value="ok"><i class="bi bi-slash-circle me-1"></i>Block IP</button>
	</form>
</div>
<?php
if(isset($_POST['submit_block'], $_POST['ip_address'])) {
	if($common->blockIpAddress($_POST['ip_address'], $_SESSION['username'])) {
		message('success','IP address blocked.');
	} else {
		message('error','Error blocking IP.');
	}
}
if(isset($_GET['unblock'])) {
	if($common->unblockIpAddress($_REQUEST['unblock'])) {
		message('success','IP address unblocked.');
	} else {
		message('error','Error unblocking IP.');
	}
}
$blockedIPs = $common->retrieveBlockedIPs();
if(is_array($blockedIPs)) {
	echo '<div class="acp-card"><div class="acp-card-header">Blocked IPs</div>';
	echo '<table id="blocked_ips" class="table table-hover mb-0"><thead><tr><th>IP Address</th><th>Blocked By</th><th>Date</th><th></th></tr></thead><tbody>';
	foreach($blockedIPs as $thisIP) {
		echo '<tr>';
		echo '<td><code>'.$thisIP['block_ip'].'</code></td>';
		echo '<td>'.$thisIP['block_by'].'</td>';
		echo '<td>'.date("Y-m-d H:i", $thisIP['block_date']).'</td>';
		echo '<td class="text-end"><a href="'.admincp_base($_REQUEST['module']."&unblock=".$thisIP['id']).'" class="btn btn-sm btn-danger">Unblock</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

?>