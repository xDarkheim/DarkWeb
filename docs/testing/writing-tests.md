# Writing Tests

This guide covers test structure, patterns, and examples for writing unit tests.

## Test layout

Tests mirror the `src/` directory structure:

```
tests/Unit/
├── Domain/
│   └── ValidatorTest.php
├── Application/
│   ├── Account/AccountTest.php
│   ├── Auth/
│   │   ├── AuthServiceTest.php
│   │   ├── CommonTest.php
│   │   ├── LoginTest.php
│   │   └── SessionManagerTest.php
│   ├── News/
│   │   ├── NewsItemTest.php
│   │   └── NewsRepositoryTest.php
│   ├── Rankings/
│   │   ├── RankingCacheTest.php
│   │   └── RankingRepositoryTest.php
│   └── Vote/VoteSiteRepositoryTest.php
└── Infrastructure/
    ├── Cache/
    │   ├── CacheManagerTest.php
    │   └── CacheRepositoryTest.php
    ├── Config/
    │   ├── ConfigRepositoryTest.php
    │   ├── JsonConfigReaderTest.php
    │   └── XmlConfigReaderTest.php
    ├── Cron/CronManagerTest.php
    ├── Database/
    │   ├── ConnectionTest.php
    │   └── dBTest.php
    ├── Email/EmailTest.php
    ├── Payment/PaypalIPNTest.php
    ├── Plugins/PluginsTest.php
    └── Routing/HandlerTest.php
```

## Test tiers

Tests are organised into three tiers based on dependency complexity:

### Tier 1 — Pure classes

**No dependencies**: no mocking, no filesystem, deterministic.

**Examples**: `Validator`, `NewsItem`, `RankingCache`

```php
<?php

namespace Tests\Unit\Domain;

use Darkheim\Domain\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testEmailValid(): void
    {
        $this->assertTrue(Validator::Email('user@example.com'));
    }

    public function testEmailInvalidFormat(): void
    {
        $this->assertFalse(Validator::Email('notanemail'));
    }
}
```

### Tier 2 — Filesystem classes

**Filesystem dependencies**: use real instances backed by `sys_get_temp_dir()`.

**Examples**: `CacheRepository`, `JsonConfigReader`, `XmlConfigReader`, `ConfigRepository`, `NewsRepository`, `RankingRepository`, `SessionManager`

> `CacheRepository`, `JsonConfigReader`, and `XmlConfigReader` are declared `final` and **cannot be mocked** by PHPUnit. Tests use real instances with temp files.

```php
<?php

namespace Tests\Unit\Infrastructure\Cache;

use Darkheim\Infrastructure\Cache\CacheRepository;
use PHPUnit\Framework\TestCase;

class CacheRepositoryTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/test_' . uniqid() . '/';
        mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '*') ?: [] as $f) @unlink($f);
        @rmdir($this->dir);
    }

    public function testLoadValidJson(): void
    {
        file_put_contents($this->dir . 'test.cache', json_encode(['key' => 'val']));
        $repo = new CacheRepository($this->dir);
        $this->assertSame(['key' => 'val'], $repo->load('test.cache'));
    }
}
```

### Tier 3 — DB-dependent classes

**Database dependencies**: bypass constructor + inject mock `dB` via reflection.

**Examples**: `Common`, `Login`, `Account`, `CreditSystem`, `Character`, `VoteSiteRepository`, `CronManager`, `Plugins`

```php
<?php

namespace Tests\Unit\Application\Auth;

use Darkheim\Application\Auth\Common;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;

class CommonTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb): Common
    {
        /** @var Common $sut */
        $sut = $this->makeWithDb(Common::class, $mockDb);
        $this->setProp($sut, '_passwordEncryption', 'phpmd5');
        $this->setProp($sut, '_sha256salt', 'salt');
        $this->setProp($sut, '_debug', false);
        return $sut;
    }

    public function testEmailExistsReturnsTrueWhenFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(['mail_addr' => 'a@b.com']);
        $sut = $this->make($db);
        $this->assertTrue($sut->emailExists('user@example.com'));
    }

    public function testEmailExistsReturnsNullWhenNotFound(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch_single')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->emailExists('user@example.com'));
    }
}
```

## Common patterns

### Testing methods that call `redirect()`

The bootstrap stub throws `RedirectException` instead of calling `die()`:

```php
use Tests\Stubs\RedirectException;

public function testLogoutRedirects(): void
{
    $this->expectException(RedirectException::class);
    $sut->logout();
}
```

### Testing `SessionManager`

Manipulate `$_SESSION` directly:

```php
protected function setUp(): void
{
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $_SESSION = [];
}

public function testIsAuthenticatedWhenAllKeysPresent(): void
{
    $_SESSION = ['valid' => true, 'userid' => 1, 'username' => 'test', 'timeout' => time()];
    $sm = new SessionManager();
    $this->assertTrue($sm->isAuthenticated());
}
```

### Testing `dB` without a real database

Use mock `PDO` + mock `PDOStatement`:

```php
private function make(): array
{
    $sut  = (new \ReflectionClass(dB::class))->newInstanceWithoutConstructor();
    $pdo  = $this->createMock(PDO::class);
    $stmt = $this->createMock(PDOStatement::class);

    (new ReflectionProperty(dB::class, 'db'))->setValue($sut, $pdo);
    (new ReflectionProperty(dB::class, 'dead'))->setValue($sut, false);

    return [$sut, $pdo, $stmt];
}

public function testQueryReturnsTrueOnSuccess(): void
{
    [$sut, $pdo, $stmt] = $this->make();
    $stmt->method('execute')->willReturn(true);
    $pdo->method('prepare')->willReturn($stmt);

    $this->assertTrue($sut->query('UPDATE Foo SET bar = ?', [1]));
}
```

## Adding a new test

1. Create `tests/Unit/<mirror-path-from-src>/YourClassTest.php`
2. Namespace: `Tests\Unit\...` (mirrors `Darkheim\...`)
3. Use the appropriate tier pattern (pure / filesystem / DB)
4. Run `composer test` to verify

Example for a new DB-coupled class:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Application\YourFeature;

use Darkheim\Application\YourFeature\YourClass;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;

class YourClassTest extends TestCase
{
    use DbTestHelper;

    private function make(): YourClass
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->makeWithDb(YourClass::class, $db);
        // Set any additional properties:
        // $this->setProp($sut, '_config', ['key' => 'value']);
        return $sut;
    }

    public function testYourMethod(): void
    {
        $sut = $this->make();
        // ... assertions
    }
}
```

Then:

```bash
docker compose exec web composer dump-autoload --optimize
docker compose exec web composer test
```

