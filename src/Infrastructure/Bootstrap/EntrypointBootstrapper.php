<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Bootstrap;

use Darkheim\Infrastructure\Routing\Handler;

final class EntrypointBootstrapper
{
    public static function boot(string $projectRoot): Handler
    {
        $root = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $autoloadFile = $root . '/vendor/autoload.php';
        if (! is_file($autoloadFile)) {
            throw new \RuntimeException('Could not load Composer autoloader.');
        }

        require_once $autoloadFile;

        $kernel = new AppKernel($root . '/includes');
        $kernel->boot();

        return $kernel->handler();
    }
}

