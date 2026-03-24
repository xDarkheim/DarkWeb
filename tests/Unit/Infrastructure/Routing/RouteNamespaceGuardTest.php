<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouteNamespaceGuardTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = dirname(__DIR__, 4);
    }

    #[DataProvider('routeConfigFilesProvider')]
    public function testRouteConfigFilesDoNotUseLegacyControllerNamespaces(string $relativePath): void
    {
        $content = file_get_contents($this->projectRoot . '/' . $relativePath);
        $this->assertIsString($content);

        $this->assertStringNotContainsString('Darkheim\\Application\\Page\\', $content);
        $this->assertStringNotContainsString('Darkheim\\Application\\Subpage\\', $content);
        $this->assertStringNotContainsString('Darkheim\\Application\\Api\\', $content);
    }

    public function testRoutingMigrationMatrixDoesNotUseLegacyControllerNamespaces(): void
    {
        $content = file_get_contents($this->projectRoot . '/config/routing-migration.json');
        $this->assertIsString($content);

        $this->assertStringNotContainsString('Darkheim\\Application\\Page\\', $content);
        $this->assertStringNotContainsString('Darkheim\\Application\\Subpage\\', $content);
        $this->assertStringNotContainsString('Darkheim\\Application\\Api\\', $content);
    }

    /** @return array<int,array{0:string}> */
    public static function routeConfigFilesProvider(): array
    {
        return [
            ['config/routes.web.php'],
            ['config/routes.subpages.php'],
            ['config/routes.admincp.php'],
            ['config/routes.api.php'],
        ];
    }
}
