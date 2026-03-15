<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Cron;

use Darkheim\Infrastructure\Cron\CronManager;
use Darkheim\Infrastructure\Database\dB;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\DbTestHelper;

class CronManagerTest extends TestCase
{
    use DbTestHelper;

    private function make(dB $mockDb): CronManager
    {
        /** @var CronManager $sut */
        $sut = $this->makeWithDb(CronManager::class, $mockDb, 'muonline');
        return $sut;
    }

    // ── setId ────────────────────────────────────────────────────────────────

    public function testSetIdAcceptsUnsignedNumber(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $sut->setId(5);
        $this->assertTrue(true); // no exception thrown
    }

    public function testSetIdThrowsForNonNumeric(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->setId('abc');
    }

    // ── setFile ──────────────────────────────────────────────────────────────

    public function testSetFileThrowsWhenFileNotInCronDir(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->expectException(\Exception::class);
        $sut->setFile('nonexistent.php');
    }

    public function testSetFileAcceptsExistingFile(): void
    {
        $file = __PATH_CRON__ . 'test_job.php';
        file_put_contents($file, '<?php // cron job');

        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $sut->setFile('test_job.php');
        $this->assertTrue(true);

        @unlink($file);
    }

    // ── getCronList ──────────────────────────────────────────────────────────

    public function testGetCronListReturnsArray(): void
    {
        $rows = [['cron_id' => 1, 'cron_name' => 'Rankings']];
        $db   = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn($rows);
        $sut = $this->make($db);
        $this->assertSame($rows, $sut->getCronList());
    }

    public function testGetCronListReturnsNullWhenEmpty(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query_fetch')->willReturn(null);
        $sut = $this->make($db);
        $this->assertNull($sut->getCronList());
    }

    // ── resetCronLastRun ─────────────────────────────────────────────────────

    public function testResetCronLastRunReturnsFalseWhenNoIdSet(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertFalse($sut->resetCronLastRun());
    }

    public function testResetCronLastRunReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $sut->setId(1);
        $this->assertTrue($sut->resetCronLastRun());
    }

    // ── deleteCron ───────────────────────────────────────────────────────────

    public function testDeleteCronReturnsFalseWhenNoIdSet(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertFalse($sut->deleteCron());
    }

    public function testDeleteCronReturnsTrueOnSuccess(): void
    {
        $db = $this->createMock(dB::class);
        $db->method('query')->willReturn(true);
        $sut = $this->make($db);
        $sut->setId(2);
        $this->assertTrue($sut->deleteCron());
    }

    // ── getCronApiUrl ────────────────────────────────────────────────────────

    public function testGetCronApiUrlWithoutId(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $url = $sut->getCronApiUrl();
        $this->assertStringContainsString('cron.php', $url);
        $this->assertStringContainsString('key=', $url);
    }

    public function testGetCronApiUrlWithId(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $url = $sut->getCronApiUrl(3);
        $this->assertStringContainsString('id=3', $url);
    }

    // ── getCommonIntervals ───────────────────────────────────────────────────

    public function testGetCommonIntervalsReturns17Items(): void
    {
        $db  = $this->createMock(dB::class);
        $sut = $this->make($db);
        $this->assertCount(17, $sut->_commonIntervals);
    }

    public function testGetCommonIntervalsHasExpectedKeys(): void
    {
        $db        = $this->createMock(dB::class);
        $sut       = $this->make($db);
        $intervals = $sut->_commonIntervals;
        $this->assertArrayHasKey(60, $intervals);
        $this->assertArrayHasKey(3600, $intervals);
        $this->assertArrayHasKey(86400, $intervals);
    }
}

