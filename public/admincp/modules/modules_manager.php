<?php
$cmsModules = array(
	'_global' => array(
		array('News','news'), array('Login','login'), array('Register','register'),
		array('Downloads','downloads'), array('Donation','donation'), array('PayPal','paypal'),
		array('Rankings','rankings'), array('Castle Siege','castlesiege'), array('Email System','email'),
		array('Profiles','profiles'), array('Contact Us','contact'), array('Forgot Password','forgotpassword'),
	),
	'_usercp' => array(
		array('Add Stats','addstats'), array('Clear PK','clearpk'), array('Clear Skill-Tree','clearskilltree'),
		array('My Account','myaccount'), array('Change Password','mypassword'), array('Change Email','myemail'),
		array('Character Reset','reset'), array('Reset Stats','resetstats'), array('Unstick Character','unstick'),
		array('Vote and Reward','vote'), array('Buy Zen','buyzen'),
	),
);

echo '<h1 class="page-header"><i class="bi bi-grid me-2"></i>Module Manager</h1>';

echo '<div class="row g-3 mb-4">';

echo '<div class="col-md-6"><div class="acp-card"><div class="acp-card-header">Global Modules</div><div class="p-2">';
foreach($cmsModules['_global'] as $m) {
	echo '<a href="'.admincp_base("modules_manager&config=".$m[1]).'" class="acp-module-link">'.$m[0].'</a>';
}
echo '</div></div></div>';

echo '<div class="col-md-6"><div class="acp-card"><div class="acp-card-header">UserCP Modules</div><div class="p-2">';
foreach($cmsModules['_usercp'] as $m) {
	echo '<a href="'.admincp_base("modules_manager&config=".$m[1]).'" class="acp-module-link">'.$m[0].'</a>';
}
echo '</div></div></div>';

echo '</div>';

if(isset($_GET['config'])) {
	$filePath = __PATH_ADMINCP_MODULES__.'mconfig/'.$_GET['config'].'.php';
	if(file_exists($filePath)) {
		echo '<div class="acp-card"><div class="acp-card-header"><i class="bi bi-sliders me-1"></i>Configuration: '.htmlspecialchars($_GET['config']).'</div><div class="p-3">';
		include($filePath);
		echo '</div></div>';
	} else {
		message('error','Invalid module.');
	}
}