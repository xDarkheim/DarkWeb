<?php

declare(strict_types=1);

use Darkheim\Application\Auth\ForgotPasswordController;
use Darkheim\Application\Auth\LoginController;
use Darkheim\Application\Auth\LogoutController;
use Darkheim\Application\Auth\RegisterController;
use Darkheim\Application\Auth\VerifyEmailController;
use Darkheim\Application\CastleSiege\CastleSiegeController;
use Darkheim\Application\Donation\DonationController;
use Darkheim\Application\News\NewsController;
use Darkheim\Application\Rankings\RankingsController;
use Darkheim\Application\Usercp\UsercpController;
use Darkheim\Application\Website\ContactController;
use Darkheim\Application\Website\DownloadsController;
use Darkheim\Application\Website\HomeController;
use Darkheim\Application\Website\InfoController;
use Darkheim\Application\Website\PrivacyController;
use Darkheim\Application\Website\RefundsController;
use Darkheim\Application\Website\TosController;

/**
 * Web route registry.
 *
 * Key = top-level page key used by Handler::loadModule($page, $subpage).
 */
return [
    'castlesiege' => [
        'controller'    => CastleSiegeController::class,
        'module_config' => 'castlesiege',
    ],
    'contact' => [
        'controller'    => ContactController::class,
        'module_config' => 'contact',
    ],
    'donation' => [
        'controller'    => DonationController::class,
        'module_config' => 'donation',
    ],
    'downloads' => [
        'controller'    => DownloadsController::class,
        'module_config' => 'downloads',
    ],
    'forgotpassword' => [
        'controller'    => ForgotPasswordController::class,
        'module_config' => 'forgotpassword',
    ],
    'home' => [
        'controller'    => HomeController::class,
        'module_config' => 'home',
    ],
    'info' => [
        'controller'    => InfoController::class,
        'module_config' => 'info',
    ],
    'login' => [
        'controller'    => LoginController::class,
        'module_config' => 'login',
    ],
    'logout' => [
        'controller'    => LogoutController::class,
        'module_config' => 'logout',
    ],
    'news' => [
        'controller'    => NewsController::class,
        'module_config' => 'news',
    ],
    'privacy' => [
        'controller'    => PrivacyController::class,
        'module_config' => 'privacy',
    ],
    'rankings' => [
        'controller'    => RankingsController::class,
        'module_config' => 'rankings',
    ],
    'refunds' => [
        'controller'    => RefundsController::class,
        'module_config' => 'refunds',
    ],
    'register' => [
        'controller'    => RegisterController::class,
        'module_config' => 'register',
    ],
    'tos' => [
        'controller'    => TosController::class,
        'module_config' => 'tos',
    ],
    'usercp' => [
        'controller'    => UsercpController::class,
        'module_config' => 'usercp',
    ],
    'verifyemail' => [
        'controller'    => VerifyEmailController::class,
        'module_config' => 'verifyemail',
    ],
];
