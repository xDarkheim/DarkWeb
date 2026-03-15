# PHPStorm Configuration Checklist

Use this checklist to verify your PHPStorm is correctly configured for running PHPUnit tests in Docker.

## ✅ Pre-flight Check

Before configuring PHPStorm, verify:

```bash
# 1. Container is running
docker compose ps
# Expected: muonline_web | Up | healthy

# 2. Dependencies installed
docker compose exec web ls -la vendor/bin/phpunit
# Expected: -rwxr-xr-x ... vendor/bin/phpunit

# 3. Tests run from command line
docker compose exec web composer test
# Expected: OK (196 tests, 271 assertions)
```

If any fail, fix them first before configuring PHPStorm.

---

## 🔧 Configuration Steps

### 1. PHP Interpreter

**Path**: `File → Settings → PHP`

Expected state:
```
CLI Interpreter: docker-compose-muonline-web (Docker Compose)
PHP version:     8.4.19
Configuration:   docker-compose.yml
```

**Click … (three dots) to verify**:
```
Server:          Docker
Configuration:   docker-compose.yml
Service:         web
Lifecycle:       Connect to existing container ('docker compose exec')

Path mappings:
  <Project root> → /var/www/html
```

---

### 2. Test Frameworks

**Path**: `File → Settings → PHP → Test Frameworks`

Expected configuration:
```
+ PHPUnit by Remote Interpreter
    CLI Interpreter:             docker-compose-muonline-web
    
    PHPUnit library:
      ○ Use Composer autoloader
      ● Path to script:           /var/www/html/vendor/autoload.php
    
    Test Runner:
      ☑ Default configuration file: /var/www/html/phpunit.xml
      ☑ Default bootstrap file:     /var/www/html/tests/bootstrap.php
```

⚠️ **Common mistakes**:
- ❌ `/home/your-user/.../vendor/autoload.php` — This is wrong! Must use container path.
- ❌ `$PROJECT_DIR$/vendor/autoload.php` — This is wrong! Must use absolute container path.
- ✅ `/var/www/html/vendor/autoload.php` — Correct!

---

### 3. File Verification

**Check `.idea/php.xml`**:

```bash
grep -A5 'PhpUnit' .idea/php.xml
```

Expected output:
```xml
<component name="PhpUnit">
  <phpunit_settings>
    <phpunit_by_interpreter 
      interpreter_id="docker-compose-muonline-web"
      bootstrap_file_path="/var/www/html/tests/bootstrap.php"
      configuration_file_path="/var/www/html/phpunit.xml"
      custom_loader_path="/var/www/html/vendor/autoload.php"
      use_configuration_file="true" />
  </phpunit_settings>
</component>
```

If you see local paths like `/home/...`, the configuration is **wrong**.

**Fix**: Delete the Test Framework and re-add it with correct paths.

---

## ✅ Validation Tests

### Test 1: Run Single File

1. Open `tests/Unit/Domain/ValidatorTest.php`
2. Right-click anywhere in the file
3. Select **Run 'ValidatorTest'**

**Expected result**:
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

✅ **Pass**: Green bar, all tests passed  
❌ **Fail**: See troubleshooting below

---

### Test 2: Run Single Method

1. Open `tests/Unit/Domain/ValidatorTest.php`
2. Find method `testEmailValid()`
3. Click the green ▶ icon in the left gutter
4. Select **Run 'testEmailValid'**

**Expected result**:
```
Validator (Tests\Unit\Domain\Validator)
 ✔ Email valid

OK (1 test, 1 assertion)
```

✅ **Pass**: Single test ran  
❌ **Fail**: See troubleshooting below

---

### Test 3: Run All Tests

1. Right-click `tests/` folder
2. Select **Run 'tests'**

**Expected result**:
```
OK (196 tests, 271 assertions)
Time: 00:00.039, Memory: 12.00 MB
```

✅ **Pass**: All tests green  
❌ **Fail**: See troubleshooting below

---

### Test 4: Debug with Breakpoint

1. Open `tests/Unit/Domain/ValidatorTest.php`
2. Find line: `$result = Validator::email('test@example.com');`
3. Click left gutter to set breakpoint (red dot appears)
4. Right-click method → **Debug 'testEmailValid'**

**Expected result**:
- Execution pauses at breakpoint
- **Debug** panel opens at bottom
- Variables panel shows `$result = ...`
- Blue highlight on current line

**Actions**:
- Press **F8** to step over
- Press **F9** to resume
- Press **Ctrl+F2** to stop

✅ **Pass**: Debugger stopped at breakpoint  
❌ **Fail**: See troubleshooting below

---

## 🚨 Troubleshooting

### Problem: "Cannot find PHPUnit in include path"

**Symptoms**:
```
Error: Cannot find PHPUnit in include path
```

**Diagnosis**:
```bash
# Check if PHPUnit exists in container
docker compose exec web ./vendor/bin/phpunit --version
# Expected: PHPUnit 11.5.55
```

**Fix**:
1. If command fails: `docker compose exec web composer install`
2. If command succeeds: Your PHPStorm paths are wrong
   - Go to `Settings → PHP → Test Frameworks`
   - Delete PHPUnit configuration
   - Re-add with `/var/www/html/vendor/autoload.php`

---

### Problem: "Process finished with exit code 139"

**Symptoms**:
```
Process finished with exit code 139 (interrupted by signal 11: SIGSEGV)
```

**Diagnosis**:
```bash
# Check container logs
docker compose logs web | tail -20
```

**Possible causes**:
- Out of memory
- PHP extension crash
- Xdebug misconfiguration

**Fix**:
```bash
# 1. Restart container
docker compose restart web

# 2. If persists, increase Docker memory
# Docker Desktop → Settings → Resources → Memory → 4GB

# 3. If still failing, disable Xdebug
docker compose exec web php -v
# Should NOT show Xdebug in output for basic testing
```

---

### Problem: No green ▶ icons appear

**Symptoms**:
- No run icons next to test methods
- Can't right-click and run individual tests

**Diagnosis**:
- PHPStorm hasn't recognized test files
- Index may be stale

**Fix**:
```
1. File → Invalidate Caches… → Invalidate and Restart
2. Wait for indexing to complete (progress bar at bottom)
3. Check again in 30-60 seconds
```

If still missing:
```
1. Settings → PHP → Test Frameworks
2. Verify interpreter is set to docker-compose-muonline-web
3. Click "Test" button to validate connection
```

---

### Problem: Tests run but paths are wrong

**Symptoms**:
```
/home/user/PhpstormProjects/MuOnline/src/Domain/Validator.php:45
```

Instead of:
```
src/Domain/Validator.php:45
```

**Diagnosis**:
Path mapping is incorrect.

**Fix**:
```
1. Settings → PHP → CLI Interpreter → … (three dots)
2. Check Path mappings:
   Local:  /home/your-user/PhpstormProjects/MuOnline
   Remote: /var/www/html
3. If wrong, click Refresh icon (sync)
4. Re-run tests
```

---

### Problem: "Connection refused" or "Container not found"

**Symptoms**:
```
Error: Cannot connect to Docker daemon
Could not find container: muonline_web
```

**Diagnosis**:
```bash
# 1. Is Docker running?
docker ps

# 2. Is container running?
docker compose ps
```

**Fix**:
```bash
# Start Docker Desktop (if not running)

# Start container
docker compose up -d

# Verify
docker compose ps
# Should show: muonline_web | Up | healthy

# Retry test in PHPStorm
```

---

## 📊 Success Indicators

You'll know everything is working when:

✅ Green ▶ icons appear next to all test methods  
✅ Right-click → Run works on any test file  
✅ Tests execute in < 1 second  
✅ Test output shows container paths (`/var/www/html/...`)  
✅ Debug breakpoints pause execution  
✅ Variables panel shows test data during debug  

---

## 🎯 Final Checklist

Before closing this guide, confirm:

- [ ] `docker compose ps` shows container running
- [ ] `docker compose exec web composer test` passes all tests
- [ ] PHPStorm interpreter set to Docker Compose
- [ ] Test Frameworks paths use `/var/www/html`
- [ ] `.idea/php.xml` shows container paths (not local)
- [ ] Can run single test file via right-click
- [ ] Can run single test method via gutter icon
- [ ] Can run all tests via `tests/` folder
- [ ] Debug breakpoints work
- [ ] No error messages in PHPStorm event log

If all checked ✅ → **You're ready to write tests!**

If any ❌ → Review the relevant troubleshooting section above.

---

## 📚 Next Steps

- [Write your first test](writing-tests.md)
- [Set up Xdebug for advanced debugging](xdebug.md)
- [Configure other IDEs](ide-setup.md) (VS Code, Neovim, Sublime)


