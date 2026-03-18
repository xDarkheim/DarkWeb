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
cms_darkcore  (port 8081 internal — PHP 8.4 + Apache + Xdebug)
   │  volume mount: ./  →  /var/www/html
   ▼
Microsoft SQL Server  (external — your MuOnline game database)
```

> The container name (`cms_darkcore`) and the `proxy` network are defined directly
> in `docker-compose.yml`. Edit that file if you need a different name or network.

## Requirements

- Docker Engine + docker compose v2
- A running Microsoft SQL Server instance (game database)
- A reverse proxy already running on the same Docker host (Nginx Proxy Manager, Traefik, etc.)

## Configuration before first start

 File  Contains  Git-tracked 
 :---  :---  :---: 
 `includes/config/cms.json`  Database, site settings, features  No 
 `docker/config.env`  Docker runtime: domain, timezone, cron, Xdebug  No 

Both have committed example/default files to copy from.

### 1. Copy config files

```bash
cp includes/config/cms.json.default includes/config/cms.json
cp docker/config.env.example docker/config.env
```

### 2. Fill in `includes/config/cms.json`

| Key | Example | Description |
| :--- | :---: | :--- |
| `SQL_DB_HOST` | `"192.168.1.56"` | SQL Server IP or hostname |
| `SQL_DB_NAME` | `"MuOnline"` | Database name |
| `SQL_DB_USER` | `"sa"` | Database user |
| `SQL_DB_PASS` | `"yourpassword"` | Database password |
| `SQL_DB_PORT` | `"1433"` | SQL Server port |

See [Configuration](configuration.md) for all available keys.

### 3. Fill in `docker/config.env`

| Variable | Local default | Production example |
| :--- | :---: | :--- |
| `DOCKER_SERVER_NAME` | `localhost` | `mu.example.com` |
| `DOCKER_TIMEZONE` | `UTC` | `Europe/Moscow` |
| `DOCKER_CRON_URL` | `http://localhost:8081/api/cron.php?key=123456` | `https://mu.example.com/api/cron.php?key=SECRET` |
| `DOCKER_XDEBUG_MODE` | `off` | `off` |

> **`DOCKER_SERVER_NAME`** is injected into the Apache `VirtualHost` as `ServerName` at container start.
> Changing `docker/config.env` only requires `docker compose restart` — no rebuild needed.

The default container name is **`cms_darkcore`**. To change it, edit `docker-compose.yml`:

```yaml
services:
  web:
    container_name: your_container_name
```

> The container name is what you enter as **Forward Hostname** in Nginx Proxy Manager.

## Quick start

```bash
# 1. Clone the repository
git clone <your-repo-url> DarkCore
cd DarkCore

# 2. Copy and configure both files
cp includes/config/cms.json.default includes/config/cms.json
cp docker/config.env.example docker/config.env
# → Edit cms.json with your SQL Server credentials
# → Edit docker/config.env with your domain and timezone

# 3. Create the shared proxy network (once per Docker host — skip if it already exists)
docker network create proxy

# 4. Build and start
docker compose up -d --build

# 5. Run the web installer (first time only)
# Open https://your-domain/install/ in a browser
# → Delete the install/ directory after setup is complete
```

> **Rebuilding the image is required** when you change `docker/Dockerfile` or
> `docker/entrypoint.sh`. For `cms.json` changes only — `docker compose restart` is enough.

## Reverse proxy setup (Nginx Proxy Manager)

Create the `proxy` network once on your Docker host (if it doesn't exist yet):

```bash
docker network create proxy
```

Then configure a **Proxy Host** in Nginx Proxy Manager:

| Field | Value |
| :--- | :--- |
| Domain Names | `your-domain.com` |
| Scheme | `http` |
| Forward Hostname / IP | `cms_darkcore` *(your container name)* |
| Forward Port | `8081` |
 Block Common Exploits  Yes 

SSL → **Request a new SSL Certificate** (Let's Encrypt).

## Docker files

| File | Purpose |
| :--- | :--- |
| `docker/Dockerfile` | Builds the image: PHP 8.4 + Apache + FreeTDS + Xdebug + all required extensions |
| `docker/config.env.example` | **Commit this.** Template for `docker/config.env` with local-dev defaults |
| `docker/config.env` | **Git-ignored.** Your actual runtime config (domain, timezone, cron, Xdebug) |
| `docker/xdebug.ini` | Xdebug 3 config: port 9003, `host.docker.internal`, idekey `PHPSTORM` |
| `docker/entrypoint.sh` | Runs on every container start |
| `docker-compose.yml` | Service definition — image build, volume mount, proxy network, healthcheck |

### What the Dockerfile builds

- Base image: `php:8.4-apache`
- Installs: FreeTDS (`pdo_dblib`), GD, mbstring, zip, cron, curl
- Installs **Xdebug 3** via PECL (disabled by default — mode set at runtime)
- PHP limits: `upload_max_filesize = 20M`, `post_max_size = 20M`, `memory_limit = 256M`
- OPcache: enabled with JIT tracing, 64 MB JIT buffer
- Apache: listens on **port 8081**, `mod_rewrite` + `mod_headers` + `mod_remoteip` enabled

### What entrypoint.sh does on each start

1. Reads `DOCKER_*` variables from the container environment
2. Creates all required directories (`includes/cache/` subtree, `includes/logs/`, `includes/config/`)
3. Creates all required cache and log files if missing
4. Drops `Deny from all` `.htaccess` into `cache/`, `logs/`, `config/`
5. Fixes ownership/permissions: `www-data:www-data`, mode `775`
6. Runs `composer install --no-interaction --optimize-autoloader`
7. Applies timezone from `DOCKER_TIMEZONE`
8. Writes `/etc/cron.d/cms-cron` from `DOCKER_CRON_URL`
9. Starts the cron service
10. Exports `DOCKER_SERVER_NAME`, `XDEBUG_MODE`, `PHP_IDE_CONFIG`
11. Starts Apache via `exec apache2-foreground`

> Steps 2–5 are idempotent — safe to run on every restart.

## Local development

For local development without a reverse proxy, use the provided override example:

```bash
cp docker-compose.override.yml.example docker-compose.override.yml
docker compose up -d --build
# Site available at http://localhost:8081
```

The override file:
- Maps port `8081` to your host
- Replaces the external `proxy` network with a plain local bridge — no need to run `docker network create proxy`

`docker-compose.override.yml` is git-ignored so it never pollutes the repository.

## Useful commands

```bash
docker compose up -d --build   # first start / rebuild
docker compose restart         # restart after cms.json changes
docker compose down            # stop and remove containers
docker compose logs -f web     # follow logs
docker compose exec web bash   # open shell in container
docker compose exec web php -v # check PHP / Xdebug version
```
