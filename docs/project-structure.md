# Project Structure

This map shows where the main CMS components live and which paths are safe to modify.

```
/
├── src/                            # PSR-4 autoloaded application code (namespace: Darkheim\)
│   ├── Application/                # Use-case / feature layer
│   │   ├── Account/
│   │   │   └── Account.php         # Account read/write helpers
│   │   ├── Admincp/
│   │   │   ├── Controller/
│   │   │   │   ├── Dashboard/
│   │   │   │   ├── Accounts/
│   │   │   │   ├── News/
│   │   │   │   ├── Settings/
│   │   │   │   ├── Plugins/
│   │   │   │   ├── Security/
│   │   │   │   └── Operations/
│   │   │   ├── Layout/
│   │   │   │   ├── AdmincpLayoutDataProvider.php
│   │   │   │   └── AdmincpUrlGenerator.php
│   │   │   ├── Support/
│   │   │   │   ├── AdmincpConfigurationChecker.php
│   │   │   │   ├── DownloadLinkService.php
│   │   │   │   ├── ModuleConfigCatalog.php
│   │   │   │   └── XmlModuleConfigSaver.php
│   │   ├── Auth/
│   │   │   ├── AdminGuard.php      # AdminCP access check (canAccess)
│   │   │   ├── AuthService.php     # Core authentication logic
│   │   │   ├── Common.php          # Shared auth + IP-block helpers
│   │   │   ├── Login.php           # Login handler
│   │   │   ├── SessionManager.php  # Session lifecycle
│   │   │   ├── LoginController.php # Top-level route controller (`/login`)
│   │   │   ├── LogoutController.php # Top-level route controller (`/logout`)
│   │   │   ├── RegisterController.php # Top-level route controller (`/register`)
│   │   │   ├── ForgotPasswordController.php # Top-level route controller (`/forgotpassword`)
│   │   │   └── VerifyEmailController.php # Top-level route controller (`/verifyemail`)
│   │   ├── CastleSiege/
│   │   │   ├── CastleSiegeController.php # Top-level route controller (`/castlesiege`)
│   │   │   └── CastleSiege.php     # Castle siege data access
│   │   ├── Character/
│   │   │   └── Character.php       # Character read/write helpers
│   │   ├── Credits/
│   │   │   └── CreditSystem.php    # Credits & donation logic
│   │   ├── Shared/
│   │   │   ├── Game/
│   │   │   │   └── GameHelper.php      # Class avatars, maps, PK levels, Gens, guild logo
│   │   │   ├── Language/
│   │   │   │   ├── LanguageRepository.php  # Lists installed language packs
│   │   │   │   └── Translator.php      # lang() / langf() — phrase lookup + debug wrapping
│   │   │   ├── Support/
│   │   │   │   ├── Encoder.php         # URL-safe Base64 encode/decode
│   │   │   │   └── TimeHelper.php      # sec_to_hms / sec_to_dhms conversions
│   │   │   └── UI/
│   │   │       └── MessageRenderer.php # Toast (popup) and inline styled messages
│   │   ├── News/
│   │   │   ├── NewsController.php  # Top-level route controller (`/news`)
│   │   │   ├── NewsItem.php        # News value object
│   │   │   ├── NewsRepository.php  # DB-backed news reads
│   │   │   └── NewsService.php     # News orchestration
│   │   ├── Language/
│   │   │   └── LanguageSwitchSubpageController.php # Subpage route controller (`language/switch`)
│   │   ├── Website/                # Top-level public page controllers
│   │   │   ├── HomeController.php
│   │   │   ├── ContactController.php
│   │   │   ├── DownloadsController.php
│   │   │   ├── InfoController.php
│   │   │   ├── PrivacyController.php
│   │   │   ├── RefundsController.php
│   │   │   └── TosController.php
│   │   ├── Donation/
│   │   │   ├── DonationController.php # Top-level route controller (`/donation`)
│   │   │   └── DonationPaypalSubpageController.php # Subpage route controller (`donation/paypal`)
│   │   ├── Usercp/
│   │   │   ├── UsercpController.php # Top-level route controller (`/usercp`)
│   │   │   └── Subpage/
│   │   │       ├── AbstractCharacterActionTableSubpageController.php
│   │   │       └── *SubpageController.php
│   │   ├── Profile/
│   │   │   ├── ProfileRenderer.php # Player / guild profile link builder
│   │   │   ├── ProfileRepository.php
│   │   │   ├── ProfileGuildSubpageController.php
│   │   │   └── ProfilePlayerSubpageController.php
│   │   ├── Rankings/
│   │   │   ├── RankingCache.php    # Cache read/write for rankings
│   │   │   ├── RankingRepository.php
│   │   │   ├── RankingsService.php
│   │   │   └── RankingsSectionController.php # Shared rankings subpage controller (`rankings/*`)
│   │   ├── Theme/
│   │   │   └── Layout/
│   │   │       └── DefaultThemeLayoutBuilder.php # Prepares default theme layout context (navbar, sidebar, footer, assets)
│   │   └── Vote/
│   │       ├── Vote.php
│   │       └── VoteSiteRepository.php
│   │
│   ├── Domain/
│   │   └── Validation/
│   │       └── Validator.php       # Input validation helpers (hasValue, Email, Ip, …)
│   │
│   └── Infrastructure/             # I/O, frameworks, DB drivers
│       ├── Bootstrap/
│       │   ├── AppKernel.php       # Composition root — wires all services, boots app
│       │   ├── BootstrapContext.php # Static registry: configProvider / runtimeState / handler
│       │   ├── ConfigProvider.php  # Loads config.json, module XML configs
│       │   ├── RuntimeState.php    # In-memory bag: language phrases, module config, custom tables
│       │   └── TimezoneInitializer.php
│       ├── Cache/
│       │   ├── CacheBuilder.php    # Builds cache payloads (legacy text, JSON encode, timestamped write)
│       │   ├── CacheManager.php
│       │   └── CacheRepository.php # load() / save() / loadLegacyText()
│       ├── Config/
│       │   ├── ConfigRepository.php
│       │   ├── JsonConfigReader.php
│       │   └── XmlConfigReader.php
│       ├── Cron/
│       │   ├── CronExecutor.php    # CLI/runtime cron execution wrapper
│       │   └── CronManager.php     # Cron CRUD, enable/disable, updateLastRun()
│       ├── Database/
│       │   ├── Connection.php      # Factory: Connection::Database('MuOnline')
│       │   ├── DatabaseFactory.php
│       │   └── dB.php              # PDO wrapper — always uses pdo_dblib (FreeTDS)
│       ├── Email/
│       │   └── Email.php           # PHPMailer wrapper
│       ├── Helpers/
│       │   └── FileHelper.php      # readJson(), listDirectories(), readableSize()
│       ├── Http/
│       │   ├── GeoIpService.php    # Country-code lookup (ip-api.com) + flag URL
│       │   └── Redirector.php      # HTTP redirects (header / meta-refresh / raw)
│       ├── Payment/
│       │   └── PaypalIPN.php       # PayPal IPN handler
│       ├── Plugins/
│       │   └── Plugins.php
│       ├── View/
│       │   └── ViewRenderer.php    # Renders view templates — lookup: theme override → views/
│       ├── Routing/
│       │   ├── Dispatchers/
│       │   │   ├── Handler.php                  # Entry point — loadPage() / loadModule() / loadAdminCPModule()
│       │   │   ├── AdmincpModuleDispatcher.php  # Resolves AdminCP route metadata, loads module_config, calls controller->render()
│       │   │   ├── ApiRouteDispatcher.php       # Dispatches /api/{endpoint} to controller->render()
│       │   │   ├── ControllerRouteDispatcher.php# Resolves page name → Controller via WebRouteRegistry and calls render()
│       │   │   ├── PageAccessDispatcher.php     # Enforces the `access` constant and renders the theme shell
│       │   │   └── SubpageRouteDispatcher.php   # Dispatches sub-page routes (config/routes.subpages.php)
│       │   ├── Registries/
│       │   │   ├── AdmincpRouteRegistry.php     # Loads & caches config/routes.admincp.php route table
│       │   │   ├── ApiRouteRegistry.php         # Loads & caches config/routes.api.php route table
│       │   │   ├── SubpageRouteRegistry.php     # Loads & caches config/routes.subpages.php route table
│       │   │   └── WebRouteRegistry.php         # Loads & caches config/routes.web.php route table
│       │   └── Support/
│       │       ├── LanguageBootstrapper.php     # Applies session language override before rendering
│       │       ├── ModuleRouteResolver.php      # Normalises (?page=x&subpage=y) into a typed route descriptor
│       │       ├── RequestParameterParser.php   # Populates QueryStore from raw $_GET on each request
│       │       └── RouteInputSanitizer.php      # Strips dangerous chars from $page / $subpage tokens
│       ├── Runtime/
│       │   ├── Contracts/
│       │   │   ├── SessionStore.php    # Session abstraction
│       │   │   ├── QueryStore.php      # `$_GET` abstraction
│       │   │   ├── RequestStore.php    # `$_REQUEST` abstraction
│       │   │   └── PostStore.php       # `$_POST` abstraction
│       │   ├── Native/
│       │   │   ├── NativeSessionStore.php
│       │   │   ├── NativeQueryStore.php
│       │   │   ├── NativeRequestStore.php
│       │   │   └── NativePostStore.php
│       │   └── Support/
│       │       └── ServerContext.php   # Server metadata accessor (`REMOTE_ADDR`, etc.)
│       └── Security/
│           └── IpBlocker.php       # Checks REMOTE_ADDR against blocked_ip.cache
│
├── vendor/                         # Composer-managed dependencies (do not edit)
│   ├── autoload.php
│   ├── google/recaptcha/
│   └── phpmailer/phpmailer/
│
├── public/                         # ★ DocumentRoot — only this directory is web-accessible
│   ├── index.php                   # Web entry point → EntrypointBootstrapper::boot()
│   ├── .htaccess                   # mod_rewrite routing rules
│   ├── robots.txt
│   ├── assets/                     # Global CSS / JS bundles
│   ├── img/                        # Public static images
│   │   └── flags/                  # Country flag GIFs (ISO 3166-1 alpha-2)
│   ├── admincp/                    # Admin control panel (Bootstrap 5, separate auth)
│   │   ├── index.php               # Thin AdminCP front controller
│   │   ├── css/                    # Admin-specific styles
│   │   ├── js/                     # Admin-specific scripts
│   │   └── .htaccess
│   ├── install/                    # Web-based installer (remove after setup)
│   └── themes/                  # ★ Theme files (web-accessible CSS/JS/img)
│       └── default/                # Default dark-fantasy theme (Bootstrap 3)
│           ├── index.php           # Template entry point — consumes prepared theme layout context
│           ├── css/                # Template-level CSS
│           ├── js/                 # Template JS
│           ├── img/                # Template images
│           ├── fonts/              # Local webfonts
│           └── inc/                # Server-side partials (PHP includes)
│               └── modules/
│                   ├── footer.php
│                   └── sidebar.php
│
├── bin/
│   └── cron.php                    # CLI cron runner (preferred over HTTP trigger)
│
├── src/Application/<Feature>/      # API endpoint controllers are now feature-local
│   ├── CastleSiege/CastleSiegeApiController.php
│   ├── Donation/PaypalApiController.php
│   ├── Profile/GuildmarkApiController.php
│   └── Website/{EventsApiController,ServerTimeApiController,VersionApiController}.php
│
├── config/                         # ★ Configuration files (NOT web-accessible)
│   ├── config.json                 # Main config (DB, language, server info, …)
│   ├── config.default.json         # Template for fresh installs
│   ├── tables.php                  # Core DB table/column name constants
│   ├── tables.custom.php           # Project-specific column overrides
│   ├── castle-siege.json           # Castle Siege config
│   ├── usercp-menu.json            # UserCP sidebar menu items
│   ├── navigation.json             # Navigation bar items
│   ├── email-templates.xml         # Email templates
│   ├── timezone-config.php         # date_default_timezone_set()
│   ├── admincp-layout.php          # AdminCP shell/sidebar metadata
│   ├── routes.admincp.php          # AdminCP controller route table (AdmincpRouteRegistry)
│   ├── routes.web.php              # Top-level controller route table (WebRouteRegistry)
│   ├── routes.subpages.php         # Sub-page route table (SubpageRouteRegistry)
│   ├── routing-migration.json      # Machine-readable migration status per page
│   ├── writable.json               # Paths checked for write permissions on install
│   └── modules/                    # Per-module XML configs (feature toggles)
│
├── includes/                       # Runtime support files (NOT web-accessible)
│   ├── languages/                  # Phrase files — one PHP file per language code
│   ├── emails/                     # Email template helpers
│   ├── cron/                       # Cron job scripts
│   └── plugins/                    # Runtime plugin files
│
├── var/                            # Runtime-only data (NOT web-accessible)
│   ├── cache/                      # JSON/text caches, ranking caches, news cache, plugins cache
│   └── logs/                       # PHP and DB error logs
│
├── views/                          # ★ View templates (NOT web-accessible)
│   ├── home.php
│   ├── news.php
│   ├── ranking.php                 # Shared rankings table template (used by RankingsSectionController)
│   ├── login.php / register.php / forgotpassword.php
│   ├── castlesiege.php / downloads.php / info.php
│   ├── tos.php / privacy.php / refunds.php
│   ├── contact.php / donation.php / verifyemail.php / usercp.php
│   ├── admincp/                    # AdminCP shell + module templates
│   │   ├── layout.php              # AdminCP shell (topbar, sidebar, assets, JS init)
│   │   ├── *.php                   # AdminCP controller-backed module views
│   │   └── mconfig/                # Transitional module-config partials
│   ├── subpages/                   # Sub-page templates (donation/language/profile/usercp)
│   └── ...                         # Permanent, theme-agnostic templates
│                                   # Optional override: public/themes/{theme}/views/{name}.php
│
├── docker/
│   ├── Dockerfile                  # PHP 8.4 + Apache + FreeTDS + pdo_dblib
│   ├── entrypoint.sh               # Container startup: dirs, permissions, cron
│   └── xdebug.ini
│
├── .htaccess                       # Root-level safety guard (redirects to public/ on misconfiguration)
├── composer.json                   # Declares dependencies + PSR-4 autoload map
├── composer.lock
├── docker-compose.yml
└── phpstan.neon / phpunit.xml
```

## Bootstrap path

```
public/index.php
  ├── vendor/autoload.php                 ← Composer PSR-4 autoloader
  └── EntrypointBootstrapper::boot()      ← Darkheim\Infrastructure\Bootstrap\EntrypointBootstrapper
        └── AppKernel::boot()             ← Darkheim\Infrastructure\Bootstrap\AppKernel
              ├── ConfigProvider          ← reads config/config.json + XML configs
              ├── RuntimeState            ← in-memory bag: language phrases, module config
              ├── config/tables.php
              ├── config/timezone-config.php
              ├── plugin files            ← from var/cache/plugins.cache
              └── Handler::loadPage()     ← Darkheim\Infrastructure\Routing\Dispatchers\Handler
```

## AdminCP bootstrap path

```
public/admincp/index.php
  ├── vendor/autoload.php
  └── EntrypointBootstrapper::boot()
        ├── AdmincpConfigurationChecker::ensureValid()
        ├── AdmincpLayoutDataProvider::sidebarGroups()
        ├── ViewRenderer::render('admincp/layout')
        └── Handler::loadAdminCPModule($module)
              └── AdmincpModuleDispatcher::dispatch()
                    ├── config/routes.admincp.php
                    ├── BootstrapContext::loadModuleConfig($route['module_config'])
                    └── <AdminCP controller>::render()
```

## Path constants defined by AppKernel

| Constant            | Points to                                           |
|:--------------------|:----------------------------------------------------|
| `__ROOT_DIR__`      | Project root filesystem path                        |
| `__PUBLIC_DIR__`    | `public/` filesystem path (DocumentRoot)            |
| `__PATH_INCLUDES__` | `includes/` filesystem path                         |
| `__PATH_VIEWS__`    | `views/` filesystem path — permanent view templates |
| `__PATH_THEMES__`   | `public/themes/` filesystem path                    |
| `__PATH_CONFIGS__`  | `config/` filesystem path                           |
| `__PATH_CACHE__`    | `var/cache/` filesystem path                        |
| `__PATH_LOGS__`     | `var/logs/` filesystem path                         |
| `__RELATIVE_ROOT__` | Site root path only (e.g. `/`, `/cms/`)            |
| `__BASE_URL__`      | Site URL (e.g. `https://example.com/`)              |
| `__PATH_IMG__`      | `__BASE_URL__ . 'img/'`                             |
| `__PATH_ASSETS__`   | `__BASE_URL__ . 'assets/'`                          |
| `__PATH_API__`      | `__BASE_URL__ . 'api/'`                             |
| `__PATH_ADMINCP__`  | `public/admincp/` filesystem path                   |

`__BASE_URL__` remains the server-side absolute origin for generated links and assets.
Browser-side JS uses `__RELATIVE_ROOT__` via the theme shell for runtime API requests so the same
page works correctly behind HTTPS-terminating reverse proxies.

## Runtime boundary

The intended direction is for classes under `src/` to avoid reading PHP superglobals directly. Runtime state is funneled through small adapters in `src/Infrastructure/Runtime/`:

| Adapter         | Wraps       | Used by                                         |
|:----------------|:------------|:------------------------------------------------|
| `Contracts\SessionStore` | `$_SESSION` | `SessionManager`, `Login`, `Plugins`, `Handler` |
| `Contracts\QueryStore`   | `$_GET`     | `Handler`, `CreditSystem`                       |
| `Contracts\RequestStore` | `$_REQUEST` | `RankingsService`                               |
| `Contracts\PostStore`    | `$_POST`    | `PaypalIPN`                                     |
| `Support\ServerContext`  | `$_SERVER`  | `Login`, `Account`, `CreditSystem`              |

This keeps the composition root inside `src/Infrastructure/Bootstrap/` explicit while making namespaced services easier to test in isolation. Some direct superglobal usages still remain in `src/` and are tracked in `docs/backlog-legacy-eradication.md`.

## Namespace map

All classes under `src/` are autoloaded via Composer PSR-4 with the root namespace `Darkheim\`:

| Namespace                             | Directory                       | Purpose                                                                 |
|:--------------------------------------|:--------------------------------|:------------------------------------------------------------------------|
| `Darkheim\Application\Account\*`      | `src/Application/Account/`      | Account read/write helpers                                              |
| `Darkheim\Application\Admincp\*`      | `src/Application/Admincp/`      | AdminCP controller groups, layout/support helpers, downloads service    |
| `Darkheim\Application\Auth\*`         | `src/Application/Auth/`         | Authentication, session, AdminCP guard                                  |
| `Darkheim\Application\CastleSiege\*`  | `src/Application/CastleSiege/`  | Castle siege data access                                                |
| `Darkheim\Application\Character\*`    | `src/Application/Character/`    | Character read/write helpers                                            |
| `Darkheim\Application\Credits\*`      | `src/Application/Credits/`      | Credits & donation logic                                                |
| `Darkheim\Application\Shared\Game\*` | `src/Application/Shared/Game/`  | Class avatars, maps, Gens, guild logo                                   |
| `Darkheim\Application\Shared\Language\*` | `src/Application/Shared/Language/` | Translator, LanguageRepository                                      |
| `Darkheim\Application\Shared\Support\*` | `src/Application/Shared/Support/` | Encoder, TimeHelper                                                 |
| `Darkheim\Application\News\*`         | `src/Application/News/`         | News value object, repository, service                                  |
| `Darkheim\Application\Language\*`     | `src/Application/Language/`     | Language switch subpage controller                                      |
| `Darkheim\Application\Website\*`      | `src/Application/Website/`      | Top-level informational page controllers                                  |
| `Darkheim\Application\Donation\*`     | `src/Application/Donation/`     | Top-level donation page controller                                        |
| `Darkheim\Application\Usercp\*`       | `src/Application/Usercp/`       | Top-level UserCP page controller                                          |
| `Darkheim\Application\Usercp\Subpage\*` | `src/Application/Usercp/Subpage/` | UserCP subpage controllers                                         |
| `Darkheim\Application\Profile\*`      | `src/Application/Profile/`      | Profile link builder, repository                                        |
| `Darkheim\Application\Rankings\*`     | `src/Application/Rankings/`     | Ranking cache, repository, service                                      |
| `Darkheim\Application\Shared\UI\*`   | `src/Application/Shared/UI/`    | MessageRenderer (toast + inline)                                        |
| `Darkheim\Application\Vote\*`         | `src/Application/Vote/`         | Vote tracking                                                           |
| `Darkheim\Application\Theme\Layout\*` | `src/Application/Theme/Layout/` | Theme layout context builders (`DefaultThemeLayoutBuilder`)             |
| `Darkheim\Domain\Validation\*`        | `src/Domain/Validation/`        | Pure domain validation helpers (Validator)                              |
| `Darkheim\Infrastructure\Bootstrap\*` | `src/Infrastructure/Bootstrap/` | AppKernel, ConfigProvider, RuntimeState, BootstrapContext               |
| `Darkheim\Infrastructure\Cache\*`     | `src/Infrastructure/Cache/`     | CacheBuilder, CacheRepository, CacheManager                             |
| `Darkheim\Infrastructure\Config\*`    | `src/Infrastructure/Config/`    | JSON/XML config readers                                                 |
| `Darkheim\Infrastructure\Cron\*`      | `src/Infrastructure/Cron/`      | CronManager, CronExecutor                                               |
| `Darkheim\Infrastructure\Database\*`  | `src/Infrastructure/Database/`  | PDO wrapper + connection factory                                        |
| `Darkheim\Infrastructure\Email\*`     | `src/Infrastructure/Email/`     | PHPMailer wrapper                                                       |
| `Darkheim\Infrastructure\Helpers\*`   | `src/Infrastructure/Helpers/`   | FileHelper (JSON, directories, file size)                               |
| `Darkheim\Infrastructure\Http\*`      | `src/Infrastructure/Http/`      | Redirector, GeoIpService                                                |
| `Darkheim\Infrastructure\Payment\*`   | `src/Infrastructure/Payment/`   | PayPal IPN                                                              |
| `Darkheim\Infrastructure\Plugins\*`   | `src/Infrastructure/Plugins/`   | Plugin loader                                                           |
| `Darkheim\Infrastructure\Routing\Dispatchers\*` | `src/Infrastructure/Routing/Dispatchers/` | Request dispatchers (`Handler`, page/subpage/admincp/api)    |
| `Darkheim\Infrastructure\Routing\Registries\*`  | `src/Infrastructure/Routing/Registries/`  | Route registries for web/subpages/admincp/api                |
| `Darkheim\Infrastructure\Routing\Support\*`     | `src/Infrastructure/Routing/Support/`     | Input parsing/sanitizing and language bootstrapping helpers   |
| `Darkheim\Infrastructure\View\*`      | `src/Infrastructure/View/`      | ViewRenderer — theme-aware template engine                              |
| `Darkheim\Infrastructure\Runtime\Contracts\*` | `src/Infrastructure/Runtime/Contracts/` | Runtime boundary contracts (`*Store`)                       |
| `Darkheim\Infrastructure\Runtime\Native\*`    | `src/Infrastructure/Runtime/Native/`    | Native `$_GET`/`$_POST`/`$_REQUEST`/`$_SESSION` adapters     |
| `Darkheim\Infrastructure\Runtime\Support\*`   | `src/Infrastructure/Runtime/Support/`   | Runtime support helpers (`ServerContext`)                     |
| `Darkheim\Infrastructure\Security\*`  | `src/Infrastructure/Security/`  | IpBlocker                                                               |

## Helper policy

Global bootstrap helper wrappers were removed. Runtime code now calls namespaced classes directly.

| Legacy helper | Class-based replacement |
|:--------------|:------------------------|
| `check_value()` | `Validator::hasValue()` |
| `redirect()` | `Redirector::go()` |
| `isLoggedIn()` | `SessionManager::websiteAuthenticated()` |
| `message()` / `inline_message()` | `MessageRenderer::toast()` / `MessageRenderer::inline()` |
| `lang()` / `langf()` | `Translator::phrase()` / `Translator::phraseFmt()` |
| `config()` | `BootstrapContext::cmsValue()` |
| `mconfig()` | `BootstrapContext::moduleValue()` |
| `loadModuleConfigs()` | `BootstrapContext::loadModuleConfig()` |
| `enabledisableCheckboxes()` | `FormFieldRenderer::enabledisableCheckboxes()` |

## What to edit vs. what not to touch

| Path                                      | Edit? | Notes                                                                |
|:------------------------------------------|:-----:|:---------------------------------------------------------------------|
| `src/`                                    |   ✅   | Application / domain / infrastructure classes                        |
| `views/`                                  |   ✅   | View templates — write once, works for all themes                    |
| `views/subpages/`                         |   ✅   | Sub-page templates dispatched by `SubpageRouteDispatcher`            |
| `public/themes/{theme}/views/`            |   ✅   | Optional per-theme template overrides (only when markup must differ) |
| `public/themes/default/index.php`         |   ✅   | Keep logic-light — render prepared layout data only                  |
| `public/themes/default/inc/modules/*.php` |   ✅   | Pure partials only; no runtime/config/service calls                  |
| `src/Infrastructure/Bootstrap/EntrypointBootstrapper.php` | ✅ | Entrypoint coordinator used by web/admincp/cron front scripts |
| `config/config.json`                      |   ✅   | Main config: DB credentials, server name, feature toggles            |
| `config/admincp-layout.php`               |   ✅   | AdminCP shell/sidebar metadata                                       |
| `config/routes.admincp.php`               |   ✅   | AdminCP controller route table                                       |
| `config/routes.web.php`                   |   ✅   | Top-level controller route table — add new page routes here          |
| `config/routes.subpages.php`              |   ✅   | Sub-page route table — add new sub-page routes here                  |
| `config/routing-migration.json`           |   ✅   | Machine-readable migration status — keep in sync with route tables   |
| `views/admincp/`                          |   ✅   | AdminCP layout + controller-backed module templates                  |
| `views/admincp/mconfig/`                  |   ✅   | Transitional AdminCP module-config partials — prefer promoting screens instead of adding new ones |
| `public/assets/css/*.css`                 |   ✅   | Global page/component styles                                         |
| `public/themes/default/css/*.css`         |   ✅   | Template layout styles                                               |
| `public/themes/default/js/*.js`           |   ✅   | Template JS                                                          |
| `includes/languages/*/language.php`       |   ✅   | Translation phrases                                                  |
| `vendor/`                                 |   ❌   | Managed by Composer — run `composer install` / `composer update`     |
| `var/cache/`                              |   ❌   | Runtime cache managed by CMS                                         |
| `var/logs/`                               |   ❌   | Runtime logs managed by CMS                                          |

## Controller-backed views and pure templates

DarkCore's target architecture treats controllers as the only place where request handling, config access, cache reads,
service orchestration, redirects, and messages are prepared.

**View / theme rule:** templates should contain only markup plus simple `echo`, `if`, and `foreach`
over already-prepared values.

Do **not** add these directly to `views/` or `public/themes/default/` templates:

- `$_GET`, `$_POST`, `$_REQUEST`, `$_SESSION`
- `BootstrapContext::cmsValue()`, `BootstrapContext::moduleValue()`, cache reads/writes
- `MessageRenderer::toast()`, `Redirector::go()`, `BootstrapContext::loadModuleConfig()`
- `new ServiceClass(...)`

Examples already following this rule:

- `Darkheim\Application\Rankings\RankingsSectionController` → `views/ranking.php`
- `Darkheim\Application\Usercp\Subpage\AbstractCharacterActionTableSubpageController` → `views/subpages/usercp/actiontables.php`
- `Darkheim\Application\Theme\Layout\DefaultThemeLayoutBuilder` → `public/themes/default/index.php` + `inc/modules/*.php`

Known transitional exceptions still tracked in `docs/backlog-legacy-eradication.md`:

- `views/admincp/layout.php` still reads `$_SESSION['username']`
- `views/admincp/modulesmanager.php` still includes `views/admincp/mconfig/*.php` dynamically

## AdminCP MVC notes

- AdminCP no longer uses `public/admincp/modules/*.php` as runtime module entry points.
- `AdmincpModuleDispatcher` does **not** fall back to include-based legacy AdminCP modules.
- AdminCP shell metadata lives in `config/admincp-layout.php` and is normalized by `AdmincpLayoutDataProvider`.
- The canonical AdminCP shell template is `views/admincp/layout.php`.
- Transitional module-config partials now live in `views/admincp/mconfig/` and are still dynamically included by `views/admincp/modulesmanager.php`.

## Adding a new controller-backed view

1. Create or update a controller under the matching feature namespace in `src/Application/` (for example `Website/`, `Auth/`, `News/`, `Rankings/`, `Usercp/Subpage/`, `Profile/`, `Donation/`, `Language/`).
2. Prepare a complete view-model array in the controller (formatted text, URLs, CSS classes, booleans, row data).
3. Register the route in `config/routes.web.php` or `config/routes.subpages.php`.
4. Render a template via `Darkheim\Infrastructure\View\ViewRenderer`.
5. Keep the template logic-light and add/update routing tests in `tests/Unit/Infrastructure/Routing/`.

For repeated layouts, prefer one shared template over many near-identical files. Current examples:

- `views/ranking.php` for all `rankings/*` sub-routes
- `views/subpages/usercp/actiontables.php` for repeated UserCP character action tables

### Recipe: add a top-level page

1. Create `src/Application/<Feature>/<Name>Controller.php` with `render(): void` (for example `Website/HomeController.php`).
2. Create a final template in `views/<name>.php`.
3. Register the controller in `config/routes.web.php`.
4. If the page is tracked, update `config/routing-migration.json`.
5. Add or update tests in `tests/Unit/Infrastructure/Routing/`.

### Recipe: add a subpage route

1. Decide whether the route needs its own template or can reuse a shared one.
2. Prepare the full view-model in a controller.
3. Register the route in `config/routes.subpages.php`.
4. Render the final template with `ViewRenderer`.
5. Add route coverage in `SubpageRouteRegistryTest` or a related routing test.

### Recipe: add a shared template

Use a shared template when multiple routes render the same layout shape and differ only by prepared data.

Examples:

- rankings tables → `views/ranking.php`
- repeated UserCP action tables → `views/subpages/usercp/actiontables.php`

When adding another shared template:

1. Create one final template file.
2. Keep all formatting, labels, URLs, row classes, and booleans in the controller/builder.
3. Point all participating routes/controllers to that single template.

### Recipe: change the default theme safely

1. Treat `public/themes/default/index.php` and `inc/modules/*.php` as rendering-only files.
2. If the theme needs new data, add it to `Darkheim\Application\Theme\Layout\DefaultThemeLayoutBuilder`.
3. Do not read request/session/config/cache directly from theme templates.
4. Keep optional per-theme view overrides in `public/themes/{theme}/views/` only when the markup must differ from `views/`.

## Legacy helper migration status

The following global helper functions were replaced with direct class calls across runtime paths:

| Helper | Replacement | Status |
|--------|-------------|--------|
| `check_value()` | `Validator::hasValue()` | ✅ Replaced |
| `lang()` | `Translator::phrase()` | ✅ Replaced |
| `langf()` | `Translator::phraseFmt()` | ✅ Replaced |
| `admincp_base()` | `AdmincpUrlGenerator::base()` | ✅ Replaced |
| `message()` | `MessageRenderer::toast()` | ✅ Replaced |
| `inline_message()` | `MessageRenderer::inline()` | ✅ Replaced |
| `mconfig()` | `BootstrapContext::moduleValue()` | ✅ Replaced |
| `config()` | `BootstrapContext::cmsValue()` | ✅ Replaced |
| `loadModuleConfigs()` | `BootstrapContext::loadModuleConfig()` | ✅ Replaced |
| `isLoggedIn()` | `SessionManager::websiteAuthenticated()` | ✅ Replaced |
| `redirect()` | `Redirector::go()` | ✅ Replaced |

`includes/bootstrap/compat.php` and `includes/bootstrap/boot.php` were removed; entrypoints now call `EntrypointBootstrapper::boot()` directly.
