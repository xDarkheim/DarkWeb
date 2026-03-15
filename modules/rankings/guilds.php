<?php

use Darkheim\Application\Rankings\RankingsService as Rankings;
use Darkheim\Application\Rankings\RankingRepository;
use Darkheim\Infrastructure\Cache\CacheRepository;

try {
	echo '<div class="page-title"><span>'.lang('module_titles_txt_10',true).'</span></div>';

	$Rankings = new Rankings();
	$Rankings->rankingsMenu();
	loadModuleConfigs('rankings');

	if(!mconfig('rankings_enable_guilds')) throw new Exception(lang('error_44',true));
	if(!mconfig('active')) throw new Exception(lang('error_44',true));

	$rankRepo  = new RankingRepository(new CacheRepository(__PATH_CACHE__));
	$rankCache = $rankRepo->load('rankings_guilds.cache');
	if($rankCache === null) throw new Exception(lang('error_58',true));

	$onlineCharacters = mconfig('show_online_status') ? $rankRepo->loadOnlineCharacters() : [];

	echo '<table class="rankings-table">';
	echo '<tr>';
	if(mconfig('rankings_show_place_number')) echo '<td style="font-weight:bold;"></td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_17',true).'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_28',true).'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_18',true).'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_19',true).'</td>';
	echo '</tr>';

	$i = 1;
	foreach($rankCache->entries as $rdata) {
		$onlineStatus = mconfig('show_online_status') ? (in_array(
            $rdata[1],
            $onlineCharacters,
            true
        ) ? '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>' : '<img src="'.__PATH_OFFLINE_STATUS__.'" class="online-status-indicator"/>') : '';
		$multiplier = mconfig('guild_score_formula') == 1 ? 1 : mconfig('guild_score_multiplier');
		$rankClass = ($i <= 3) ? ' rank-'.$i : '';
		echo '<tr class="rankings-row'.$rankClass.'">';
		if(mconfig('rankings_show_place_number')) echo '<td class="rankings-table-place">'.$i.'</td>';
		echo '<td>'.guildProfile($rdata[0]).'</td>';
		echo '<td>'.returnGuildLogo($rdata[3], 40).'</td>';
		echo '<td>'.playerProfile($rdata[1]).$onlineStatus.'</td>';
		echo '<td>'.number_format(floor($rdata[2]*$multiplier)).'</td>';
		echo '</tr>';
		$i++;
	}
	echo '</table>';

	if(mconfig('rankings_show_date')) {
		echo '<div class="rankings-update-time">'.lang('rankings_txt_20',true).' '.date("m/d/Y - h:i A", $rankCache->timestamp).'</div>';
	}

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}