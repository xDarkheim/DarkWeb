<?php

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;

if(!isLoggedIn()) redirect(1,'login');

$cfg = loadConfig('usercp');
if(!is_array($cfg)) throw new Exception('Could not load usercp, please contact support.');

// ── Gather player data for dashboard ─────────────────────────────
$common      = new Common();
$accountInfo = $common->accountInformation($_SESSION['userid']);
$isOnline    = $accountInfo ? $common->accountOnline($_SESSION['username']) : false;
$isBlocked   = $accountInfo && $accountInfo[_CLMN_BLOCCODE_] == 1;

// Count characters
$Character         = new Character();
$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
$charCount         = is_array($AccountCharacters) ? count($AccountCharacters) : 0;
$onlineCharacters  = loadCache('online_characters.cache') ?: [];
$charsOnline       = 0;
if(is_array($AccountCharacters)) {
	foreach($AccountCharacters as $cn) {
		if(in_array($cn, $onlineCharacters, true)) $charsOnline++;
	}
}

// First character for a quick preview
$firstCharData = null;
$firstCharAvatar = null;
if(is_array($AccountCharacters) && !empty($AccountCharacters)) {
	$firstCharData   = $Character->CharacterData($AccountCharacters[0]);
	$firstCharAvatar = $firstCharData ? getPlayerClassAvatar($firstCharData[_CLMN_CHR_CLASS_], false) : null;
}

// Credits (first visible config)
$creditLabel  = '';
$creditAmount = '';
try {
	$creditSystem    = new CreditSystem();
	$creditCofigList = $creditSystem->showConfigs();
	if(is_array($creditCofigList)) {
		foreach($creditCofigList as $cr) {
			if(!$cr['config_display']) continue;
			$creditSystem->setConfigId($cr['config_id']);
			switch($cr['config_user_col_id']) {
				case 'userid':   $creditSystem->setIdentifier($accountInfo[_CLMN_MEMBID_]); break;
				case 'username': $creditSystem->setIdentifier($accountInfo[_CLMN_USERNM_]); break;
				case 'email':    $creditSystem->setIdentifier($accountInfo[_CLMN_EMAIL_]);   break;
				default: continue 2;
			}
			$creditLabel  = $cr['config_title'];
			$creditAmount = number_format($creditSystem->getCredits());
			break;
		}
	}
} catch(Exception $ex) {}

// ── Map icon filename → Bootstrap Icon ───────────────────────────
function ucpBiIcon($iconFile): string
{
	$f = strtolower($iconFile);
	if(str_contains($f, 'account')) return 'bi-person-circle';
	if(str_contains($f, 'password')) return 'bi-key-fill';
	if(str_contains($f, 'email')) return 'bi-envelope-fill';
	if(str_contains($f, 'addstat')) return 'bi-bar-chart-fill';
	if(str_contains($f, 'fixstat') || str_contains($f, 'resetstat')) return 'bi-arrow-counterclockwise';
	if(str_contains($f, 'reset')) return 'bi-person-dash-fill';
	if(str_contains($f, 'vote')) return 'bi-star-fill';
	if(str_contains($f, 'zen')) return 'bi-coin';
	if(str_contains($f, 'donat')) return 'bi-gem';
	if(str_contains($f, 'clearpk') || str_contains($f, 'pk')) return 'bi-shield-x';
	if(str_contains($f, 'skill') || str_contains($f, 'clearst')) return 'bi-lightning-fill';
	if(str_contains($f, 'unstick')) return 'bi-geo-alt-fill';
	if(str_contains($f, 'buy')) return 'bi-bag-fill';
	return 'bi-grid';
}

// ── Category colouring by icon ────────────────────────────────────
function ucpAccentClass($iconFile): string
{
	$f = strtolower($iconFile);
	if(str_contains($f, 'vote') || str_contains($f, 'donat') || str_contains(
            $f,
            'zen'
        )
        || str_contains($f, 'buy')
    ) return 'ucp-tile-gold';
	if(str_contains($f, 'reset') || str_contains($f, 'unstick')) return 'ucp-tile-red';
	if(str_contains($f, 'stat') || str_contains($f, 'skill')) return 'ucp-tile-blue';
	if(str_contains($f, 'pk')) return 'ucp-tile-purple';
	return 'ucp-tile-default';
}

// ═══════════════════════════════════════════════════════════════════
// 1. WELCOME BANNER
// ═══════════════════════════════════════════════════════════════════
$statusClass = $isBlocked ? 'ma-pill-banned' : 'ma-pill-active';
$statusText  = $isBlocked ? lang('myaccount_txt_8') : lang('myaccount_txt_7');
$onlineClass = $isOnline  ? 'ma-pill-online' : 'ma-pill-offline';
$onlineText  = $isOnline  ? lang('myaccount_txt_9') : lang('myaccount_txt_10');

echo '<div class="ucp-dashboard-banner">';

	// Left: avatar + name
	echo '<div class="ucp-db-left">';
		echo '<div class="ucp-db-avatar">';
		if($firstCharAvatar) {
			echo '<img src="'.$firstCharAvatar.'" alt="" />';
		} else {
			echo '<i class="bi bi-person-fill"></i>';
		}
		echo '</div>';
		echo '<div class="ucp-db-identity">';
			echo '<div class="ucp-db-username">'.htmlspecialchars($accountInfo[_CLMN_USERNM_]).'</div>';
			echo '<div class="ucp-db-subtitle">'.lang('usercp_menu_title', true).'</div>';
			echo '<div class="ucp-db-pills">';
				echo '<span class="ma-status-pill '.$statusClass.'">'.$statusText.'</span>';
				echo '<span class="ma-online-pill '.$onlineClass.'">'.$onlineText.'</span>';
			echo '</div>';
		echo '</div>';
	echo '</div>';

	// Right: stat counters
	echo '<div class="ucp-db-stats">';

		echo '<div class="ucp-db-stat">';
			echo '<span class="ucp-db-stat-val">'.$charCount.'</span>';
			echo '<span class="ucp-db-stat-lbl"><i class="bi bi-person-badge-fill"></i> Characters</span>';
		echo '</div>';

		if($charsOnline > 0) {
			echo '<div class="ucp-db-stat ucp-db-stat-online">';
				echo '<span class="ucp-db-stat-val">'.$charsOnline.'</span>';
				echo '<span class="ucp-db-stat-lbl"><i class="bi bi-circle-fill"></i> Online</span>';
			echo '</div>';
		}

		if($creditAmount !== '') {
			echo '<div class="ucp-db-stat ucp-db-stat-credits">';
				echo '<span class="ucp-db-stat-val">'.$creditAmount.'</span>';
				echo '<span class="ucp-db-stat-lbl"><i class="bi bi-coin"></i> '.htmlspecialchars($creditLabel).'</span>';
			echo '</div>';
		}

	echo '</div>';

echo '</div>'; // ucp-dashboard-banner

// ═══════════════════════════════════════════════════════════════════
// 2. MENU TILES
// ═══════════════════════════════════════════════════════════════════
echo '<div class="ucp-tiles-grid">';

foreach($cfg as $element) {
	if(!is_array($element)) continue;
	if(!$element['active']) continue;
	if($element['visibility'] === 'guest' && isLoggedIn()) continue;
	if($element['visibility'] === 'user'  && !isLoggedIn()) continue;

	// All items shown on the dashboard including myaccount

	$link       = $element['type'] == 'internal' ? __BASE_URL__ . $element['link'] : $element['link'];
	$title      = check_value(lang($element['phrase'], true)) ? lang($element['phrase']) : 'ERROR';
	$icon       = check_value($element['icon']) ? __PATH_TEMPLATE_IMG__ . 'icons/' . $element['icon'] : __PATH_TEMPLATE_IMG__ . 'icons/usercp_default.png';
	$target     = $element['newtab'] ? ' target="_blank"' : '';
	$biIcon     = ucpBiIcon($element['icon'] ?? '');
	$accentCls  = ucpAccentClass($element['icon'] ?? '');

	echo '<a href="'.$link.'"'.$target.' class="ucp-tile '.$accentCls.'">';
		echo '<div class="ucp-tile-icon-wrap">';
			echo '<img src="'.$icon.'" alt="'.$title.'" class="ucp-tile-img" />';
			echo '<i class="bi '.$biIcon.' ucp-tile-bi"></i>';
		echo '</div>';
		echo '<span class="ucp-tile-title">'.$title.'</span>';
		echo '<i class="bi bi-chevron-right ucp-tile-arrow"></i>';
	echo '</a>';
}

echo '</div>';
