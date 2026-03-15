<?php

use Darkheim\Application\CastleSiege\CastleSiege;

// File Name
$file_name = basename(__FILE__);

// Castle Siege
$castleSiege = new CastleSiege();
$castleSiege->updateSiegeCache();

// UPDATE CRON
updateCronLastRun($file_name);