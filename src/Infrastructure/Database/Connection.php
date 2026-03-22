<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Database;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;

/**
 * Database connection factory — returns a live dB instance by name.
 */
class Connection
{
    public static function Database(string $database = ''): ?dB
    {
        switch ($database) {
            case 'MuOnline':
                $host = self::_config('SQL_DB_HOST');
                $port = self::_config('SQL_DB_PORT');
                $name = self::_config('SQL_DB_NAME');
                $user = self::_config('SQL_DB_USER');
                $pass = self::_config('SQL_DB_PASS');

                // Validate required config values before passing to dB
                if (! is_string($host) || ! is_string($port) || ! is_string($name) || ! is_string($user) || ! is_string($pass)) {
                    throw new \Exception('Missing required database configuration (host, port, name, user, or password)');
                }

                $db = new dB($host, $port, $name, $user, $pass);
                if ($db->dead) {
                    if (self::_config('error_reporting')) {
                        throw new \Exception($db->error);
                    }
                    throw new \Exception('Connection to database failed (' . $name . ')');
                }
                return $db;
            default:
                return null;
        }
    }

    private static function _config(string $config): mixed
    {
        $cmsConfig = BootstrapContext::configProvider()?->cms() ?? [];
        if (! array_key_exists($config, $cmsConfig)) {
            return null;
        }
        return $cmsConfig[$config];
    }
}
