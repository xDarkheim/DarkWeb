# DarkWeb — Documentation

Reference guides for developers and server administrators.

> **License:** [MIT](../LICENSE) — open source, free to use, modify and distribute.
>
> **Emulator compatibility:** X-Team, MuEmu, Louis, Darkheim Emulator (IGCN and other emulators not yet supported).

## Read first

If you are new to this codebase, start here:

1. [Project Structure](project-structure.md)
2. [Configuration](configuration.md)
3. [Deployment](deployment.md)

## Documentation index

| Document | Focus |
|---|---|
| [Project Structure](project-structure.md) | Bootstrap path, module routing, where to make changes |
| [Configuration](configuration.md) | `cms.json`, `docker/config.env`, config file map, security notes |
| [Frontend Assets](build.md) | CSS/JS loading order and asset workflow |
| [CSS Architecture](css-architecture.md) | `dh-*` class naming, load order, mobile breakpoints, dark mode |
| [Deployment](deployment.md) | Docker setup, environment variables, reverse proxy flow |
| **Testing** | |
| → [PHPUnit](phpunit.md) | Running tests, what's covered, quick reference |
| → [Writing Tests](testing/writing-tests.md) | Test structure, tiers, patterns, examples |
| → [IDE Setup](testing/ide-setup.md) | PHPStorm, VS Code, Neovim, Sublime Text integration |
| → [Xdebug](testing/xdebug.md) | Installing and configuring Xdebug for debugging (all IDEs) |
| **Static Analysis** | |
| → [PHPStan](phpstan.md) | Running analysis, configuration, quick reference |
| → [Suppression Rules](static-analysis/suppression-rules.md) | Global ignores, inline annotations, common errors |

## Common tasks

| Task | Open |
|---|---|
| Start local environment | [Deployment → Quick start](deployment.md#quick-start) |
| Update CMS config | [Configuration → cms.json](configuration.md) |
| Update Docker config | [Configuration → Docker](configuration.md#docker) |
| Add a new CSS file | [Frontend Assets → Adding a new CSS file](build.md#adding-a-new-css-file) |
| Add a new JS file | [Frontend Assets → Adding a new JS file](build.md#adding-a-new-js-file) |
| Work with layout/mobile styles | [CSS Architecture → Mobile responsive](css-architecture.md#mobile-responsive) |
| Add a language | [Configuration → Language](configuration.md#language) |
| Run tests | [PHPUnit](phpunit.md#quick-start) |
| Run static analysis | [PHPStan](phpstan.md#quick-start) |
| Write a new test | [Writing Tests](testing/writing-tests.md#adding-a-new-test) |
| Setup IDE for tests | [IDE Setup](testing/ide-setup.md) |
| Enable Xdebug | [Xdebug](testing/xdebug.md#enable-xdebug) — set `DOCKER_XDEBUG_MODE` in `docker/config.env` |

## Scope note

This folder documents the existing architecture and workflows used in this repository.

*MIT License © 2024-2026 Dmytro Hovenko (Darkheim)*
