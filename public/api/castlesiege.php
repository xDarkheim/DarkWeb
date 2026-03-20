<?php
use Darkheim\Application\CastleSiege\CastleSiege;
define('access', 'api');

try {
	
	if(!@include(rtrim(str_replace('\\','/', dirname(__DIR__, 2)), '/') . '/includes/bootstrap/boot.php')) {
		throw new RuntimeException('Could not load CMS.');
	}

	$castleSiege = new CastleSiege();
	$siegeData = $castleSiege->siegeData();
	if(!is_array($siegeData)) {
		throw new RuntimeException(lang('error_103'));
	}
	
	http_response_code(200);
	echo json_encode(array(
		'TimeLeft' => $siegeData['warfare_stage_timeleft']
	), JSON_THROW_ON_ERROR);

} catch(Exception $ex) {
	http_response_code(500);
	echo json_encode(array('code' => 500, 'error' => $ex->getMessage()),
		JSON_THROW_ON_ERROR);
}