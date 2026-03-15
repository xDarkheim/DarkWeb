# IDE Setup — Running & Debugging Tests

Run and debug PHPUnit tests directly from your IDE with clickable stack traces, inline results, and breakpoints.

**Choose your IDE:**

- [PHPStorm / IntelliJ IDEA](#phpstorm--intellij-idea)
- [Visual Studio Code](#visual-studio-code)
- [Neovim / Vim](#neovim--vim)
- [Sublime Text](#sublime-text)

---

## PHPStorm / IntelliJ IDEA

### 1. Configure Docker Compose as Remote Interpreter

1. **File → Settings → PHP** (or **PhpStorm → Preferences → PHP** on macOS)
2. Click **CLI Interpreter** → **…** (three dots)
3. Click **+** → **From Docker, Vagrant, VM, WSL, Remote…**
4. Select **Docker Compose**
5. Fill in:
   - **Configuration files**: `docker-compose.yml`
   - **Service**: `web`
   - **Lifecycle**: `Connect to existing container ('docker compose exec')`
6. Click **OK** — PHPStorm will validate and show PHP 8.4.19
7. Verify **Path mappings**:
   - **Local**: `<your-project-path>`
   - **Remote**: `/var/www/html`
8. Click **OK**

### 2. Configure PHPUnit

1. **File → Settings → PHP → Test Frameworks**
2. Click **+** → **PHPUnit by Remote Interpreter**
3. Select the Docker Compose interpreter
4. **PHPUnit library**:
   - ☑ **Path to script**: `/var/www/html/vendor/autoload.php` ⚠️ **Must be container path, not local!**
5. **Test Runner**:
   - ☑ **Default configuration file**: `/var/www/html/phpunit.xml` ⚠️ **Must be container path!**
   - ☑ **Default bootstrap file**: `/var/www/html/tests/bootstrap.php` ⚠️ **Must be container path!**
6. Click **OK**

**Important**: All paths must use `/var/www/html` (the container's working directory), **not** your local project path like `/home/user/PhpstormProjects/MuOnline`.

### 3. Verify Configuration

After setup, check `.idea/php.xml` contains:

```xml
<phpunit_by_interpreter 
  interpreter_id="docker-compose-muonline-web"
  bootstrap_file_path="/var/www/html/tests/bootstrap.php"
  configuration_file_path="/var/www/html/phpunit.xml"
  custom_loader_path="/var/www/html/vendor/autoload.php"
  use_configuration_file="true" />
```

If paths show `/home/…` or `$PROJECT_DIR$`, delete the Test Framework configuration and repeat step 2.

### 4. Run tests

- **All tests**: Right-click `tests/` → **Run 'tests'**
- **Single class**: Right-click `ValidatorTest.php` → **Run 'ValidatorTest'**
- **Single method**: Click green ▶ icon next to method → **Run**
- **From toolbar**: Use **All Tests** or **All Tests (No Coverage)** run configurations

### 5. Debug

1. Set breakpoint (click left gutter)
2. Right-click test → **Debug 'ValidatorTest'**
3. Execution pauses — inspect variables in **Debug** panel
4. **F8** (step over), **F7** (step into), **F9** (resume)

> Xdebug is pre-installed in the Docker image. Enable it by setting `"docker_xdebug_mode": "debug"` in `cms.json` and restarting the container. See [Xdebug guide](xdebug.md) for full setup including Server configuration.

**Shortcuts**: `Ctrl+Shift+F10` (run), `Ctrl+Shift+F9` (debug), `Shift+F10` (re-run)

### Troubleshooting PHPStorm

**Tests don't run / "Cannot find PHPUnit"**

1. Check interpreter is set to Docker Compose (`File → Settings → PHP → CLI Interpreter`)
2. Verify container is running: `docker compose ps` should show `muonline_web` as `Up`
3. Check Test Frameworks paths use `/var/www/html`, not local paths
4. Invalidate caches: `File → Invalidate Caches… → Invalidate and Restart`

**"Process finished with exit code 139" (segfault)**

Container PHP crashed. Check logs:
```bash
docker compose logs web
```

Often caused by:
- Out of memory (increase Docker memory limit)
- Xdebug misconfiguration
- Missing PHP extensions

**Tests run but results show wrong file paths**

Path mapping incorrect. Verify `File → Settings → PHP → CLI Interpreter → …` shows:
- Local: `/home/your-user/PhpstormProjects/MuOnline`
- Remote: `/var/www/html`

**Green ▶ icons don't appear next to test methods**

1. Ensure file is named `*Test.php` and class extends `TestCase`
2. Right-click project root → **Reload from Disk**
3. `File → Invalidate Caches… → Invalidate and Restart`



---

## Visual Studio Code

### 1. Install PHP Debug Extension

1. Open **Extensions** (`Ctrl+Shift+X`)
2. Search: **PHP Debug** (by Xdebug)
3. Click **Install**

### 2. Install PHPUnit Test Explorer (optional)

1. Search: **PHPUnit Test Explorer** (by Recca Tsai)
2. Click **Install** — adds test sidebar and inline run buttons

### 3. Configure Tasks

Create `.vscode/tasks.json`:

```json
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "Run PHPUnit Tests",
      "type": "shell",
      "command": "docker compose exec web ./vendor/bin/phpunit --no-coverage",
      "group": {
        "kind": "test",
        "isDefault": true
      },
      "presentation": {
        "echo": true,
        "reveal": "always",
        "panel": "dedicated"
      },
      "problemMatcher": []
    },
    {
      "label": "Run Current Test File",
      "type": "shell",
      "command": "docker compose exec web ./vendor/bin/phpunit --no-coverage ${relativeFile}",
      "group": "test",
      "presentation": {
        "reveal": "always",
        "panel": "dedicated"
      }
    }
  ]
}
```

### 4. Run tests

**Via tasks**:
- **Terminal → Run Task** → **Run PHPUnit Tests**
- Or: `Ctrl+Shift+B` (runs default test task)

**Via Test Explorer** (if installed):
- Open **Testing** sidebar (beaker icon)
- Click ▶ next to test class or method

**Via terminal**:
- Open integrated terminal (`Ctrl+`\`)
- Run: `docker compose exec web composer test`

### 5. Debug with Xdebug

Xdebug is pre-installed in the Docker image. Enable it by setting `"docker_xdebug_mode": "debug"` in `cms.json` and restarting the container.

See [Xdebug guide → VS Code](xdebug.md#visual-studio-code) for `launch.json` configuration and path mappings.

---

## Neovim / Vim

### 1. Install vim-test Plugin

Using **vim-plug**, add to `.vimrc` / `init.vim`:

```vim
Plug 'vim-test/vim-test'
```

Then: `:PlugInstall`

### 2. Configure vim-test for Docker

Add to `.vimrc` / `init.vim`:

```vim
" Use Docker Compose for test commands
let test#php#phpunit#executable = 'docker compose exec web ./vendor/bin/phpunit'

" Key mappings
nmap <silent> <leader>tn :TestNearest<CR>
nmap <silent> <leader>tf :TestFile<CR>
nmap <silent> <leader>ts :TestSuite<CR>
nmap <silent> <leader>tl :TestLast<CR>
```

### 3. Run tests

- **Test nearest** (method under cursor): `<leader>tn`
- **Test file**: `<leader>tf`
- **Test suite** (all): `<leader>ts`
- **Re-run last**: `<leader>tl`

Output appears in a split window.

### 4. Debug with Vdebug (Xdebug client)

Xdebug is pre-installed in the Docker image. Enable it by setting `"docker_xdebug_mode": "debug"` in `cms.json` and restarting the container.

See [Xdebug guide → Neovim](xdebug.md#neovim--vim) for Vdebug configuration and path mappings.

---

## Sublime Text

### 1. Install PHPUnit Package

1. **Tools → Command Palette** (`Ctrl+Shift+P`)
2. Type: **Package Control: Install Package**
3. Search: **PHPUnit**
4. Click to install

### 2. Configure Build System

Create **Tools → Build System → New Build System**:

```json
{
  "shell_cmd": "docker compose exec web ./vendor/bin/phpunit --no-coverage",
  "working_dir": "${project_path}",
  "selector": "source.php",
  "variants": [
    {
      "name": "Current File",
      "shell_cmd": "docker compose exec web ./vendor/bin/phpunit --no-coverage ${file}"
    }
  ]
}
```

Save as `PHPUnit-Docker.sublime-build`.

### 3. Run tests

1. Open any test file
2. **Tools → Build System** → **PHPUnit-Docker**
3. Press **Ctrl+B** to run all tests
4. **Ctrl+Shift+B** → select **Current File** to test one file

Results appear in output panel at bottom.

### 4. Debug with Xdebug Sublime Client

Xdebug is pre-installed in the Docker image. Enable it by setting `"docker_xdebug_mode": "debug"` in `cms.json` and restarting the container.

See [Xdebug guide → Sublime Text](xdebug.md#sublime-text) for package configuration and path mappings.

---

## Troubleshooting (All IDEs)

### Tests run but show wrong paths

**Cause**: Path mapping missing or incorrect.

**Fix**: Ensure your IDE maps:
- **Local**: `<your-project-directory>`
- **Remote**: `/var/www/html`

### "vendor/autoload.php not found"

**Cause**: Dependencies not installed in container.

**Fix**:
```bash
docker compose exec web composer install
```

### Xdebug not connecting

Xdebug is pre-installed but disabled by default. Ensure `"docker_xdebug_mode": "debug"` is set in `cms.json` and the container has been restarted.

**Check**:
1. Xdebug loaded? `docker compose exec web php -m | grep xdebug`
2. Mode active? `docker compose exec web php -r "echo getenv('XDEBUG_MODE');"`
3. Port 9003 open? `sudo lsof -i :9003`
4. IDE server name matches `docker_server_name` from `cms.json`?
5. Path mappings correct?

See [Xdebug guide → Troubleshooting](xdebug.md#troubleshooting) for detailed steps.

### Docker permission errors

```bash
# Add user to docker group
sudo usermod -aG docker $USER
# Log out and back in
```

