<?php

/**
 * DarkWeb
 *
 * @package     DarkWeb
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright   2024-2026 Dmytro Hovenko (Darkheim)
 * @license     MIT
 * @link        https://darkheim.net
 *
 * Core bootstrap — session, paths, libraries, configuration loader.
 */

use Darkheim\Infrastructure\Routing\Handler;

// Composer autoloader (loaded when vendor/ is present)
if(file_exists(__DIR__.'/../vendor/autoload.php')) {
	require_once __DIR__.'/../vendor/autoload.php';
}

// Version
define('__CMS_VERSION__', '0.0.1');

// Set Encoding
@ini_set('default_charset', 'utf-8');
@mb_internal_encoding('UTF-8');

session_name('Darkheim010'); # session name (change to your server name and uncomment)
//session_set_cookie_params(0, '/', 'muonline.com'); # same session with and without www protocol (edit with your domain and uncomment)
if(access != 'cron') {
	@header('Content-Type: text/html; charset=UTF-8');
	@ob_start();
	session_start();
}

// CloudFlare IP Workaround
if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

// CloudFlare HTTPS Workaround
if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
	$_SERVER['HTTPS'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ? 'on' : 'off';
}

// Global Paths
define('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? 'CLI');
define('SERVER_PROTOCOL', (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'https://' );
define('__ROOT_DIR__', str_replace('\\','/', dirname(__FILE__, 2)).'/'); // /home/user/public_html/
define('__RELATIVE_ROOT__', (!empty($_SERVER['SCRIPT_NAME'])) ? str_ireplace(rtrim(str_replace('\\','/', realpath(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']))), '/'), '', __ROOT_DIR__) : '/');// /
define('__BASE_URL__', SERVER_PROTOCOL.HTTP_HOST.__RELATIVE_ROOT__); // http(s)://www.mysite.com/

// Private Paths
define('__PATH_INCLUDES__', __ROOT_DIR__.'includes/');
define('__PATH_TEMPLATES__', __ROOT_DIR__.'templates/');
define('__PATH_LANGUAGES__', __PATH_INCLUDES__ . 'languages/');
define('__PATH_CLASSES__', __PATH_INCLUDES__.'classes/');
define('__PATH_FUNCTIONS__', __PATH_INCLUDES__.'functions/');
define('__PATH_MODULES__', __ROOT_DIR__.'modules/');
define('__PATH_MODULES_USERCP__', __PATH_MODULES__.'usercp/');
define('__PATH_EMAILS__', __PATH_INCLUDES__.'emails/');
define('__PATH_CACHE__', __PATH_INCLUDES__.'cache/');
define('__PATH_ADMINCP__', __ROOT_DIR__.'admincp/');
define('__PATH_ADMINCP_INC__', __ROOT_DIR__.'admincp/inc/');
define('__PATH_ADMINCP_MODULES__', __ROOT_DIR__.'admincp/modules/');
define('__PATH_NEWS_CACHE__', __PATH_CACHE__.'news/');
define('__PATH_NEWS_TRANSLATIONS_CACHE__', __PATH_NEWS_CACHE__.'translations/');
define('__PATH_PLUGINS__', __PATH_INCLUDES__.'plugins/');
define('__PATH_CONFIGS__', __PATH_INCLUDES__.'config/');
define('__PATH_MODULE_CONFIGS__', __PATH_CONFIGS__.'modules/');
define('__PATH_CRON__', __PATH_INCLUDES__.'cron/');
define('__PATH_LOGS__', __PATH_INCLUDES__.'logs/');
define('__PATH_GUILD_PROFILES_CACHE__', __PATH_CACHE__.'profiles/guilds/');
define('__PATH_PLAYER_PROFILES_CACHE__', __PATH_CACHE__.'profiles/players/');

// Public Paths
define('__PATH_MODULES_RANKINGS__', __BASE_URL__.'rankings/');
define('__PATH_ADMINCP_HOME__', __BASE_URL__.'admincp/');
define('__PATH_IMG__', __BASE_URL__.'img/');
define('__PATH_COUNTRY_FLAGS__', __PATH_IMG__.'flags/');
define('__PATH_API__', __BASE_URL__.'api/');
define('__PATH_ONLINE_STATUS__', __PATH_IMG__.'online.png');
define('__PATH_OFFLINE_STATUS__', __PATH_IMG__.'offline.png');

// CMS Assets — template-independent CSS/JS (components, toasts, etc.)
define('__PATH_ASSETS__',     __BASE_URL__.'assets/');
define('__PATH_ASSETS_CSS__', __PATH_ASSETS__.'css/');
define('__PATH_ASSETS_JS__',  __PATH_ASSETS__.'js/');

// Other Paths
define('DARKHEIM_DATABASE_ERRORLOG', __PATH_LOGS__.'database_errors.log');
define('DARKHEIM_WRITABLE_PATHS', __PATH_CONFIGS__.'writable.paths.json');
define('DARKHEIM_PHP_ERRORLOG', __PATH_LOGS__.'php_errors.log');

// PHP Error Logs
ini_set('log_errors', 1);
ini_set('error_log', DARKHEIM_PHP_ERRORLOG);

// Darkheim CMS Tables
if(!@include(__PATH_CONFIGS__ . 'cms.tables.php')) throw new Exception('Could not load Darkheim CMS table definitions.');

// Timezone
if(!@include(__PATH_CONFIGS__ . 'timezone.php')) throw new Exception('Could not load timezone.');


// Load Functions
if(!@include(__PATH_INCLUDES__ . 'functions.php')) throw new Exception('Could not load functions.');

// Darkheim Configurations
$config = cmsConfigs();

// Installation Status
if(!$config['cms_installed']) {
	header('Location: '.__BASE_URL__.'install/');
	die();
}

if(array_key_exists('blacklisted', $config)) {
	throw new Exception('Could not load Darkheim CMS.');
}

// Configurations Check
$checkConfigs = true;
if($checkConfigs) {
	
	// default template
	if(!file_exists(__PATH_TEMPLATES__ . $config['website_template'])) throw new Exception('The default template doesn\'t exist.');
	
	// required configs
	if(!check_value($config['SQL_DB_HOST'])) throw new Exception('The database host configuration is required to connect to your database.');
	if(!check_value($config['SQL_DB_NAME'])) throw new Exception('The database name configuration is required to connect to your database.');
	if(!check_value($config['SQL_DB_USER'])) throw new Exception('The database user configuration is required to connect to your database.');
	if(!check_value($config['SQL_DB_PASS'])) throw new Exception('The database password configuration is required to connect to your database.');
	if(!check_value($config['SQL_DB_PORT'])) throw new Exception('The database port configuration is required to connect to your database.');
}

// Load Table Definitions
if(!@include(__PATH_CONFIGS__ . 'custom.tables.php')) throw new Exception('Could not load the table definitions.');

// CMS Status
if(!$config['system_active'] && access != 'cron') {
	if(!array_key_exists($_SESSION['username'], $config['admins'])) {
		header('Location: ' . $config['maintenance_page']);
		die();
	}
	
	// show website status to the admin
	echo '<div style="text-align:center;border-bottom:1px solid #aa0000;padding:15px;background:#000;color:#ff0000;font-size:12pt;">';
		echo 'OFFLINE MODE';
	echo '</div>';
}

// Error Reporting
if($config['error_reporting']) {
	ini_set('display_errors', true);
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', false);
	error_reporting(0);
}

// IP Blocking System
if($config['ip_block_system_enable'] && checkBlockedIp()) throw new Exception('Your IP address has been blocked.');

// Load Plugins
if($config['plugins_system_enable']) {
	$pluginsCache = loadCache('plugins.cache');
	if(is_array($pluginsCache)) {
		foreach($pluginsCache as $pluginData) {
			if(!is_array($pluginData['files'])) continue;
			foreach($pluginData['files'] as $pluginFile) {
				if(!@include(__PATH_PLUGINS__.$pluginData['folder'].'/'.$pluginFile)) throw new Exception('Could not load plugin file ('.$pluginData['folder'].'/'.$pluginFile.').');
			}
		}
	}
}

// Template Paths
define('__PATH_TEMPLATE_ROOT__', __PATH_TEMPLATES__ . $config['website_template'] . '/');
define('__PATH_TEMPLATE__', __BASE_URL__ . 'templates/' . $config['website_template'] . '/');
define('__PATH_TEMPLATE_IMG__', __PATH_TEMPLATE__ . 'img/');
define('__PATH_TEMPLATE_CSS__', __PATH_TEMPLATE__ . 'css/');
define('__PATH_TEMPLATE_JS__', __PATH_TEMPLATE__ . 'js/');
define('__PATH_TEMPLATE_FONTS__', __PATH_TEMPLATE__ . 'fonts/');

// Handler Instance
$handler = new Handler();
$handler->loadPage();