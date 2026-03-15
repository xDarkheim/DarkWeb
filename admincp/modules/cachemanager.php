<?php
use Darkheim\Infrastructure\Cache\CacheManager;
echo '<h1 class="page-header"><i class="bi bi-arrow-clockwise me-2"></i>Cache Manager</h1>';
try {
	$cacheManager = new CacheManager();
	$cacheFileList = $cacheManager->getCacheFileListAndData();
	if(!is_array($cacheFileList)) {
		throw new RuntimeException('No cache files found.');
	}

	if(isset($_GET['action'])) {
		try {
			switch($_GET['action']) {
				case 'clear': $cacheManager->_file = $_GET['file']; $cacheManager->clearCacheData(); break;
				case 'deleteguildcache': $cacheManager->deleteGuildCache(); break;
				case 'deleteplayercache': $cacheManager->deletePlayerCache(); break;
				default: throw new RuntimeException('Invalid action.');
			}
			redirect(3, admincp_base('cachemanager'));
		} catch(Exception $ex) { message('error', $ex->getMessage()); }
	}

	// Main cache files table
	echo '<div class="acp-card mb-4"><div class="acp-card-header">Cache Files</div>';
	echo '<table class="table table-hover mb-0"><thead><tr><th>File</th><th>Size</th><th>Last Modified</th><th>Writable</th><th></th></tr></thead><tbody>';
	foreach($cacheFileList as $row) {
		$writable = $row['write'] == 1 ? '<span class="badge-status on">Yes</span>' : '<span class="badge-status off">Not Writable</span>';
		echo '<tr>';
		echo '<td><code>'.$row['file'].'</code></td>';
		echo '<td>'.readableFileSize($row['size']).'</td>';
		echo '<td>'.$row['edit'].'</td>';
		echo '<td>'.$writable.'</td>';
		echo '<td class="text-end"><a href="'.admincp_base('cachemanager&action=clear&file='.urlencode($row['file'])).'" class="btn btn-sm btn-danger">Clear</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';

	// Profile caches
	echo '<div class="row g-3">';
	foreach(['guild'=>'Guild Profiles','player'=>'Player Profiles'] as $type => $label) {
		$profileCache = $cacheManager->getCacheFileListAndData($type);
		$count = is_array($profileCache) ? count($profileCache) : 0;
		$size  = 0;
		if(is_array($profileCache)) {
			foreach ($profileCache as $f) {
				$size += $f['size'];
			}
		}
		$action = $type === 'guild' ? 'deleteguildcache' : 'deleteplayercache';
		echo '<div class="col-md-6"><div class="acp-card"><div class="acp-card-header">'.$label.'</div>';
		echo '<table class="dash-table"><tr><td>Cache Files</td><td>'.number_format($count).'</td></tr>';
		echo '<tr><td>Total Size</td><td>'.readableFileSize($size).'</td></tr></table>';
		if($count > 0) {
			echo '<div class="p-3"><a href="'.admincp_base(
					'cachemanager&action='.$action
				).'" class="btn btn-sm btn-danger">Delete '.$label
				.' Cache</a></div>';
		}
		echo '</div></div>';
	}
	echo '</div>';

} catch(Exception $ex) { message('error', $ex->getMessage()); }
