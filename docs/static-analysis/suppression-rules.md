# Suppression Rules

PHPStan is configured to catch real type errors. Only one pattern is suppressed globally, and a small number of comparisons require inline annotations.

## Global suppressions

Only one pattern is suppressed in `phpstan.neon`:

| Pattern | Reason |
|---|---|
| `Constant [A-Za-z0-9_]+ not found` | DB table/column constants (e.g. `_TBL_CHR_`, `_CLMN_CHR_LVL_`) are defined at runtime by `includes/config/custom.tables.php`. The `bootstrapFiles` setting loads the file, but if a constant is still missing the suppression prevents false positives. |

**Do not add new global patterns.** Fix type errors in source instead.

## Inline suppressions

A small number of intentional constructs require `@phpstan-ignore` annotations in source files. Each is documented with a justification comment.

### `_TBL_CHR_ == _TBL_MASTERLVL_` comparisons

`custom.tables.php` defines `_TBL_CHR_` as `'Character'` and `_TBL_MASTERLVL_` as `'MasterSkillTree'` — two different string literals. PHPStan therefore reports the equality check as **always false** (or **always true** for `!=`).

**The check is intentional**: some MU Online configurations store character and master-level data in the **same table**. In that setup, the operator redefines both constants to the same value in `custom.tables.php`, making the comparison runtime-conditional.

The annotation preserves this flexibility while silencing the static warning.

#### Affected files

| File | Method(s) | Identifier |
|---|---|---|
| `src/Application/Character/Character.php` | `CharacterClearSkillTree`, `CharacterAddStats` | `notEqual.alwaysTrue` |
| `src/Application/Profile/ProfileRepository.php` | `_cachePlayerData` | `equal.alwaysFalse` |
| `src/Application/Rankings/RankingsService.php` | `_masterlevelRanking`, `_getLevelRankingData`, `_getResetRankingData`, `_getKillersRankingData` | `equal.alwaysFalse` |

#### Example

```php
/** @phpstan-ignore equal.alwaysFalse */
if (_TBL_CHR_ == _TBL_MASTERLVL_) {
    // Single-table configuration path
    $result = $this->mu->query_fetch("SELECT TOP 10 Name, cLevel+MasterLevel as cLevel FROM Character ...");
} else {
    // Joined-table configuration path (default)
    $result = $this->mu->query_fetch("SELECT TOP 10 c.Name, c.cLevel+m.MasterLevel as cLevel FROM Character c JOIN MasterSkillTree m ...");
}
```

## Common PHPStan errors and fixes

| PHPStan error | Typical cause | Fix |
|---|---|---|
| `Loose comparison … will always evaluate to true/false` | Two constant string literals compared with `==` / `!=` | If intentional (runtime-configurable constants), add `@phpstan-ignore equal.alwaysFalse` or `notEqual.alwaysTrue` with a comment. Otherwise rewrite the logic. |
| `Parameter #N $value of function curl_setopt expects bool, int given` | Passing `1` / `0` to a bool curl option | Use `true` / `false` literals |
| `Call to function is_array() with array will always evaluate to true` | Return type of the called method is `array` | Remove the redundant `is_array()` guard |
| `Offset N on non-empty-list … always exists and is not nullable` | `?? null` after array access on a chunk/non-empty result | Remove the `?? null` fallback |
| `Parameter … of function str_replace expects string, int given` | Passing a typed `int` to `str_replace` | Cast explicitly: `(string) $value` |
| `Undefined variable` | Variable declared only inside an `if` branch that PHPStan treats as dead | Ensure the variable is declared before the conditional block |
| `Constant … not found` | DB constant not loaded (bootstrap missing) | Ensure `bootstrapFiles` in `phpstan.neon` includes the relevant tables file |

## How to add an inline suppression

1. **Identify the PHPStan error identifier**:
   ```
   ------ ------------------------------------------ 
    Line   Application/Rankings/RankingsService.php  
   ------ ------------------------------------------ 
    265    Comparison operation "==" between string and string is always false.
           🪪  equal.alwaysFalse                    
   ------ ------------------------------------------ 
   ```
   The identifier is `equal.alwaysFalse`.

2. **Add the annotation** immediately above the line:
   ```php
   /** @phpstan-ignore equal.alwaysFalse */
   if (_TBL_CHR_ == _TBL_MASTERLVL_) {
   ```

3. **Add a justification comment** explaining why the suppression is intentional:
   ```php
   // Check if Character and MasterLevel data are in the same table (custom config)
   /** @phpstan-ignore equal.alwaysFalse */
   if (_TBL_CHR_ == _TBL_MASTERLVL_) {
   ```

4. **Re-run PHPStan** to verify the error is suppressed:
   ```bash
   docker compose exec web composer analyse
   ```

## Removing a suppression

If you refactor code and an inline suppression is no longer needed, PHPStan will report it as an **unmatched ignore**:

```
------ ------------------------------------------ 
 Line   Application/Rankings/RankingsService.php  
------ ------------------------------------------ 
 265    No error to ignore is reported on line 265.
        🪪  phpstan.rules.unmatchedIgnore          
------ ------------------------------------------ 
```

Remove the `@phpstan-ignore` annotation and the error will disappear.

