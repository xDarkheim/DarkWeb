<?php

// access
define('access', 'admincp');

use Darkheim\Application\Admincp\AdmincpConfigurationChecker;
use Darkheim\Application\Admincp\AdmincpLayoutDataProvider;
use Darkheim\Application\Admincp\AdmincpUrlGenerator;
use Darkheim\Application\Auth\AdminGuard;
use Darkheim\Application\Auth\SessionManager;
use Darkheim\Infrastructure\Bootstrap\EntrypointBootstrapper;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\View\ViewRenderer;

try {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $handler = EntrypointBootstrapper::boot(dirname(__DIR__, 2));
    if (! SessionManager::websiteAuthenticated()) {
        Redirector::go();
    }
    if (! AdminGuard::canAccess((string) ($_SESSION['username'] ?? ''))) {
        Redirector::go();
    }
    new AdmincpConfigurationChecker()->ensureValid();
} catch (Throwable $ex) {
    $errorPage = file_get_contents('../../includes/error.html');
    echo str_replace('{ERROR_MESSAGE}', $ex->getMessage(), $errorPage);
    die();
}

$currentModule      = (string) ($_REQUEST['module'] ?? '');
$layoutDataProvider = new AdmincpLayoutDataProvider();
$admincpUrl         = new AdmincpUrlGenerator();

$view = new ViewRenderer();
$view->render('admincp/layout', [
    'sidebarGroups'        => $layoutDataProvider->sidebarGroups(),
    'currentModule'        => $currentModule,
    'admincpHomeUrl'       => $admincpUrl->base(),
    'admincpModuleBaseUrl' => $admincpUrl->base() . '?module=',
    'handler'              => $handler,
]);
