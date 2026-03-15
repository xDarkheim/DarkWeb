<?php
// Define CMS access
define('access', 'index');

try {
	
	// Load CMS
	if(!@include('includes/cms.php')) {
		throw new RuntimeException('Could not load CMS.');
	}

} catch (Exception $ex) {
	ob_clean();
	$errorPage = file_get_contents('includes/error.html');
	echo str_replace("{ERROR_MESSAGE}", $ex->getMessage(), $errorPage);
	
}
