<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Admincp;

use Darkheim\Application\Admincp\AdmincpLayoutDataProvider;
use Darkheim\Infrastructure\Routing\AdmincpRouteRegistry;
use PHPUnit\Framework\TestCase;

final class AdmincpLayoutDataProviderTest extends TestCase
{
    public function testSidebarGroupsReturnsNormalizedLayoutData(): void
    {
        $layoutFile = sys_get_temp_dir() . '/darkcore_admincp_layout_' . uniqid('', true) . '.php';
        $routesFile = sys_get_temp_dir() . '/darkcore_admincp_layout_routes_' . uniqid('', true) . '.php';

        file_put_contents($layoutFile, <<<'PHP'
<?php
return [
    [
        'title' => 'Testing',
        'icon' => 'bi-bezier',
        'links' => [
            ['module' => 'home', 'label' => 'Dashboard'],
        ],
    ],
];
PHP
);

        file_put_contents($routesFile, <<<'PHP'
<?php
return [
    'home' => [
        'controller' => 'Tests\\Fake\\HomeController',
        'module_config' => 'home',
    ],
];
PHP
);

        $provider = new AdmincpLayoutDataProvider($layoutFile, new AdmincpRouteRegistry($routesFile));
        $groups = $provider->sidebarGroups();

        $this->assertCount(1, $groups);
        $this->assertSame('Testing', $groups[0]['title']);
        $this->assertSame('bi-bezier', $groups[0]['icon']);
        $this->assertSame('sm_Testing', $groups[0]['id']);
        $this->assertSame('home', $groups[0]['links'][0]['module']);
        $this->assertSame('Dashboard', $groups[0]['links'][0]['label']);
        $this->assertSame(__PATH_ADMINCP_HOME__ . '?module=home', $groups[0]['links'][0]['url']);

        @unlink($layoutFile);
        @unlink($routesFile);
    }

    public function testSidebarGroupsThrowsWhenLinkTargetsUnknownRoute(): void
    {
        $layoutFile = sys_get_temp_dir() . '/darkcore_admincp_layout_invalid_' . uniqid('', true) . '.php';
        $routesFile = sys_get_temp_dir() . '/darkcore_admincp_layout_invalid_routes_' . uniqid('', true) . '.php';

        file_put_contents($layoutFile, <<<'PHP'
<?php
return [
    [
        'title' => 'Testing',
        'icon' => 'bi-bezier',
        'links' => [
            ['module' => 'missing-route', 'label' => 'Broken'],
        ],
    ],
];
PHP
);

        file_put_contents($routesFile, <<<'PHP'
<?php
return [];
PHP
);

        $provider = new AdmincpLayoutDataProvider($layoutFile, new AdmincpRouteRegistry($routesFile));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("AdminCP layout references unknown module 'missing-route'.");
        $provider->sidebarGroups();

        @unlink($layoutFile);
        @unlink($routesFile);
    }
}

