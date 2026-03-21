# PHPStan — Static Analysis

PHPStan analyses all PHP classes in `src/`, catching type errors, undefined variables, dead code,
and other bugs without running the code.

## Quick start

```bash
docker compose exec web composer analyse

# Or directly
docker compose exec web ./vendor/bin/phpstan analyse --no-progress
```

A clean run prints `[OK] No errors`.

Always run after editing any file in `src/`:

```bash
docker compose exec web composer dump-autoload --optimize
docker compose exec web composer analyse
```

## Configuration

**`phpstan.neon`** — level 5, analyses `src/` only.

| Parameter | Value |
| :--- | :--- |
| `level` | 5 |
| `phpVersion` | 80400 |
| `paths` | `src/` |
| `bootstrapFiles` | `includes/bootstrap/compat.php`, `config/tables.php`, `config/tables.custom.php` |

Bootstrap files make global helpers (`lang()`, `config()`) and DB constants (`_TBL_CHR_`, `_CLMN_CHR_LVL_`)
visible to PHPStan without a live database.

## Runtime-boundary pattern

`src/` now routes environment access through small adapters in `src/Infrastructure/Runtime/` instead of reading PHP superglobals inside services. When refactoring namespaced code, prefer injecting `SessionStore`, `QueryStore`, `RequestStore`, `PostStore`, or `ServerContext` rather than introducing new direct `$_SESSION` / `$_GET` / `$_POST` reads.

## What it catches

- Type mismatches (`string` passed where `int` expected)
- Undefined variables and properties
- Always-true / always-false conditions
- Dead code after `return` / `throw`
- Missing return types
- Nullable offset access without `??` fallback
- Wrong parameter types in built-in functions

## Suppression rules

> **Do not add new global suppressions.** Fix type errors in source instead.

### Global suppression (`phpstan.neon`)

| Pattern | Reason |
| :--- | :--- |
| `Constant [A-Za-z0-9_]+ not found` | DB table/column constants are defined at runtime by `tables.custom.php`. Bootstrap files load it, but a suppression prevents false positives if a constant is still missing. |

### Inline suppressions

A small number of intentional constructs require `@phpstan-ignore` annotations.

**`_TBL_CHR_ == _TBL_MASTERLVL_` comparisons**

`tables.custom.php` defines these as two different string literals — PHPStan flags
the equality check as always-false. The check is intentional: some configurations store
character and master-level data in the same table, making it runtime-conditional.

| File | Methods | Identifier |
| :--- | :--- | :---: |
| `src/Application/Character/Character.php` | `CharacterClearSkillTree`, `CharacterAddStats` | `notEqual.alwaysTrue` |
| `src/Application/Profile/ProfileRepository.php` | `_cachePlayerData` | `equal.alwaysFalse` |
| `src/Application/Rankings/RankingsService.php` | `_masterlevelRanking`, `_getLevelRankingData`, `_getResetRankingData`, `_getKillersRankingData` | `equal.alwaysFalse` |

Example annotation:

```php
/** @phpstan-ignore equal.alwaysFalse */
if (_TBL_CHR_ == _TBL_MASTERLVL_) {
    // single-table path
}
```

## Common errors and fixes

| Error | Cause | Fix |
| :--- | :--- | :--- |
| `Loose comparison … will always evaluate to true/false` | Two constant string literals compared with `==` / `!=` | If intentional, add `@phpstan-ignore` with a comment. Otherwise rewrite the logic. |
| `Parameter … expects bool, int given` | Passing `1` / `0` to a bool parameter (e.g. `curl_setopt`) | Use `true` / `false` literals |
| `Call to is_array() with array will always evaluate to true` | Return type is already `array` | Remove the redundant `is_array()` guard |
| `Offset … always exists and is not nullable` | `?? null` after array access on a non-empty result | Remove the `?? null` fallback |
| `Parameter … expects string, int given` | Passing a typed `int` to `str_replace` | Cast explicitly: `(string) $value` |
| `Undefined variable` | Variable only declared inside an `if` branch PHPStan treats as dead | Declare the variable before the conditional block |
| `Constant … not found` | DB constant not loaded | Ensure `bootstrapFiles` in `phpstan.neon` includes the relevant tables file |
