<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Application\Rankings\RankingsSectionController;
use Darkheim\Application\Usercp\Subpage\AddStatsSubpageController;
use Darkheim\Application\Usercp\Subpage\BuyZenSubpageController;
use Darkheim\Application\Usercp\Subpage\MyAccountSubpageController;
use Darkheim\Infrastructure\Routing\Registries\SubpageRouteRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SubpageRouteRegistryTest extends TestCase
{
    public function testRouteForReturnsConfiguredEntry(): void
    {
        $routesFile = sys_get_temp_dir() . '/darkcore_sub_routes_' . uniqid('', true) . '.php';
        file_put_contents(
            $routesFile,
            <<<'PHP'
                <?php
                return [
                    'usercp/addstats' => [
                        'module_config' => 'usercp.addstats',
                    ],
                ];
                PHP
        );

        $registry = new SubpageRouteRegistry($routesFile);
        $route    = $registry->routeFor('usercp', 'addstats');

        $this->assertIsArray($route);
        $this->assertSame('usercp.addstats', $route['module_config']);
        $this->assertNull($registry->routeFor('usercp', 'missing'));

        @unlink($routesFile);
    }

    public function testMyAccountRouteUsesControllerBackedRendering(): void
    {
        $registry = new SubpageRouteRegistry();

        $route = $registry->routeFor('usercp', 'myaccount');

        $this->assertIsArray($route);
        $this->assertSame('usercp.myaccount', $route['module_config'] ?? null);
        $this->assertSame(MyAccountSubpageController::class, $route['controller'] ?? null);
    }

    public function testAddStatsRouteUsesControllerBackedRendering(): void
    {
        $registry = new SubpageRouteRegistry();

        $route = $registry->routeFor('usercp', 'addstats');

        $this->assertIsArray($route);
        $this->assertSame('usercp.addstats', $route['module_config'] ?? null);
        $this->assertSame(AddStatsSubpageController::class, $route['controller'] ?? null);
    }

    public function testBuyZenRouteUsesControllerBackedRendering(): void
    {
        $registry = new SubpageRouteRegistry();

        $route = $registry->routeFor('usercp', 'buyzen');

        $this->assertIsArray($route);
        $this->assertSame('usercp.buyzen', $route['module_config'] ?? null);
        $this->assertSame(BuyZenSubpageController::class, $route['controller'] ?? null);
    }

    #[DataProvider('rankingsSubpagesProvider')]
    public function testRankingsRouteUsesControllerBackedRendering(string $subpage): void
    {
        $registry = new SubpageRouteRegistry();

        $route = $registry->routeFor('rankings', $subpage);

        $this->assertIsArray($route);
        $this->assertSame('rankings', $route['module_config'] ?? null);
        $this->assertSame(RankingsSectionController::class, $route['controller'] ?? null);
    }

    /** @return array<int, array{0:string}> */
    public static function rankingsSubpagesProvider(): array
    {
        return [
            ['level'],
            ['resets'],
            ['killers'],
            ['guilds'],
            ['grandresets'],
            ['online'],
            ['votes'],
            ['gens'],
            ['master'],
        ];
    }
}
