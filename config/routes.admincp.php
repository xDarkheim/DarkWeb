<?php

declare(strict_types=1);

use Darkheim\Application\Admincp\Controller\Accounts\AccountInfoController;
use Darkheim\Application\Admincp\Controller\Accounts\AccountsFromIpController;
use Darkheim\Application\Admincp\Controller\Accounts\BanAccountController;
use Darkheim\Application\Admincp\Controller\Accounts\EditCharacterController;
use Darkheim\Application\Admincp\Controller\Accounts\SearchAccountController;
use Darkheim\Application\Admincp\Controller\Accounts\SearchBanController;
use Darkheim\Application\Admincp\Controller\Accounts\SearchCharacterController;
use Darkheim\Application\Admincp\Controller\Dashboard\HomeController;
use Darkheim\Application\Admincp\Controller\Dashboard\LatestBansController;
use Darkheim\Application\Admincp\Controller\Dashboard\LatestPaypalController;
use Darkheim\Application\Admincp\Controller\Dashboard\NewRegistrationsController;
use Darkheim\Application\Admincp\Controller\Dashboard\OnlineAccountsController;
use Darkheim\Application\Admincp\Controller\Dashboard\TopVotesController;
use Darkheim\Application\Admincp\Controller\News\AddNewsController;
use Darkheim\Application\Admincp\Controller\News\AddNewsTranslationController;
use Darkheim\Application\Admincp\Controller\News\EditNewsController;
use Darkheim\Application\Admincp\Controller\News\EditNewsTranslationController;
use Darkheim\Application\Admincp\Controller\News\ManageNewsController;
use Darkheim\Application\Admincp\Controller\Operations\CacheManagerController;
use Darkheim\Application\Admincp\Controller\Operations\CronManagerController;
use Darkheim\Application\Admincp\Controller\Plugins\PluginInstallController;
use Darkheim\Application\Admincp\Controller\Plugins\PluginsController;
use Darkheim\Application\Admincp\Controller\Security\AdminCPAccessController;
use Darkheim\Application\Admincp\Controller\Security\BlockedIpsController;
use Darkheim\Application\Admincp\Controller\Settings\ConnectionSettingsController;
use Darkheim\Application\Admincp\Controller\Settings\CreditsConfigsController;
use Darkheim\Application\Admincp\Controller\Settings\CreditsManagerController;
use Darkheim\Application\Admincp\Controller\Settings\ModulesManagerController;
use Darkheim\Application\Admincp\Controller\Settings\NavbarController;
use Darkheim\Application\Admincp\Controller\Settings\PhrasesController;
use Darkheim\Application\Admincp\Controller\Settings\UsercpMenuController;
use Darkheim\Application\Admincp\Controller\Settings\WebsiteSettingsController;

/**
 * AdminCP module route registry.
 *
 * Key = AdminCP module key used by `Handler::loadAdminCPModule($module)`.
 */
return [
    'accountinfo' => [
        'controller'    => AccountInfoController::class,
        'module_config' => 'accountinfo',
    ],
    'accountsfromip' => [
        'controller'    => AccountsFromIpController::class,
        'module_config' => 'accountsfromip',
    ],
    'addnews' => [
        'controller'    => AddNewsController::class,
        'module_config' => 'news',
    ],
    'addnewstranslation' => [
        'controller'    => AddNewsTranslationController::class,
        'module_config' => 'news',
    ],
    'admincp_access' => [
        'controller'    => AdminCPAccessController::class,
        'module_config' => 'admincp_access',
    ],
    'banaccount' => [
        'controller'    => BanAccountController::class,
        'module_config' => 'banaccount',
    ],
    'blockedips' => [
        'controller'    => BlockedIpsController::class,
        'module_config' => 'blockedips',
    ],
    'cachemanager' => [
        'controller'    => CacheManagerController::class,
        'module_config' => 'cachemanager',
    ],
    'connection_settings' => [
        'controller'    => ConnectionSettingsController::class,
        'module_config' => 'connection_settings',
    ],
    'creditsconfigs' => [
        'controller'    => CreditsConfigsController::class,
        'module_config' => 'creditsconfigs',
    ],
    'creditsmanager' => [
        'controller'    => CreditsManagerController::class,
        'module_config' => 'creditsmanager',
    ],
    'cronmanager' => [
        'controller'    => CronManagerController::class,
        'module_config' => 'cronmanager',
    ],
    'editcharacter' => [
        'controller'    => EditCharacterController::class,
        'module_config' => 'editcharacter',
    ],
    'editnews' => [
        'controller'    => EditNewsController::class,
        'module_config' => 'news',
    ],
    'editnewstranslation' => [
        'controller'    => EditNewsTranslationController::class,
        'module_config' => 'news',
    ],
    'home' => [
        'controller'    => HomeController::class,
        'module_config' => 'home',
    ],
    'latestbans' => [
        'controller'    => LatestBansController::class,
        'module_config' => 'latestbans',
    ],
    'latestpaypal' => [
        'controller'    => LatestPaypalController::class,
        'module_config' => 'latestpaypal',
    ],
    'managenews' => [
        'controller'    => ManageNewsController::class,
        'module_config' => 'news',
    ],
    'modules_manager' => [
        'controller'    => ModulesManagerController::class,
        'module_config' => 'modules_manager',
    ],
    'navbar' => [
        'controller'    => NavbarController::class,
        'module_config' => 'navbar',
    ],
    'newregistrations' => [
        'controller'    => NewRegistrationsController::class,
        'module_config' => 'newregistrations',
    ],
    'onlineaccounts' => [
        'controller'    => OnlineAccountsController::class,
        'module_config' => 'onlineaccounts',
    ],
    'phrases' => [
        'controller'    => PhrasesController::class,
        'module_config' => 'phrases',
    ],
    'plugin_install' => [
        'controller'    => PluginInstallController::class,
        'module_config' => 'plugin_install',
    ],
    'plugins' => [
        'controller'    => PluginsController::class,
        'module_config' => 'plugins',
    ],
    'searchaccount' => [
        'controller'    => SearchAccountController::class,
        'module_config' => 'searchaccount',
    ],
    'searchban' => [
        'controller'    => SearchBanController::class,
        'module_config' => 'searchban',
    ],
    'searchcharacter' => [
        'controller'    => SearchCharacterController::class,
        'module_config' => 'searchcharacter',
    ],
    'topvotes' => [
        'controller'    => TopVotesController::class,
        'module_config' => 'topvotes',
    ],
    'usercp' => [
        'controller'    => UsercpMenuController::class,
        'module_config' => 'usercp',
    ],
    'website_settings' => [
        'controller'    => WebsiteSettingsController::class,
        'module_config' => 'website_settings',
    ],
];
