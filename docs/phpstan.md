# PHPStan — Static Analysis

PHPStan performs static analysis on all PHP classes in `src/`, catching type errors, undefined variables, dead code, and other bugs without running the code.

## Quick start

```bash
docker compose exec web composer analyse
```

Or directly:

```bash
docker compose exec web ./vendor/bin/phpstan analyse --no-progress
```

A clean run prints `[OK] No errors`.

## Configuration

- **phpstan.neon** — level 5, analyses `src/` only
- **Bootstrap files**: `includes/functions.php`, `includes/config/cms.tables.php`, `includes/config/custom.tables.php`
- **Excluded paths**: `vendor/`, `install/`, `docker/`

| Parameter | Value |
|---|---|
| `level` | 5 |
| `phpVersion` | 80400 |
| `paths` | `src/` |
| `bootstrapFiles` | Global helpers and DB constants loaded for analysis |

Bootstrap files make global helpers (`lang()`, `config()`) and DB constants (`_TBL_CHR_`, `_CLMN_CHR_LVL_`) visible to PHPStan without requiring a live database.

## What it catches

- Type mismatches (`string` passed where `int` expected)
- Undefined variables and properties
- Always-true/always-false conditions
- Dead code after `return` / `throw`
- Missing return types
- Nullable offset access without `??` fallback
- Wrong parameter types in built-in functions

## After editing code

Always run PHPStan after editing any file in `src/`:

```bash
docker compose exec web composer dump-autoload --optimize
docker compose exec web composer analyse
```

Fix all errors before committing. Do **not** add new global `ignoreErrors` patterns — fix the issue in source.

## Further reading

- [Suppression Rules](static-analysis/suppression-rules.md) — global ignore patterns, inline suppressions, why they exist

