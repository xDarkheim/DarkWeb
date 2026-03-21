# Routing Migration Matrix

This matrix tracks frontend top-level module routing through controllers and subpage routing through the subpage registry.

## Status meanings

- `migrated`: module is routed by a `*Controller` registered in `config/routes.web.php`. Regression is caught by `HandlerMigrationGateTest`.
- `subpage`: route is registered in `config/routes.subpages.php` and dispatched by `SubpageRouteDispatcher`; it may render either a dedicated subpage template or a shared controller-backed view.

## Source of truth

Machine-readable status lives in `config/routing-migration.json`.
Top-level controller routes are registered in `config/routes.web.php` (`WebRouteRegistry`).
Sub-page routes are registered in `config/routes.subpages.php` (`SubpageRouteRegistry`).

## Current matrix (v1)

| Page | Status | Controller |
|---|---|---|
| castlesiege | migrated | `Darkheim\\Application\\Page\\CastleSiegeController` |
| contact | migrated | `Darkheim\\Application\\Page\\ContactController` |
| donation | migrated | `Darkheim\\Application\\Page\\DonationController` |
| downloads | migrated | `Darkheim\\Application\\Page\\DownloadsController` |
| forgotpassword | migrated | `Darkheim\\Application\\Page\\ForgotPasswordController` |
| home | migrated | `Darkheim\\Application\\Page\\HomeController` |
| info | migrated | `Darkheim\\Application\\Page\\InfoController` |
| login | migrated | `Darkheim\\Application\\Page\\LoginController` |
| logout | migrated | `Darkheim\\Application\\Page\\LogoutController` |
| news | migrated | `Darkheim\\Application\\Page\\NewsController` |
| privacy | migrated | `Darkheim\\Application\\Page\\PrivacyController` |
| rankings | migrated | `Darkheim\\Application\\Page\\RankingsController` |
| refunds | migrated | `Darkheim\\Application\\Page\\RefundsController` |
| register | migrated | `Darkheim\\Application\\Page\\RegisterController` |
| tos | migrated | `Darkheim\\Application\\Page\\TosController` |
| usercp | migrated | `Darkheim\\Application\\Page\\UsercpController` |
| verifyemail | migrated | `Darkheim\\Application\\Page\\VerifyEmailController` |

## Update rules

When migrating a top-level page to a controller:

1. Create a `*Controller` under `src/Application/Page/` with a `render(): void` method.
2. Add/update the route entry in `config/routes.web.php`.
3. Update `config/routing-migration.json` status → `migrated` + controller FQCN.
4. Add or update tests in `tests/Unit/Infrastructure/Routing/`.

When adding a sub-page route:

1. Register the route in `config/routes.subpages.php`.
2. If the route is controller-backed, prepare the full view-model in the controller and render the final template with `ViewRenderer`.
3. Use a dedicated template only when the markup is unique; otherwise prefer a shared template.
4. Mark status `subpage` in `config/routing-migration.json` if tracked.

Current shared-template examples:

- `rankings/*` → `Darkheim\Application\Page\RankingsSectionController` → `views/ranking.php`
- repeated UserCP character actions → `Darkheim\Application\Subpage\Usercp\AbstractCharacterActionTableSubpageController` → `views/subpages/usercp/actiontables.php`
