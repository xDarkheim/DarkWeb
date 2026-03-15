<?php

/**
 * DarkWeb
 *
 * @package     DarkWeb
 * @author      Dmytro Hovenko <dmytro.hovenko@gmail.com>
 * @copyright   2024-2026 Dmytro Hovenko (Darkheim)
 * @license     MIT
 * @link        https://darkheim.net
 *
 * Global helper functions — utilities, cache, config, profiles, formatting.
 */

use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Domain\Validator;

function check_value($value) {
	if((@count((array)$value)>0 and !@empty($value) and @isset($value)) || $value=='0') {
		return true;
	}
}

function redirect($type = 1, $location = null, $delay = 0): void
{
	if(!check_value($location)) {
		$to = __BASE_URL__;
	} else {
		$to = __BASE_URL__ . $location;
		
		if($location == 'login') {
			$_SESSION['login_last_location'] = $_REQUEST['page'].'/';
			if(isset($_REQUEST['subpage'])) {
				$_SESSION['login_last_location'] .= $_REQUEST['subpage'].'/';
			}
		}
	}

	switch($type) {
		default:
			header('Location: '.$to);
			die();
		break;
        case 2:
			echo '<meta http-equiv="REFRESH" content="'.$delay.';url='.$to.'">';
		break;
		case 3:
			header('Location: '.$location);
			die();
		break;
	}
}

function isLoggedIn() {
	$session = new \Darkheim\Application\Auth\SessionManager();
	if(!$session->isAuthenticated()) return;

	$loginConfigs = loadConfigurations('login');
	if(is_array($loginConfigs) && $loginConfigs['enable_session_timeout']
        && $session->hasTimedOut((int)$loginConfigs['session_timeout'])
    ) {
        logOutUser();
        return;
    }

	$session->refreshTimeout();
	return true;
}

function logOutUser(): void
{
	new \Darkheim\Application\Auth\SessionManager()->clearSession();
}

function message($type='info', $message="", $title=""): void
{
	// Map type to toast type
    $toastType = match ($type) {
        'error' => 'error',
        'success' => 'success',
        'warning' => 'warning',
        default => 'info',
    };

	// Strip any HTML tags so they display cleanly in toasts
	$plainMsg = '';
	if(check_value($title)) {
		$plainMsg = strip_tags($title) . ': ' . strip_tags($message);
	} else {
		$plainMsg = strip_tags($message);
	}
	$plainMsg = htmlspecialchars($plainMsg, ENT_QUOTES);

	// Output invisible trigger — toast.js picks this up on DOMContentLoaded
	echo '<span class="dh-toast-trigger" data-type="' . $toastType . '" data-message="' . $plainMsg . '" style="display:none;"></span>';
}

/**
 * Inline message — renders a visible styled block (no toast).
 * Use for passive empty-state notices inside panels/cards
 * where the user needs to see the message in context (not as a popup).
 */
function inline_message($type='info', $message="", $title=""): void
{
	$colors = [
		'error'   => ['bg'=>'rgba(40,10,10,.6)',  'border'=>'#7a2d2d', 'left'=>'#ef5350', 'text'=>'#e89090'],
		'success' => ['bg'=>'rgba(10,30,10,.6)',  'border'=>'#2d7a2d', 'left'=>'#4caf50', 'text'=>'#a0e8a0'],
		'warning' => ['bg'=>'rgba(35,24,0,.6)',   'border'=>'#7a5a00', 'left'=>'#ffa726', 'text'=>'#e8c878'],
		'info'    => ['bg'=>'rgba(10,20,38,.6)',  'border'=>'#2d4a7a', 'left'=>'#42a5f5', 'text'=>'#90b8e8'],
	];
	$c = $colors[$type] ?? $colors['info'];
	$style = 'display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-radius:6px;'
		. 'background:'.$c['bg'].';border:1px solid '.$c['border'].';border-left:3px solid '.$c['left'].';'
		. 'color:'.$c['text'].';font-size:13px;line-height:1.5;margin:6px 0;';
	echo '<div style="'.$style.'">';
	if(check_value($title)) {
		echo '<strong>' . htmlspecialchars(strip_tags($title), ENT_QUOTES) . ':</strong>&nbsp;';
	}
	echo htmlspecialchars(strip_tags($message), ENT_QUOTES);
	echo '</div>';
}

function lang($phrase, $return=true) {
	global $lang;
	if(!array_key_exists($phrase, $lang)) {
		$result = 'ERROR';
	} else {
		$result = $lang[$phrase];
	}
	
	if(config('language_debug',true)) {
		if($return) {
			return '<span title="'.$phrase.'">'.$result.'</span>';
		}

        echo '<span title="'.$phrase.'">'.$result.'</span>';
    } else {
		if($return) {
			return $result;
		}

        echo $result;
    }
}

function langf($phrase, $args=array(), $print=false) {
	global $lang;
	$result = @vsprintf($lang[$phrase], $args);
	if(!$result) $result = 'ERROR';
	
	if(config('language_debug',true)) {
		if($print) {
			echo '<span title="'.$phrase.'">'.$result.'</span>';
		} else {
			return '<span title="'.$phrase.'">'.$result.'</span>';
		}
	} elseif($print) {
        echo $result;
    } else {
        return $result;
    }
}

function debug($value): void
{
	echo '<pre>';
		print_r($value);
	echo '</pre>';
}

function canAccessAdminCP($username) {
	if(!check_value($username)) return;
	if(array_key_exists($username, config('admins',true))) return true;
	return false;
}

function BuildCacheData($data_array): ?string
{
	$result = null;
	if(is_array($data_array)) {
		foreach($data_array as $row) {
			$count = count($row);
			$i = 1;
			foreach($row as $data) {
				$result .= $data;
				if($i < $count) {
					$result .= '¦';
				}
				$i++;
			}
			$result .= "\n";
		}
		return $result;
	}

    return null;
}

function UpdateCache($file_name, $data) {
	$file = __PATH_CACHE__.$file_name;
	if(!file_exists($file)) return;
	if(!is_writable($file)) return;
	
	$fp = fopen($file, 'wb');
	fwrite($fp, time()."\n");
	fwrite($fp, $data);
	fclose($fp);
	return true;
}

function LoadCacheData($file_name): ?array
{
    return new \Darkheim\Infrastructure\Cache\CacheRepository(
        __PATH_CACHE__
    )->loadLegacyText($file_name);
}

function sec_to_hms($input_seconds=0): array
{
	$result = sec_to_dhms($input_seconds);
	if(!is_array($result)) return array(0,0,0);
	return array((($result[0]*24)+$result[1]), $result[2], $result[3]);
}

function sec_to_dhms($input_seconds=0): array
{
	if($input_seconds < 1) return array(0,0,0,0);
	$days_module = $input_seconds % 86400;
	$days = ($input_seconds-$days_module)/86400;
	$hours_module = $days_module % 3600;
	$hours = ($days_module-$hours_module)/3600;
	$minutes_module = $hours_module % 60;
	$minutes = ($hours_module-$minutes_module)/60;
	$seconds = $minutes_module;
	return array($days,$hours,$minutes,$seconds);
}

function updateCronLastRun($file) {
	$database = Connection::Database('MuOnline');
	$update = $database->query("UPDATE ".Cron." SET cron_last_run = ? WHERE cron_file_run = ?", array(time(), $file));
	if(!$update) return;
	return true;
}

function returnGuildLogo($binaryData="", $size=40): string
{
	$imgSize = Validator::UnsignedNumber($size) ? $size : 40;
	return '<img src="'.__PATH_API__.'guildmark.php?data='.$binaryData.'&size='.urlencode($size).'" width="'.$imgSize.'" height="'.$imgSize.'">';
}

function getGensRank($contributionPoints) {
	global $custom;
	foreach($custom['gens_ranks'] as $points => $title) {
		if($contributionPoints >= $points) return $title;
	}
	return $title;
}

function getGensLeadershipRank($rankPosition) {
	global $custom;
	foreach($custom['gens_ranks_leadership'] as $title => $range) {
		if($rankPosition >= $range[0] && $rankPosition <= $range[1]) return $title;
	}
}

function cmsConfigs(): array
{
    return new \Darkheim\Infrastructure\Config\ConfigRepository(
        __PATH_CONFIGS__
    )->loadCmsOrFail();
}

function config($config_name, $return = false) {
	$config = cmsConfigs();
	if(!array_key_exists($config_name, $config)) return;
	if($return) {
		return $config[$config_name];
	}

    echo $config[$config_name];
}

function convertXML($object) {
	return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
}

function loadModuleConfigs($module): void
{
	global $mconfig;
	if(!moduleConfigExists($module)) return;
	$mconfig = [];
	$reader = new \Darkheim\Infrastructure\Config\XmlConfigReader();
	$result = $reader->readFile(__PATH_MODULE_CONFIGS__ . $module . '.xml');
	if(is_array($result)) $mconfig = $result;
}

function moduleConfigExists($module) {
	if(file_exists(__PATH_MODULE_CONFIGS__.$module.'.xml')) {
		return true;
	}
}

function globalConfigExists($config_file) {
	if(file_exists(__PATH_CONFIGS__.$config_file.'.xml')) {
		return true;
	}
}

function mconfig($configuration) {
	global $mconfig;
	if(is_array($mconfig) && array_key_exists($configuration, $mconfig)) {
		return $mconfig[$configuration];
	}

    return null;
}

function gconfig($config_file,$return=true) {
	global $gconfig;
	if(!globalConfigExists($config_file)) return;
	$reader = new \Darkheim\Infrastructure\Config\XmlConfigReader();
	$result = $reader->readFile(__PATH_CONFIGS__ . $config_file . '.xml');
	if(!is_array($result)) return;
	if($return) return $result;
	$gconfig = $result;
}

function loadConfigurations($file) {
	if(!check_value($file)) return;
	if(!moduleConfigExists($file)) return;

    return new \Darkheim\Infrastructure\Config\XmlConfigReader()->readFile(__PATH_MODULE_CONFIGS__ . $file . '.xml');
}

function loadConfig($name="cms") {
	if(!check_value($name)) return;

    return new \Darkheim\Infrastructure\Config\ConfigRepository(
        __PATH_CONFIGS__
    )->load($name);
}

function getPlayerClassAvatar($code=0, $htmlImageTag=true, $tooltip=true, $cssClass=null): string {
	global $custom;
	$imageFileName = array_key_exists($code, $custom['character_class']) ? $custom['character_class'][$code][2] : 'avatar.jpg';
	$imageFullPath = __PATH_TEMPLATE_IMG__ . config('character_avatars_dir', true) . '/' . $imageFileName;
	$className = array_key_exists($code, $custom['character_class']) ? $custom['character_class'][$code][0] : '';
	if(!$htmlImageTag) return $imageFullPath;
	$result = '<img';
	if(check_value($cssClass)) $result .= ' class="'.$cssClass.'"';
	if($tooltip) $result .= ' data-toggle="tooltip" data-placement="top" title="'.$className.'" alt="'.$className.'"';
	$result .= ' src="'.$imageFullPath.'" />';
	return $result;
}

function playerProfile($playerName, $returnLinkOnly=false) {
	if(!config('player_profiles',true)) return $playerName;
	
	$profileConfig = loadConfigurations('profiles');
	if(is_array($profileConfig) && array_key_exists('encode', $profileConfig) && $profileConfig['encode'] == 1) {
		if($returnLinkOnly) {
			return __BASE_URL__.'profile/player/req/'.base64url_encode($playerName);
		}
		return '<a href="'.__BASE_URL__.'profile/player/req/'.base64url_encode($playerName).'/">'.$playerName.'</a>';
	}
	if($returnLinkOnly) {
		return __BASE_URL__.'profile/player/req/'.urlencode($playerName);
	}
	return '<a href="'.__BASE_URL__.'profile/player/req/'.urlencode($playerName).'/">'.$playerName.'</a>';
}

function guildProfile($guildName, $returnLinkOnly=false) {
	if(!config('guild_profiles',true)) return $guildName;
	
	$profileConfig = loadConfigurations('profiles');
	if(is_array($profileConfig) && array_key_exists('encode', $profileConfig) && $profileConfig['encode'] == 1) {
		if($returnLinkOnly) {
			return __BASE_URL__.'profile/guild/req/'.base64url_encode($guildName);
		}
		return '<a href="'.__BASE_URL__.'profile/guild/req/'.base64url_encode($guildName).'/">'.$guildName.'</a>';
	}
	if($returnLinkOnly) {
		return __BASE_URL__.'profile/guild/req/'.urlencode($guildName);
	}
	return '<a href="'.__BASE_URL__.'profile/guild/req/'.urlencode($guildName).'/">'.$guildName.'</a>';
}

function encodeCache($data, $pretty=false): false|string
{
	if($pretty) return json_encode(
        $data,
        JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
    );
	return json_encode($data, JSON_THROW_ON_ERROR);
}

function updateCacheFile($fileName, $data): bool
{
    return new \Darkheim\Infrastructure\Cache\CacheRepository(
        __PATH_CACHE__
    )->save($fileName, $data);
}

function loadCache($fileName): ?array
{
    return new \Darkheim\Infrastructure\Cache\CacheRepository(
        __PATH_CACHE__
    )->load($fileName);
}

function checkBlockedIp() {
	if(access == 'cron') return;
	if(!isset($_SERVER['REMOTE_ADDR'])) return true;
	if(!Validator::Ip($_SERVER['REMOTE_ADDR'])) return true;
	$blockedIpCache = loadCache('blocked_ip.cache');
	if(!is_array($blockedIpCache)) return;
	if(in_array($_SERVER['REMOTE_ADDR'], $blockedIpCache, true)) return true;
}

function getCronList() {
	$db = Connection::Database('MuOnline');
	$result = $db->query_fetch("SELECT * FROM ".Cron." ORDER BY cron_id");
	if(!is_array($result)) return;
	return $result;
}

function getRankingMenuLinks() {
	global $rankingMenuLinks;
	if(!is_array($rankingMenuLinks)) return;
	return $rankingMenuLinks;
}

function loadJsonFile($filePath) {
	if(!file_exists($filePath)) return;
	if(!is_readable($filePath)) return;
	$jsonData = file_get_contents($filePath);
	if(!$jsonData) return;
	$result = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
	if(!is_array($result)) return;	
	return $result;
}

function getCountryCodeFromIp($ip) {
	$api = 'https://ip-api.com/json/'.$ip.'?fields=status,countryCode';
	$handle = curl_init();
	curl_setopt($handle, CURLOPT_URL, $api);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	$json = curl_exec($handle);
	curl_close($handle);
	if(!check_value($json)) return;
	$result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	if(!is_array($result)) return;
	if($result['status'] == 'fail') return;
	if(!check_value($result['countryCode'])) return;
	return $result['countryCode'];
}

function getCountryFlag($countryCode='default'): string
{
	if(!check_value($countryCode)) $countryCode = 'default';
	return __PATH_COUNTRY_FLAGS__ . strtolower($countryCode) . '.gif';
}

function returnMapName($id=0) {
	global $custom;
	if(!is_array($custom['map_list'])) return 'Lorencia Bar';
	if(!array_key_exists($id, $custom['map_list'])) {
		if(config('error_reporting',true)) return 'Map Number ('.$id.')';
		return 'Lorencia Bar';
	}
	return $custom['map_list'][$id];
}

function returnPkLevel($id) {
	global $custom;
	if(!is_array($custom['pk_level'])) return;
	if(!array_key_exists($id, $custom['pk_level'])) return;
	return $custom['pk_level'][$id];
}

function getDirectoryListFromPath($path) {
	if(!file_exists($path)) return;
	$files = scandir($path);
	foreach($files as $row) {
		if(in_array($row, array('.','..'))) continue;
		if(!is_dir($path.$row)) continue;
		$result[] = $row;
	}
	if(!is_array($result)) return;
	return $result;
}

function getInstalledLanguagesList() {
	$languageDir = getDirectoryListFromPath(__PATH_LANGUAGES__);
	if(!is_array($languageDir)) return;
	foreach($languageDir as $language) {
		if(!file_exists(__PATH_LANGUAGES__.$language.'/language.php')) continue;
		$result[] = $language;
	}
	if(!is_array($result)) return;
	return $result;
}

// https://www.php.net/manual/en/function.filesize.php#106569
function readableFileSize($bytes, $decimals = 2): string
{
	$sz = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / (1024 ** $factor)) . @$sz[$factor];
}

function getPlayerClass($class=0) {
	global $custom;
	if(!array_key_exists($class, $custom['character_class'])) return 'Unknown';
	return $custom['character_class'][$class][0];
}

function custom($index) {
	global $custom;
	if(!is_array($custom)) return;
	if(!array_key_exists($index, $custom)) return;
	return $custom[$index];
}

//https://base64.guru/developers/php/examples/base64url
function base64url_encode($data): false|string
{
	$b64 = base64_encode($data . '!we');
    $url = strtr($b64, '+/', '-_');
	return rtrim($url, '=');
}

function base64url_decode($data, $strict=false) {
	$b64 = strtr($data, '-_', '+/');
	$decoded = base64_decode($b64, $strict);
	$end = substr($decoded, -3);
	if($end !== '!we') return;
	return substr($decoded, 0, -3);
}
