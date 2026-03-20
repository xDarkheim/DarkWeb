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
│   │   │   ├── Common.php          # Shared auth helpers
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
│       │   ├── ConfigProvider.php  # Loads cms.json, module XML configs
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
│       ├── Routing/
│       │   └── Handler.php         # Request dispatcher — loadPage() / loadModule()
│       ├── Runtime/
│       │   ├── SessionStore.php    # Session abstraction + native adapter
│       │   ├── QueryStore.php      # `$_GET` abstraction + native adapter
│       │   ├── RequestStore.php    # `$_REQUEST` abstraction + native adapter
│       │   ├── PostStore.php       # `$_POST` abstraction + native adapter
│       │   └── ServerContext.php   # Server metadata accessor (`REMOTE_ADDR`, etc.)
│       └── Security/
│           └── IpBlocker.php       # Checks REMOTE_ADDR against blocked_ip.cache
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
│           ├── index.php           # Template entry point — injects CSS/JS, renders layout
│           ├── css/                # Template-level CSS
│           ├── js/                 # Template JS
│           ├── img/                # Template images
│           ├── fonts/              # Local webfonts
│           └── inc/                # Server-side partials (PHP includes)
│               ├── theme.functions.php
│               └── modules/
│                   ├── footer.php
│                   └── sidebar.php
│
├── includes/                       # CMS bootstrap layer (NOT web-accessible)
│   ├── bootstrap/
│   │   ├── boot.php                # Entry point: loads autoloader + boots AppKernel
│   │   └── compat.php              # Global function shim — thin wrappers over src/ classes
│   ├── config/                     # Configuration files (see configuration.md)
│   │   ├── cms.json                # Main config (DB, language, server info, …)
│   │   ├── cms.json.default        # Template for fresh installs
│   │   ├── cms.tables.php          # Core DB column name mappings
│   │   ├── custom.tables.php       # Project-specific column overrides
│   │   ├── castlesiege.json        # Castle Siege config
│   │   ├── usercp.json             # UserCP sidebar menu items
│   │   ├── navbar.json             # Navigation bar items
│   │   ├── email.xml               # Email templates
│   │   ├── timezone.php            # date_default_timezone_set()
│   │   ├── modules/                # Per-module XML configs (feature toggles)
│   │   └── writable.paths.json     # Paths checked for write permissions on install
│   ├── languages/                  # Phrase files — one PHP file per language code
│   ├── emails/                     # Email template helpers
│   ├── cron/                       # Cron job scripts
│   └── plugins/                    # Runtime plugin files
│
├── var/                            # Runtime-only data (NOT web-accessible)
│   ├── cache/                      # JSON/text caches, ranking caches, news cache, plugins cache
│   └── logs/                       # PHP and DB error logs
│
├── modules/                        # Frontend page modules (NOT web-accessible directly)
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── news.php
│   ├── rankings.php
│   ├── usercp.php
│   ├── donation.php
│   ├── info.php
│   ├── downloads.php
│   ├── contact.php
│   ├── forgotpassword.php
│   ├── castlesiege.php
│   ├── tos.php / privacy.php / refunds.php
│   └── usercp/                     # UserCP sub-page modules
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
              ├── ConfigProvider        ← reads includes/config/cms.json + XML configs
              ├── RuntimeState          ← in-memory bag: language phrases, module config
              ├── includes/config/cms.tables.php
              ├── includes/config/timezone.php
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
| `__PATH_MODULES__` | `modules/` filesystem path |
| `__PATH_THEMES__` | `public/themes/` filesystem path |
| `__PATH_CONFIGS__` | `includes/config/` filesystem path |
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
| `Darkheim\Application\News\*` | `src/Application/News/` | News value object, repository, service |
| `Darkheim\Application\Profile\*` | `src/Application/Profile/` | Profile link builder, repository |
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
| `Darkheim\Infrastructure\Routing\*` | `src/Infrastructure/Routing/` | Request handler / module loader |
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
| `includes/bootstrap/compat.php` | ⚠️ | Add wrappers only; no logic — logic goes in `src/` |
| `includes/bootstrap/boot.php` | ❌ | Entry point — do not add logic here |
| `includes/config/cms.json` | ✅ | Main config: DB credentials, server name, feature toggles |
| `public/assets/css/*.css` | ✅ | Global page/component styles |
| `public/themes/default/css/*.css` | ✅ | Template layout styles |
| `public/themes/default/js/*.js` | ✅ | Template JS |
| `modules/usercp/*.php` | ✅ | Individual UserCP sub-pages |
| `includes/languages/*/language.php` | ✅ | Translation phrases |
| `vendor/` | ❌ | Managed by Composer — run `composer install` / `composer update` |
| `var/cache/` | ❌ | Runtime cache managed by CMS |
| `var/logs/` | ❌ | Runtime logs managed by CMS |

