<?php
define('access', 'api');

include('../../includes/bootstrap/boot.php');

echo json_encode(array(
	'ServerTime' => date("Y/m/d H:i:s")
), JSON_THROW_ON_ERROR);
