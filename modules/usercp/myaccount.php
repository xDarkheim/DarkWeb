<?php

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Infrastructure\Database\Connection;

if(!isLoggedIn()) redirect(1,'login');

if(!mconfig('active')) throw new Exception(lang('error_12'));

$common   = new Common();
$accountInfo = $common->accountInformation($_SESSION['userid']);
if(!is_array($accountInfo)) throw new Exception(lang('error_12'));

$isOnlineAccount = $common->accountOnline($_SESSION['username']);
$isBlocked       = ($accountInfo[_CLMN_BLOCCODE_] == 1);

$Character        = new Character();
$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
$onlineCharacters  = loadCache('online_characters.cache') ?: [];

// ── Credits collect ───────────────────────────────────────────────
$creditsRows = [];
try {
	$creditSystem    = new CreditSystem();
	$creditCofigList = $creditSystem->showConfigs();
	if(is_array($creditCofigList)) {
		foreach($creditCofigList as $myCredits) {
			if(!$myCredits['config_display']) continue;
			$creditSystem->setConfigId($myCredits['config_id']);
			switch($myCredits['config_user_col_id']) {
				case 'userid':   $creditSystem->setIdentifier($accountInfo[_CLMN_MEMBID_]); break;
				case 'username': $creditSystem->setIdentifier($accountInfo[_CLMN_USERNM_]); break;
				case 'email':    $creditSystem->setIdentifier($accountInfo[_CLMN_EMAIL_]);   break;
				default: continue 2;
			}
			$creditsRows[] = ['title' => $myCredits['config_title'], 'amount' => $creditSystem->getCredits()];
		}
	}
} catch(Exception $ex) {}

// ── 1. PROFILE BANNER ─────────────────────────────────────────────
$statusPill   = $isBlocked
	? '<span class="ma-status-pill ma-pill-banned">'.lang('myaccount_txt_8').'</span>'
	: '<span class="ma-status-pill ma-pill-active">'.lang('myaccount_txt_7').'</span>';
$onlinePill   = $isOnlineAccount
	? '<span class="ma-online-pill ma-pill-online">'.lang('myaccount_txt_9').'</span>'
	: '<span class="ma-online-pill ma-pill-offline">'.lang('myaccount_txt_10').'</span>';

echo '<div class="ma-banner">';
	echo '<div class="ma-banner-inner">';
		// Avatar placeholder — large user icon
		echo '<div class="ma-avatar"><i class="bi bi-person-fill"></i></div>';
		echo '<div class="ma-banner-info">';
			echo '<div class="ma-username">'.htmlspecialchars($accountInfo[_CLMN_USERNM_]).'</div>';
			echo '<div class="ma-pills">'.$statusPill.$onlinePill.'</div>';
		echo '</div>';
		// Quick-action buttons top-right
		echo '<div class="ma-banner-actions">';
			echo '<a href="'.__BASE_URL__.'usercp/myemail/" class="ma-action-btn"><i class="bi bi-envelope-fill"></i> '.lang('myaccount_txt_3').'</a>';
			echo '<a href="'.__BASE_URL__.'usercp/mypassword/" class="ma-action-btn"><i class="bi bi-key-fill"></i> '.lang('myaccount_txt_4').'</a>';
		echo '</div>';
	echo '</div>';
echo '</div>';

// ── 2. INFO STRIP (email + credits) ──────────────────────────────
echo '<div class="ma-info-strip">';

	// Email cell
	echo '<div class="ma-info-cell">';
		echo '<span class="ma-info-label"><i class="bi bi-envelope"></i> '.lang('myaccount_txt_3').'</span>';
		echo '<span class="ma-info-value">'.htmlspecialchars($accountInfo[_CLMN_EMAIL_]).'</span>';
	echo '</div>';

	// Password cell
	echo '<div class="ma-info-cell">';
		echo '<span class="ma-info-label"><i class="bi bi-shield-lock"></i> '.lang('myaccount_txt_4').'</span>';
		echo '<span class="ma-info-value ma-dots">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span>';
	echo '</div>';

	// Credits cells
	foreach($creditsRows as $cr) {
		echo '<div class="ma-info-cell">';
			echo '<span class="ma-info-label"><i class="bi bi-coin"></i> '.htmlspecialchars($cr['title']).'</span>';
			echo '<span class="ma-info-value ma-credits">'.number_format($cr['amount']).'</span>';
		echo '</div>';
	}

echo '</div>';

// ── 3. CHARACTERS ─────────────────────────────────────────────────
echo '<div class="ma-section-title"><i class="bi bi-person-badge-fill"></i>'.lang('myaccount_txt_15').'</div>';

if(is_array($AccountCharacters)) {
	echo '<div class="ma-chars-grid">';
	foreach($AccountCharacters as $characterName) {
		$cd = $Character->CharacterData($characterName);
		if(!is_array($cd)) continue;

		// Add the master level to the total level
		if(defined('_TBL_MASTERLVL_')) {
			if(_TBL_MASTERLVL_ != _TBL_CHR_) {
				$mlData = $Character->getMasterLevelInfo($characterName);
				if(is_array($mlData)) $cd[_CLMN_CHR_LVL_] += $mlData[_CLMN_ML_LVL_];
			} else {
				$cd[_CLMN_CHR_LVL_] += $cd[_CLMN_ML_LVL_];
			}
		}

		$avatar   = getPlayerClassAvatar($cd[_CLMN_CHR_CLASS_], false);
		$charOnline = in_array($characterName, $onlineCharacters, true);
		$charClass  = getPlayerClass($cd[_CLMN_CHR_CLASS_]);

		echo '<div class="ma-char-card'.($charOnline ? ' ma-char-online' : '').'">';

			// Online dot
			echo '<span class="ma-char-dot'.($charOnline ? ' dot-online' : ' dot-offline').'"></span>';

			// Avatar
			echo '<a href="'.playerProfile($characterName, true).'" target="_blank" class="ma-char-avatar-wrap">';
				echo '<img src="'.$avatar.'" alt="'.$characterName.'" class="ma-char-avatar" />';
			echo '</a>';

			// Name
			echo '<div class="ma-char-name">'.playerProfile($characterName).'</div>';

			// Class
			if($charClass) echo '<div class="ma-char-class">'.$charClass.'</div>';

			// Level badge
			echo '<div class="ma-char-lvl">LVL <strong>'.$cd[_CLMN_CHR_LVL_].'</strong></div>';

			// Location
			echo '<div class="ma-char-loc"><i class="bi bi-geo-alt-fill"></i>'.returnMapName($cd[_CLMN_CHR_MAP_]).'</div>';

		echo '</div>';
	}
	echo '</div>';
} else {
	inline_message('info', lang('error_46', true));
}

// ── 4. CONNECTION HISTORY ─────────────────────────────────────────
$hasConnectionHistory = defined('_TBL_CH_')
	&& defined('_CLMN_CH_ACCID_')
	&& defined('_CLMN_CH_ID_')
	&& defined('_CLMN_CH_DATE_')
	&& defined('_CLMN_CH_SRVNM_')
	&& defined('_CLMN_CH_IP_')
	&& defined('_CLMN_CH_STATE_');

if($hasConnectionHistory) {
	$tblCh = constant('_TBL_CH_');
	$clmnChAccid = constant('_CLMN_CH_ACCID_');
	$clmnChId = constant('_CLMN_CH_ID_');
	$clmnChDate = constant('_CLMN_CH_DATE_');
	$clmnChServer = constant('_CLMN_CH_SRVNM_');
	$clmnChIp = constant('_CLMN_CH_IP_');
	$clmnChState = constant('_CLMN_CH_STATE_');

	echo '<div class="ma-section-title" style="margin-top:24px;"><i class="bi bi-clock-history"></i>'.lang('myaccount_txt_16').'</div>';
	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-body" style="padding:0;">';
			$me = Connection::Database('MuOnline');
			$connectionHistory = $me->query_fetch(
				"SELECT TOP 10 * FROM ".$tblCh." WHERE ".$clmnChAccid." = ? ORDER BY ".$clmnChId." DESC",
				array($_SESSION['username'])
			);
			if(is_array($connectionHistory)) {
				echo '<table class="table general-table-ui" style="margin-bottom:0;">';
					echo '<thead><tr>';
						echo '<th>'.lang('myaccount_txt_13').'</th>';
						echo '<th>'.lang('myaccount_txt_17').'</th>';
						echo '<th>'.lang('myaccount_txt_18').'</th>';
						echo '<th>'.lang('myaccount_txt_19').'</th>';
					echo '</tr></thead><tbody>';
					foreach($connectionHistory as $row) {
						echo '<tr>';
							echo '<td>'.$row[$clmnChDate].'</td>';
							echo '<td>'.$row[$clmnChServer].'</td>';
							echo '<td>'.$row[$clmnChIp].'</td>';
							echo '<td>'.$row[$clmnChState].'</td>';
						echo '</tr>';
					}
					echo '</tbody>';
				echo '</table>';
			}
		echo '</div>';
	echo '</div>';
}