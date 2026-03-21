<?php

declare(strict_types=1);

use Darkheim\Application\Admincp\AccountInfoController;
use Darkheim\Application\Admincp\AccountsFromIpController;
use Darkheim\Application\Admincp\AddNewsController;
use Darkheim\Application\Admincp\AddNewsTranslationController;
use Darkheim\Application\Admincp\AdminCPAccessController;
use Darkheim\Application\Admincp\BanAccountController;
use Darkheim\Application\Admincp\BlockedIpsController;
use Darkheim\Application\Admincp\CacheManagerController;
use Darkheim\Application\Admincp\ConnectionSettingsController;
use Darkheim\Application\Admincp\CreditsConfigsController;
use Darkheim\Application\Admincp\CreditsManagerController;
use Darkheim\Application\Admincp\CronManagerController;
use Darkheim\Application\Admincp\EditCharacterController;
use Darkheim\Application\Admincp\EditNewsController;
use Darkheim\Application\Admincp\EditNewsTranslationController;
use Darkheim\Application\Admincp\HomeController;
use Darkheim\Application\Admincp\LatestBansController;
use Darkheim\Application\Admincp\LatestPaypalController;
use Darkheim\Application\Admincp\ManageNewsController;
use Darkheim\Application\Admincp\ModulesManagerController;
use Darkheim\Application\Admincp\NavbarController;
use Darkheim\Application\Admincp\NewRegistrationsController;
use Darkheim\Application\Admincp\OnlineAccountsController;
use Darkheim\Application\Admincp\PhrasesController;
use Darkheim\Application\Admincp\PluginInstallController;
use Darkheim\Application\Admincp\PluginsController;
use Darkheim\Application\Admincp\SearchAccountController;
use Darkheim\Application\Admincp\SearchBanController;
use Darkheim\Application\Admincp\SearchCharacterController;
use Darkheim\Application\Admincp\TopVotesController;
use Darkheim\Application\Admincp\UsercpMenuController;
use Darkheim\Application\Admincp\WebsiteSettingsController;

/**
 * AdminCP module route registry.
 *
 * Key = AdminCP module key used by `Handler::loadAdminCPModule($module)`.
 */
return [
    'accountinfo' => [
        'controller' => AccountInfoController::class,
        'module_config' => 'accountinfo',
    ],
    'accountsfromip' => [
        'controller' => AccountsFromIpController::class,
        'module_config' => 'accountsfromip',
    ],
    'addnews' => [
        'controller' => AddNewsController::class,
        'module_config' => 'news',
    ],
    'addnewstranslation' => [
        'controller' => AddNewsTranslationController::class,
        'module_config' => 'news',
    ],
    'admincp_access' => [
        'controller' => AdminCPAccessController::class,
        'module_config' => 'admincp_access',
    ],
    'banaccount' => [
        'controller' => BanAccountController::class,
        'module_config' => 'banaccount',
    ],
    'blockedips' => [
        'controller' => BlockedIpsController::class,
        'module_config' => 'blockedips',
    ],
    'cachemanager' => [
        'controller' => CacheManagerController::class,
        'module_config' => 'cachemanager',
    ],
    'connection_settings' => [
        'controller' => ConnectionSettingsController::class,
        'module_config' => 'connection_settings',
    ],
    'creditsconfigs' => [
        'controller' => CreditsConfigsController::class,
        'module_config' => 'creditsconfigs',
    ],
    'creditsmanager' => [
        'controller' => CreditsManagerController::class,
        'module_config' => 'creditsmanager',
    ],
    'cronmanager' => [
        'controller' => CronManagerController::class,
        'module_config' => 'cronmanager',
    ],
    'editcharacter' => [
        'controller' => EditCharacterController::class,
        'module_config' => 'editcharacter',
    ],
    'editnews' => [
        'controller' => EditNewsController::class,
        'module_config' => 'news',
    ],
    'editnewstranslation' => [
        'controller' => EditNewsTranslationController::class,
        'module_config' => 'news',
    ],
    'home' => [
        'controller' => HomeController::class,
        'module_config' => 'home',
    ],
    'latestbans' => [
        'controller' => LatestBansController::class,
        'module_config' => 'latestbans',
    ],
    'latestpaypal' => [
        'controller' => LatestPaypalController::class,
        'module_config' => 'latestpaypal',
    ],
    'managenews' => [
        'controller' => ManageNewsController::class,
        'module_config' => 'news',
    ],
    'modules_manager' => [
        'controller' => ModulesManagerController::class,
        'module_config' => 'modules_manager',
    ],
    'navbar' => [
        'controller' => NavbarController::class,
        'module_config' => 'navbar',
    ],
    'newregistrations' => [
        'controller' => NewRegistrationsController::class,
        'module_config' => 'newregistrations',
    ],
    'onlineaccounts' => [
        'controller' => OnlineAccountsController::class,
        'module_config' => 'onlineaccounts',
    ],
    'phrases' => [
        'controller' => PhrasesController::class,
        'module_config' => 'phrases',
    ],
    'plugin_install' => [
        'controller' => PluginInstallController::class,
        'module_config' => 'plugin_install',
    ],
    'plugins' => [
        'controller' => PluginsController::class,
        'module_config' => 'plugins',
    ],
    'searchaccount' => [
        'controller' => SearchAccountController::class,
        'module_config' => 'searchaccount',
    ],
    'searchban' => [
        'controller' => SearchBanController::class,
        'module_config' => 'searchban',
    ],
    'searchcharacter' => [
        'controller' => SearchCharacterController::class,
        'module_config' => 'searchcharacter',
    ],
    'topvotes' => [
        'controller' => TopVotesController::class,
        'module_config' => 'topvotes',
    ],
    'usercp' => [
        'controller' => UsercpMenuController::class,
        'module_config' => 'usercp',
    ],
    'website_settings' => [
        'controller' => WebsiteSettingsController::class,
        'module_config' => 'website_settings',
    ],
];

