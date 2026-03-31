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
 * Key = clean endpoint name used by /api/{key}.
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
