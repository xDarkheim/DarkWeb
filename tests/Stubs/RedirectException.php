<?php

declare(strict_types=1);

namespace Tests\Stubs;

/**
 * Thrown by the redirect() stub instead of calling die().
 * Tests that expect a redirect use:
 *   $this->expectException(RedirectException::class);
 */
class RedirectException extends \RuntimeException {}

