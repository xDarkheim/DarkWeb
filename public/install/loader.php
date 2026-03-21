<?php
/**
 * DarkCore
 *
 * @version 1.1.0
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 */

if(!defined('access') or !access or access != 'install') die();

session_name('DarkheimInstaller010');
session_start();
ob_start();

@ini_set('default_charset', 'utf-8');

define('HTTP_HOST', $_SERVER['HTTP_HOST']);
define('SERVER_PROTOCOL', (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on' ) ? 'https://' : 'http://');
// Project root is 3 levels up from public/install/loader.php
define('__ROOT_DIR__', str_replace('\\','/', dirname(__FILE__, 3)).'/');
// Installer lives at /install/install.php → site root is one dirname() above /install/
define('__RELATIVE_ROOT__', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '', 2)), '/') . '/');
define('__BASE_URL__', SERVER_PROTOCOL.HTTP_HOST.__RELATIVE_ROOT__);
define('__PATH_INCLUDES__', __ROOT_DIR__.'includes/');
define('__PATH_CRON__', __PATH_INCLUDES__.'cron/');
define('__PATH_CONFIGS__', __ROOT_DIR__.'config/');
// __INSTALL_ROOT__ points to the installer directory itself (public/install/)
define('__INSTALL_ROOT__', str_replace('\\', '/', __DIR__) . '/');
define('__INSTALL_URL__', __BASE_URL__ . 'install/');

try {

        // Composer autoloader — provides classmap (dB, Validator, etc.) and PSR-4 src/
        $composerAutoload = __ROOT_DIR__ . 'vendor/autoload.php';
        if(file_exists($composerAutoload)) {
                require_once $composerAutoload;
        }

        // Global aliases — installer step files use short class names without namespace
        class_alias(\Darkheim\Infrastructure\Database\dB::class, 'dB');
        class_alias(\Darkheim\Domain\Validator::class, 'Validator');

        if(!@include(__PATH_CONFIGS__ . 'tables.php')) throw new Exception('Could not load DarkCore table definitions.');
        if(!@include(__INSTALL_ROOT__ . 'definitions.php')) throw new Exception('Could not load DarkCore Installer definitions.');

	$cmsConfigsPath = __PATH_CONFIGS__ . CMS_CONFIGURATION_FILE;
	if(!file_exists($cmsConfigsPath)) throw new Exception('DarkCore configuration file missing.');
	if(!is_readable($cmsConfigsPath)) throw new Exception('DarkCore configuration file is not readable.');
	if(!is_writable($cmsConfigsPath)) throw new Exception('DarkCore configuration file is not writable.');

	$cmsConfigsFile = file_get_contents($cmsConfigsPath);
	if($cmsConfigsFile) {
		$cmsConfig = json_decode(
            $cmsConfigsFile,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
		if(!is_array($cmsConfig)) throw new Exception('DarkCore configuration file could not be decoded.');
		if($cmsConfig['cms_installed'] === true) throw new Exception('DarkCore installation is complete. It is recommended to rename or delete the install directory.');
	}

	$cmsDefaultConfigsPath = __PATH_CONFIGS__ . CMS_DEFAULT_CONFIGURATION_FILE;
	if(!file_exists($cmsDefaultConfigsPath)) throw new Exception('DarkCore default configuration file missing.');
	if(!is_readable($cmsDefaultConfigsPath)) throw new Exception('DarkCore default configuration file is not readable.');
	$cmsDefaultConfigsFile = file_get_contents($cmsDefaultConfigsPath);
	if(!$cmsDefaultConfigsFile) throw new Exception('DarkCore default configuration file could not be loaded.');
	$cmsDefaultConfig = json_decode(
        $cmsDefaultConfigsFile,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
	if(!is_array($cmsDefaultConfig)) throw new Exception('DarkCore default configuration file could not be decoded.');

		if(!@include(__PATH_INCLUDES__ . 'bootstrap/compat.php')) throw new Exception('Could not load DarkCore functions.');
        if(!@include(__PATH_CONFIGS__ . 'timezone-config.php')) throw new Exception('Could not load DarkCore timezone.');

	$writablePaths = json_decode(
        file_get_contents(__PATH_CONFIGS__.CMS_WRITABLE_PATHS_FILE),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
	if(!is_array($writablePaths)) throw new Exception('Could not load DarkCore writable paths list.');

	if(!isset($_SESSION['install_cstep'])) {
		$_SESSION['install_cstep'] = 0;
	}


	if(isset($_GET['action'])) {
		if($_GET['action'] === 'restart') {
			$_SESSION = array();
			session_destroy();
			header('Location: install.php');
			die();
		}
	}

} catch (Exception $ex) {
	die($ex->getMessage());
}