#!/bin/bash
set -e

# ── 0. Sanity check — warn if config.env was never copied ─────────────────────
if [ -z "$DOCKER_SERVER_NAME" ] && [ -z "$DOCKER_TIMEZONE" ]; then
    echo "[startup] WARNING: No DOCKER_* environment variables found."
    echo "[startup]          Did you copy docker/config.env.example → docker/config.env?"
    echo "[startup]          Continuing with empty defaults — the site may not work correctly."
fi

CMS_JSON="/var/www/html/config/config.json"

# ── Helper: read a key from config.json via PHP (safe, no jq dependency) ─────────
cms_get() {
    php -r "
        \$f = '${CMS_JSON}';
        if(!file_exists(\$f)) { echo ''; exit; }
        \$c = json_decode(file_get_contents(\$f), true);
        echo (is_array(\$c) && isset(\$c['$1'])) ? \$c['$1'] : '';
    " 2>/dev/null
}

# ── 1. Create required directories if they don't exist (fresh clone) ──────────
echo "[startup] Creating required directories..."
mkdir -p /var/www/html/var/cache/news/translations \
         /var/www/html/var/cache/profiles/guilds \
         /var/www/html/var/cache/profiles/players \
         /var/www/html/var/logs \
         /var/www/html/config

# ── 1a. Touch required cache & log files ──────────────────────────────────────
echo "[startup] Creating required cache and log files..."
for f in \
    blocked_ip.cache \
    castle_siege.cache \
    character_country.cache \
    downloads.cache \
    news.cache \
    online_characters.cache \
    plugins.cache \
    rankings_gens.cache \
    rankings_gr.cache \
    rankings_guilds.cache \
    rankings_level.cache \
    rankings_master.cache \
    rankings_online.cache \
    rankings_pk.cache \
    rankings_resets.cache \
    rankings_votes.cache \
    server_info.cache
do
    touch "/var/www/html/var/cache/${f}"
done

touch /var/www/html/var/logs/database_errors.log \
      /var/www/html/var/logs/php_errors.log

# ── 2. Protect sensitive directories with .htaccess (if not already present) ──
echo "[startup] Securing runtime and config directories..."
for path in /var/www/html/var/cache /var/www/html/var/logs /var/www/html/config; do
    htfile="${path}/.htaccess"
    if [ ! -f "$htfile" ]; then
        echo "Deny from all" > "$htfile"
        echo "[startup] Created .htaccess in ${path}"
    fi
done

# ── 3. Fix permissions ────────────────────────────────────────────────────────
echo "[startup] Setting up permissions..."
chown -R www-data:www-data \
    /var/www/html/var/cache \
    /var/www/html/var/logs \
    /var/www/html/config 2>/dev/null || true
chmod -R 775 \
    /var/www/html/var/cache \
    /var/www/html/var/logs \
    /var/www/html/config 2>/dev/null || true

# ── 4. Composer — install dependencies and regenerate autoloader ──────────────
echo "[startup] Running composer install..."
cd /var/www/html
COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-interaction \
    --optimize-autoloader \
    --quiet \
    && echo "[startup] Composer: dependencies OK, autoloader optimized." \
    || echo "[startup] WARNING: composer install failed — site may be broken."

# ── 5. Apply timezone ─────────────────────────────────────────────────────────
if [ -n "$DOCKER_TIMEZONE" ]; then
    echo "[startup] Setting timezone: ${DOCKER_TIMEZONE}"
    ln -snf /usr/share/zoneinfo/${DOCKER_TIMEZONE} /etc/localtime
    echo "${DOCKER_TIMEZONE}" > /etc/timezone
fi

# ── 6. Setup cron ─────────────────────────────────────────────────────────────
CRON_COMMAND="${DOCKER_CRON_COMMAND:-/usr/local/bin/php /var/www/html/bin/cron.php}"

if [ -n "$CRON_COMMAND" ]; then
    echo "[startup] Configuring CMS cron (command mode)..."
    {
        echo "SHELL=/bin/sh"
        echo "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
        echo "* * * * * root cd /var/www/html && ${CRON_COMMAND} >> /var/log/cron.log 2>&1"
    } > /etc/cron.d/cms-cron
    chmod 0644 /etc/cron.d/cms-cron
    echo "[startup] Cron configured (command): ${CRON_COMMAND}"
else
    echo "[startup] WARNING: DOCKER_CRON_COMMAND is not set in docker/config.env — cron job skipped."
fi

# ── 7. Start cron service ─────────────────────────────────────────────────────
echo "[startup] Starting cron service..."
service cron start

# ── 8. Xdebug (Xdebug 3 reads XDEBUG_MODE env var natively) ──────────────────
export XDEBUG_MODE="${DOCKER_XDEBUG_MODE}"
export PHP_IDE_CONFIG="serverName=${DOCKER_SERVER_NAME}"
echo "[startup] Xdebug mode: ${XDEBUG_MODE}"
echo "[startup] Xdebug server name: ${DOCKER_SERVER_NAME}"

# ── 9. Start Apache ──────────────────────────────────────────────────────────
echo "[startup] Starting Apache on port 8081..."
exec apache2-foreground

