<?php

declare(strict_types=1);

namespace Darkheim\Domain;

/**
 * Input validation — all methods are static.
 */
class Validator
{
    private static function textHit(string $string, array|string $exclude = ''): bool
    {
        if (empty($exclude)) return false;
        if (is_array($exclude)) {
            if (array_any(
                $exclude,
                fn($text) => str_contains($string, $text)
            )
            ) {
                return true;
            }
        } elseif (str_contains($string, $exclude)) return true;
        return false;
    }

    private static function numberBetween($integer, $max = null, $min = 0): bool
    {
        if (is_numeric($min) && $integer < $min) return false;
        if (is_numeric($max) && $integer > $max) return false;
        return true;
    }

    public static function Email(string $string, array|string $exclude = ''): bool
    {
        if (self::textHit($string, $exclude)) return false;
        return (bool) preg_match("/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i", $string);
    }

    public static function Url(string $string, array|string $exclude = ''): bool
    {
        if (self::textHit($string, $exclude)) return false;
        return (bool) preg_match("/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $string);
    }

    public static function Ip(string $string): bool
    {
        return (bool) filter_var($string, FILTER_VALIDATE_IP);
    }

    public static function Number($integer, $max = null, $min = 0): bool
    {
        if (preg_match("/^-?[0-9e]+$/", (string) $integer)) {
            if (!self::numberBetween($integer, $max, $min)) return false;
            return true;
        }
        return false;
    }

    public static function UnsignedNumber($integer): bool
    {
        return (bool) preg_match("/^\+?[0-9]+$/", (string) $integer);
    }

    public static function Float($string): bool
    {
        return ($string == (string)(float)$string);
    }

    public static function Alpha(string $string): bool
    {
        return (bool) preg_match("/^[a-zA-Z]+$/", $string);
    }

    public static function AlphaNumeric(string $string): bool
    {
        return (bool) preg_match("/^[0-9a-zA-Z]+$/", $string);
    }

    public static function Chars(string $string, array $allowed = ['a-z']): bool
    {
        return (bool) preg_match("/^[" . implode("", $allowed) . "]+$/", $string);
    }

    public static function Length(string $string, $max = null, $min = 0): bool
    {
        $length = strlen($string);
        if (!self::numberBetween($length, $max, $min)) return false;
        return true;
    }

    public static function Date(string $string): bool
    {
        $date = date('Y', (int) strtotime($string));
        return $date !== '1970';
    }

    public static function UsernameLength(string $string): bool
    {
        return !(strlen($string) < config('username_min_len', true) || strlen($string) > config('username_max_len', true));
    }

    public static function PasswordLength(string $string): bool
    {
        return !(strlen($string) < config('password_min_len', true) || strlen($string) > config('password_max_len', true));
    }
}

