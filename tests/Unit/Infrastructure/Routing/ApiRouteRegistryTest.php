<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Application\Api\EventsApiController;
use Darkheim\Application\Api\GuildmarkApiController;
use Darkheim\Infrastructure\Routing\ApiRouteRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ApiRouteRegistryTest extends TestCase
{
    public function testRouteForReturnsConfiguredApiEntry(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_api_registry_' . uniqid('', true) . '.php';
        file_put_contents($routesFile, <<<'PHP'
<?php
return [
    'events' => [
        'controller' => 'Tests\\Api\\EventsController',
    ],
];
PHP
);

        $registry = new ApiRouteRegistry($routesFile);

        $route = $registry->routeFor('events');

        $this->assertIsArray($route);
        $this->assertSame('Tests\\Api\\EventsController', $route['controller'] ?? null);
        $this->assertNull($registry->routeFor('missing'));

        @unlink($routesFile);
    }

    #[DataProvider('configuredApiRoutesProvider')]
    public function testConfiguredApiRoutesUseControllers(string $endpoint, string $controller): void
    {
        $registry = new ApiRouteRegistry();

        $route = $registry->routeFor($endpoint);

        $this->assertIsArray($route);
        $this->assertSame($controller, $route['controller'] ?? null);
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function configuredApiRoutesProvider(): array
    {
        return [
            ['events', EventsApiController::class],
            ['guildmark', GuildmarkApiController::class],
        ];
    }
}

