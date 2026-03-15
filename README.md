<div align="center">

# DarkWeb CMS

**Open-source CMS for MU Online private servers**

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-2-885630?style=flat-square&logo=composer&logoColor=white)](https://getcomposer.org/)
[![Apache](https://img.shields.io/badge/Apache-2.4%2B-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![SQL Server](https://img.shields.io/badge/SQL_Server-MSSQL-CC2927?style=flat-square&logo=microsoftsqlserver&logoColor=white)](https://www.microsoft.com/en-us/sql-server)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Version](https://img.shields.io/badge/Version-0.0.1-0f0f0f?style=flat-square)](CHANGELOG.md)

</div>

---

## ⚠️ Emulator compatibility

> **DarkWeb currently works with [X-Team](https://github.com/xTeam-emu), [MuEmu](https://github.com/muemu), [Louis](https://github.com/djnandinho26/MUEMU) and **Darkheim Emulator** builds.**
>
> The database table structure is based on the **X-Team / MuEmu / Louis** schema (`MasterSkillTree`, `ResetCount`, `MasterResetCount`, `Gens_Rank`, etc.).
>
> Compatibility with other MU Online emulators (e.g. IGCN, zTeam, OpenMU) is **not yet implemented**.
> Contributions and pull requests to extend compatibility are welcome.

---

## At a glance

| Area | Details |
|------|---------|
| Backend | PHP 8.4, Apache 2.4+ (`mod_rewrite`), Composer 2 (PSR-4 autoload) |
| Database | Microsoft SQL Server via `pdo_dblib` (Linux / FreeTDS) |
| Frontend | Bootstrap 3, Vanilla JS, CSS bundles — fully mobile responsive |
| Runtime | Docker + docker compose |
| License | MIT |

---

## Quick start

```bash
git clone https://github.com/xDarkheim/DarkWeb DarkWeb
cd DarkWeb

# App config — database credentials, site settings
cp includes/config/cms.json.default includes/config/cms.json

# Docker config — domain, timezone, cron, Xdebug
cp docker/config.env.example docker/config.env

# Edit both files, then build and start
docker compose up -d --build
```

Then open `https://your-domain/install/` in the browser, complete setup, and remove `install/`.

> See [`docs/deployment.md`](docs/deployment.md) for the full guide including reverse proxy and container naming.

---

## Documentation

All project docs live in [`docs/`](docs/):

| File | What it covers |
|------|----------------|
| [`docs/project-structure.md`](docs/project-structure.md) | Bootstrap path, module routing, key directories |
| [`docs/configuration.md`](docs/configuration.md) | `cms.json` keys, `docker/config.env`, config files |
| [`docs/build.md`](docs/build.md) | Frontend assets, load order, cache busting |
| [`docs/css-architecture.md`](docs/css-architecture.md) | Layout classes, CSS naming conventions, mobile |
| [`docs/deployment.md`](docs/deployment.md) | Docker setup, reverse proxy, container naming |
| [`docs/phpunit.md`](docs/phpunit.md) | Running tests, coverage |
| [`docs/phpstan.md`](docs/phpstan.md) | Static analysis |

---

## Features

| Feature | Description |
|---------|-------------|
| Authentication | Registration, login, forgot password, email verification |
| Character management | View and edit character stats |
| Rankings | Player and guild ranking pages with class filter |
| News | Multi-language news with translations |
| Donations & credits | PayPal integration and credits system |
| Admin panel | Full-featured control panel (`admincp/`) |
| Plugins | Runtime-loadable plugin system |
| Multi-language | Built-in EN, RU, CN, ES, PT, RO |
| Mobile responsive | Hamburger menu, stacking grid, touch-friendly tables |
| Cron & API | Scheduled tasks via `api/cron.php` |
| Info page | Server rates, 10 character classes, game features, maps |

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you'd like to change.

Please make sure to update tests as appropriate and run:

```bash
composer test       # PHPUnit
composer analyse    # PHPStan
```

---

## License

[MIT](LICENSE) © 2024-2026 [Dmytro Hovenko](https://darkheim.net)
