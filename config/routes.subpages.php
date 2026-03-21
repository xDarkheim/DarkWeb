<?php

declare(strict_types=1);

/**
 * Subpage route registry.
 *
 * Key format: "{page}/{subpage}".
 */
return [
    'donation/paypal' => [
        'module_config' => 'donation-paypal',
    ],
    'language/switch' => [
        'module_config' => null,
    ],
    'profile/guild' => [
        'module_config' => 'profiles',
    ],
    'profile/player' => [
        'module_config' => 'profiles',
    ],
    'rankings/gens' => [
        'module_config' => 'rankings',
    ],
    'rankings/grandresets' => [
        'module_config' => 'rankings',
    ],
    'rankings/guilds' => [
        'module_config' => 'rankings',
    ],
    'rankings/killers' => [
        'module_config' => 'rankings',
    ],
    'rankings/level' => [
        'module_config' => 'rankings',
    ],
    'rankings/master' => [
        'module_config' => 'rankings',
    ],
    'rankings/online' => [
        'module_config' => 'rankings',
    ],
    'rankings/resets' => [
        'module_config' => 'rankings',
    ],
    'rankings/votes' => [
        'module_config' => 'rankings',
    ],
    'usercp/addstats' => [
        'module_config' => 'usercp.addstats',
    ],
    'usercp/buyzen' => [
        'module_config' => 'usercp.buyzen',
    ],
    'usercp/clearpk' => [
        'module_config' => 'usercp.clearpk',
    ],
    'usercp/clearskilltree' => [
        'module_config' => 'usercp.clearskilltree',
    ],
    'usercp/myaccount' => [
        'module_config' => 'usercp.myaccount',
    ],
    'usercp/myemail' => [
        'module_config' => 'usercp.myemail',
    ],
    'usercp/mypassword' => [
        'module_config' => 'usercp.mypassword',
    ],
    'usercp/reset' => [
        'module_config' => 'usercp.reset',
    ],
    'usercp/resetstats' => [
        'module_config' => 'usercp.resetstats',
    ],
    'usercp/unstick' => [
        'module_config' => 'usercp.unstick',
    ],
    'usercp/vote' => [
        'module_config' => 'usercp.vote',
    ],
];

