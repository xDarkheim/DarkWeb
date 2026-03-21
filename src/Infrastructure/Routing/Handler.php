<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Runtime\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\NativeSessionStore;
use Darkheim\Infrastructure\Runtime\QueryStore;
use Darkheim\Infrastructure\Runtime\SessionStore;
use Darkheim\Application\Auth\Common;
use Darkheim\Application\Page\HomeController;
use Darkheim\Application\Page\LoginController;
use Darkheim\Application\Page\RegisterController;

/**
 * Request handler — routing, module loading, theme rendering.
 */
class Handler
{
    private SessionStore $session;
    private QueryStore $query;

    public function __construct(?SessionStore $session = null, ?QueryStore $query = null)
    {
        $this->session = $session ?? new NativeSessionStore();
        $this->query = $query ?? new NativeQueryStore();
    }

    public function loadPage(): void
    {
        $config = cmsConfigs();
        $custom = customData();
        $lang = getLanguagePhrases();
        $tSettings = [];
        $handler = $this;

        if (strtolower($config['language_default']) != 'en') {
            $this->_loadLanguagePhrases('en');
        }
        $this->_loadLanguagePhrases($config['language_default']);
        if ($config['language_switch_active']
            && $this->session->has('language_display')
            && $this->session->get('language_display') != $config['language_default']
        ) {
            $this->_loadLanguagePhrases((string) $this->session->get('language_display'));
        }

        $lang = getLanguagePhrases();

        if (!defined('access')) throw new \Exception('Access forbidden.');
        switch (access) {
            case 'index':
                if (!$this->themeExists($config['website_theme'])) throw new \Exception('The chosen theme cannot be loaded (' . $config['website_theme'] . ').');
                include(__PATH_THEMES__ . $config['website_theme'] . '/index.php');
                break;
            case 'api':
            case 'cron':
            case 'admincp':
            case 'install':
                break;
            default:
                throw new \Exception('Access forbidden.');
        }
    }

    public function loadModule(?string $page = 'news', ?string $subpage = 'home'): void
    {
        $config = cmsConfigs();
        $custom = customData();
        $lang = getLanguagePhrases();
        $mconfig = moduleConfigData();
        $tSettings = [];
        try {
            $handler  = $this;
            $page     = $this->cleanRequest($page);
            $subpage  = $this->cleanRequest($subpage);

            if ($this->query->has('request')) {
                $request = explode('/', (string) $this->query->get('request', ''));
                foreach (array_chunk($request, 2) as $pair) {
                    $key = $pair[0];
                    $val = $pair[1] ?? null;
                    if (!empty($key)) {
                        $this->query->set($key, ($val !== null && $val !== '')
                            ? htmlspecialchars($val)
                            : null);
                    }
                }
            }

            if (!check_value($page)) { $page = 'home'; }

            // First controller-based slice: replace direct file includes for core pages.
            if (!check_value($subpage) && in_array($page, ['home', 'login', 'register'], true)) {
                // Keep legacy mconfig() behavior for modules rendered via controllers.
                @loadModuleConfigs($page);
                $mconfig = moduleConfigData();

                switch ($page) {
                    case 'home':
                        (new HomeController())->render();
                        break;
                    case 'login':
                        (new LoginController())->render();
                        break;
                    case 'register':
                        (new RegisterController())->render();
                        break;
                }
                return;
            }

            if (!check_value($subpage)) {
                if ($this->moduleExists($page)) {
                    @loadModuleConfigs($page);
                    $mconfig = moduleConfigData();
                    include(__PATH_MODULES__ . $page . '.php');
                } else {
                    $this->module404();
                }
            } else {
                switch ($page) {
                    case 'news':
                        if ($this->moduleExists($page)) {
                            @loadModuleConfigs($page);
                            $mconfig = moduleConfigData();
                            include(__PATH_MODULES__ . $page . '.php');
                        } else {
                            $this->module404();
                        }
                        break;
                    default:
                        $path = $page . '/' . $subpage;
                        if ($this->moduleExists($path)) {
                            $cnf = $page . '.' . $subpage;
                            @loadModuleConfigs($cnf);
                            $mconfig = moduleConfigData();
                            include(__PATH_MODULES__ . $path . '.php');
                        } else {
                            $this->module404();
                        }
                        break;
                }
            }
        } catch (\Exception $ex) {
            message('error', $ex->getMessage());
        }
    }

    public function loadAdminCPModule($module = 'home'): void
    {
        $config = cmsConfigs();
        $lang = getLanguagePhrases();
        $custom = customData();
        $handler = $this;
        $mconfig = moduleConfigData();
        $gconfig = [];
        $cms = null;

        $dB     = Connection::Database('MuOnline');
        $common = new Common();

        $module = (check_value($module) ? $module : 'home');
        if ($this->admincpmoduleExists($module)) {
            try {
                include(__PATH_ADMINCP_MODULES__ . $module . '.php');
            } catch (\Exception $ex) {
                message('error', 'Module error: ' . $ex->getMessage());
            }
        } else {
            message('error', 'INVALID MODULE');
        }
    }

    public function darkcorePowered(): void
    {
        echo '<p style="font-size:11px;color:#aaa;">Powered by <a href="https://darkheim.net" target="_blank" style="color:#aaa;">DarkCore CMS</a> v' . __CMS_VERSION__ . '</p>';
    }

    public function websiteTitle(): void
    {
        $websiteTitle = (check_value(lang('website_title', true)) && lang('website_title', true) != 'ERROR'
            ? lang('website_title', true)
            : config('website_title', true));
        echo $websiteTitle;
    }

    public function switchLanguage($language): bool
    {
        if (!check_value($language)) return false;
        if (!$this->languageExists($language)) return false;
        $this->session->set('language_display', $language);
        return true;
    }

    private function moduleExists($page): bool
    {
        return file_exists(__PATH_MODULES__ . $page . '.php');
    }

    private function themeExists($theme): bool
    {
        return file_exists(__PATH_THEMES__ . $theme . '/index.php');
    }

    private function languageExists($language): bool
    {
        return file_exists(__PATH_LANGUAGES__ . $language . '/language.php');
    }

    private function admincpmoduleExists($page): bool
    {
        return file_exists(__PATH_ADMINCP_MODULES__ . $page . '.php');
    }

    private function cleanRequest($string): ?string
    {
        if ($string === null) return null;
        return preg_replace("/[^a-zA-Z0-9\s\/]/", "", $string);
    }

    private function module404(): void { redirect(); }

    private function _loadLanguagePhrases($language): void
    {
        $langFile = __PATH_LANGUAGES__ . $language . '/language.php';
        if (file_exists($langFile)) {
            $lang = getLanguagePhrases();
            include($langFile);
            setLanguagePhrases($lang);
        }
    }
}

