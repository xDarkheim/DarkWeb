<?php
use Darkheim\Infrastructure\Database\Connection;
/**
 * DarkCore
 *
 * @package     DarkCore
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright   2026 Dmytro Hovenko (Darkheim)
 * @license     MIT
 * @link        https://darkheim.net
 *
 * Admin Control Panel helpers — module URLs, downloads, cron, settings utilities.
 */

function admincp_base($module=""): string {
	if(check_value($module)) {
		return __PATH_ADMINCP_HOME__."?module=".$module;
	}
	return __PATH_ADMINCP_HOME__;
}

function enabledisableCheckboxes($name,$checked,$e_txt,$d_txt): void {
	echo '<div class="radio">';
	echo '<label class="radio">';
	if($checked == 1) {
		echo '<input type="radio" name="'.$name.'" value="1" checked>';
	} else {
		echo '<input type="radio" name="'.$name.'" value="1">';
	}
	echo $e_txt;
	echo '</label>';
	echo '<label class="radio">';
	if($checked == 0) {
		echo '<input type="radio" name="'.$name.'" value="0" checked>';
	} else {
		echo '<input type="radio" name="'.$name.'" value="0">';
	}
	echo $d_txt;
	echo '</label>';
	echo '</div>';
}


function getDownloadsList() {
	$db = Connection::Database('MuOnline');
	$result = $db->query_fetch("SELECT * FROM ".Downloads." ORDER BY download_type ASC, download_id ASC");
	if(!is_array($result)) {
		return;
	}
	return $result;
}

function addDownload($title, $description='', $link, $size=0, $type=1) {
	$db = Connection::Database('MuOnline');
	if(!check_value($title)) {
		return;
	}
	if(!check_value($link)) {
		return;
	}
	if(!check_value($size)) {
		return;
	}
	if(!check_value($type)) {
		return;
	}
	if(strlen($title) > 100) {
		return;
	}
	if(strlen($description) > 100) {
		return;
	}
	
	$result = $db->query("INSERT INTO ".Downloads." (download_title, download_description, download_link, download_size, download_type) VALUES (?, ?, ?, ?, ?)", array($title, $description, $link, $size, $type));
	if(!$result) {
		return;
	}
	
	@updateDownloadsCache();
	return true;
}

function editDownload($id, $title, $description='', $link, $size=0, $type=1) {
	$db = Connection::Database('MuOnline');
	if(!check_value($id)) {
		return;
	}
	if(!check_value($title)) {
		return;
	}
	if(!check_value($link)) {
		return;
	}
	if(!check_value($size)) {
		return;
	}
	if(!check_value($type)) {
		return;
	}
	if(strlen($title) > 100) {
		return;
	}
	if(strlen($description) > 100) {
		return;
	}
	
	$result = $db->query("UPDATE ".Downloads." SET download_title = ?, download_description = ?, download_link = ?, download_size = ?, download_type = ? WHERE download_id = ?", array($title, $description, $link, $size, $type, $id));
	if(!$result) {
		return;
	}
	
	@updateDownloadsCache();
	return true;
}

function deleteDownload($id) {
	$db = Connection::Database('MuOnline');
	if(!check_value($id)) {
		return;
	}
	$result = $db->query("DELETE FROM ".Downloads." WHERE download_id = ?", array($id));
	if(!$result) {
		return;
	}
	
	@updateDownloadsCache();
	return true;
}

function updateDownloadsCache(): bool {
	$db = Connection::Database('MuOnline');
	$downloadsData = $db->query_fetch("SELECT * FROM ".Downloads." ORDER BY download_type ASC, download_id ASC");
	$cacheData = encodeCache($downloadsData);
	updateCacheFile('downloads.cache', $cacheData);
	return true;
}

function weekDaySelectOptions($selected='Monday'): string {
	$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	$result = '';
	foreach($days as $row) {
		if($selected == $row) {
			$result .= '<option value="'.$row.'" selected>'.$row.'</option>';
		} else {
			$result .= '<option value="'.$row.'">'.$row.'</option>';
		}
	}
	return $result;
}