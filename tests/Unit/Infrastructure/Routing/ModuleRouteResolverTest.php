<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Routing;

use Darkheim\Infrastructure\Routing\Support\ModuleRouteResolver;
use PHPUnit\Framework\TestCase;

final class ModuleRouteResolverTest extends TestCase
{
    private ModuleRouteResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ModuleRouteResolver();
    }

    public function testResolveWithoutSubpageReturnsModuleRoute(): void
    {
        $resolved = $this->resolver->resolve('login', null);

        $this->assertSame('module', $resolved['type']);
        $this->assertSame('login', $resolved['page']);
    }

    public function testResolveNewsWithSubpageStillReturnsModuleRoute(): void
    {
        $resolved = $this->resolver->resolve('news', 'view');

        $this->assertSame('module', $resolved['type']);
        $this->assertSame('news', $resolved['page']);
    }

    public function testResolveWithSubpageReturnsSubmoduleRoute(): void
    {
        $resolved = $this->resolver->resolve('rankings', 'level');

        $this->assertSame('submodule', $resolved['type']);
        $this->assertSame('rankings', $resolved['page']);
        $this->assertSame('level', $resolved['subpage']);
    }
}
