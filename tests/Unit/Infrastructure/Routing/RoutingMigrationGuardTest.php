<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use PHPUnit\Framework\TestCase;

final class RoutingMigrationGuardTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = dirname(__DIR__, 4);
    }

    public function testMigrationMatrixTracksAllTopLevelWebRoutes(): void
    {
        $matrix = $this->loadMatrix();
        $trackedPages = array_keys($matrix['pages']);
        sort($trackedPages);

        /** @var mixed $routes */
        $routes = include $this->projectRoot . '/config/routes.web.php';
        $this->assertIsArray($routes);
        $routePages = array_keys($routes);
        sort($routePages);

        $this->assertSame(
            $routePages,
            $trackedPages,
            'config/routing-migration.json must track all config/routes.web.php pages exactly.'
        );
    }

    public function testMatrixStatusesAreValidAndMigratedRoutesMatchRouteRegistry(): void
    {
        $matrix = $this->loadMatrix();
        $allowedStatuses = $matrix['statuses'];

        /** @var mixed $routes */
        $routes = include $this->projectRoot . '/config/routes.web.php';
        $this->assertIsArray($routes);

        foreach ($matrix['pages'] as $page => $meta) {
            $this->assertIsArray($meta, 'Each page entry must be an object: ' . $page);

            $status = $meta['status'] ?? null;
            $this->assertIsString($status, 'Missing status for: ' . $page);
            $this->assertContains($status, $allowedStatuses, 'Invalid status for: ' . $page);

            $route = $routes[$page] ?? null;

            $this->assertIsArray($route, 'Missing controller route for migrated/subpage page: ' . $page);
            $this->assertIsString($meta['controller'] ?? null, 'Missing controller in matrix: ' . $page);
            $this->assertIsString($meta['module_config'] ?? null, 'Missing module_config in matrix: ' . $page);

            $this->assertSame($meta['controller'], $route['controller'] ?? null, 'Controller mismatch for: ' . $page);
            $this->assertSame($meta['module_config'], $route['module_config'] ?? null, 'module_config mismatch for: ' . $page);
        }
    }

    /**
     * @return array{version:int,statuses:array<int,string>,pages:array<string,array<string,mixed>>}
     */
    private function loadMatrix(): array
    {
        $raw = file_get_contents($this->projectRoot . '/config/routing-migration.json');
        $this->assertIsString($raw);

        /** @var array{version:int,statuses:array<int,string>,pages:array<string,array<string,mixed>>} $matrix */
        $matrix = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('version', $matrix);
        $this->assertArrayHasKey('statuses', $matrix);
        $this->assertArrayHasKey('pages', $matrix);

        return $matrix;
    }
}

