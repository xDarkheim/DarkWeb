# Configuration

This document maps the config files and keys used by the CMS.

All configuration files live in `config/`. The directory is web-blocked by `.htaccess` (`Deny from all`) and created automatically by the Docker entrypoint on first start.

## config.json — main config

Copy `config.default.json` to `config.json` and fill in your values. The installer does this for you.

### System

| Key | Type | Description |
| :--- | :---: | :--- |
| `system_active` | bool | `false` → shows maintenance redirect |
| `maintenance_page` | string | URL to redirect to when system is inactive |
| `error_reporting` | bool | Enable PHP error output (disable in production) |
| `website_theme` | string | Theme directory name under `themes/` (default: `"default"`) |
| `cms_installed` | bool | Set to `true` by the installer — blocks re-running install |

### Server info

| Key | Type | Description |
| :--- | :---: | :--- |
| `server_name` | string | Server name shown in header, login modal, meta tags |
| `server_tagline` | string | Subtitle line in the header |
| `website_title` | string | `<title>` tag content |
| `website_meta_description` | string | Meta description |
| `website_meta_keywords` | string | Meta keywords |
| `website_forum_link` | string | Forum URL (used in navbar/links) |
| `server_info_season` | string | Season/version label (e.g. `"Darkheim v1"`) |
| `server_info_exp` | string | EXP rate label |
| `server_info_masterexp` | string | Master EXP rate label |
| `server_info_drop` | string | Drop rate label |
| `server_info_exp_type` | string | Rate category (e.g. `"High Rates"`) |
| `server_info_max_level` | string | Max character level |
| `server_info_max_reset` | string | Max reset count |
| `maximum_online` | string | Max online count for the progress bar |

### Database

| Key | Type | Description |
| :--- | :---: | :--- |
| `SQL_DB_HOST` | string | SQL Server hostname or IP |
| `SQL_DB_NAME` | string | Database name (default: `"MuOnline"`) |
| `SQL_DB_USER` | string | Database user |
| `SQL_DB_PASS` | string | Database password |
| `SQL_DB_PORT` | string | Port (default: `"1433"`) |
| `SQL_PASSWORD_ENCRYPTION` | string | Password hashing: `"none"`, `"wzmd5"`, `"phpmd5"`, `"sha256"` |
| `SQL_SHA256_SALT` | string | Salt used when `SQL_PASSWORD_ENCRYPTION = "sha256"` |

### Language

| Key | Type | Description |
| :--- | :---: | :--- |
| `language_default` | string | Default language code. Supported: `"en"`, `"ru"`, `"cn"`, `"es"`, `"pt"`, `"ro"` |
| `language_switch_active` | bool | Show language switcher in the top bar |
| `language_debug` | bool | Highlight missing phrases (dev only) |

> **Active languages:** EN, RU, CN, ES, PT, RO.
> Language phrase files live in `includes/languages/<code>/language.php`.
> To add a new language: create the directory and phrase file, then update the language list in `src/Infrastructure/Theme/DefaultThemeLayoutBuilder.php`.

### Authentication & registration

| Key | Type | Description |
| :--- | :---: | :--- |
| `username_min_len` | int | Minimum username length |
| `username_max_len` | int | Maximum username length |
| `password_min_len` | int | Minimum password length |
| `password_max_len` | int | Maximum password length |

### Features

| Key | Type | Description |
| :--- | :---: | :--- |
| `player_profiles` | bool | Enable public player profile pages |
| `guild_profiles` | bool | Enable public guild profile pages |
| `character_avatars_dir` | string | Sub-directory under `img/` for character avatar images |
| `plugins_system_enable` | bool | Enable the plugin system |
| `ip_block_system_enable` | bool | Enable IP blocking |
| `season_1_support` | bool | Enable Season 1 compatibility mode |

### Cron

Cron is CLI-only.

Use:

```bash
php bin/cron.php
```

Optional single-task run:

```bash
php bin/cron.php --id=3
```

### Social links

| Key | Description |
| :--- | :--- |
| `social_link_facebook` | Facebook URL |
| `social_link_instagram` | Instagram URL |
| `social_link_discord` | Discord invite URL |

### Admin access

```json
{
    "admins": {
        "username": 100
    }
}
```

Maps admin usernames to their access level. Currently only level `100` (full access) is used.

### Docker

Docker runtime settings live in **`docker/config.env`** — a separate file that is git-ignored and never committed.

```bash
cp docker/config.env.example docker/config.env
```

| Variable | Default | Description |
| :--- | :---: | :--- |
| `DOCKER_SERVER_NAME` | `localhost` | Domain name — injected into Apache `VirtualHost` as `ServerName` and into `PHP_IDE_CONFIG` for Xdebug path mapping |
| `DOCKER_TIMEZONE` | `UTC` | IANA timezone applied to the container via `/etc/localtime` |
| `DOCKER_CRON_COMMAND` | `/usr/local/bin/php /var/www/html/bin/cron.php` | CLI command written to `/etc/cron.d/cms-cron` and executed every minute |
| `DOCKER_XDEBUG_MODE` | `off` | Xdebug 3 mode: `off`, `debug`, `profile`, `trace`, `coverage`, or comma-separated combos |

> **When to rebuild vs restart:**
> - `docker/config.env` change only → `docker compose up -d --force-recreate`
> - `docker/Dockerfile` or `docker/entrypoint.sh` change → `docker compose up -d --build --force-recreate`

## Other config files

| File | Purpose |
| :--- | :--- |
| `tables.php` | Maps CMS internal column names to your actual DB column names |
| `tables.custom.php` | Project-specific column overrides — takes precedence over `tables.php` |
| `castle-siege.json` | Castle Siege configuration (guild, schedule, prize) |
| `usercp-menu.json` | UserCP menu items — controls which pages appear in the sidebar |
| `navigation.json` | Navigation bar items configuration |
| `email-templates.xml` | Email template definitions (subject, body for registration, password reset, etc.) |
| `timezone-config.php` | Sets `date_default_timezone_set()`. Defaults to `Europe/Kiev` |
| `writable.json` | List of paths the installer checks for write permissions |

## Security notes

- `config/`, `var/cache/`, and `var/logs/` are protected from direct web access (`public/` is the only DocumentRoot)
- Never commit `config.json` to a public repository — it contains database credentials
- `docker-compose.override.yml` is in `.gitignore` and must never be committed
- Delete the `public/install/` directory after running the web installer
