<?php
// Timezone is configured in includes/config/cms.json → "docker_timezone"
// http://php.net/manual/en/timezones.php
$_tzRaw = @file_get_contents(__DIR__ . '/cms.json');
$_tzCfg = $_tzRaw ? json_decode($_tzRaw, true) : [];
$_tz    = (is_array($_tzCfg) && !empty($_tzCfg['docker_timezone']))
    ? $_tzCfg['docker_timezone']
    : 'UTC';
date_default_timezone_set($_tz);
unset($_tzRaw, $_tzCfg, $_tz);
