<?php

use Darkheim\Application\Rankings\RankingsService as Rankings;

// File Name
$file_name = basename(__FILE__);

// Load Rankings Class
$Rankings = new Rankings();

// Load Ranking Configs
loadModuleConfigs('rankings');

if(mconfig('active') && mconfig('rankings_enable_online')) {
    $Rankings->UpdateRankingCache('online');
}

// UPDATE CRON
updateCronLastRun($file_name);