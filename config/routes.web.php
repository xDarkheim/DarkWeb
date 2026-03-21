<?php

declare(strict_types=1);

use Darkheim\Application\Page\HomeController;
use Darkheim\Application\Page\LoginController;
use Darkheim\Application\Page\RegisterController;

/**
 * Web route registry (phase 1).
 *
 * Key = legacy module page key used by Handler::loadModule($page, $subpage).
 */
return [
    'home' => [
        'controller' => HomeController::class,
        'module_config' => 'home',
    ],
    'login' => [
        'controller' => LoginController::class,
        'module_config' => 'login',
    ],
    'register' => [
        'controller' => RegisterController::class,
        'module_config' => 'register',
    ],
];

