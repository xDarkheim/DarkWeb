<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing;

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Runtime\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\NativeSessionStore;
use Darkheim\Infrastructure\Runtime\QueryStore;
use Darkheim\Infrastructure\Runtime\SessionStore;
use Darkheim\Application\Auth\Common;

/**
 * Request handler — routing, module loading, theme rendering.
 */
class Handler
{
    private SessionStore $session;
    private QueryStore $query;
    private ControllerRouteDispatcher $controllerDispatcher;
    private AdmincpModuleDispatcher $admincpModuleDispatcher;
    private RequestParameterParser $requestParameterParser;
    private RouteInputSanitizer $routeInputSanitizer;
    private LanguageBootstrapper $languageBootstrapper;
    private ModuleRouteResolver $moduleRouteResolver;
    private SubpageRouteDispatcher $subpageRouteDispatcher;
    private PageAccessDispatcher $pageAccessDispatcher;

    public function __construct(
        ?SessionStore $session = null,
        ?QueryStore $query = null,
        ?ControllerRouteDispatcher $controllerDispatcher = null,
        ?AdmincpModuleDispatcher $admincpModuleDispatcher = null,
        ?RequestParameterParser $requestParameterParser = null,
        ?RouteInputSanitizer $routeInputSanitizer = null,
        ?LanguageBootstrapper $languageBootstrapper = null,
        ?ModuleRouteResolver $moduleRouteResolver = null,
        ?SubpageRouteDispatcher $subpageRouteDispatcher = null,
        ?PageAccessDispatcher $pageAccessDispatcher = null,
    ) {
        $this->session = $session ?? new NativeSessionStore();
        $this->query = $query ?? new NativeQueryStore();
        $this->controllerDispatcher = $controllerDispatcher ?? new ControllerRouteDispatcher();
        $this->admincpModuleDispatcher = $admincpModuleDispatcher ?? new AdmincpModuleDispatcher();
        $this->requestParameterParser = $requestParameterParser ?? new RequestParameterParser();
        $this->routeInputSanitizer = $routeInputSanitizer ?? new RouteInputSanitizer();
        $this->languageBootstrapper = $languageBootstrapper ?? new LanguageBootstrapper();
        $this->moduleRouteResolver = $moduleRouteResolver ?? new ModuleRouteResolver();
        $this->subpageRouteDispatcher = $subpageRouteDispatcher ?? new SubpageRouteDispatcher();
        $this->pageAccessDispatcher = $pageAccessDispatcher ?? new PageAccessDispatcher();
    }

    public function loadPage(): void
    {
        $config = cmsConfigs();
        $custom = customData();
        $lang = getLanguagePhrases();
        $tSettings = [];
        $handler = $this;

        $this->languageBootstrapper->bootstrap($this->session, $config);

        $lang = getLanguagePhrases();

        if (!defined('access')) throw new \Exception('Access forbidden.');
        $this->pageAccessDispatcher->dispatch(
            (string) access,
            (string) $config['website_theme'],
            compact('config', 'custom', 'lang', 'tSettings', 'handler')
        );
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
            $page     = $this->routeInputSanitizer->sanitize($page);
            $subpage  = $this->routeInputSanitizer->sanitize($subpage);

            $this->requestParameterParser->parseInto($this->query);

            if (!check_value($page)) { $page = 'home'; }

            // Controller-based routes are defined in config/routes.web.php.
            if (!check_value($subpage) && $this->controllerDispatcher->dispatch((string) $page)) {
                return;
            }

            // Top-level pages must be controller-routed; no legacy fallback.
            if (!check_value($subpage)) {
                $this->module404();
                return;
            }

            $resolved = $this->moduleRouteResolver->resolve((string) $page, $subpage);

            if ($resolved['type'] === 'module') {
                if ($this->controllerDispatcher->dispatch($resolved['page'])) {
                    return;
                }
                $this->module404();
            } else {
                if ($this->subpageRouteDispatcher->dispatch($resolved['page'], (string) ($resolved['subpage'] ?? ''))) {
                    $mconfig = moduleConfigData();
                } else {
                    $this->module404();
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
        if ($this->admincpModuleDispatcher->dispatch((string) $module, compact(
            'config', 'lang', 'custom', 'handler', 'mconfig', 'gconfig', 'cms', 'dB', 'common'
        ))) {
            return;
        }

        message('error', 'INVALID MODULE');
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


    private function languageExists($language): bool
    {
        return file_exists(__PATH_LANGUAGES__ . $language . '/language.php');
    }

    private function module404(): void { redirect(); }

}

