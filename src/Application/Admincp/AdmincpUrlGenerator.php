<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

final class AdmincpUrlGenerator
{
    public function base(string $module = ''): string
    {
        $base = defined('__PATH_ADMINCP_HOME__') ? (string) __PATH_ADMINCP_HOME__ : 'admincp/';

        if ($module !== '') {
            return $base . '?module=' . $module;
        }

        return $base;
    }
}

