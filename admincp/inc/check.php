<?php
$configError = array();

$writablePaths = loadJsonFile(DARKHEIM_WRITABLE_PATHS);
if(!is_array($writablePaths)) {
        throw new RuntimeException('Could not load DarkWeb writable paths list.');
}

// File permission check
foreach($writablePaths as $thisPath) {
	if(file_exists(__PATH_INCLUDES__ . $thisPath)) {
		if(!is_writable(__PATH_INCLUDES__ . $thisPath)) {
			$configError[] = "<span style=\"color:#aaaaaa;\">[Permission Error]</span> " . $thisPath . " <span style=\"color:red;\">(file must be writable)</span>";
		}
	} else {
		$configError[] = "<span style=\"color:#aaaaaa;\">[Not Found]</span> " . $thisPath. " <span style=\"color:orange;\">(re-upload file)</span>";
	}
}

// Check cURL
if(!function_exists('curl_version')) {
	$configError[]
		= "<span style=\"color:#aaaaaa;\">[PHP]</span> <span style=\"color:green;\">cURL extension is not loaded (DarkWeb requires cURL)</span>";
}

if(count($configError) >= 1) {
	throw new RuntimeException("<strong>The following errors ocurred:</strong><br /><br />" . implode("<br />", $configError));
}