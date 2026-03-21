# Project Structure

This map shows where the main CMS components live and which paths are safe to modify.

```
/
├── src/                            # PSR-4 autoloaded application code (namespace: Darkheim\)
│   ├── Application/                # Use-case / feature layer
│   │   ├── Account/
│   │   │   └── Account.php         # Account read/write helpers
│   │   ├── Auth/
│   │   │   ├── AdminGuard.php      # AdminCP access check (canAccess)
│   │   │   ├── AuthService.php     # Core authentication logic
│   │   │   ├── Common.php          # Shared auth + IP-block helpers
│   │   │   ├── Login.php           # Login handler
│   │   │   └── SessionManager.php  # Session lifecycle
│   │   ├── CastleSiege/
│   │   │   └── CastleSiege.php     # Castle siege data access
│   │   ├── Character/
│   │   │   └── Character.php       # Character read/write helpers
│   │   ├── Credits/
│   │   │   └── CreditSystem.php    # Credits & donation logic
│   │   ├── Game/
│   │   │   └── GameHelper.php      # Class avatars, maps, PK levels, Gens, guild logo
│   │   ├── Helpers/
│   │   │   ├── Encoder.php         # URL-safe Base64 encode/decode
│   │   │   └── TimeHelper.php      # sec_to_hms / sec_to_dhms conversions
│   │   ├── Language/
│   │   │   ├── LanguageRepository.php  # Lists installed language packs
│   │   │   └── Translator.php      # lang() / langf() — phrase lookup + debug wrapping
│   │   ├── News/
│   │   │   ├── NewsItem.php        # News value object
│   │   │   ├── NewsRepository.php  # DB-backed news reads
│   │   │   └── NewsService.php     # News orchestration
│   │   ├── Page/                   # ★ Top-level page controllers (one per public route)
│   │   │   ├── CastleSiegeController.php
│   │   │   ├── ContactController.php
│   │   │   ├── DonationController.php
│   │   │   ├── DownloadsController.php
│   │   │   ├── ForgotPasswordController.php
│   │   │   ├── HomeController.php
│   │   │   ├── InfoController.php
│   │   │   ├── LoginController.php
│   │   │   ├── LogoutController.php
│   │   │   ├── NewsController.php
│   │   │   ├── PrivacyController.php
│   │   │   ├── RankingsController.php
│   │   │   ├── RankingsSectionController.php
│   │   │   ├── RefundsController.php
│   │   │   ├── RegisterController.php
│   │   │   ├── TosController.php
│   │   │   ├── UsercpController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── Profile/
│   │   │   ├── ProfileRenderer.php # Player / guild profile link builder
│   │   │   └── ProfileRepository.php
│   │   ├── Rankings/
│   │   │   ├── RankingCache.php    # Cache read/write for rankings
│   │   │   ├── RankingRepository.php
│   │   │   └── RankingsService.php
│   │   ├── View/
│   │   │   └── MessageRenderer.php # Toast (popup) and inline styled messages
│   │   └── Vote/
│   │       ├── Vote.php
│   │       └── VoteSiteRepository.php
│   │
│   ├── Domain/
│   │   └── Validator.php           # Input validation helpers (hasValue, Email, Ip, …)
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
│       │   ├── Handler.php                  # Entry point — loadPage() / loadModule() / loadAdminCPModule()
│       │   ├── AdmincpModuleDispatcher.php  # Locates & includes AdminCP module files, injects context via extract()
│       │   ├── ControllerRouteDispatcher.php# Resolves page name → Controller via WebRouteRegistry and calls render()
│       │   ├── LanguageBootstrapper.php     # Applies session language override before rendering
│       │   ├── ModuleRouteResolver.php      # Normalises (?page=x&subpage=y) into a typed route descriptor
│       │   ├── PageAccessDispatcher.php     # Enforces the `access` constant and renders the theme shell
│       │   ├── RequestParameterParser.php   # Populates QueryStore from raw $_GET on each request
│       │   ├── RouteInputSanitizer.php      # Strips dangerous chars from $page / $subpage tokens
│       │   ├── SubpageRouteDispatcher.php   # Dispatches sub-page template routes (config/routes.subpages.php)
│       │   ├── SubpageRouteRegistry.php     # Loads & caches config/routes.subpages.php route table
│       │   └── WebRouteRegistry.php         # Loads & caches config/routes.web.php route table
│       ├── Runtime/
│       │   ├── SessionStore.php    # Session abstraction + native adapter
│       │   ├── QueryStore.php      # `$_GET` abstraction + native adapter
│       │   ├── RequestStore.php    # `$_REQUEST` abstraction + native adapter
│       │   ├── PostStore.php       # `$_POST` abstraction + native adapter
│       │   └── ServerContext.php   # Server metadata accessor (`REMOTE_ADDR`, etc.)
│       └── Security/
│           └── IpBlocker.php       # Checks REMOTE_ADDR against blocked_ip.cache
│       ├── Theme/
│       │   └── DefaultThemeLayoutBuilder.php # Prepares default theme layout context (navbar, sidebar, footer, assets)
│
├── vendor/                         # Composer-managed dependencies (do not edit)
│   ├── autoload.php
│   ├── google/recaptcha/
│   └── phpmailer/phpmailer/
│
├── public/                         # ★ DocumentRoot — only this directory is web-accessible
│   ├── index.php                   # Web entry point → ../includes/bootstrap/boot.php
│   ├── .htaccess                   # mod_rewrite routing rules
│   ├── robots.txt
│   ├── assets/                     # Global CSS / JS bundles
│   ├── img/                        # Public static images
│   │   └── flags/                  # Country flag GIFs (ISO 3166-1 alpha-2)
│   ├── admincp/                    # Admin control panel (Bootstrap 5, separate auth)
│   │   ├── index.php               # Admin entry point
│   │   ├── css/                    # Admin-specific styles
│   │   ├── js/                     # Admin-specific scripts
│   │   ├── inc/                    # Auth check + admin helpers
│   │   └── modules/                # Admin feature modules
│   ├── api/                        # Public REST-like endpoints
│   │   ├── castlesiege.php
│   │   ├── cron.php                # Cron trigger (Docker cron or external)
│   │   ├── events.php              # Standalone — does NOT bootstrap boot.php
│   │   ├── guildmark.php
│   │   ├── paypal.php              # PayPal IPN receiver
│   │   ├── servertime.php
│   │   └── version.php
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
│   ├── routes.web.php              # Top-level controller route table (WebRouteRegistry)
│   ├── routes.subpages.php         # Sub-page route table (SubpageRouteRegistry)
│   ├── routing-migration.json      # Machine-readable migration status per page
│   ├── writable.json               # Paths checked for write permissions on install
│   └── modules/                    # Per-module XML configs (feature toggles)
│
├── includes/                       # CMS bootstrap layer (NOT web-accessible)
│   ├── bootstrap/
│   │   ├── boot.php                # Entry point: loads autoloader + boots AppKernel
│   │   └── compat.php              # Global function shim — thin wrappers over src/ classes
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
  └── ../includes/bootstrap/boot.php    ← composition root
        ├── vendor/autoload.php         ← Composer PSR-4 autoloader
        └── AppKernel::boot()           ← Darkheim\Infrastructure\Bootstrap\AppKernel
              ├── ConfigProvider        ← reads config/config.json + XML configs
              ├── RuntimeState          ← in-memory bag: language phrases, module config
              ├── config/tables.php
              ├── config/timezone-config.php
              ├── includes/bootstrap/compat.php
              ├── plugin files          ← from var/cache/plugins.cache
              └── Handler::loadPage()  ← Darkheim\Infrastructure\Routing\Handler
```

## Path constants defined by AppKernel

| Constant | Points to |
| :--- | :--- |
| `__ROOT_DIR__` | Project root filesystem path |
| `__PUBLIC_DIR__` | `public/` filesystem path (DocumentRoot) |
| `__PATH_INCLUDES__` | `includes/` filesystem path |
| `__PATH_VIEWS__` | `views/` filesystem path — permanent view templates |
| `__PATH_THEMES__` | `public/themes/` filesystem path |
| `__PATH_CONFIGS__` | `config/` filesystem path |
| `__PATH_CACHE__` | `var/cache/` filesystem path |
| `__PATH_LOGS__` | `var/logs/` filesystem path |
| `__BASE_URL__` | Site URL (e.g. `https://example.com/`) |
| `__PATH_IMG__` | `__BASE_URL__ . 'img/'` |
| `__PATH_ASSETS__` | `__BASE_URL__ . 'assets/'` |
| `__PATH_API__` | `__BASE_URL__ . 'api/'` |
| `__PATH_ADMINCP__` | `public/admincp/` filesystem path |

## Runtime boundary

Classes under `src/` avoid reading PHP superglobals directly. Runtime state is funneled through small adapters in `src/Infrastructure/Runtime/`:

| Adapter | Wraps | Used by |
| :--- | :--- | :--- |
| `SessionStore` | `$_SESSION` | `SessionManager`, `Login`, `Plugins`, `Handler` |
| `QueryStore` | `$_GET` | `Handler`, `CreditSystem` |
| `RequestStore` | `$_REQUEST` | `RankingsService` |
| `PostStore` | `$_POST` | `PaypalIPN` |
| `ServerContext` | `$_SERVER` | `Login`, `Account`, `CreditSystem` |

This keeps the composition root in `includes/bootstrap/boot.php` explicit while making namespaced services easier to test in isolation.

## Namespace map

All classes under `src/` are autoloaded via Composer PSR-4 with the root namespace `Darkheim\`:

| Namespace | Directory | Purpose |
| :--- | :--- | :--- |
| `Darkheim\Application\Account\*` | `src/Application/Account/` | Account read/write helpers |
| `Darkheim\Application\Auth\*` | `src/Application/Auth/` | Authentication, session, AdminCP guard |
| `Darkheim\Application\CastleSiege\*` | `src/Application/CastleSiege/` | Castle siege data access |
| `Darkheim\Application\Character\*` | `src/Application/Character/` | Character read/write helpers |
| `Darkheim\Application\Credits\*` | `src/Application/Credits/` | Credits & donation logic |
| `Darkheim\Application\Game\*` | `src/Application/Game/` | Class avatars, maps, Gens, guild logo |
| `Darkheim\Application\Helpers\*` | `src/Application/Helpers/` | Encoder, TimeHelper |
| `Darkheim\Application\Language\*` | `src/Application/Language/` | Translator, LanguageRepository |
 `Darkheim\Application\News\*`  `src/Application/News/`  News value object, repository, service 
 `Darkheim\Application\Page\*`  `src/Application/Page/`  Top-level page controllers (one per public route) 
 `Darkheim\Application\Profile\*`  `src/Application/Profile/`  Profile link builder, repository 
| `Darkheim\Application\Rankings\*` | `src/Application/Rankings/` | Ranking cache, repository, service |
| `Darkheim\Application\View\*` | `src/Application/View/` | MessageRenderer (toast + inline) |
| `Darkheim\Application\Vote\*` | `src/Application/Vote/` | Vote tracking |
| `Darkheim\Domain\*` | `src/Domain/` | Pure domain helpers (Validator) |
| `Darkheim\Infrastructure\Bootstrap\*` | `src/Infrastructure/Bootstrap/` | AppKernel, ConfigProvider, RuntimeState, BootstrapContext |
| `Darkheim\Infrastructure\Cache\*` | `src/Infrastructure/Cache/` | CacheBuilder, CacheRepository, CacheManager |
| `Darkheim\Infrastructure\Config\*` | `src/Infrastructure/Config/` | JSON/XML config readers |
| `Darkheim\Infrastructure\Cron\*` | `src/Infrastructure/Cron/` | CronManager |
| `Darkheim\Infrastructure\Database\*` | `src/Infrastructure/Database/` | PDO wrapper + connection factory |
| `Darkheim\Infrastructure\Email\*` | `src/Infrastructure/Email/` | PHPMailer wrapper |
| `Darkheim\Infrastructure\Helpers\*` | `src/Infrastructure/Helpers/` | FileHelper (JSON, directories, file size) |
| `Darkheim\Infrastructure\Http\*` | `src/Infrastructure/Http/` | Redirector, GeoIpService |
| `Darkheim\Infrastructure\Payment\*` | `src/Infrastructure/Payment/` | PayPal IPN |
| `Darkheim\Infrastructure\Plugins\*` | `src/Infrastructure/Plugins/` | Plugin loader |
| `Darkheim\Infrastructure\Routing\*` | `src/Infrastructure/Routing/` | Handler, Controller/Subpage/AdminCP dispatchers, registries, sanitizers |
| `Darkheim\Infrastructure\Theme\*` | `src/Infrastructure/Theme/` | Theme layout context builders (`DefaultThemeLayoutBuilder`) |
| `Darkheim\Infrastructure\View\*` | `src/Infrastructure/View/` | ViewRenderer — theme-aware template engine |
| `Darkheim\Infrastructure\Runtime\*` | `src/Infrastructure/Runtime/` | Request/session/server boundary adapters |
| `Darkheim\Infrastructure\Security\*` | `src/Infrastructure/Security/` | IpBlocker |

## Global function shim (`compat.php`)

`includes/bootstrap/compat.php` is a **backward-compatibility layer** — it declares the global
procedural functions that legacy modules call, but contains no logic itself. Each function is a
one-to-three-line wrapper that casts arguments and delegates to the matching `src/` class.

> **Rule:** never add business logic to `compat.php`. If you need a new helper, create a class in
> `src/` and add a thin wrapper here only if legacy call-sites need it.

| Global function | Delegates to |
| :--- | :--- |
| `check_value()` | `Validator::hasValue()` |
| `redirect()` | `Redirector::go()` |
| `isLoggedIn()` / `logOutUser()` | `SessionManager` |
| `canAccessAdminCP()` | `AdminGuard::canAccess()` |
| `message()` / `inline_message()` | `MessageRenderer::toast()` / `::inline()` |
| `lang()` / `langf()` | `Translator::phrase()` / `::phraseFmt()` |
| `config()` / `cmsConfigs()` | `ConfigProvider::cms()` via `bootstrapConfigProvider()` |
| `loadConfigurations()` / `loadConfig()` / `mconfig()` / `gconfig()` | `ConfigProvider` + `RuntimeState` |
| `BuildCacheData()` / `UpdateCache()` / `encodeCache()` | `CacheBuilder` |
| `LoadCacheData()` / `loadCache()` / `updateCacheFile()` | `CacheRepository` |
| `sec_to_hms()` / `sec_to_dhms()` | `TimeHelper` |
| `updateCronLastRun()` / `getCronList()` | `CronManager` |
| `getPlayerClass()` / `getPlayerClassAvatar()` / `returnMapName()` / `returnPkLevel()` / `getGensRank()` / `getGensLeadershipRank()` / `returnGuildLogo()` | `GameHelper` |
| `playerProfile()` / `guildProfile()` | `ProfileRenderer` |
| `checkBlockedIp()` | `IpBlocker::isCurrentIpBlocked()` |
| `getCountryCodeFromIp()` / `getCountryFlag()` | `GeoIpService` |
| `loadJsonFile()` / `readableFileSize()` / `getDirectoryListFromPath()` | `FileHelper` |
| `getInstalledLanguagesList()` | `LanguageRepository::getInstalled()` |
| `base64url_encode()` / `base64url_decode()` | `Encoder` |

## What to edit vs. what not to touch

| Path | Edit? | Notes |
| :--- | :---: | :--- |
| `src/` | ✅ | Application / domain / infrastructure classes |
| `views/` | ✅ | View templates — write once, works for all themes |
| `views/subpages/` | ✅ | Sub-page templates dispatched by `SubpageRouteDispatcher` |
| `public/themes/{theme}/views/` | ✅ | Optional per-theme template overrides (only when markup must differ) |
| `public/themes/default/index.php` | ✅ | Keep logic-light — render prepared layout data only |
| `public/themes/default/inc/modules/*.php` | ✅ | Pure partials only; no runtime/config/service calls |
| `includes/bootstrap/compat.php` | ⚠️ | Add wrappers only; no logic — logic goes in `src/` |
| `includes/bootstrap/boot.php` | ❌ | Entry point — do not add logic here |
| `config/config.json` | ✅ | Main config: DB credentials, server name, feature toggles |
| `config/routes.web.php` | ✅ | Top-level controller route table — add new page routes here |
| `config/routes.subpages.php` | ✅ | Sub-page route table — add new sub-page routes here |
| `config/routing-migration.json` | ✅ | Machine-readable migration status — keep in sync with route tables |
| `public/assets/css/*.css` | ✅ | Global page/component styles |
| `public/themes/default/css/*.css` | ✅ | Template layout styles |
| `public/themes/default/js/*.js` | ✅ | Template JS |
| `includes/languages/*/language.php` | ✅ | Translation phrases |
| `vendor/` | ❌ | Managed by Composer — run `composer install` / `composer update` |
| `var/cache/` | ❌ | Runtime cache managed by CMS |
| `var/logs/` | ❌ | Runtime logs managed by CMS |

## Controller-backed views and pure templates

DarkCore now treats controllers as the only place where request handling, config access, cache reads,
service orchestration, redirects, and messages are prepared.

**View / theme rule:** templates should contain only markup plus simple `echo`, `if`, and `foreach`
over already-prepared values.

Do **not** add these directly to `views/` or `public/themes/default/` templates:

- `$_GET`, `$_POST`, `$_REQUEST`, `$_SESSION`
- `config()`, `mconfig()`, `loadCache()`, `LoadCacheData()`
- `message()`, `redirect()`, `loadModuleConfigs()`
- `new ServiceClass(...)`

Examples already following this rule:

- `Darkheim\Application\Page\RankingsSectionController` → `views/ranking.php`
- `Darkheim\Application\Subpage\Usercp\AbstractCharacterActionTableSubpageController` → `views/subpages/usercp/actiontables.php`
- `Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder` → `public/themes/default/index.php` + `inc/modules/*.php`

## Adding a new controller-backed view

1. Create or update a controller under `src/Application/Page/` or `src/Application/Subpage/...`.
2. Prepare a complete view-model array in the controller (formatted text, URLs, CSS classes, booleans, row data).
3. Register the route in `config/routes.web.php` or `config/routes.subpages.php`.
4. Render a template via `Darkheim\Infrastructure\View\ViewRenderer`.
5. Keep the template logic-light and add/update routing tests in `tests/Unit/Infrastructure/Routing/`.

For repeated layouts, prefer one shared template over many near-identical files. Current examples:

- `views/ranking.php` for all `rankings/*` sub-routes
- `views/subpages/usercp/actiontables.php` for repeated UserCP character action tables

### Recipe: add a top-level page

1. Create `src/Application/Page/<Name>Controller.php` with `render(): void`.
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
2. If the theme needs new data, add it to `Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder`.
3. Do not read request/session/config/cache directly from theme templates.
4. Keep optional per-theme view overrides in `public/themes/{theme}/views/` only when the markup must differ from `views/`.

