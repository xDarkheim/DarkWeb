<?php

declare(strict_types=1);

use Darkheim\Application\CastleSiege\CastleSiegeApiController;
use Darkheim\Application\Donation\PaypalApiController;
use Darkheim\Application\Profile\GuildmarkApiController;
use Darkheim\Application\Website\EventsApiController;
use Darkheim\Application\Website\ServerTimeApiController;
use Darkheim\Application\Website\VersionApiController;

/**
 * API route registry.
 *
 * Key = endpoint name used by /api/{key}.php.
 */
return [
    'castlesiege' => [
        'controller' => CastleSiegeApiController::class,
    ],
    'events' => [
        'controller' => EventsApiController::class,
    ],
    'guildmark' => [
        'controller' => GuildmarkApiController::class,
    ],
    'paypal' => [
        'controller' => PaypalApiController::class,
    ],
    'servertime' => [
        'controller' => ServerTimeApiController::class,
    ],
    'version' => [
        'controller' => VersionApiController::class,
    ],
];
