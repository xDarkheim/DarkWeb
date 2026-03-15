# Project Structure

This map shows where the main CMS components live and which paths are safe to modify.

```
/
├── src/                            # PSR-4 autoloaded application code (namespace: Darkheim\)
│   ├── Application/                # Use-case / feature layer
│   │   ├── Account/
│   │   │   └── Account.php         # Account read/write helpers
│   │   ├── Auth/
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
│   │   ├── News/
│   │   │   ├── NewsItem.php        # News value object
│   │   │   ├── NewsRepository.php  # DB-backed news reads
│   │   │   └── NewsService.php     # News orchestration
│   │   ├── Profile/
│   │   │   └── ProfileRepository.php
│   │   ├── Rankings/
│   │   │   ├── RankingCache.php    # Cache read/write for rankings
│   │   │   ├── RankingRepository.php
│   │   │   └── RankingsService.php
│   │   └── Vote/
│   │       ├── Vote.php
│   │       └── VoteSiteRepository.php
│   │
│   ├── Domain/
│   │   └── Validator.php           # Input validation helpers
│   │
│   └── Infrastructure/             # I/O, frameworks, DB drivers
│       ├── Cache/
│       │   ├── CacheManager.php
│       │   └── CacheRepository.php
│       ├── Config/
│       │   ├── ConfigRepository.php
│       │   ├── JsonConfigReader.php
│       │   └── XmlConfigReader.php
│       ├── Cron/
│       │   └── CronManager.php
│       ├── Database/
│       │   ├── Connection.php      # Factory: Connection::Database('MuOnline')
│       │   ├── DatabaseFactory.php
│       │   └── dB.php              # PDO wrapper — always uses pdo_dblib (FreeTDS)
│       ├── Email/
│       │   └── Email.php           # PHPMailer wrapper
│       ├── Payment/
│       │   └── PaypalIPN.php       # PayPal IPN handler
│       ├── Plugins/
│       │   └── Plugins.php
│       └── Routing/
│           └── Handler.php         # Request dispatcher — loadPage() / loadModule()
│
├── vendor/                         # Composer-managed dependencies (do not edit)
│   ├── autoload.php
│   ├── google/recaptcha/
│   └── phpmailer/phpmailer/
│
├── admincp/                        # Admin control panel (Bootstrap 5, separate auth)
│   ├── index.php                   # Admin entry point
│   ├── css/                        # Admin-specific styles
│   ├── js/                         # Admin-specific scripts
│   ├── inc/                        # Auth check + admin helpers
│   └── modules/                    # Admin feature modules
│
├── api/                            # Public REST-like endpoints
│   ├── castlesiege.php
│   ├── cron.php                    # Cron trigger (Docker cron or external)
│   ├── events.php                  # Standalone — does NOT bootstrap cms.php
│   ├── guildmark.php
│   ├── paypal.php                  # PayPal IPN receiver
│   ├── servertime.php
│   └── version.php
│
├── img/                            # Public static images
│   ├── flags/                      # Country flag GIFs (ISO 3166-1 alpha-2)
│   └── brand.jpg
│
├── includes/                       # CMS bootstrap layer (not web-accessible)
│   ├── cms.php                     # Bootstrap: autoload, config, routing
│   ├── functions.php               # Global procedural helpers
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
│   ├── cache/                      # Runtime cache (auto-created, web-blocked)
│   ├── logs/                       # Runtime logs  (auto-created, web-blocked)
│   ├── emails/                     # Email template helpers
│   ├── cron/                       # Cron job scripts
│   └── plugins/                    # Runtime plugin files
│
├── modules/                        # Frontend page modules (Bootstrap 3 + jQuery 2)
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
├── templates/
│   └── default/                    # Default dark-fantasy theme (Bootstrap 3)
│       ├── index.php               # Template entry point — injects all CSS/JS, renders layout
│       ├── css/                    # Template-level CSS (style.css, override.css, profiles.css, castle-siege.css)
│       ├── js/                     # Template JS (main.js, events.js)
│       ├── img/                    # Template images (logo, backgrounds, social icons, profiles)
│       ├── fonts/                  # Local webfonts
│       └── inc/                    # Partials
│           ├── template.functions.php  # templateBuildNavbar(), templateLanguageSelector(), etc.
│           └── modules/
│               ├── footer.php      # Footer HTML
│               └── sidebar.php     # Sidebar (login form or UserCP menu)
│
├── install/                        # Web-based installer (remove after setup)
│
├── docker/
│   ├── Dockerfile                  # PHP 8.4 + Apache + FreeTDS + pdo_dblib
│   ├── entrypoint.sh               # Container startup: dirs, permissions, cron
│   └── npm/                        # Optional server-level proxy helper stack
│
├── composer.json                   # Declares dependencies + PSR-4 autoload map
├── composer.lock                   # Locked dependency tree (commit this)
├── docker-compose.yml
├── docker-compose.override.yml     # Server-only (not committed)
├── index.php                       # Web entry point → includes/cms.php
└── .htaccess                       # mod_rewrite routing rules
```

## Bootstrap path

```
index.php
  └── includes/cms.php              ← composition root
        ├── vendor/autoload.php     ← Composer PSR-4 autoloader
        ├── includes/config/cms.tables.php
        ├── includes/config/timezone.php
        ├── includes/functions.php
        ├── cmsConfigs()            ← reads includes/config/cms.json
        ├── plugin files            ← from includes/cache/plugins.cache
        └── Handler::loadPage()    ← Darkheim\Infrastructure\Routing\Handler
```

## Namespace map

All classes under `src/` are autoloaded via Composer PSR-4 with the root namespace `Darkheim\`:

| Namespace | Directory | Purpose |
|-----------|-----------|---------|
| `Darkheim\Application\*` | `src/Application/` | Feature use-cases and repositories |
| `Darkheim\Domain\*` | `src/Domain/` | Pure domain helpers (Validator, etc.) |
| `Darkheim\Infrastructure\Database\*` | `src/Infrastructure/Database/` | PDO wrapper + connection factory |
| `Darkheim\Infrastructure\Routing\*` | `src/Infrastructure/Routing/` | Request handler / module loader |
| `Darkheim\Infrastructure\Cache\*` | `src/Infrastructure/Cache/` | Cache read/write |
| `Darkheim\Infrastructure\Config\*` | `src/Infrastructure/Config/` | JSON/XML config readers |
| `Darkheim\Infrastructure\Email\*` | `src/Infrastructure/Email/` | PHPMailer wrapper |
| `Darkheim\Infrastructure\Payment\*` | `src/Infrastructure/Payment/` | PayPal IPN |
| `Darkheim\Infrastructure\Plugins\*` | `src/Infrastructure/Plugins/` | Plugin loader |
| `Darkheim\Infrastructure\Cron\*` | `src/Infrastructure/Cron/` | Cron manager |

## What to edit vs. what not to touch

| Path | Edit? | Notes |
|------|-------|-------|
| `src/` | Yes | Application / domain / infrastructure classes |
| `includes/config/cms.json` | Yes | Main config: DB credentials, server name, feature toggles |
| `assets/css/*.css` | Yes | Page/component styles — add filename to `$_cssFiles` in `templates/default/index.php` |
| `templates/default/css/*.css` | Yes | Template layout styles — add `<link>` tag in `templates/default/index.php` before `override.css` |
| `templates/default/js/*.js` | Yes | Template JS — add `<script>` tag in `templates/default/index.php` |
| `modules/usercp/*.php` | Yes | Individual UserCP sub-pages |
| `includes/languages/*/language.php` | Yes | Translation phrases |
| `vendor/` | No | Managed by Composer — run `composer install` / `composer update` |
| `includes/cache/` | No | Runtime cache managed by CMS |
| `includes/logs/` | No | Runtime logs managed by CMS |
