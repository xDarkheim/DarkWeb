<?php

declare(strict_types=1);

namespace Darkheim\Infrastructure\Cron;

use Darkheim\Domain\Validator;

/**
 * Executes configured cron jobs using the existing CronManager schedule rules.
 */
final class CronExecutor
{
    private CronManager $manager;

    public function __construct(?CronManager $manager = null)
    {
        $this->manager = $manager ?? new CronManager();
    }

    /**
     * @return array<int,string>
     */
    public function execute(?string $singleCronId = null): array
    {
        if ($singleCronId !== null && !Validator::UnsignedNumber($singleCronId)) {
            throw new \RuntimeException('The provided cron id is not valid.');
        }

        $cronList = $this->manager->getCronList();
        if (!is_array($cronList)) {
            throw new \RuntimeException('There are no crons.');
        }

        if ($singleCronId !== null) {
            return $this->executeSingle($cronList, $singleCronId);
        }

        return $this->executeAll($cronList);
    }

    /**
     * @param array<int,array<string,mixed>> $cronList
     * @return array<int,string>
     */
    private function executeAll(array $cronList): array
    {
        $executedCrons = [];

        foreach ($cronList as $cron) {
            if (($cron['cron_status'] ?? null) != 1) {
                continue;
            }

            $runTime = isset($cron['cron_run_time']) ? (int) $cron['cron_run_time'] : 0;
            $lastRun = check_value($cron['cron_last_run'] ?? null)
                ? (int) $cron['cron_last_run'] + $runTime
                : $runTime;

            if (time() <= $lastRun) {
                continue;
            }

            $fileName = (string) ($cron['cron_file_run'] ?? '');
            if ($fileName === '') {
                continue;
            }

            $this->loadCronFile(__PATH_CRON__ . $fileName);
            $executedCrons[] = $fileName;
        }

        return $executedCrons;
    }

    /**
     * @param array<int,array<string,mixed>> $cronList
     * @return array<int,string>
     */
    private function executeSingle(array $cronList, string $singleCronId): array
    {
        $executedCrons = [];

        foreach ($cronList as $cron) {
            if ((string) ($cron['cron_id'] ?? '') !== $singleCronId) {
                continue;
            }

            $fileName = (string) ($cron['cron_file_run'] ?? '');
            if ($fileName === '') {
                continue;
            }

            $this->loadCronFile(__PATH_CRON__ . $fileName);
            $executedCrons[] = $fileName;
            break;
        }

        if ($executedCrons === []) {
            throw new \RuntimeException('The provided cron id is not valid.');
        }

        return $executedCrons;
    }

    private function loadCronFile(string $path): void
    {
        if (is_file($path)) {
            include $path;
        }
    }
}

