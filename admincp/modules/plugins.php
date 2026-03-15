<?php
use Darkheim\Infrastructure\Plugins\Plugins;
?>
<h1 class="page-header"><i class="bi bi-plug-fill me-2"></i>Plugin Manager</h1>
<?php
if(!config('plugins_system_enable',true)) {
	inline_message('warning','The plugin system is not enabled. Enable it in Website Settings.');
}
define('PLUGIN_ALLOW_UNINSTALL', true);
$Plugins = new Plugins();
if(isset($_REQUEST['enable'])) {
	$Plugins->updatePluginStatus($_REQUEST['enable'], 1);
}
if(isset($_REQUEST['disable'])) {
	$Plugins->updatePluginStatus($_REQUEST['disable'], 0);
}
if(isset($_REQUEST['uninstall'])) {
	$Plugins->uninstallPlugin($_REQUEST['uninstall']) ? message('success','Plugin uninstalled.') : message('error','Could not uninstall plugin.');
	if(!$Plugins->rebuildPluginsCache()) {
		message('error', 'Could not update plugins cache.');
	}
}
$plugins = $Plugins->retrieveInstalledPlugins();
echo '<div class="mb-3"><a href="'.admincp_base('plugin_install').'" class="btn btn-primary"><i class="bi bi-download me-1"></i>Import Plugin</a></div>';
if(is_array($plugins)) {
	echo '<div class="acp-card"><div class="acp-card-header">Installed Plugins</div>';
	echo '<table class="table table-hover mb-0"><thead><tr><th>Name</th><th>Author</th><th>Version</th><th>Compatibility</th><th>Installed</th><th>Status</th><th></th></tr></thead><tbody>';
	foreach($plugins as $p) {
		$isOn   = $p['status'] == 1;
		$status = $isOn ? '<span class="badge-status on">Enabled</span>' : '<span class="badge-status off">Disabled</span>';
		$toggle = $isOn
			? '<a href="'.admincp_base('plugins&disable='.$p['id']).'" class="btn btn-sm btn-default">Disable</a>'
			: '<a href="'.admincp_base('plugins&enable='.$p['id']).'" class="btn btn-sm btn-success">Enable</a>';
		$uninstall = PLUGIN_ALLOW_UNINSTALL ? '<a href="'.admincp_base('plugins&uninstall='.$p['id']).'" class="btn btn-sm btn-danger ms-1" onclick="return confirm(\'Uninstall?\')">Uninstall</a>' : '';
		echo '<tr>';
		echo '<td><strong>'.$p['name'].'</strong></td>';
		echo '<td>'.$p['author'].'</td>';
		echo '<td>'.$p['version'].'</td>';
		echo '<td>'.implode(', ',explode('|',$p['compatibility'])).'</td>';
		echo '<td>'.date("Y-m-d",$p['install_date']).'</td>';
		echo '<td>'.$status.'</td>';
		echo '<td class="text-end">'.$toggle.$uninstall.'</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
} else {
	inline_message('info','No plugins installed yet.');
}
?>