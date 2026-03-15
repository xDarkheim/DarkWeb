<?php
// check the installation directory — only warn if not blocked by .htaccess
$_installHtaccess = __ROOT_DIR__ . 'install/.htaccess';
if(file_exists(__ROOT_DIR__ . 'install/') && !(file_exists($_installHtaccess) && strpos(file_get_contents($_installHtaccess), 'Require all denied') !== false)) {
	// Toast notification
	message('warning', 'Install directory still exists — rename or delete it.', 'WARNING');
	// Persistent inline banner on the dashboard
	inline_message('warning', 'Your install/ directory still exists. It is strongly recommended that you rename or delete it before going live.');
}

$database = $dB;

try { $totalAccounts   = $database->query_fetch_single("SELECT COUNT(*) as result FROM MEMB_INFO"); } catch(Exception $e) { $totalAccounts = ['result'=>'?']; }
try { $bannedAccounts  = $database->query_fetch_single("SELECT COUNT(*) as result FROM MEMB_INFO WHERE bloc_code = 1"); } catch(Exception $e) { $bannedAccounts = ['result'=>'?']; }
try { $totalCharacters = $dB->query_fetch_single("SELECT COUNT(*) as result FROM Character"); } catch(Exception $e) { $totalCharacters = ['result'=>'?']; }
try { $scheduledTasks  = $database->query_fetch_single("SELECT COUNT(*) as result FROM ".Cron); } catch(Exception $e) { $scheduledTasks = ['result'=>'?']; }

$pluginStatus = config('plugins_system_enable', true) ? '<span class="badge-status on">Enabled</span>' : '<span class="badge-status off">Disabled</span>';
?>

<h1 class="page-header"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>

<!-- Stat Cards -->
<div class="dash-cards">

	<div class="dash-card">
		<div class="dash-card-icon" style="background:#1a2a1a;">
			<i class="bi bi-people-fill" style="color:#4caf50;"></i>
		</div>
		<div class="dash-card-body">
			<div class="dash-card-value"><?php echo number_format($totalAccounts['result']); ?></div>
			<div class="dash-card-label">Registered Accounts</div>
		</div>
	</div>

	<div class="dash-card">
		<div class="dash-card-icon" style="background:#2a1a1a;">
			<i class="bi bi-person-fill-slash" style="color:#ef5350;"></i>
		</div>
		<div class="dash-card-body">
			<div class="dash-card-value"><?php echo number_format($bannedAccounts['result']); ?></div>
			<div class="dash-card-label">Banned Accounts</div>
		</div>
	</div>

	<div class="dash-card">
		<div class="dash-card-icon" style="background:#1a1a2a;">
			<i class="bi bi-controller" style="color:#42a5f5;"></i>
		</div>
		<div class="dash-card-body">
			<div class="dash-card-value"><?php echo number_format($totalCharacters['result']); ?></div>
			<div class="dash-card-label">Characters</div>
		</div>
	</div>

	<div class="dash-card">
		<div class="dash-card-icon" style="background:#1a2200;">
			<i class="bi bi-list-task" style="color:#c8a96e;"></i>
		</div>
		<div class="dash-card-body">
			<div class="dash-card-value"><?php echo number_format($scheduledTasks['result']); ?></div>
			<div class="dash-card-label">Cron Tasks</div>
		</div>
	</div>

</div>

<!-- Info Row -->
<div class="dash-row">

	<!-- System Info -->
	<div class="dash-block">
		<div class="dash-block-header"><i class="bi bi-cpu me-2"></i>System</div>
		<table class="dash-table">
			<tr><td>Operating System</td><td><?php echo PHP_OS; ?></td></tr>
			<tr><td>PHP Version</td><td><?php echo PHP_VERSION; ?></td></tr>
			<tr><td>CMS Version</td><td><?php echo __CMS_VERSION__; ?></td></tr>
			<tr><td>Server Time</td><td><?php echo date("Y-m-d H:i"); ?></td></tr>
			<tr><td>Plugin System</td><td><?php echo $pluginStatus; ?></td></tr>
		</table>
	</div>

	<!-- Quick Actions -->
	<div class="dash-block">
		<div class="dash-block-header"><i class="bi bi-lightning-fill me-2"></i>Quick Actions</div>
		<div class="dash-actions">
			<a href="<?php echo admincp_base('addnews'); ?>" class="dash-action-btn">
				<i class="bi bi-newspaper"></i> Publish News
			</a>
			<a href="<?php echo admincp_base('searchaccount'); ?>" class="dash-action-btn">
				<i class="bi bi-search"></i> Search Account
			</a>
			<a href="<?php echo admincp_base('banaccount'); ?>" class="dash-action-btn">
				<i class="bi bi-slash-circle"></i> Ban Account
			</a>
			<a href="<?php echo admincp_base('creditsmanager'); ?>" class="dash-action-btn">
				<i class="bi bi-cash-coin"></i> Credits Manager
			</a>
			<a href="<?php echo admincp_base('cachemanager'); ?>" class="dash-action-btn">
				<i class="bi bi-arrow-clockwise"></i> Clear Cache
			</a>
			<a href="<?php echo admincp_base('website_settings'); ?>" class="dash-action-btn">
				<i class="bi bi-gear"></i> Settings
			</a>
		</div>
	</div>

	<!-- Admins -->
	<div class="dash-block">
		<div class="dash-block-header"><i class="bi bi-shield-fill me-2"></i>Administrators</div>
		<table class="dash-table">
			<?php foreach(config('admins', true) as $adminName => $adminLevel): ?>
			<tr>
				<td><i class="bi bi-person-fill me-1" style="color:var(--accent);"></i><?php echo htmlspecialchars($adminName); ?></td>
				<td><span class="badge-level">Level <?php echo $adminLevel; ?></span></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>

</div>
