<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Database;

/**
 * Database connection factory — returns a live dB instance by name.
 */
class Connection
{
    public static function Database(string $database = ''): ?dB
    {
        switch ($database) {
            case 'MuOnline':
                $db = new dB(
                    self::_config('SQL_DB_HOST'),
                    self::_config('SQL_DB_PORT'),
                    self::_config('SQL_DB_NAME'),
                    self::_config('SQL_DB_USER'),
                    self::_config('SQL_DB_PASS')
                );
                if ($db->dead) {
                    if (self::_config('error_reporting')) {
                        throw new \Exception($db->error);
                    }
                    throw new \Exception('Connection to database failed (' . self::_config('SQL_DB_NAME') . ')');
                }
                return $db;
            default:
                return null;
        }
    }

    private static function _config(string $config): mixed
    {
        $cmsConfig = cmsConfigs();
        if (!array_key_exists($config, $cmsConfig)) return null;
        return $cmsConfig[$config];
    }
}

