<?php

use Darkheim\Infrastructure\Database\Connection;

// File Name
$file_name = basename(__FILE__);

// load database
$me = Connection::Database('MuOnline');
$charactersDB = config('SQL_DB_NAME', true);
$accountsDB = $charactersDB;

$result = array();

$query = "SELECT t2."._CLMN_GAMEIDC_." FROM ".$accountsDB.".[dbo]."._TBL_MS_." t1 INNER JOIN ".$charactersDB.".[dbo]."._TBL_AC_." t2 ON t1."._CLMN_MS_MEMBID_." = t2."._CLMN_AC_ID_." WHERE t1."._CLMN_CONNSTAT_." = 1";

$onlineCharactersList = $me->query_fetch($query);
if(is_array($onlineCharactersList)) {
	foreach($onlineCharactersList as $onlineCharacterData) {
		if(in_array($onlineCharacterData[_CLMN_GAMEIDC_], $result, true)) continue;
		$result[] = $onlineCharacterData[_CLMN_GAMEIDC_];
	}
}

$cacheData = encodeCache($result);
updateCacheFile('online_characters.cache', $cacheData);

// UPDATE CRON
updateCronLastRun($file_name);