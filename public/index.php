<?php
// Define CMS access
define('access', 'index');

try {
	$bootFile = __DIR__ . '/../includes/bootstrap/boot.php';
	if(!@include($bootFile)) {
		throw new RuntimeException('Could not load CMS.');
	}

} catch (Exception $ex) {
	if (ob_get_level() > 0) {
		ob_clean();
	}
	$errorPagePath = __DIR__ . '/../includes/error.html';
	$errorPage = @file_get_contents($errorPagePath);
	if (!is_string($errorPage) || $errorPage === '') {
		http_response_code(500);
		echo 'Error: ' . htmlspecialchars($ex->getMessage(), ENT_QUOTES, 'UTF-8');
		return;
	}
	echo str_replace('{ERROR_MESSAGE}', $ex->getMessage(), $errorPage);
}
