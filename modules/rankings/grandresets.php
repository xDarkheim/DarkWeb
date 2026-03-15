<?php

use Darkheim\Application\Rankings\RankingsService as Rankings;
use Darkheim\Application\Rankings\RankingRepository;
use Darkheim\Infrastructure\Cache\CacheRepository;

try {
	echo '<div class="page-title"><span>'.lang('module_titles_txt_10',true).'</span></div>';

	$Rankings = new Rankings();
	$Rankings->rankingsMenu();
	loadModuleConfigs('rankings');

	if(!mconfig('rankings_enable_gr')) throw new Exception(lang('error_44',true));
	if(!mconfig('active')) throw new Exception(lang('error_44',true));

	$rankRepo  = new RankingRepository(new CacheRepository(__PATH_CACHE__));
	$rankCache = $rankRepo->load('rankings_gr.cache');
	if($rankCache === null) throw new Exception(lang('error_58',true));

	$showPlayerCountry = (bool)mconfig('show_country_flags');
	$charactersCountry = $rankRepo->loadCharacterCountries();
	if(empty($charactersCountry)) $showPlayerCountry = false;

	$onlineCharacters = mconfig('show_online_status') ? $rankRepo->loadOnlineCharacters() : [];

	if(mconfig('rankings_class_filter')) $Rankings->rankingsFilterMenu();

	echo '<table class="rankings-table">';
	echo '<tr>';
	if(mconfig('rankings_show_place_number')) echo '<td style="font-weight:bold;"></td>';
	if($showPlayerCountry) echo '<td style="font-weight:bold;">'.lang('rankings_txt_33').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_11').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_10').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_13').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_21').'</td>';
	if(mconfig('show_location')) echo '<td style="font-weight:bold;">'.lang('rankings_txt_34').'</td>';
	echo '</tr>';

	$i = 1;
	foreach($rankCache->entries as $rdata) {
		$characterIMG = getPlayerClassAvatar($rdata[3], true, true, 'rankings-class-image');
		$onlineStatus = mconfig('show_online_status') ? (in_array(
            $rdata[0],
            $onlineCharacters,
            true
        ) ? '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>' : '<img src="'.__PATH_OFFLINE_STATUS__.'" class="online-status-indicator"/>') : '';
		$rankClass = ($i <= 3) ? ' rank-'.$i : '';
		echo '<tr data-class-id="'.$rdata[3].'" class="rankings-row'.$rankClass.'">';
		if(mconfig('rankings_show_place_number')) echo '<td class="rankings-table-place">'.$i.'</td>';
		if($showPlayerCountry) echo '<td><img src="'.getCountryFlag(array_key_exists($rdata[0], $charactersCountry) ? $charactersCountry[$rdata[0]] : 'default').'" /></td>';
		echo '<td>'.$characterIMG.'</td>';
		echo '<td>'.playerProfile($rdata[0]).$onlineStatus.'</td>';
		echo '<td>'.number_format($rdata[2]).'</td>';
		echo '<td>'.number_format($rdata[1]).'</td>';
		if(mconfig('show_location')) echo '<td>'.returnMapName($rdata[4]).'</td>';
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