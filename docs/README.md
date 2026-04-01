# DarkCore — Developer Docs

> **Emulator compatibility:** X-Team, MuEmu, Louis.
> IGCN and other emulators are not yet supported.

## Index

| Document                                                | Focus                                                                |
|:--------------------------------------------------------|:---------------------------------------------------------------------|
| [Project Structure](project-structure.md)               | Directory layout, bootstrap path, namespace map, what to edit        |
| [Configuration](configuration.md)                       | `config.json` keys, `docker/config.env` variables, security notes    |
| [Deployment](deployment.md)                             | Docker setup, reverse proxy (Nginx Proxy Manager), useful commands   |
| [Frontend Assets](build.md)                             | CSS/JS load order, cache busting, adding new files                   |
| [CSS Architecture](css-architecture.md)                 | `dh-*` class naming, dark mode, mobile breakpoints                   |
| [Routing Migration Matrix](routing-migration-matrix.md) | Web/subpage route flow, controller-backed subpages, shared templates |
| [Legacy Eradication Backlog](backlog-legacy-eradication.md) | Internal roadmap for removing legacy runtime/data/config patterns |
| [PHPUnit](phpunit.md)                                   | Running tests, writing tests, IDE setup, Xdebug                      |
| [PHPStan](phpstan.md)                                   | Static analysis, suppression rules, common errors                    |

## Common tasks

| Task                               | Where                                                                                                               |
|:-----------------------------------|:--------------------------------------------------------------------------------------------------------------------|
| Start local environment            | [Deployment → Quick start](deployment.md#quick-start)                                                               |
| Edit CMS config                    | [Configuration → config.json](configuration.md#configjson--main-config)                                             |
| Edit Docker config                 | [Configuration → Docker](configuration.md#docker)                                                                   |
| Add a CSS file                     | [Frontend Assets → Adding a new CSS file](build.md#adding-a-new-css-file)                                           |
| Add a JS file                      | [Frontend Assets → Adding a new JS file](build.md#adding-a-new-js-file)                                             |
| Add a top-level page               | [Project Structure → Recipe: add a top-level page](project-structure.md#recipe-add-a-top-level-page)                |
| Add a subpage route                | [Project Structure → Recipe: add a subpage route](project-structure.md#recipe-add-a-subpage-route)                  |
| Add a controller-backed route/view | [Project Structure → Adding a new controller-backed view](project-structure.md#adding-a-new-controller-backed-view) |
| Work on AdminCP MVC                | [Project Structure → AdminCP MVC notes](project-structure.md#admincp-mvc-notes)                                     |
| Change the default theme shell     | [Frontend Assets → Changing the default theme shell](build.md#changing-the-default-theme-shell)                     |
| Add a language                     | [Configuration → Language](configuration.md#language)                                                               |
| Run tests                          | [PHPUnit → Quick start](phpunit.md#quick-start)                                                                     |
| Run static analysis                | [PHPStan → Quick start](phpstan.md#quick-start)                                                                     |
| Write a new test                   | [PHPUnit → Writing tests](phpunit.md#writing-tests)                                                                 |
| Enable Xdebug                      | [PHPUnit → Xdebug](phpunit.md#xdebug)                                                                               |
| Open a contribution PR             | [`../CONTRIBUTING.md`](../CONTRIBUTING.md)                                                                          |

*MIT License 2026 Dmytro Hovenko (Darkheim)*
