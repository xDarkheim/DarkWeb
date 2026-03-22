<?php

use Darkheim\Application\Rankings\RankingsService as Rankings;
use Darkheim\Infrastructure\Cron\CronManager;

// File Name
$file_name = basename(__FILE__);

// Load Rankings Class
$Rankings = new Rankings();

// Load Ranking Configs
\Darkheim\Infrastructure\Bootstrap\BootstrapContext::loadModuleConfig('rankings');

if (\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active') && \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('rankings_enable_votes')) {
    $Rankings->UpdateRankingCache('votes');
}

// UPDATE CRON
new CronManager()->updateLastRun($file_name);
