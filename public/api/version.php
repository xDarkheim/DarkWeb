<?php
use Darkheim\Infrastructure\Routing\Handler;
define('access', 'api');
header('Content-Type: application/json');

try {
	
	// Load Darkheim
	if(!@include(rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/') . '/includes/bootstrap/boot.php')) {
		throw new RuntimeException('Could not load Darkheim.');
	}
	
	// Apache Version
	if(!function_exists('apache_get_version')) {
		function apache_get_version() {
			if(!isset($_SERVER['SERVER_SOFTWARE']) || $_SERVER['SERVER_SOFTWARE']
				=== ''
			) {
				return '';
			}
			return $_SERVER['SERVER_SOFTWARE'];
		}
	}
	
	// Listener
	$handler = new Handler();
	
	// Response
	http_response_code(200);
	echo json_encode(
		array(
			'code' => 200,
			'apache' => apache_get_version(),
			'php' => PHP_VERSION,
			'darkheim' => __CMS_VERSION__
		),
		JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
	);

} catch(Exception $ex) {
	http_response_code(500);
	echo json_encode(array('code' => 500, 'error' => $ex->getMessage()),
		JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}