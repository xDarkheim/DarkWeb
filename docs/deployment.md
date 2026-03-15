# Deployment

Practical runtime and infrastructure setup for this project.

## Architecture overview

```
Internet
   │
   ▼
Reverse proxy / TLS terminator  (port 80/443 — e.g. Nginx Proxy Manager)
   │  docker network: proxy  (external, shared)
   ▼
cms_darkweb  (port 8081 internal — PHP 8.4 + Apache + Xdebug)
   │  volume mount: ./  →  /var/www/html
   ▼
Microsoft SQL Server  (external — your MuOnline game database)
```

> The container name (`cms_darkweb`) and the `proxy` network are defined directly
> in `docker-compose.yml`. Edit that file if you need a different name or network.

## Requirements

- Docker Engine + docker compose v2
- A running Microsoft SQL Server instance (game database)
- A reverse proxy already running on the same Docker host (Nginx Proxy Manager, Traefik, etc.)

## Configuration before first start

The project uses **two separate config files** — one for the app, one for Docker:

| File | Contains | Git-tracked |
|------|----------|-------------|
| `includes/config/cms.json` | Database, site settings, features | ❌ (git-ignored) |
| `docker/config.env` | Docker runtime: domain, timezone, cron, Xdebug | ❌ (git-ignored) |

Both have committed example/default files to copy from.

### 1. Copy config files

```bash
cp includes/config/cms.json.default includes/config/cms.json
cp docker/config.env.example docker/config.env
```

### 2. Fill in `includes/config/cms.json`

| Key | Example | Description |
|-----|---------|-------------|
| `SQL_DB_HOST` | `"192.168.1.56"` | SQL Server IP or hostname |
| `SQL_DB_NAME` | `"MuOnline"` | Database name |
| `SQL_DB_USER` | `"sa"` | Database user |
| `SQL_DB_PASS` | `"yourpassword"` | Database password |
| `SQL_DB_PORT` | `"1433"` | SQL Server port |

See [Configuration](configuration.md) for all available keys.

### 3. Fill in `docker/config.env`

| Variable | Local default | Production example |
|----------|---------------|--------------------|
| `DOCKER_SERVER_NAME` | `localhost` | `mu.example.com` |
| `DOCKER_TIMEZONE` | `UTC` | `Europe/Moscow` |
| `DOCKER_CRON_URL` | `http://localhost:8081/api/cron.php?key=123456` | `https://mu.example.com/api/cron.php?key=SECRET` |
| `DOCKER_XDEBUG_MODE` | `off` | `off` |

> **`DOCKER_SERVER_NAME`** is injected into the Apache `VirtualHost` as `ServerName` at container start.
> Changing `docker/config.env` only requires `docker compose restart` — no rebuild needed.

The default container name is **`cms_darkweb`**. To change it, edit `docker-compose.yml`:

```yaml
services:
  web:
    container_name: your_container_name   # ← change here
```

> The container name is what you enter as **Forward Hostname** in Nginx Proxy Manager.

## Quick start

```bash
# 1. Clone the repository
git clone <your-repo-url> DarkWeb
cd DarkWeb

# 2. Copy and configure both files
cp includes/config/cms.json.default includes/config/cms.json
cp docker/config.env.example docker/config.env
# Edit cms.json        — fill in SQL_DB_*
# Edit docker/config.env — fill in DOCKER_SERVER_NAME, DOCKER_TIMEZONE, DOCKER_CRON_URL

# 3. Build and start
docker compose up -d --build

# 4. Run the web installer (first time only)
# Open https://your-domain/install/ in a browser
# → Delete or rename the install/ directory after setup is complete
```

> **Rebuilding the image is required** when you change `docker/Dockerfile` or
> `docker/entrypoint.sh`. For `cms.json` changes only — `docker compose restart` is enough.

## Reverse proxy setup (Nginx Proxy Manager)

The `proxy` external network is already declared in `docker-compose.yml`:

```yaml
networks:
  proxy:
    external: true
```

Create the network once on your Docker host (if it doesn't exist yet):

```bash
docker network create proxy
```

Then configure a **Proxy Host** in Nginx Proxy Manager:

| Field | Value |
|-------|-------|
| Domain Names | `your-domain.com` |
| Scheme | `http` |
| Forward Hostname / IP | `cms_darkweb` *(your container name)* |
| Forward Port | `8081` |
| Block Common Exploits | ✅ |

SSL → **Request a new SSL Certificate** (Let's Encrypt).

> The container is reachable by name because it is on the same `proxy` network as NPM.

## Docker files

| File | Purpose |
|------|---------|
| `docker/Dockerfile` | Builds the image: PHP 8.4 + Apache + FreeTDS + Xdebug + all required PHP extensions |
| `docker/config.env.example` | **Commit this.** Template for `docker/config.env` with local-dev defaults |
| `docker/config.env` | **Git-ignored.** Your actual runtime config (domain, timezone, cron, Xdebug) |
| `docker/xdebug.ini` | Xdebug 3 config: port 9003, `host.docker.internal`, idekey `PHPSTORM`. Mode controlled by `DOCKER_XDEBUG_MODE` in `config.env` |
| `docker/entrypoint.sh` | Runs on every container start — see [below](#what-entrypointsh-does-on-each-start) |
| `docker-compose.yml` | Service definition — image build, volume mount, `env_file`, proxy network, `extra_hosts`, healthcheck |

### What the Dockerfile builds

- Base image: `php:8.4-apache`
- Installs: FreeTDS (MSSQL via `pdo_dblib`), GD, mbstring, zip, cron, curl
- Installs **Xdebug 3** via PECL (disabled by default — mode set at runtime via `cms.json`)
- PHP limits: `upload_max_filesize = 20M`, `post_max_size = 20M`, `memory_limit = 256M`
- OPcache: enabled with JIT tracing, 64 MB JIT buffer
- Apache: listens on **port 8081**, `mod_rewrite` + `mod_headers` + `mod_remoteip` enabled
- Apache `VirtualHost` uses `ServerName ${DOCKER_SERVER_NAME}` — resolved dynamically at container start from `cms.json`

### What entrypoint.sh does on each start

1. Reads `DOCKER_*` variables from the container environment (injected from `docker/config.env` via `env_file`)
2. Creates all required directories (`includes/cache/` subtree, `includes/logs/`, `includes/config/`)
3. Creates all required cache files (`*.cache`) and log files (`database_errors.log`, `php_errors.log`) if missing
4. Drops `Deny from all` `.htaccess` into `cache/`, `logs/`, `config/`
5. Fixes ownership/permissions: `www-data:www-data`, mode `775`, recursively
6. Runs `composer install --no-interaction --optimize-autoloader`
7. Applies timezone from `DOCKER_TIMEZONE` (writes `/etc/localtime` + `/etc/timezone`)
8. Writes `/etc/cron.d/cms-cron` from `DOCKER_CRON_URL` (every-minute curl call)
9. Starts the cron service
10. Exports `DOCKER_SERVER_NAME` (→ Apache vhost `ServerName`), `XDEBUG_MODE`, `PHP_IDE_CONFIG`
11. Starts Apache via `exec apache2-foreground`

> Steps 2–5 are idempotent — safe to run on every restart, nothing is overwritten if already present.

## Local development

For local development the proxy network is not needed. Temporarily remove the `networks` block
from `docker-compose.yml`, or just expose the port:

```yaml
services:
  web:
    ports:
      - "8081:8081"
```

Then:

```bash
docker compose up -d --build
# Site available at http://localhost:8081
```

### Enabling Xdebug

Set in `docker/config.env`:

```env
DOCKER_XDEBUG_MODE=debug
```

Restart (no rebuild needed):

```bash
docker compose restart
```

See [Xdebug guide](testing/xdebug.md) for IDE setup instructions.

## Useful commands

```bash
# First start (build image + create container)
docker compose up -d --build

# Restart after cms.json changes (no rebuild)
docker compose restart

# Rebuild after Dockerfile or entrypoint.sh changes
docker compose up -d --build

# Stop and remove containers
docker compose down

# Follow logs
docker compose logs -f web

# Open shell in container
docker compose exec web bash

# Check PHP / Xdebug version
docker compose exec web php -v
```
