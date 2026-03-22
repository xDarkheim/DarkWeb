<?php

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\Cron\CronManager;
use Darkheim\Infrastructure\Http\GeoIpService;

// File Name
$file_name = basename(__FILE__);

// load databases
$db = Connection::Database('MuOnline');

// add country to accounts with no country
$accountList = $db->query_fetch("SELECT TOP 40 * FROM "._TBL_MS_." WHERE "._CLMN_MS_MEMBID_." NOT IN(SELECT account FROM ".Account_Country.") AND "._CLMN_MS_IP_." IS NOT NULL");
if(is_array($accountList)) {
	$Account = new Account();
	foreach($accountList as $row) {
		$countryCode = GeoIpService::getCountryCode((string) $row[_CLMN_MS_IP_]);
		if(!\Darkheim\Domain\Validator::hasValue($countryCode)) continue;
		$Account->_account = $row[_CLMN_MS_MEMBID_];
		$Account->_country = $countryCode;
		$Account->insertAccountCountry();
	}
}

// UPDATE CRON
(new CronManager())->updateLastRun($file_name);
