<?php

declare(strict_types=1);

use Darkheim\Application\Page\CastleSiegeController;
use Darkheim\Application\Page\ContactController;
use Darkheim\Application\Page\DonationController;
use Darkheim\Application\Page\DownloadsController;
use Darkheim\Application\Page\ForgotPasswordController;
use Darkheim\Application\Page\HomeController;
use Darkheim\Application\Page\InfoController;
use Darkheim\Application\Page\LoginController;
use Darkheim\Application\Page\LogoutController;
use Darkheim\Application\Page\NewsController;
use Darkheim\Application\Page\PrivacyController;
use Darkheim\Application\Page\RankingsController;
use Darkheim\Application\Page\RefundsController;
use Darkheim\Application\Page\RegisterController;
use Darkheim\Application\Page\TosController;
use Darkheim\Application\Page\UsercpController;
use Darkheim\Application\Page\VerifyEmailController;

/**
 * Web route registry.
 *
 * Key = top-level page key used by Handler::loadModule($page, $subpage).
 */
return [
    'castlesiege' => [
        'controller' => CastleSiegeController::class,
        'module_config' => 'castlesiege',
    ],
    'contact' => [
        'controller' => ContactController::class,
        'module_config' => 'contact',
    ],
    'donation' => [
        'controller' => DonationController::class,
        'module_config' => 'donation',
    ],
    'downloads' => [
        'controller' => DownloadsController::class,
        'module_config' => 'downloads',
    ],
    'forgotpassword' => [
        'controller' => ForgotPasswordController::class,
        'module_config' => 'forgotpassword',
    ],
    'home' => [
        'controller' => HomeController::class,
        'module_config' => 'home',
    ],
    'info' => [
        'controller' => InfoController::class,
        'module_config' => 'info',
    ],
    'login' => [
        'controller' => LoginController::class,
        'module_config' => 'login',
    ],
    'logout' => [
        'controller' => LogoutController::class,
        'module_config' => 'logout',
    ],
    'news' => [
        'controller' => NewsController::class,
        'module_config' => 'news',
    ],
    'privacy' => [
        'controller' => PrivacyController::class,
        'module_config' => 'privacy',
    ],
    'rankings' => [
        'controller' => RankingsController::class,
        'module_config' => 'rankings',
    ],
    'refunds' => [
        'controller' => RefundsController::class,
        'module_config' => 'refunds',
    ],
    'register' => [
        'controller' => RegisterController::class,
        'module_config' => 'register',
    ],
    'tos' => [
        'controller' => TosController::class,
        'module_config' => 'tos',
    ],
    'usercp' => [
        'controller' => UsercpController::class,
        'module_config' => 'usercp',
    ],
    'verifyemail' => [
        'controller' => VerifyEmailController::class,
        'module_config' => 'verifyemail',
    ],
];
