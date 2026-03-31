<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Shared\Game;

use Darkheim\Application\Shared\Game\GameHelper;
use PHPUnit\Framework\TestCase;

final class GameHelperTest extends TestCase
{
    public function testGuildLogoUsesCleanApiEndpoint(): void
    {
        $html = GameHelper::guildLogo('abc123', 20);

        $this->assertSame(
            '<img src="http://localhost:8081/api/guildmark?data=abc123&size=20" width="20" height="20">',
            $html,
        );
    }
}
