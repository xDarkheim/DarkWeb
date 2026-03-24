<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Admincp\Layout;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use PHPUnit\Framework\TestCase;

final class AdmincpUrlGeneratorTest extends TestCase
{
    public function testBaseReturnsAdmincpHomeWhenModuleIsEmpty(): void
    {
        $generator = new AdmincpUrlGenerator();

        $this->assertSame(__PATH_ADMINCP_HOME__, $generator->base());
    }

    public function testBaseAppendsModuleQueryString(): void
    {
        $generator = new AdmincpUrlGenerator();

        $this->assertSame(__PATH_ADMINCP_HOME__ . '?module=cachemanager', $generator->base('cachemanager'));
    }
}
