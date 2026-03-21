# Contributing to DarkCore CMS

## Getting started

```bash
git clone https://github.com/xDarkheim/DarkCore DarkCore
cd DarkCore
cp config/config.default.json config/config.json
cp docker/config.env.example docker/config.env
docker compose up -d --build
```

## Workflow

1. Fork the repository
2. Create a feature branch from the active integration branch for the task (not automatically from `main`): `git checkout -b fix/short-description`
3. Make your changes
4. Make sure all checks pass (see below)
5. Open a Pull Request against the branch currently used for the workstream
6. After that PR is merged, merge or forward-port the same changes into the `1.1.0` branch

### Branch targets

- Use the branch named in the current task / workstream as the first PR target.
- Do **not** assume `main` is the correct integration branch for every change.
- Keep `1.1.0` updated after the task branch PR is merged.

Typical sequence:

```bash
git checkout <current-work-branch>
git checkout -b docs/short-description

# ...make changes, run checks, push branch...

# open PR -> <current-work-branch>
# after merge:
git checkout 1.1.0
git merge <current-work-branch>
```

Branch naming:

| Prefix | When to use |
| --- | --- |
| `fix/` | Bug fix |
| `feat/` | New feature |
| `refactor/` | Refactoring |
| `docs/` | Documentation only |
| `chore/` | Tooling, CI, dependencies |

## Before submitting

```bash
composer test              # PHPUnit — all tests must pass
composer analyse           # PHPStan level 5 — zero errors
vendor/bin/php-cs-fixer fix  # Code style
```

CI runs the same checks automatically on every pull request. A PR cannot be merged if CI fails.

## Code style

- PHP 8.4, strict types on every file (`declare(strict_types=1)`)
- PSR-4 autoloading, namespace `Darkheim\`
- No inline HTML in PHP classes
- No credentials, tokens, or hardcoded paths

## Reporting bugs

Use the [Bug Report](https://github.com/xDarkheim/DarkCore/issues/new?template=bug_report.md) issue template.

## Suggesting features

Use the [Feature Request](https://github.com/xDarkheim/DarkCore/issues/new?template=feature_request.md) issue template.

## License

By contributing you agree that your code will be released under the [MIT License](LICENSE).

