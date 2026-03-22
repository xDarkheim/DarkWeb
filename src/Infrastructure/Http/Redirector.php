<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Http;

/**
 * HTTP redirect helper.
 *
 * Centralises all redirect logic previously inlined in the global \Darkheim\Infrastructure\Http\Redirector::go()
 * helper function. Use the global helper for backward-compatible call-sites;
 * call this class directly in new code.
 */
final class Redirector
{
    /**
     * Performs an HTTP redirect.
     *
     * @param int         $type     1 = header redirect + exit (default)
     *                              2 = <meta http-equiv="REFRESH"> (no exit)
     *                              3 = raw header to $location as-is + exit
     * @param string|null $location Path appended to __BASE_URL__ (types 1/2), or a
     *                              full URL (type 3). Null redirects to the base URL.
     * @param int         $delay    Delay in seconds for type 2 meta-refresh.
     */
    public static function go(int $type = 1, ?string $location = null, int $delay = 0): void
    {
        $base = defined('__BASE_URL__') ? (string) __BASE_URL__ : '/';

        if (empty($location)) {
            $to = $base;
        } else {
            $to = $base . $location;

            if ($location === 'login') {
                $page = $_REQUEST['page'] ?? '';
                $sub  = $_REQUEST['subpage'] ?? null;
                $_SESSION['login_last_location'] = $page . '/';
                if ($sub !== null) {
                    $_SESSION['login_last_location'] .= $sub . '/';
                }
            }
        }

        switch ($type) {
            case 2:
                echo '<meta http-equiv="REFRESH" content="' . $delay . ';url=' . $to . '">';
                break;
            case 3:
                header('Location: ' . $location);
                exit();
            default:
                header('Location: ' . $to);
                exit();
        }
    }
}

