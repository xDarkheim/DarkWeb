# Xdebug — Step Debugging

Xdebug 3 is **pre-installed** in the Docker image. It is controlled by the `docker_xdebug_mode` key in `cms.json` and is disabled (`off`) by default.

## Enable Xdebug

Set in `includes/config/cms.json`:

```json
"docker_xdebug_mode": "debug"
```

Then restart the container:

```bash
docker compose restart web
```

## Verify

```bash
docker compose exec web php -v
```

Expected output includes:

```
with Xdebug v3.x.x, Copyright (c) 2002-2024, by Derick Rethans
```

Check the active mode:

```bash
docker compose exec web php -r "echo ini_get('xdebug.mode');"
```

## How it works

| File | Role |
|------|------|
| `docker/Dockerfile` | Installs Xdebug via `pecl install xdebug`, copies `docker/xdebug.ini` |
| `docker/xdebug.ini` | Static defaults: port `9003`, host `host.docker.internal`, idekey `PHPSTORM`, mode `off` |
| `docker/entrypoint.sh` | Reads `docker_xdebug_mode` and `docker_server_name` from `cms.json`, exports `XDEBUG_MODE` and `PHP_IDE_CONFIG` env vars |
| `docker-compose.yml` | `extra_hosts: host.docker.internal:host-gateway` — resolves the host IP on Linux |

Xdebug 3 reads the `XDEBUG_MODE` environment variable natively and overrides the ini `xdebug.mode` value. The `PHP_IDE_CONFIG=serverName=<docker_server_name>` variable tells IDEs which project the debug session belongs to — critical when multiple containers run on the same machine.

## IDE configuration

The **Server Name** in your IDE must match your `docker_server_name` from `cms.json` exactly (e.g. `mu.darkheim.net`). This is how the IDE maps an incoming Xdebug connection to the correct project.

### PHPStorm / IntelliJ IDEA

1. **File → Settings → PHP → Debug**
   - **Debug port**: `9003`
   - ✅ **Can accept external connections**
   - ❌ **Force break at first line…** (both unchecked)
2. **File → Settings → PHP → Servers** → **+**
   - **Name**: your `docker_server_name` value (e.g. `mu.darkheim.net`) — **must match exactly**
   - **Host**: your `docker_server_name` value (e.g. `mu.darkheim.net`)
   - **Port**: `443` (or `8081` for local-only setups)
   - **Debugger**: `Xdebug`
   - ✅ **Use path mappings**
   - Map project root → `/var/www/html`
3. Click **Start Listening for PHP Debug Connections** (phone icon in toolbar — turns green)
4. Set a breakpoint and open the site in a browser — PhpStorm will pause at the breakpoint

### Visual Studio Code

1. Install **PHP Debug** extension (by Xdebug)
2. Create `.vscode/launch.json`:
   ```json
   {
     "version": "0.2.0",
     "configurations": [
       {
         "name": "Listen for Xdebug",
         "type": "php",
         "request": "launch",
         "port": 9003,
         "pathMappings": {
           "/var/www/html": "${workspaceFolder}"
         }
       }
     ]
   }
   ```
3. Press **F5** to start listening

### Neovim / Vim

1. Install **Vdebug** plugin
2. Add to `.vimrc` / `init.vim`:
   ```vim
   let g:vdebug_options = {
   \    'port': 9003,
   \    'path_maps': {'/var/www/html': getcwd()},
   \    'break_on_open': 0
   \}
   ```
3. Start listener: `<F5>`, set breakpoint: `<F10>`

### Sublime Text

1. Install **Xdebug Client** package
2. **Project → Edit Project**:
   ```json
   {
     "settings": {
       "xdebug": {
         "port": 9003,
         "path_mapping": {
           "/var/www/html": "${folder}"
         }
       }
     }
   }
   ```
3. **Tools → Xdebug → Start Debugging**

## Debugging tests

1. Set a breakpoint in the test or in the class being tested
2. Start your IDE's debug listener
3. Run the test:
   ```bash
   docker compose exec web ./vendor/bin/phpunit tests/Unit/Domain/ValidatorTest.php
   ```
4. The IDE pauses at the breakpoint

**PHPStorm shortcut**: Right-click a test → **Debug 'YourTest'** — works automatically when the interpreter is configured via Docker Compose.

### Common debugger controls

| Action | PHPStorm | VS Code | Neovim | Sublime |
|---|---|---|---|---|
| Step Over | F8 | F10 | `:Over` | F10 |
| Step Into | F7 | F11 | `:Into` | F11 |
| Step Out | Shift+F8 | Shift+F11 | `:Out` | Shift+F11 |
| Resume | F9 | F5 | `:Run` | F8 |

## Coverage reports

Enable coverage mode in `cms.json`:

```json
"docker_xdebug_mode": "debug,coverage"
```

Restart the container, then:

```bash
docker compose exec web ./vendor/bin/phpunit --coverage-html coverage/
```

Open `coverage/index.html` in a browser.

**PHPStorm**: Right-click test → **Run with Coverage**.

## Disable Xdebug

Set in `cms.json`:

```json
"docker_xdebug_mode": "off"
```

Restart the container. Xdebug remains installed but inactive — zero performance impact.

## Troubleshooting

### Breakpoints are not hit

1. **IDE listening?** PHPStorm phone icon should be green; VS Code debug panel shows "Listening for Xdebug".
2. **Xdebug loaded?** `docker compose exec web php -m | grep xdebug` → should print `xdebug`.
3. **Mode correct?** `docker compose exec web php -r "echo getenv('XDEBUG_MODE');"` → should print `debug`.
4. **Server name matches?** The IDE server name must equal `docker_server_name` from `cms.json`. Check entrypoint log: `docker compose logs web | grep "Xdebug server name"`.
5. **Path mappings?** Project root must map to `/var/www/html`.
6. **Firewall?** Port 9003 must be open on the host for incoming connections from Docker.

### "Cannot find server" / "Cannot map path"

The IDE received a debug connection but cannot match it to a project. Ensure:
- PHPStorm: **Settings → PHP → Servers** has an entry where **Name** equals your `docker_server_name`
- VS Code: `pathMappings` in `launch.json` maps `/var/www/html` to `${workspaceFolder}`

### Tests run extremely slowly

Xdebug adds overhead even in `debug` mode. For regular test runs without debugging:

```bash
docker compose exec -e XDEBUG_MODE=off web ./vendor/bin/phpunit --no-coverage
```

This overrides the mode for a single command without changing `cms.json`.
