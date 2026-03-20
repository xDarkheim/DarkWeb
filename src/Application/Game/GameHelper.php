<?php

declare(strict_types=1);

namespace Darkheim\Application\Game;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Domain\Validator;

/**
 * Game-specific display/lookup helpers.
 *
 * Centralises all character-class, map, PK-level, Gens, and guild-logo logic
 * that was previously scattered across global functions in functions.php.
 *
 * All methods are static so they can be used both from new OOP call-sites
 * and from the thin global-function wrappers in includes/bootstrap/compat.php.
 */
final class GameHelper
{
    // -----------------------------------------------------------------------
    // Character class
    // -----------------------------------------------------------------------

    /**
     * Returns the display name of a character class by its numeric code.
     */
    public static function playerClass(int $code): string
    {
        $custom = self::custom();
        if (!array_key_exists($code, $custom['character_class'] ?? [])) {
            return 'Unknown';
        }

        return (string) $custom['character_class'][$code][0];
    }

    /**
     * Returns an <img> tag (or the raw file path) for a character class avatar.
     *
     * @param int         $code          Character class code
     * @param bool        $htmlImageTag  When false returns the full filesystem path
     * @param bool        $tooltip       Attach data-toggle tooltip attributes
     * @param string|null $cssClass      Optional CSS class added to the <img>
     */
    public static function playerClassAvatar(
        int     $code = 0,
        bool    $htmlImageTag = true,
        bool    $tooltip = true,
        ?string $cssClass = null,
    ): string {
        $custom    = self::custom();
        $classes   = $custom['character_class'] ?? [];
        $avatarDir = defined('__PATH_TEMPLATE_IMG__') ? (string) __PATH_TEMPLATE_IMG__ : '';

        // Config key for the avatar subdirectory
        try {
            $dir = BootstrapContext::configProvider()?->cms()['character_avatars_dir'] ?? '';
        } catch (\Throwable) {
            $dir = '';
        }

        $imageFile = array_key_exists($code, $classes) ? (string) $classes[$code][2] : 'avatar.jpg';
        $fullPath  = $avatarDir . $dir . '/' . $imageFile;
        $className = array_key_exists($code, $classes) ? (string) $classes[$code][0] : '';

        if (!$htmlImageTag) {
            return $fullPath;
        }

        $tag = '<img';
        if (Validator::hasValue($cssClass)) {
            $tag .= ' class="' . htmlspecialchars($cssClass, ENT_QUOTES) . '"';
        }
        if ($tooltip) {
            $tag .= ' data-toggle="tooltip" data-placement="top"'
                . ' title="' . htmlspecialchars($className, ENT_QUOTES) . '"'
                . ' alt="' . htmlspecialchars($className, ENT_QUOTES) . '"';
        }
        $tag .= ' src="' . $fullPath . '" />';

        return $tag;
    }

    // -----------------------------------------------------------------------
    // Map & PK level
    // -----------------------------------------------------------------------

    /** Returns a human-readable map name for the given map ID. */
    public static function mapName(int $id): string
    {
        $custom = self::custom();
        $maps   = $custom['map_list'] ?? [];

        if (!is_array($maps)) {
            return 'Lorencia Bar';
        }

        if (!array_key_exists($id, $maps)) {
            try {
                $debug = BootstrapContext::configProvider()?->cms()['error_reporting'] ?? false;
            } catch (\Throwable) {
                $debug = false;
            }

            return $debug ? 'Map Number (' . $id . ')' : 'Lorencia Bar';
        }

        return (string) $maps[$id];
    }

    /** Returns the PK-level label for the given numeric ID, or null when unknown. */
    public static function pkLevel(int $id): ?string
    {
        $custom = self::custom();
        $levels = $custom['pk_level'] ?? [];

        if (!is_array($levels) || !array_key_exists($id, $levels)) {
            return null;
        }

        return (string) $levels[$id];
    }

    // -----------------------------------------------------------------------
    // Gens
    // -----------------------------------------------------------------------

    /** Returns the Gens rank title for the given contribution-point total. */
    public static function gensRank(int $contributionPoints): string
    {
        $custom = self::custom();
        $title  = '';
        foreach ($custom['gens_ranks'] ?? [] as $points => $t) {
            if ($contributionPoints >= $points) {
                $title = (string) $t;
            }
        }

        return $title;
    }

    /** Returns the Gens leadership rank for the given rank position, or null. */
    public static function gensLeadershipRank(int $rankPosition): ?string
    {
        $custom = self::custom();
        foreach ($custom['gens_ranks_leadership'] ?? [] as $title => $range) {
            if ($rankPosition >= $range[0] && $rankPosition <= $range[1]) {
                return (string) $title;
            }
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Guild logo
    // -----------------------------------------------------------------------

    /** Returns an <img> tag that loads the guild mark from the API endpoint. */
    public static function guildLogo(string $binaryData = '', int $size = 40): string
    {
        $imgSize = Validator::UnsignedNumber($size) ? $size : 40;
        $api     = defined('__PATH_API__') ? (string) __PATH_API__ : '';

        return '<img src="' . $api . 'guildmark.php?data=' . $binaryData
            . '&size=' . urlencode((string) $size) . '" width="' . $imgSize . '" height="' . $imgSize . '">';
    }

    // -----------------------------------------------------------------------
    // Internal
    // -----------------------------------------------------------------------

    /**
     * Returns the current custom-tables config via RuntimeState.
     *
     * @return array<string, mixed>
     */
    private static function custom(): array
    {
        return BootstrapContext::runtimeState()?->customConfig() ?? [];
    }
}

