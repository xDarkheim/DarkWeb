<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Routing\Dispatchers;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Application\Theme\Layout\DefaultThemeLayoutBuilder;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\Routing\Support\LanguageBootstrapper;
use Darkheim\Infrastructure\Routing\Support\ModuleRouteResolver;
use Darkheim\Infrastructure\Routing\Support\RequestParameterParser;
use Darkheim\Infrastructure\Routing\Support\RouteInputSanitizer;
use Darkheim\Infrastructure\Runtime\Contracts\QueryStore;
use Darkheim\Infrastructure\Runtime\Contracts\SessionStore;
use Darkheim\Infrastructure\Runtime\Native\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\Native\NativeSessionStore;

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
    private ApiRouteDispatcher $apiRouteDispatcher;
    private PageAccessDispatcher $pageAccessDispatcher;
    private DefaultThemeLayoutBuilder $themeLayoutBuilder;

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
        ?ApiRouteDispatcher $apiRouteDispatcher = null,
        ?PageAccessDispatcher $pageAccessDispatcher = null,
        ?DefaultThemeLayoutBuilder $themeLayoutBuilder = null,
    ) {
        $this->session                 = $session                 ?? new NativeSessionStore();
        $this->query                   = $query                   ?? new NativeQueryStore();
        $this->controllerDispatcher    = $controllerDispatcher    ?? new ControllerRouteDispatcher();
        $this->admincpModuleDispatcher = $admincpModuleDispatcher ?? new AdmincpModuleDispatcher();
        $this->requestParameterParser  = $requestParameterParser  ?? new RequestParameterParser();
        $this->routeInputSanitizer     = $routeInputSanitizer     ?? new RouteInputSanitizer();
        $this->languageBootstrapper    = $languageBootstrapper    ?? new LanguageBootstrapper();
        $this->moduleRouteResolver     = $moduleRouteResolver     ?? new ModuleRouteResolver();
        $this->subpageRouteDispatcher  = $subpageRouteDispatcher  ?? new SubpageRouteDispatcher();
        $this->apiRouteDispatcher      = $apiRouteDispatcher      ?? new ApiRouteDispatcher();
        $this->pageAccessDispatcher    = $pageAccessDispatcher    ?? new PageAccessDispatcher();
        $this->themeLayoutBuilder      = $themeLayoutBuilder      ?? new DefaultThemeLayoutBuilder();
    }

    public function loadPage(): void
    {
        $config    = BootstrapContext::configProvider()?->cms()           ?? [];
        $custom    = BootstrapContext::runtimeState()?->customConfig()    ?? [];
        $lang      = BootstrapContext::runtimeState()?->languagePhrases() ?? [];
        $tSettings = [];
        $handler   = $this;

        $this->languageBootstrapper->bootstrap($this->session, $config);

        $lang = BootstrapContext::runtimeState()?->languagePhrases() ?? [];

        $currentPage = isset($_REQUEST['page'])
            ? $this->routeInputSanitizer->sanitize((string) $_REQUEST['page'])
            : '';
        $currentSubpage = isset($_REQUEST['subpage'])
            ? $this->routeInputSanitizer->sanitize((string) $_REQUEST['subpage'])
            : '';

        if ($this->dispatchApiRequest($currentPage, $currentSubpage)) {
            return;
        }

        $moduleHtml  = $this->renderModuleHtml($currentPage, $currentSubpage);
        $themeLayout = $this->themeLayoutBuilder->build($currentPage, $currentSubpage);

        if (! defined('access')) {
            throw new \Exception('Access forbidden.');
        }
        $this->pageAccessDispatcher->dispatch(
            access,
            (string) $config['website_theme'],
            compact('config', 'custom', 'lang', 'tSettings', 'handler', 'moduleHtml', 'themeLayout'),
        );
    }

    private function renderModuleHtml(string $page, string $subpage): string
    {
        ob_start();
        $this->loadModule($page, $subpage);
        return (string) ob_get_clean();
    }

    public function loadModule(?string $page = 'news', ?string $subpage = 'home'): void
    {
        $config    = BootstrapContext::configProvider()?->cms()           ?? [];
        $custom    = BootstrapContext::runtimeState()?->customConfig()    ?? [];
        $lang      = BootstrapContext::runtimeState()?->languagePhrases() ?? [];
        $mconfig   = BootstrapContext::runtimeState()?->moduleConfig()    ?? [];
        $tSettings = [];
        try {
            $handler = $this;
            $page    = $this->routeInputSanitizer->sanitize($page);
            $subpage = $this->routeInputSanitizer->sanitize($subpage);

            $this->requestParameterParser->parseInto($this->query);

            if (! Validator::hasValue($page)) {
                $page = 'home';
            }

            // Controller-based routes are defined in config/routes.web.php.
            if (! Validator::hasValue($subpage) && $this->controllerDispatcher->dispatch((string) $page)) {
                return;
            }

            // Top-level pages must be controller-routed; no legacy fallback.
            if (! Validator::hasValue($subpage)) {
                $this->module404();
                return;
            }

            $resolved = $this->moduleRouteResolver->resolve((string) $page, $subpage);

            if ($resolved['type'] === 'module') {
                if ($this->controllerDispatcher->dispatch($resolved['page'])) {
                    return;
                }
                $this->module404();
            } elseif ($this->subpageRouteDispatcher->dispatch(
                $resolved['page'],
                ($resolved['subpage'] ?? ''),
            )) {
                $mconfig = BootstrapContext::runtimeState()?->moduleConfig() ?? [];
            } else {
                $this->module404();
            }
        } catch (\Exception $ex) {
            MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    public function loadAdminCPModule($module = 'home'): void
    {
        $config  = BootstrapContext::configProvider()?->cms()           ?? [];
        $lang    = BootstrapContext::runtimeState()?->languagePhrases() ?? [];
        $custom  = BootstrapContext::runtimeState()?->customConfig()    ?? [];
        $handler = $this;
        $mconfig = BootstrapContext::runtimeState()?->moduleConfig() ?? [];
        $gconfig = [];
        $cms     = null;

        $dB     = Connection::Database('MuOnline');
        $common = new Common();

        $module = (Validator::hasValue($module) ? $module : 'home');
        if ($this->admincpModuleDispatcher->dispatch((string) $module, compact(
            'config',
            'lang',
            'custom',
            'handler',
            'mconfig',
            'gconfig',
            'cms',
            'dB',
            'common',
        ))) {
            return;
        }

        MessageRenderer::toast('error', 'INVALID MODULE');
    }

    public function switchLanguage($language): bool
    {
        if (! Validator::hasValue($language)) {
            return false;
        }
        if (! $this->languageExists($language)) {
            return false;
        }
        $this->session->set('language_display', $language);
        return true;
    }


    private function languageExists($language): bool
    {
        return file_exists(__PATH_LANGUAGES__ . $language . '/language.php');
    }

    private function module404(): void
    {
        Redirector::go();
    }

    private function dispatchApiRequest(string $page, string $subpage): bool
    {
        if ($page !== 'api' || $subpage === '') {
            return false;
        }

        if (! $this->apiRouteDispatcher->dispatch($subpage)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'code'  => 404,
                'error' => 'API endpoint not found.',
            ], JSON_THROW_ON_ERROR);
        }

        return true;
    }

}
