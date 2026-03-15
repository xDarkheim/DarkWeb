<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap — loaded before any test class.
 *
 * Provides:
 *  - Composer autoload
 *  - All __PATH_* / __BASE_URL__ constants (pointing to temp dirs for isolation)
 *  - All DB table/column constants (from the real config files)
 *  - Safe stubs for every global CMS helper function
 */

require_once __DIR__ . '/../vendor/autoload.php';

// ── Constants ────────────────────────────────────────────────────────────────

$tmpBase = sys_get_temp_dir() . '/darkheim_tests/';
@mkdir($tmpBase, 0777, true);

define('__PATH_CACHE__',             $tmpBase . 'cache/');
define('__PATH_CONFIGS__',           $tmpBase . 'config/');
define('__PATH_MODULE_CONFIGS__',    $tmpBase . 'config/modules/');
define('__PATH_LANGUAGES__',         $tmpBase . 'languages/');
define('__PATH_EMAILS__',            $tmpBase . 'emails/');
define('__PATH_CRON__',              $tmpBase . 'cron/');
define('__PATH_PLUGINS__',           $tmpBase . 'plugins/');
define('__PATH_TEMPLATES__',         $tmpBase . 'templates/');
define('__PATH_MODULES__',           $tmpBase . 'modules/');
define('__PATH_ADMINCP_MODULES__',   $tmpBase . 'admincp/modules/');
define('__PATH_PLAYER_PROFILES_CACHE__', $tmpBase . 'cache/profiles/players/');
define('__PATH_GUILD_PROFILES_CACHE__',  $tmpBase . 'cache/profiles/guilds/');
define('__PATH_TEMPLATE_IMG__',      $tmpBase . 'img/');
define('__PATH_API__',               'http://localhost:8081/api/');
define('__PATH_ADMINCP_HOME__',      'http://localhost:8081/admincp/');
define('__BASE_URL__',               'http://localhost:8081/');
define('__CMS_VERSION__',            '0.0.1');
define('DARKHEIM_DATABASE_ERRORLOG', $tmpBase . 'logs/db_errors.log');

foreach ([
    __PATH_CACHE__,
    __PATH_CONFIGS__,
    __PATH_MODULE_CONFIGS__,
    __PATH_LANGUAGES__,
    __PATH_EMAILS__,
    __PATH_CRON__,
    __PATH_PLUGINS__,
    __PATH_TEMPLATES__,
    __PATH_MODULES__,
    __PATH_ADMINCP_MODULES__,
    __PATH_PLAYER_PROFILES_CACHE__,
    __PATH_GUILD_PROFILES_CACHE__,
    $tmpBase . 'logs/',
] as $dir) {
    @mkdir($dir, 0777, true);
}

// ── DB table/column constants from the real config files ────────────────────

require_once __DIR__ . '/../includes/config/cms.tables.php';
require_once __DIR__ . '/../includes/config/custom.tables.php';

// ── Global test config (used by config() / cmsConfigs() stubs) ──────────────

$GLOBALS['_TEST_CMS_CONFIG'] = [
    'SQL_DB_HOST'               => '127.0.0.1',
    'SQL_DB_PORT'               => '1433',
    'SQL_DB_NAME'               => 'MuOnline',
    'SQL_DB_USER'               => 'test',
    'SQL_DB_PASS'               => 'test',
    'SQL_PASSWORD_ENCRYPTION'   => 'phpmd5',
    'SQL_SHA256_SALT'           => 'testsalt',
    'language_default'          => 'en',
    'language_switch_active'    => false,
    'language_debug'            => false,
    'error_reporting'           => false,
    'website_template'          => 'default',
    'cms_installed'             => true,
    'username_min_len'          => 4,
    'username_max_len'          => 13,
    'password_min_len'          => 4,
    'password_max_len'          => 20,
    'player_profiles'           => false,
    'guild_profiles'            => false,
    'plugins_system_enable'     => false,
    'ip_block_system_enable'    => false,
    'admins'                    => [],
    'website_title'             => 'Test',
    'cron_api_key'              => 'test-key',
];

// ── Global function stubs ────────────────────────────────────────────────────

function check_value($value): bool
{
    return (@count((array) $value) > 0 && !@empty($value) && @isset($value)) || $value === '0';
}

function cmsConfigs(): array
{
    return $GLOBALS['_TEST_CMS_CONFIG'];
}

function config(string $config_name, bool $return = false): mixed
{
    $cfg = cmsConfigs();
    if (!array_key_exists($config_name, $cfg)) return null;
    return $return ? $cfg[$config_name] : null;
}

function lang(string $phrase, bool $return = true): mixed
{
    if ($return) return $phrase;
    echo $phrase;
    return null;
}

function langf(string $phrase, array $args = [], bool $print = false): mixed
{
    $result = @vsprintf($phrase, $args) ?: $phrase;
    if ($print) { echo $result; return null; }
    return $result;
}

function mconfig(string $configuration): mixed
{
    global $mconfig;
    if (is_array($mconfig) && array_key_exists($configuration, $mconfig)) {
        return $mconfig[$configuration];
    }
    return null;
}

function redirect(int $type = 1, ?string $location = null, int $delay = 0): never
{
    throw new \Tests\Stubs\RedirectException('redirect:' . ($location ?? ''));
}

function message(string $type = 'info', string $message = '', string $title = ''): void
{
    // no-op in tests
}

function inline_message(string $type = 'info', string $message = '', string $title = ''): void
{
    // no-op in tests
}

function loadConfigurations(string $file): ?array
{
    return null;
}

function loadModuleConfigs(string $module): void
{
    // no-op
}

function loadConfig(string $name = 'cms'): ?array
{
    return null;
}

function loadCache(string $fileName): ?array
{
    return null;
}

function LoadCacheData(string $file_name): ?array
{
    return null;
}

function UpdateCache(string $file_name, ?string $data): mixed
{
    return null;
}

function updateCacheFile(string $fileName, string $data): mixed
{
    return null;
}

function gconfig(string $config_file, bool $return = true): mixed
{
    return null;
}

function isLoggedIn(): mixed
{
    return null;
}

function canAccessAdminCP(string $username): mixed
{
    return null;
}

function templateBuildNavbar(): string
{
    return '';
}

function getPlayerClassAvatar(int $code = 0, bool $htmlImageTag = true, bool $tooltip = true, ?string $cssClass = null): string
{
    return '';
}

function playerProfile(string $playerName, bool $returnLinkOnly = false): string
{
    return $playerName;
}

function guildProfile(string $guildName, bool $returnLinkOnly = false): string
{
    return $guildName;
}

function BuildCacheData(?array $data_array): ?string
{
    if (!is_array($data_array)) return null;
    $result = '';
    foreach ($data_array as $row) {
        $count = count($row);
        $i = 1;
        foreach ($row as $data) {
            $result .= $data;
            if ($i < $count) $result .= '¦';
            $i++;
        }
        $result .= "\n";
    }
    return $result;
}

function convertXML(mixed $object): ?array
{
    return json_decode(json_encode($object), true);
}

function getInstalledLanguagesList(): array
{
    return ['en'];
}

function addRankingMenuLink(string $phrase, string $module, mixed $filesExclusivity = null): void
{
    // no-op
}

function moduleConfigExists(string $module): bool
{
    return file_exists(__PATH_MODULE_CONFIGS__ . $module . '.xml');
}

function returnGuildLogo(string $binaryData = '', int $size = 40): string
{
    return '';
}

function getGensRank(int $contributionPoints): string
{
    return 'Recruit';
}

function getGensLeadershipRank(int $rankPosition): string
{
    return 'Leader';
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function sec_to_hms(int $input_seconds = 0): array
{
    return [0, 0, 0];
}

function sec_to_dhms(int $input_seconds = 0): array
{
    return [0, 0, 0, 0];
}

function debug(mixed $value): void
{
    // no-op
}

