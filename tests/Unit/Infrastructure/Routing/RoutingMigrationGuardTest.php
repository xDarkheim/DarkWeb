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

    public function testMigrationMatrixTracksAllTopLevelModules(): void
    {
        $matrix = $this->loadMatrix();
        $trackedPages = array_keys($matrix['pages']);
        sort($trackedPages);

        $moduleFiles = glob($this->projectRoot . '/modules/*.php') ?: [];
        $modulePages = array_map(
            static fn (string $path): string => basename($path, '.php'),
            $moduleFiles
        );
        sort($modulePages);

        $this->assertSame(
            $modulePages,
            $trackedPages,
            'config/routing-migration.json must track all modules/*.php pages exactly.'
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

            if ($status === 'legacy') {
                $this->assertNull(
                    $route,
                    'Legacy page has controller route; mark it as hybrid/migrated instead: ' . $page
                );
                continue;
            }

            $this->assertIsArray($route, 'Missing controller route for migrated/hybrid page: ' . $page);
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

