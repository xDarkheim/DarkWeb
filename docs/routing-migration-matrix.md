# Routing Migration Matrix

This matrix tracks frontend top-level module migration from legacy include-based loading to controller-based routing.

## Status meanings

- `legacy`: module is loaded only through `LegacyModuleAdapter`.
- `hybrid`: module has partial controller routing but still uses some legacy fallback.
- `migrated`: module is routed by controller config (`config/routes.web.php`) and should not regress.

## Source of truth

Machine-readable status lives in `config/routing-migration.json`.

## Current matrix (v1)

| Page | Status | Controller |
|---|---|---|
| castlesiege | legacy | - |
| contact | legacy | - |
| donation | legacy | - |
| downloads | legacy | - |
| forgotpassword | legacy | - |
| home | migrated | `Darkheim\Application\Page\HomeController` |
| info | legacy | - |
| login | migrated | `Darkheim\Application\Page\LoginController` |
| logout | legacy | - |
| news | legacy | - |
| privacy | legacy | - |
| rankings | legacy | - |
| refunds | legacy | - |
| register | migrated | `Darkheim\Application\Page\RegisterController` |
| tos | legacy | - |
| usercp | legacy | - |
| verifyemail | legacy | - |

## Update rules

When migrating a page:

1. Add/update route entry in `config/routes.web.php`.
2. Update `config/routing-migration.json` status and controller.
3. Add or update tests in `tests/Unit/Infrastructure/Routing`.
4. Remove obsolete legacy branch for that page when feasible in the same slice.

