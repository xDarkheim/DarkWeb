# Changelog

All notable changes to this project are documented in this file.

The format is based on Keep a Changelog.

## [Unreleased]

### Changed
- Documented `website_url` and `trust_proxy_headers` as deployment-critical settings for canonical links and trusted proxy setups.
- Clarified that `SQL_PASSWORD_ENCRYPTION=none` remains supported for emulator compatibility and must match the existing account table format.

### Fixed
- Switched browser-side API calls to clean `/api/{endpoint}` routes and the site-relative root, fixing homepage widgets behind HTTPS reverse proxies that were failing due to mixed-content requests.

### Security
- Documented the move to one-time server-side password recovery / verification actions and the removal of plaintext passwords from recovery emails.
- Updated privacy-facing copy to avoid false claims about password storage on legacy plaintext-compatible deployments.

## [1.1.1] - 2026-03-26

### Changed
- Redesigned AdminCP UI with a modern dark theme.
- Redesigned the web installer and refactored cron jobs.
- Reorganized application and infrastructure namespaces by feature.
- Applied broad code style and formatting cleanup.
- Bumped CMS runtime and installer versions to `1.1.1` and synchronized `@version` metadata.

### Removed
- Removed deprecated global helper/config/auth compatibility wrappers and legacy bootstrap shims.

### Fixed
- Cleaned up imports and removed deprecated HTML output methods.

### Security
- Added security notices on authentication pages.

## [1.1.0] - 2026-03-22

### Added
- Migrated project web root to `public/` and runtime data paths to `var/`.
- Introduced controller-based routing migration layer and migration guard tests.
- Added initial MVC view split (`ViewRenderer`) for web pages.

### Changed
- Migrated from template terminology/layout to `themes` across runtime, installer, tests, and docs.
- Moved configuration to root `config/` and updated runtime constants and paths.
- Improved Docker fresh-setup flow and deployment documentation.

## [1.0.0] - 2026-03-17

### Added
- Initial public release.

[1.1.1]: https://github.com/darkheim/DarkCore/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/darkheim/DarkCore/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/darkheim/DarkCore/releases/tag/v1.0.0
