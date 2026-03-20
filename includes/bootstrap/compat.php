<?php

/**
 * DarkCore — Global function compatibility shim.
 *
 * Every function here is a one-to-three-line wrapper that delegates to a
 * proper namespaced class in src/.  No business logic lives here.
 *
 * Legacy modules/templates may continue using the global functions unchanged;
 * new code should call the underlying classes directly.
 *
 * @package     DarkCore
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright   2026 Dmytro Hovenko (Darkheim)
 * @license     MIT
 * @link        https://darkheim.net
 */

use Darkheim\Application\Auth\AdminGuard;
use Darkheim\Application\Auth\SessionManager;
use Darkheim\Application\Game\GameHelper;
use Darkheim\Application\Helpers\Encoder;
use Darkheim\Application\Helpers\TimeHelper;
use Darkheim\Application\Language\LanguageRepository;
use Darkheim\Application\Language\Translator;
use Darkheim\Application\Profile\ProfileRenderer;
use Darkheim\Application\View\MessageRenderer;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Bootstrap\ConfigProvider;
use Darkheim\Infrastructure\Bootstrap\RuntimeState;
use Darkheim\Infrastructure\Cache\CacheBuilder;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\Cron\CronManager;
use Darkheim\Infrastructure\Helpers\FileHelper;
use Darkheim\Infrastructure\Http\GeoIpService;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\Security\IpBlocker;

// ---------------------------------------------------------------------------
// Value / validation
// ---------------------------------------------------------------------------

function check_value($value): bool
{
    return Validator::hasValue($value);
}

// ---------------------------------------------------------------------------
// HTTP
// ---------------------------------------------------------------------------

function redirect($type = 1, $location = null, $delay = 0): void
{
    Redirector::go((int) $type, $location !== null ? (string) $location : null, (int) $delay);
}

// ---------------------------------------------------------------------------
// Auth / session
// ---------------------------------------------------------------------------

function isLoggedIn(): ?bool
{
    $session = new SessionManager();
    if (!$session->isAuthenticated()) {
        return null;
    }

    $loginConfigs = loadConfigurations('login');
    if (
        is_array($loginConfigs)
        && ($loginConfigs['enable_session_timeout'] ?? false)
        && $session->hasTimedOut((int) ($loginConfigs['session_timeout'] ?? 0))
    ) {
        logOutUser();
        return null;
    }

    $session->refreshTimeout();
    return true;
}

function logOutUser(): void
{
    (new SessionManager())->clearSession();
}

function canAccessAdminCP($username): bool
{
    return AdminGuard::canAccess((string) $username);
}

// ---------------------------------------------------------------------------
// UI messages
// ---------------------------------------------------------------------------

function message($type = 'info', $message = '', $title = ''): void
{
    MessageRenderer::toast((string) $type, (string) $message, (string) $title);
}

function inline_message($type = 'info', $message = '', $title = ''): void
{
    MessageRenderer::inline((string) $type, (string) $message, (string) $title);
}

// ---------------------------------------------------------------------------
// Language / translation
// ---------------------------------------------------------------------------

function lang($phrase, $return = true)
{
    $result = Translator::phrase((string) $phrase);
    if ($return) {
        return $result;
    }
    echo $result;
    return null;
}

function langf($phrase, $args = [], $print = false)
{
    $result = Translator::phraseFmt((string) $phrase, (array) $args);
    if ($print) {
        echo $result;
        return null;
    }
    return $result;
}

function setLanguagePhrases(array $phrases): void
{
    BootstrapContext::runtimeState()?->setLanguagePhrases($phrases);
}

function getLanguagePhrases(): array
{
    return BootstrapContext::runtimeState()?->languagePhrases() ?? [];
}

// ---------------------------------------------------------------------------
// Bootstrap context helpers
// ---------------------------------------------------------------------------

function bootstrapConfigProvider(): ConfigProvider
{
    $provider = BootstrapContext::configProvider();
    if ($provider instanceof ConfigProvider) {
        return $provider;
    }

    static $fallback = null;
    if (!$fallback instanceof ConfigProvider) {
        $fallback = new ConfigProvider(__PATH_CONFIGS__);
    }
    return $fallback;
}

function bootstrapRuntimeState(): RuntimeState
{
    $state = BootstrapContext::runtimeState();
    if ($state instanceof RuntimeState) {
        return $state;
    }

    static $fallback = null;
    if (!$fallback instanceof RuntimeState) {
        $fallback = new RuntimeState();
    }
    return $fallback;
}

// ---------------------------------------------------------------------------
// Config accessors
// ---------------------------------------------------------------------------

function cmsConfigs(): array
{
    return bootstrapConfigProvider()->cms();
}

function config($config_name, $return = false)
{
    $config = cmsConfigs();
    if (!array_key_exists($config_name, $config)) {
        return null;
    }
    if ($return) {
        return $config[$config_name];
    }
    echo $config[$config_name];
    return null;
}

function loadModuleConfigs($module): void
{
    if (!moduleConfigExists($module)) {
        bootstrapRuntimeState()->setModuleConfig([]);
        return;
    }
    $result = bootstrapConfigProvider()->moduleConfig((string) $module);
    bootstrapRuntimeState()->setModuleConfig(is_array($result) ? $result : []);
}

function moduleConfigExists($module): bool
{
    return file_exists(__PATH_MODULE_CONFIGS__ . $module . '.xml');
}

function globalConfigExists($config_file): bool
{
    return file_exists(__PATH_CONFIGS__ . $config_file . '.xml');
}

function mconfig($configuration)
{
    $mconfig = moduleConfigData();
    return array_key_exists($configuration, $mconfig) ? $mconfig[$configuration] : null;
}

function moduleConfigData(): array
{
    return bootstrapRuntimeState()->moduleConfig();
}

function gconfig($config_file, $return = true)
{
    if (!globalConfigExists($config_file)) {
        return null;
    }
    $result = bootstrapConfigProvider()->globalXml((string) $config_file);
    if (!is_array($result)) {
        return null;
    }
    if ($return) {
        return $result;
    }
    bootstrapRuntimeState()->setGlobalConfig($result);
    return null;
}

function loadConfigurations($file)
{
    if (!check_value($file) || !moduleConfigExists($file)) {
        return null;
    }
    return bootstrapConfigProvider()->moduleConfig((string) $file);
}

function loadConfig($name = 'cms')
{
    if (!check_value($name)) {
        return null;
    }
    return bootstrapConfigProvider()->config((string) $name);
}

// ---------------------------------------------------------------------------
// Cache
// ---------------------------------------------------------------------------

function BuildCacheData($data_array): ?string
{
    return is_array($data_array) ? CacheBuilder::buildLegacyText($data_array) : null;
}

function UpdateCache($file_name, $data): bool
{
    return CacheBuilder::writeTimestamped(__PATH_CACHE__ . $file_name, (string) $data);
}

function LoadCacheData($file_name): ?array
{
    return (new CacheRepository(__PATH_CACHE__))->loadLegacyText((string) $file_name);
}

function encodeCache($data, $pretty = false): string
{
    return CacheBuilder::encode($data, (bool) $pretty);
}

function updateCacheFile($fileName, $data): bool
{
    return (new CacheRepository(__PATH_CACHE__))->save((string) $fileName, (string) $data);
}

function loadCache($fileName): ?array
{
    return (new CacheRepository(__PATH_CACHE__))->load((string) $fileName);
}

// ---------------------------------------------------------------------------
// Time
// ---------------------------------------------------------------------------

function sec_to_hms($input_seconds = 0): array
{
    return TimeHelper::secToHms((int) $input_seconds);
}

function sec_to_dhms($input_seconds = 0): array
{
    return TimeHelper::secToDhms((int) $input_seconds);
}

// ---------------------------------------------------------------------------
// Cron
// ---------------------------------------------------------------------------

function updateCronLastRun($file): bool
{
    return (new CronManager())->updateLastRun((string) $file);
}

function getCronList(): ?array
{
    $result = (new CronManager())->getCronList();
    return is_array($result) ? $result : null;
}

// ---------------------------------------------------------------------------
// Game helpers
// ---------------------------------------------------------------------------

function getPlayerClass($class = 0): string
{
    return GameHelper::playerClass((int) $class);
}

function getPlayerClassAvatar($code = 0, $htmlImageTag = true, $tooltip = true, $cssClass = null): string
{
    return GameHelper::playerClassAvatar(
        (int) $code,
        (bool) $htmlImageTag,
        (bool) $tooltip,
        $cssClass !== null ? (string) $cssClass : null,
    );
}

function returnMapName($id = 0): string
{
    return GameHelper::mapName((int) $id);
}

function returnPkLevel($id): ?string
{
    return GameHelper::pkLevel((int) $id);
}

function getGensRank($contributionPoints): string
{
    return GameHelper::gensRank((int) $contributionPoints);
}

function getGensLeadershipRank($rankPosition): ?string
{
    return GameHelper::gensLeadershipRank((int) $rankPosition);
}

function returnGuildLogo($binaryData = '', $size = 40): string
{
    return GameHelper::guildLogo((string) $binaryData, (int) $size);
}

// ---------------------------------------------------------------------------
// Profiles
// ---------------------------------------------------------------------------

function playerProfile($playerName, $returnLinkOnly = false): string
{
    return ProfileRenderer::player((string) $playerName, (bool) $returnLinkOnly);
}

function guildProfile($guildName, $returnLinkOnly = false): string
{
    return ProfileRenderer::guild((string) $guildName, (bool) $returnLinkOnly);
}

// ---------------------------------------------------------------------------
// IP / security
// ---------------------------------------------------------------------------

function checkBlockedIp(): bool
{
    if (defined('access') && access === 'cron') {
        return false;
    }
    return IpBlocker::isCurrentIpBlocked();
}

// ---------------------------------------------------------------------------
// Geo / flags
// ---------------------------------------------------------------------------

function getCountryCodeFromIp($ip): ?string
{
    return GeoIpService::getCountryCode((string) $ip);
}

function getCountryFlag($countryCode = 'default'): string
{
    return GeoIpService::flagUrl((string) $countryCode);
}

// ---------------------------------------------------------------------------
// File / filesystem
// ---------------------------------------------------------------------------

function loadJsonFile($filePath): ?array
{
    return FileHelper::readJson((string) $filePath);
}

function readableFileSize($bytes, $decimals = 2): string
{
    return FileHelper::readableSize((int) $bytes, (int) $decimals);
}

function getDirectoryListFromPath($path): ?array
{
    return FileHelper::listDirectories((string) $path);
}

function getInstalledLanguagesList(): ?array
{
    return LanguageRepository::getInstalled();
}

// ---------------------------------------------------------------------------
// XML / JSON
// ---------------------------------------------------------------------------

function convertXML($object)
{
    return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
}

// ---------------------------------------------------------------------------
// Custom / runtime data
// ---------------------------------------------------------------------------

function custom($index)
{
    $data = customData();
    return array_key_exists($index, $data) ? $data[$index] : null;
}

function customData(): array
{
    return BootstrapContext::runtimeState()?->customConfig() ?? [];
}

function getRankingMenuLinks(): ?array
{
    global $rankingMenuLinks;
    return is_array($rankingMenuLinks) ? $rankingMenuLinks : null;
}

// ---------------------------------------------------------------------------
// Encoding
// ---------------------------------------------------------------------------

// https://base64.guru/developers/php/examples/base64url
function base64url_encode($data): string
{
    return Encoder::base64urlEncode((string) $data);
}

function base64url_decode($data, $strict = false): ?string
{
    return Encoder::base64urlDecode((string) $data, (bool) $strict);
}

// ---------------------------------------------------------------------------
// Debug
// ---------------------------------------------------------------------------

function debug($value): void
{
    // Intentional legacy helper for manual local diagnostics.
    $output = is_scalar($value) || $value === null
        ? (string) $value
        : var_export($value, true);

    echo '<pre>';
    echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    echo '</pre>';
}
