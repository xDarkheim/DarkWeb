<?php

declare(strict_types=1);

namespace Darkheim\Application\Shared\Support;

/**
 * Time conversion utilities.
 *
 * Replaces the global sec_to_hms() / sec_to_dhms() helpers.
 */
final class TimeHelper
{
    /**
     * Converts a number of seconds to [totalHours, minutes, seconds].
     *
     * @return array{0: int, 1: int, 2: int}
     */
    public static function secToHms(int $inputSeconds): array
    {
        $dhms = self::secToDhms($inputSeconds);

        if ($dhms === [0, 0, 0, 0]) {
            return [0, 0, 0];
        }

        return [($dhms[0] * 24) + $dhms[1], $dhms[2], $dhms[3]];
    }

    /**
     * Converts a number of seconds to [days, hours, minutes, seconds].
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    public static function secToDhms(int $inputSeconds): array
    {
        if ($inputSeconds < 1) {
            return [0, 0, 0, 0];
        }

        $daysModule    = $inputSeconds % 86400;
        $days          = (int) (($inputSeconds - $daysModule) / 86400);
        $hoursModule   = $daysModule % 3600;
        $hours         = (int) (($daysModule - $hoursModule) / 3600);
        $minutesModule = $hoursModule % 60;
        $minutes       = (int) (($hoursModule - $minutesModule) / 60);
        $seconds       = $minutesModule;

        return [$days, $hours, $minutes, $seconds];
    }
}
