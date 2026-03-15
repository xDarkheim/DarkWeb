# PHPStorm Quick Start — Testing Setup

This guide will get PHPUnit running in PHPStorm in 5 minutes.

## Prerequisites

- ✅ Docker Desktop running
- ✅ Container started: `docker compose up -d`
- ✅ Dependencies installed: `docker compose exec web composer install`

## Step-by-Step Setup

### 1. Verify Container is Running

```bash
docker compose ps
```

You should see:
```
NAME           IMAGE          COMMAND                  SERVICE   CREATED       STATUS
muonline_web   muonline-web   "/usr/local/bin/dock…"   web       X hours ago   Up X hours (healthy)
```

If not running: `docker compose up -d`

---

### 2. Configure PHP Interpreter

1. Open **File → Settings → PHP** (Windows/Linux) or **PhpStorm → Preferences → PHP** (macOS)

2. Click **CLI Interpreter** → **…** (three dots next to the dropdown)

3. Click **+** → **From Docker, Vagrant, VM, WSL, Remote…**

4. Select **Docker Compose**

5. Fill in the form:
   ```
   Configuration files:  [Browse] → docker-compose.yml
   Service:             web
   Lifecycle:           ○ Connect to existing container ('docker compose exec')
   ```

6. Click **OK**

7. PHPStorm will connect and show:
   ```
   PHP version: 8.4.19
   Path mappings:
     /home/your-user/PhpstormProjects/MuOnline → /var/www/html
   ```

8. Click **OK** again to save

---

### 3. Configure PHPUnit Test Framework

1. Still in **Settings → PHP**, click **Test Frameworks** in the left sidebar

2. Click **+** → **PHPUnit by Remote Interpreter**

3. Select your Docker Compose interpreter from the dropdown

4. **PHPUnit library** section:
   - Click **Path to script** radio button
   - Enter: `/var/www/html/vendor/autoload.php`
   
   ⚠️ **Must start with `/var/www/html`** — this is the container path!

5. **Test Runner** section:
   - ☑ Default configuration file: `/var/www/html/phpunit.xml`
   - ☑ Default bootstrap file: `/var/www/html/tests/bootstrap.php`

6. Click **OK**

---

### 4. Verify Configuration Files

Open `.idea/php.xml` (in your project root) and check the `<PhpUnit>` component:

```xml
<phpunit_by_interpreter 
  interpreter_id="docker-compose-muonline-web"
  bootstrap_file_path="/var/www/html/tests/bootstrap.php"
  configuration_file_path="/var/www/html/phpunit.xml"
  custom_loader_path="/var/www/html/vendor/autoload.php"
  use_configuration_file="true" />
```

✅ All paths should contain `/var/www/html`  
❌ If you see `/home/…` or `$PROJECT_DIR$`, go back to Step 3 and re-enter the paths

---

### 5. Run Your First Test

1. In the project tree, expand: `tests/Unit/Domain/`

2. Right-click `ValidatorTest.php`

3. Select **Run 'ValidatorTest'**

4. The **Run** panel opens at the bottom showing:
   ```
   Testing started at ...
   PHPUnit 11.5.55 by Sebastian Bergmann and contributors.
   Runtime: PHP 8.4.19
   
   Validator (Tests\Unit\Domain\Validator)
    ✔ Email valid
    ✔ Email invalid format
    ...
   
   OK (37 tests, 37 assertions)
   Time: 00:00.005, Memory: 6.00 MB
   
   Process finished with exit code 0
   ```

✅ **Success!** Green checkmarks mean tests passed.

---

### 6. Run All Tests

1. Right-click the `tests/` folder in the project tree

2. Select **Run 'tests'**

3. You should see all 196 tests pass:
   ```
   OK (196 tests, 271 assertions)
   ```

---

### 7. Set Up Run Configurations (Optional)

For faster access, create toolbar shortcuts:

1. **Run → Edit Configurations…**

2. Click **+** → **PHPUnit**

3. Create three configurations:

   **Configuration 1: All Tests**
   - Name: `All Tests`
   - Test scope: `Defined in the configuration file`
   - Configuration file: `/var/www/html/phpunit.xml`
   - Interpreter: `docker-compose-muonline-web`

   **Configuration 2: All Tests (No Coverage)**
   - Name: `All Tests (No Coverage)`
   - Test scope: `Defined in the configuration file`
   - Configuration file: `/var/www/html/phpunit.xml`
   - Interpreter: `docker-compose-muonline-web`
   - Command Line → Additional Arguments: `--no-coverage`

   **Configuration 3: Current File**
   - Name: `Current Test File`
   - Test scope: `Directory`
   - Directory: `/var/www/html/tests`
   - Interpreter: `docker-compose-muonline-web`
   - ☑ Use alternative configuration file: `/var/www/html/phpunit.xml`

4. Click **OK**

Now you can run tests from the toolbar dropdown!

---

## Quick Reference

| Action | Shortcut |
|--------|----------|
| Run test under cursor | `Ctrl+Shift+F10` |
| Re-run last test | `Shift+F10` |
| Debug test under cursor | `Ctrl+Shift+F9` |
| Run with coverage | `Ctrl+Shift+F10` → select config with coverage |

---

## Troubleshooting

### "Cannot find PHPUnit in include path"

**Cause**: PHPStorm is using local paths instead of container paths.

**Fix**:
1. Go to **Settings → PHP → Test Frameworks**
2. Delete the PHPUnit configuration
3. Re-add using **exact paths** from Step 3 above
4. Verify `.idea/php.xml` shows `/var/www/html` paths

---

### "Process finished with exit code 139"

**Cause**: Container PHP crashed (segfault).

**Fix**:
1. Check container logs: `docker compose logs web`
2. Restart container: `docker compose restart web`
3. Try again

If it persists, increase Docker memory:
- **Docker Desktop → Settings → Resources → Memory** → 4GB minimum

---

### Green ▶ icons don't appear next to tests

**Cause**: PHPStorm hasn't indexed the test files.

**Fix**:
1. **File → Invalidate Caches… → Invalidate and Restart**
2. Wait for indexing to complete (progress bar at bottom)
3. Icons should appear within 30 seconds

---

### Tests run but file paths in output are wrong

**Cause**: Path mapping incorrect.

**Fix**:
1. **Settings → PHP → CLI Interpreter → …**
2. Check **Path mappings**:
   - Local: `/home/your-user/PhpstormProjects/MuOnline`
   - Remote: `/var/www/html`
3. If wrong, click **Refresh** (sync icon)

---

## Next Steps

- [Debug tests with breakpoints](ide-setup.md#phpstorm--intellij-idea)
- [Write your first test](writing-tests.md)
- [Run tests from command line](../phpunit.md)

---

## Video Walkthrough

*Coming soon: Watch a 3-minute setup screencast*


