<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Bootstrap;

use Darkheim\Infrastructure\Routing\Handler;
use Exception;

final class AppKernel
{
    private string $includesDir;
    private ConfigProvider $configProvider;
    private RuntimeState $runtimeState;
    private Handler $handler;

    public function __construct(
        string $includesDir,
        ?Handler $handler = null,
        ?ConfigProvider $configProvider = null,
        ?RuntimeState $runtimeState = null,
    ) {
        $this->includesDir = rtrim(str_replace('\\', '/', $includesDir), '/') . '/';
        $this->configProvider = $configProvider ?? new ConfigProvider($this->includesDir . 'config/');
        $this->runtimeState = $runtimeState ?? new RuntimeState();
        $this->handler = $handler ?? new Handler();
    }

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        $access = defined('access') ? (string) access : '';

        BootstrapContext::initialize($this->configProvider, $this->runtimeState, $this->handler);

        $this->defineVersion();
        $this->configureEncoding();
        $this->initializeSession($access);
        $this->normalizeProxyHeaders();
        $this->definePathConstants();
        $this->configureErrorLogging();

        $this->requireSupportFile(__PATH_CONFIGS__ . 'cms.tables.php', 'Could not load Darkheim CMS table definitions.');
        $this->requireSupportFile(__PATH_CONFIGS__ . 'timezone.php', 'Could not load timezone.');
        $this->requireSupportFile(__PATH_INCLUDES__ . 'bootstrap/compat.php', 'Could not load functions.');

        $config = $this->configProvider->cms();

        $this->ensureInstalled($config);
        if (array_key_exists('blacklisted', $config)) {
            throw new Exception('Could not load Darkheim CMS.');
        }

        $this->validateConfiguration($config);
        $this->loadCustomTables();
        $this->applyMaintenanceMode($config, $access);
        $this->configureDisplayErrors($config);

        if (($config['ip_block_system_enable'] ?? false) && checkBlockedIp()) {
            throw new Exception('Your IP address has been blocked.');
        }

        $this->loadPlugins($config);
        $this->defineTemplatePathConstants((string) $config['website_template']);
        $this->handler->loadPage();
    }

    public function handler(): Handler
    {
        return $this->handler;
    }

    public function configProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    public function runtimeState(): RuntimeState
    {
        return $this->runtimeState;
    }

    private function defineVersion(): void
    {
        if (!defined('__CMS_VERSION__')) {
            define('__CMS_VERSION__', '1.1.0');
        }
    }

    private function configureEncoding(): void
    {
        @ini_set('default_charset', 'utf-8');
        @mb_internal_encoding('UTF-8');
    }

    private function initializeSession(string $access): void
    {
        session_name('Darkheim010');
        if ($access !== 'cron') {
            @header('Content-Type: text/html; charset=UTF-8');
            @ob_start();
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }
    }

    private function normalizeProxyHeaders(): void
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $_SERVER['HTTPS'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'on' : 'off';
        }
    }

    private function definePathConstants(): void
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'CLI';
        $serverProtocol = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) === 'on') ? 'https://' : 'https://';
        $rootDir = str_replace('\\', '/', dirname($this->includesDir)) . '/';

        // Derive the URL base path from SCRIPT_NAME (e.g. '/index.php' → '/', '/cms/index.php' → '/cms/')
        // This works correctly whether DocumentRoot is the project root or public/.
        $relativeRoot = !empty($_SERVER['SCRIPT_NAME'])
            ? rtrim(str_replace('\\', '/', dirname((string) $_SERVER['SCRIPT_NAME'])), '/') . '/'
            : '/';

        $baseUrl = $serverProtocol . $httpHost . $relativeRoot;

        // Filesystem path to the web-accessible public/ directory.
        $publicDir = is_dir($rootDir . 'public') ? $rootDir . 'public/' : $rootDir;

        $constants = [
            'HTTP_HOST' => $httpHost,
            'SERVER_PROTOCOL' => $serverProtocol,
            '__ROOT_DIR__' => $rootDir,
            '__PUBLIC_DIR__' => $publicDir,
            '__RELATIVE_ROOT__' => $relativeRoot,
            '__BASE_URL__' => $baseUrl,
            '__PATH_INCLUDES__' => $rootDir . 'includes/',
            '__PATH_TEMPLATES__' => $publicDir . 'templates/',
            '__PATH_LANGUAGES__' => $rootDir . 'includes/languages/',
            '__PATH_MODULES__' => $rootDir . 'modules/',
            '__PATH_MODULES_USERCP__' => $rootDir . 'modules/usercp/',
            '__PATH_EMAILS__' => $rootDir . 'includes/emails/',
            '__PATH_CACHE__' => $rootDir . 'includes/cache/',
            '__PATH_ADMINCP__' => $publicDir . 'admincp/',
            '__PATH_ADMINCP_INC__' => $publicDir . 'admincp/inc/',
            '__PATH_ADMINCP_MODULES__' => $publicDir . 'admincp/modules/',
            '__PATH_NEWS_CACHE__' => $rootDir . 'includes/cache/news/',
            '__PATH_NEWS_TRANSLATIONS_CACHE__' => $rootDir . 'includes/cache/news/translations/',
            '__PATH_PLUGINS__' => $rootDir . 'includes/plugins/',
            '__PATH_CONFIGS__' => $rootDir . 'includes/config/',
            '__PATH_MODULE_CONFIGS__' => $rootDir . 'includes/config/modules/',
            '__PATH_CRON__' => $rootDir . 'includes/cron/',
            '__PATH_LOGS__' => $rootDir . 'includes/logs/',
            '__PATH_GUILD_PROFILES_CACHE__' => $rootDir . 'includes/cache/profiles/guilds/',
            '__PATH_PLAYER_PROFILES_CACHE__' => $rootDir . 'includes/cache/profiles/players/',
            '__PATH_MODULES_RANKINGS__' => $baseUrl . 'rankings/',
            '__PATH_ADMINCP_HOME__' => $baseUrl . 'admincp/',
            '__PATH_IMG__' => $baseUrl . 'img/',
            '__PATH_COUNTRY_FLAGS__' => $baseUrl . 'img/flags/',
            '__PATH_API__' => $baseUrl . 'api/',
            '__PATH_ONLINE_STATUS__' => $baseUrl . 'img/online.png',
            '__PATH_OFFLINE_STATUS__' => $baseUrl . 'img/offline.png',
            '__PATH_ASSETS__' => $baseUrl . 'assets/',
            '__PATH_ASSETS_CSS__' => $baseUrl . 'assets/css/',
            '__PATH_ASSETS_JS__' => $baseUrl . 'assets/js/',
            'DARKHEIM_DATABASE_ERRORLOG' => $rootDir . 'includes/logs/database_errors.log',
            'DARKHEIM_WRITABLE_PATHS' => $rootDir . 'includes/config/writable.paths.json',
            'DARKHEIM_PHP_ERRORLOG' => $rootDir . 'includes/logs/php_errors.log',
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    private function configureErrorLogging(): void
    {
        ini_set('log_errors', '1');
        ini_set('error_log', $this->includesDir . 'logs/php_errors.log');
    }

    /**
     * @throws Exception
     */
    private function loadCustomTables(): void
    {
        $custom = [];
        if (!@include(__PATH_CONFIGS__ . 'custom.tables.php')) {
            throw new Exception('Could not load the table definitions.');
        }

        $this->runtimeState->setCustomConfig($custom);
    }

    /**
     * @throws Exception
     */
    private function requireSupportFile(string $path, string $message): void
    {
        if (!@include($path)) {
            throw new Exception($message);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function ensureInstalled(array $config): void
    {
        if (!($config['cms_installed'] ?? false)) {
            header('Location: ' . __BASE_URL__ . 'install/');
            die();
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws Exception
     */
    private function validateConfiguration(array $config): void
    {
        if (!file_exists(__PATH_TEMPLATES__ . (string) $config['website_template'])) {
            throw new Exception('The default template doesn\'t exist.');
        }

        if (!check_value($config['SQL_DB_HOST'] ?? null)) {
            throw new Exception('The database host configuration is required to connect to your database.');
        }
        if (!check_value($config['SQL_DB_NAME'] ?? null)) {
            throw new Exception('The database name configuration is required to connect to your database.');
        }
        if (!check_value($config['SQL_DB_USER'] ?? null)) {
            throw new Exception('The database user configuration is required to connect to your database.');
        }
        if (!check_value($config['SQL_DB_PASS'] ?? null)) {
            throw new Exception('The database password configuration is required to connect to your database.');
        }
        if (!check_value($config['SQL_DB_PORT'] ?? null)) {
            throw new Exception('The database port configuration is required to connect to your database.');
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws Exception
     */
    private function applyMaintenanceMode(array $config, string $access): void
    {
        if (($config['system_active'] ?? false) || $access === 'cron') {
            return;
        }

        $admins = is_array($config['admins'] ?? null) ? $config['admins'] : [];
        $username = (string) ($_SESSION['username'] ?? '');
        if (!array_key_exists($username, $admins)) {
            header('Location: ' . (string) $config['maintenance_page']);
            die();
        }

        echo '<div style="text-align:center;border-bottom:1px solid #aa0000;padding:15px;background:#000;color:#ff0000;font-size:12pt;">';
        echo 'OFFLINE MODE';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureDisplayErrors(array $config): void
    {
        if ($config['error_reporting'] ?? false) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
            return;
        }

        ini_set('display_errors', '0');
        error_reporting(0);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws Exception
     */
    private function loadPlugins(array $config): void
    {
        if (!($config['plugins_system_enable'] ?? false)) {
            return;
        }

        $pluginsCache = loadCache('plugins.cache');
        if (!is_array($pluginsCache)) {
            return;
        }

        foreach ($pluginsCache as $pluginData) {
            if (!is_array($pluginData['files'] ?? null)) {
                continue;
            }

            foreach ($pluginData['files'] as $pluginFile) {
                $path = __PATH_PLUGINS__ . $pluginData['folder'] . '/' . $pluginFile;
                if (!@include($path)) {
                    throw new Exception('Could not load plugin file (' . $pluginData['folder'] . '/' . $pluginFile . ').');
                }
            }
        }
    }

    private function defineTemplatePathConstants(string $template): void
    {
        $constants = [
            '__PATH_TEMPLATE_ROOT__' => __PATH_TEMPLATES__ . $template . '/',
            '__PATH_TEMPLATE__' => __BASE_URL__ . 'templates/' . $template . '/',
            '__PATH_TEMPLATE_IMG__' => __BASE_URL__ . 'templates/' . $template . '/img/',
            '__PATH_TEMPLATE_CSS__' => __BASE_URL__ . 'templates/' . $template . '/css/',
            '__PATH_TEMPLATE_JS__' => __BASE_URL__ . 'templates/' . $template . '/js/',
            '__PATH_TEMPLATE_FONTS__' => __BASE_URL__ . 'templates/' . $template . '/fonts/',
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }
}

