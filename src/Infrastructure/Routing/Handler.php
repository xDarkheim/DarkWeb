<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Auth\Common;

/**
 * Request handler — routing, module loading, template rendering.
 */
class Handler
{
    public function loadPage(): void
    {
        global $config, $lang, $custom, $tSettings;
        $handler = $this;

        if (strtolower($config['language_default']) != 'en') {
            $this->_loadLanguagePhrases('en');
        }
        $this->_loadLanguagePhrases($config['language_default']);
        if ($config['language_switch_active']
            && isset($_SESSION['language_display'])
            && $_SESSION['language_display'] != $config['language_default']
        ) {
            $this->_loadLanguagePhrases($_SESSION['language_display']);
        }

        if (!defined('access')) throw new \Exception('Access forbidden.');
        switch (access) {
            case 'index':
                if (!$this->templateExists($config['website_template'])) throw new \Exception('The chosen template cannot be loaded (' . $config['website_template'] . ').');
                include(__PATH_TEMPLATES__ . $config['website_template'] . '/index.php');
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
        global $config, $lang, $custom, $mconfig, $tSettings;
        try {
            $handler  = $this;
            $page     = $this->cleanRequest($page);
            $subpage  = $this->cleanRequest($subpage);

            if (isset($_GET['request'])) {
                $request = explode('/', $_GET['request']);
                foreach (array_chunk($request, 2) as $pair) {
                    $key = $pair[0];
                    $val = $pair[1] ?? null;
                    if (!empty($key)) {
                        $_GET[$key] = ($val !== null && $val !== '')
                            ? htmlspecialchars($val)
                            : null;
                    }
                }
            }

            if (!check_value($page)) { $page = 'home'; }

            if (!check_value($subpage)) {
                if ($this->moduleExists($page)) {
                    @loadModuleConfigs($page);
                    include(__PATH_MODULES__ . $page . '.php');
                } else {
                    $this->module404();
                }
            } else {
                switch ($page) {
                    case 'news':
                        if ($this->moduleExists($page)) {
                            @loadModuleConfigs($page);
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
        global $config, $lang, $custom, $handler, $mconfig, $gconfig, $cms;

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

    public function darkwebPowered(): void
    {
        echo '<p style="font-size:11px;color:#aaa;">Powered by <a href="https://darkheim.net" target="_blank" style="color:#aaa;">DarkWeb CMS</a> v' . __CMS_VERSION__ . '</p>';
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
        $_SESSION['language_display'] = $language;
        return true;
    }

    private function moduleExists($page): bool
    {
        return file_exists(__PATH_MODULES__ . $page . '.php');
    }

    private function templateExists($template): bool
    {
        return file_exists(__PATH_TEMPLATES__ . $template . '/index.php');
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
        global $lang;
        $langFile = __PATH_LANGUAGES__ . $language . '/language.php';
        if (file_exists($langFile)) {
            include($langFile);
        }
    }
}

