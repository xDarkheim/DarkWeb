<?php
use Darkheim\Infrastructure\Cron\CronManager;
// access
define('access', 'api');

try {
	
	// Darkheim CMS
	if(!@include(rtrim(str_replace('\\','/', dirname(__DIR__)), '/') . '/includes/cms.php')) {
		throw new RuntimeException('Could not load Darkheim CMS.');
	}
	
	// Check Status
	if(!config('cron_api', true)) {
		throw new RuntimeException('Cron api disabled.');
	}
	if(!check_value(config('cron_api_key',true))) {
		throw new RuntimeException('Configured cron api key is not valid.');
	}
	
	// Check Key
	if(!isset($_REQUEST['key'])) {
		throw new RuntimeException('Key is not valid.');
	}
	if($_REQUEST['key'] != config('cron_api_key',true)) {
		throw new RuntimeException('Key is not valid.');
	}
	
	// Cron Manager
	$cronManager = new CronManager();
	$executedCrons = array();
	
	// Cron List
	$cronList = $cronManager->getCronList();
	if(!is_array($cronList)) {
		throw new RuntimeException('There are no crons.');
	}
	
	// Encapsulation
	function loadCronFile($path): void {
		include($path);
	}
	
	if(!isset($_GET['id'])) {
		// Execute All Enabled Crons
		foreach($cronList as $cron) {
			if($cron['cron_status'] != 1) {
				continue;
			}
			if(!check_value($cron['cron_last_run'])) {
				$lastRun = $cron['cron_run_time'];
			} else {
				$lastRun = $cron['cron_last_run']+$cron['cron_run_time'];
			}
			if(time() > $lastRun) {
				$filePath = __PATH_CRON__.$cron['cron_file_run'];
				if(file_exists($filePath)) {
					loadCronFile($filePath);
					$executedCrons[] = $cron['cron_file_run'];
				}
			}
		}
	} else {
		// Execute a single cron (regardless of status and last run)
		$singleCronExecuted = false;
		foreach($cronList as $cron) {
			if($cron['cron_id'] != $_GET['id']) {
				continue;
			}
			$filePath = __PATH_CRON__.$cron['cron_file_run'];
			if(file_exists($filePath)) {
				loadCronFile($filePath);
				$executedCrons[] = $cron['cron_file_run'];
				$singleCronExecuted = true;
			}
		}
		if(!$singleCronExecuted) {
			throw new RuntimeException('The provided cron id is not valid.');
		}
	}
	
	http_response_code(200);
	header('Content-Type: application/json');
	echo json_encode(
		array(
			'code' => 200,
			'message' => 'Crons successfully executed.',
			'executed' => $executedCrons
		),
		JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
	);
	
} catch(Exception $ex) {
	http_response_code(500);
	header('Content-Type: application/json');
	echo json_encode(array('code' => 500, 'error' => $ex->getMessage()),
		JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}