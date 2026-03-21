<?php

declare(strict_types=1);

/**
 * API route registry.
 *
 * Key = endpoint name used by /api/{key}.php.
 */
return [
    'castlesiege' => [
        'controller' => 'Darkheim\\Application\\Api\\CastleSiegeApiController',
    ],
    'events' => [
        'controller' => 'Darkheim\\Application\\Api\\EventsApiController',
    ],
    'guildmark' => [
        'controller' => 'Darkheim\\Application\\Api\\GuildmarkApiController',
    ],
    'paypal' => [
        'controller' => 'Darkheim\\Application\\Api\\PaypalApiController',
    ],
    'servertime' => [
        'controller' => 'Darkheim\\Application\\Api\\ServerTimeApiController',
    ],
    'version' => [
        'controller' => 'Darkheim\\Application\\Api\\VersionApiController',
    ],
];

