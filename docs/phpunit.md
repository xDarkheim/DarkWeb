# PHPUnit — Unit Testing

Unit tests verify that individual classes and methods work correctly in isolation. Tests live in `tests/Unit/` and mirror the `src/` directory structure.

## Quick start

Run all tests:

```bash
docker compose exec web composer test
```

Or directly:

```bash
docker compose exec web ./vendor/bin/phpunit --no-coverage
```

A passing run prints `OK (N tests, N assertions)`.

## What's tested

| Layer | Classes | Test count |
|---|---|---|
| **Domain** | `Validator` | 24 tests |
| **Application** | `Account`, `Auth/*`, `News/*`, `Rankings/*`, `Vote/*` | 68 tests |
| **Infrastructure** | `Cache/*`, `Config/*`, `Cron/*`, `Database/*`, `Email`, `Payment/*`, `Plugins`, `Routing/*` | 104 tests |
| **Total** | 31 classes | **196 tests, 271 assertions** |

## Configuration

- **phpunit.xml** — test suite configuration
- **tests/bootstrap.php** — loads before every test (stubs global functions, defines constants)
- **tests/Stubs/** — test helpers (`DbTestHelper`, `RedirectException`)

## Further reading

- [Writing Tests](testing/writing-tests.md) — test structure, tiers, patterns, examples
- [IDE Setup](testing/ide-setup.md) — run tests from PHPStorm, VS Code, Neovim, Sublime Text
- [Xdebug](testing/xdebug.md) — debugging tests with breakpoints

