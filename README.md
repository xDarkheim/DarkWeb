<div align="center">

# DarkCore CMS

**Open-source CMS for MU Online private servers**

[![CI](https://img.shields.io/github/actions/workflow/status/xDarkheim/DarkCore/ci.yml?style=flat-square&label=CI)](https://github.com/xDarkheim/DarkCore/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Apache](https://img.shields.io/badge/Apache-2.4%2B-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![SQL Server](https://img.shields.io/badge/SQL_Server-MSSQL-CC2927?style=flat-square&logo=microsoftsqlserver&logoColor=white)](https://www.microsoft.com/en-us/sql-server)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

</div>

---

## Stack

| Layer | |
| :--- | :--- |
| **Backend** | PHP 8.4, Apache 2.4+ (`mod_rewrite`), Composer 2 (PSR-4) |
| **Database** | Microsoft SQL Server via `pdo_dblib` (FreeTDS) |
| **Frontend** | Bootstrap 3, Vanilla JS, fully mobile responsive |
| **Runtime** | Docker + docker compose |

> **Emulator compatibility:** X-Team, MuEmu, Louis, Darkheim Emulator.
> IGCN, zTeam, OpenMU and other emulators are not yet supported.

---

## Quick start

```bash
git clone https://github.com/xDarkheim/DarkCore DarkCore
cd DarkCore

cp includes/config/cms.json.default includes/config/cms.json
cp docker/config.env.example docker/config.env

# Edit both files with your credentials, then:
docker compose up -d --build
```

Open `https://your-domain/install/` in the browser, complete the setup wizard, then **delete the `install/` directory**.

---

## Features

| Feature | Details |
| :--- | :--- |
| Authentication | Registration, login, forgot password, email verification |
| Character management | View and edit character stats |
| Rankings | Player and guild rankings with class filter |
| News | Multi-language news with translations |
| Donations & credits | PayPal integration and credits system |
| Admin panel | Full-featured control panel (`admincp/`) |
| Plugins | Runtime-loadable plugin system |
| Multi-language | EN, RU, CN, ES, PT, RO |
| Mobile responsive | Hamburger menu, stacking grid, touch-friendly tables |
| Cron & API | Scheduled tasks via `api/cron.php` |
| Info page | Server rates, character classes, game features, maps |

---

## Documentation

| Document | What it covers |
| :--- | :--- |
| [`docs/project-structure.md`](docs/project-structure.md) | Directory layout, bootstrap path, namespace map |
| [`docs/configuration.md`](docs/configuration.md) | `cms.json` keys, `docker/config.env` variables |
| [`docs/deployment.md`](docs/deployment.md) | Docker setup, reverse proxy, useful commands |
| [`docs/build.md`](docs/build.md) | Frontend assets, CSS/JS load order, cache busting |
| [`docs/css-architecture.md`](docs/css-architecture.md) | CSS naming conventions, dark mode, mobile breakpoints |
| [`docs/phpunit.md`](docs/phpunit.md) | Running tests, writing tests, IDE setup, Xdebug |
| [`docs/phpstan.md`](docs/phpstan.md) | Static analysis, suppression rules, common errors |
| [`CHANGELOG.md`](CHANGELOG.md) | Version history and change log |

---

## Architecture notes

- `src/Infrastructure/Bootstrap/` owns the composition-root logic through `AppKernel`, `ConfigProvider`, `RuntimeState`, and `TimezoneInitializer`.
- `src/Infrastructure/Runtime/` contains the runtime boundary for request, post, query, session, and server access.
- Classes in `src/` depend on these adapters instead of reading PHP superglobals directly.
- `includes/bootstrap/boot.php` — thin entry point; loads Composer autoloader and boots `AppKernel`.
- `includes/bootstrap/compat.php` — **global function shim**; every function is a one-liner that delegates to a namespaced class. Legacy modules keep working without change; new code calls the class directly.
- All business logic that was previously in `includes/bootstrap/functions.php` now lives in proper namespaced classes: `GameHelper`, `MessageRenderer`, `Redirector`, `Translator`, `ProfileRenderer`, `SessionManager`, `AdminGuard`, `IpBlocker`, `GeoIpService`, `CacheBuilder`, `CacheRepository`, `TimeHelper`, `Encoder`, `FileHelper`, `LanguageRepository`, and others.

---

## Development

```bash
composer test              # PHPUnit
composer analyse           # PHPStan
vendor/bin/php-cs-fixer fix  # code style
```

---

## License

MIT License © 2026 [Dmytro Hovenko](https://darkheim.net)
