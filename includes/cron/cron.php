<?php
// access
define('access', 'cron');

// Load Darkheim
if(!@include_once(str_replace('\\','/', dirname(__FILE__, 2)).'/' . 'cms.php')) die('Failed to load Darkheim CMS.');

// Cron List
$cronList = getCronList();
if(!is_array($cronList)) die();

// Encapsulation
function loadCronFile($path): void
{
	include($path);
}

// Execute Crons
foreach($cronList as $cron) {
	if($cron['cron_status'] != 1) continue;
	if(!check_value($cron['cron_last_run'])) {
		$lastRun = $cron['cron_run_time'];
	} else {
		$lastRun = $cron['cron_last_run']+$cron['cron_run_time'];
	}
	if(time() > $lastRun) {
		$filePath = __PATH_CRON__.$cron['cron_file_run'];
		if(file_exists($filePath)) {
			loadCronFile($filePath);
		}
	}
}