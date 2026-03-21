<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Cron;

use Darkheim\Infrastructure\Cron\CronExecutor;
use Darkheim\Infrastructure\Cron\CronManager;
use PHPUnit\Framework\TestCase;

final class CronExecutorTest extends TestCase
{
    public function testExecuteRejectsInvalidSingleCronId(): void
    {
        $manager = $this->createMock(CronManager::class);
        $manager->expects($this->never())->method('getCronList');

        $sut = new CronExecutor($manager);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The provided cron id is not valid.');
        $sut->execute('abc');
    }

    public function testExecuteThrowsWhenNoCronsConfigured(): void
    {
        $manager = $this->createMock(CronManager::class);
        $manager->method('getCronList')->willReturn(null);

        $sut = new CronExecutor($manager);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There are no crons.');
        $sut->execute();
    }

    public function testExecuteSingleThrowsWhenIdNotFound(): void
    {
        $manager = $this->createMock(CronManager::class);
        $manager->method('getCronList')->willReturn([
            [
                'cron_id' => '1',
                'cron_file_run' => 'missing.php',
                'cron_status' => 1,
                'cron_last_run' => 0,
                'cron_run_time' => 60,
            ],
        ]);

        $sut = new CronExecutor($manager);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The provided cron id is not valid.');
        $sut->execute('2');
    }

    public function testExecuteAllReturnsEmptyWhenNoCronIsDue(): void
    {
        $manager = $this->createMock(CronManager::class);
        $manager->method('getCronList')->willReturn([
            [
                'cron_id' => '1',
                'cron_file_run' => 'missing.php',
                'cron_status' => 1,
                'cron_last_run' => time(),
                'cron_run_time' => 999999,
            ],
        ]);

        $sut = new CronExecutor($manager);

        $this->assertSame([], $sut->execute());
    }
}

