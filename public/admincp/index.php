<?php
// access
define('access', 'admincp');

use Darkheim\Infrastructure\View\ViewRenderer;
use Darkheim\Application\Admincp\AdmincpLayoutDataProvider;
use Darkheim\Application\Admincp\AdmincpConfigurationChecker;

try {
    if (!@include('../../includes/bootstrap/boot.php')) {
        throw new RuntimeException('Could not load CMS.');
    }
    if (!isLoggedIn()) {
        redirect();
    }
    if (!canAccessAdminCP($_SESSION['username'])) {
        redirect();
    }
    (new AdmincpConfigurationChecker())->ensureValid();
} catch (Exception $ex) {
    $errorPage = file_get_contents('../../includes/error.html');
    echo str_replace('{ERROR_MESSAGE}', $ex->getMessage(), $errorPage);
    die();
}

$currentModule = (string) ($_REQUEST['module'] ?? '');
$layoutDataProvider = new AdmincpLayoutDataProvider();

$view = new ViewRenderer();
$view->render('admincp/layout', [
    'sidebarGroups' => $layoutDataProvider->sidebarGroups(),
    'currentModule' => $currentModule,
    'handler' => $handler,
]);
