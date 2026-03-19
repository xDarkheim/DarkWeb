# DarkCore — Developer Docs

> **Emulator compatibility:** X-Team, MuEmu, Louis, Darkheim Emulator.
> IGCN and other emulators are not yet supported.

## Index

| Document | Focus |
| :--- | :--- |
| [Project Structure](project-structure.md) | Directory layout, bootstrap path, namespace map, what to edit |
| [Configuration](configuration.md) | `cms.json` keys, `docker/config.env` variables, security notes |
| [Deployment](deployment.md) | Docker setup, reverse proxy (Nginx Proxy Manager), useful commands |
| [Frontend Assets](build.md) | CSS/JS load order, cache busting, adding new files |
| [CSS Architecture](css-architecture.md) | `dh-*` class naming, dark mode, mobile breakpoints |
| [PHPUnit](phpunit.md) | Running tests, writing tests, IDE setup, Xdebug |
| [PHPStan](phpstan.md) | Static analysis, suppression rules, common errors |

## Common tasks

| Task | Where |
| :--- | :--- |
| Start local environment | [Deployment → Quick start](deployment.md#quick-start) |
| Edit CMS config | [Configuration → cms.json](configuration.md#cmsjson--main-config) |
| Edit Docker config | [Configuration → Docker](configuration.md#docker) |
| Add a CSS file | [Frontend Assets → Adding a new CSS file](build.md#adding-a-new-css-file) |
| Add a JS file | [Frontend Assets → Adding a new JS file](build.md#adding-a-new-js-file) |
| Add a language | [Configuration → Language](configuration.md#language) |
| Run tests | [PHPUnit → Quick start](phpunit.md#quick-start) |
| Run static analysis | [PHPStan → Quick start](phpstan.md#quick-start) |
| Write a new test | [PHPUnit → Writing tests](phpunit.md#writing-tests) |
| Enable Xdebug | [PHPUnit → Xdebug](phpunit.md#xdebug) |

*MIT License 2026 Dmytro Hovenko (Darkheim)*
