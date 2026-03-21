<?php

declare(strict_types=1);

/**
 * AdminCP shell layout configuration.
 *
 * @return array<int,array{
 *     title:string,
 *     icon:string,
 *     links:array<int,array{module:string,label:string}>
 * }>
 */
return [
    [
        'title' => 'News Management',
        'icon' => 'bi-newspaper',
        'links' => [
            ['module' => 'addnews', 'label' => 'Publish'],
            ['module' => 'managenews', 'label' => 'Edit / Delete'],
        ],
    ],
    [
        'title' => 'Account',
        'icon' => 'bi-people-fill',
        'links' => [
            ['module' => 'searchaccount', 'label' => 'Search'],
            ['module' => 'accountsfromip', 'label' => 'Find Accounts from IP'],
            ['module' => 'onlineaccounts', 'label' => 'Online Accounts'],
            ['module' => 'newregistrations', 'label' => 'New Registrations'],
        ],
    ],
    [
        'title' => 'Character',
        'icon' => 'bi-person-fill',
        'links' => [
            ['module' => 'searchcharacter', 'label' => 'Search'],
        ],
    ],
    [
        'title' => 'Bans',
        'icon' => 'bi-slash-circle-fill',
        'links' => [
            ['module' => 'searchban', 'label' => 'Search'],
            ['module' => 'banaccount', 'label' => 'Ban Account'],
            ['module' => 'latestbans', 'label' => 'Latest Bans'],
            ['module' => 'blockedips', 'label' => 'Block IP (web)'],
        ],
    ],
    [
        'title' => 'Credits',
        'icon' => 'bi-cash-coin',
        'links' => [
            ['module' => 'creditsconfigs', 'label' => 'Credit Configurations'],
            ['module' => 'creditsmanager', 'label' => 'Credit Manager'],
            ['module' => 'latestpaypal', 'label' => 'PayPal Donations'],
            ['module' => 'topvotes', 'label' => 'Top Voters'],
        ],
    ],
    [
        'title' => 'Website Configuration',
        'icon' => 'bi-toggles',
        'links' => [
            ['module' => 'admincp_access', 'label' => 'AdminCP Access'],
            ['module' => 'connection_settings', 'label' => 'Connection Settings'],
            ['module' => 'website_settings', 'label' => 'Website Settings'],
            ['module' => 'modules_manager', 'label' => 'Modules Manager'],
            ['module' => 'navbar', 'label' => 'Navigation Menu'],
            ['module' => 'usercp', 'label' => 'UserCP Menu'],
        ],
    ],
    [
        'title' => 'Tools',
        'icon' => 'bi-wrench-adjustable',
        'links' => [
            ['module' => 'cachemanager', 'label' => 'Cache Manager'],
            ['module' => 'cronmanager', 'label' => 'Cron Job Manager'],
        ],
    ],
    [
        'title' => 'Languages',
        'icon' => 'bi-translate',
        'links' => [
            ['module' => 'phrases', 'label' => 'Phrase List'],
        ],
    ],
    [
        'title' => 'Plugins',
        'icon' => 'bi-plug-fill',
        'links' => [
            ['module' => 'plugins', 'label' => 'Plugins Manager'],
            ['module' => 'plugin_install', 'label' => 'Import Plugin'],
        ],
    ],
];

