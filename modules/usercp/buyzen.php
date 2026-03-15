<?php

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Domain\Validator;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_28',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	$db = Connection::Database('MuOnline');
	$common = new Common();

	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));

	$maxZen        = mconfig('max_zen');
	$exchangeRatio = mconfig('exchange_ratio');
	$incrementRate = mconfig('increment_rate');

	$buyOptions = array();
	for($multiplier = 1; $multiplier <= floor($maxZen / $incrementRate); $multiplier++) {
		$zenAmount    = $multiplier * $incrementRate;
		$creditAmount = ceil($zenAmount / $exchangeRatio);
		$buyOptions[] = $creditAmount;
	}

	if(isset($_POST['submit'], $_POST['character'], $_POST['credits'])) {
		try {
			if($common->accountOnline($_SESSION['username'])) throw new Exception(lang('error_28',true));
			if(!in_array($_POST['credits'], $buyOptions, true)) throw new Exception(lang('error_24',true));

			$char = $_POST['character'];
			$zen  = $_POST['credits'] * $exchangeRatio;

			if(!Validator::UnsignedNumber($_POST['credits'])) throw new Exception(lang('error_25',true));
			if($zen > $maxZen) throw new Exception(lang('error_25',true));
			if(!in_array($char, $AccountCharacters, true)) throw new Exception(lang('error_24',true));

			$characterData = $Character->CharacterData($char);
			if(!is_array($characterData)) throw new Exception(lang('error_25',true));

			$charZen = $characterData[_CLMN_CHR_ZEN_];
			if($charZen + $zen > $maxZen) throw new Exception(lang('error_55',true));

			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId(mconfig('credit_config'));
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':    $creditSystem->setIdentifier($_SESSION['userid']);   break;
				case 'username':  $creditSystem->setIdentifier($_SESSION['username']); break;
				case 'character': $creditSystem->setIdentifier($char);                break;
				default: throw new Exception("Invalid identifier (credit system).");
			}
			$creditSystem->subtractCredits($_POST['credits']);

			$db->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_ZEN_." = "._CLMN_CHR_ZEN_." + ? WHERE "._CLMN_CHR_NAME_." = ?", array($zen, $characterData[_CLMN_CHR_NAME_]));

			message('success', lang('success_21',true));
			message('info', number_format($zen) . lang('buyzen_txt_2',true) . $char);
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-coin"></i>'.lang('module_titles_txt_28',true).'</div>';
		echo '<div class="ucp-card-body">';
			echo '<form action="" method="post">';
				echo '<div class="ucp-buyzen-grid">';

					echo '<div class="ucp-form-group">';
						echo '<label>'.lang('buyzen_txt_3',true).'</label>';
						echo '<select name="character" class="form-control">';
							foreach($AccountCharacters as $char) {
								echo '<option value="'.$char.'">'.$char.'</option>';
							}
						echo '</select>';
					echo '</div>';

					echo '<div class="ucp-form-group">';
						echo '<label>'.lang('buyzen_txt_4',true).'</label>';
						echo '<select name="credits" class="form-control">';
							foreach($buyOptions as $creditValue) {
								$zenValue = $creditValue * $exchangeRatio;
								if($zenValue > $maxZen) continue;
								echo '<option value="'.$creditValue.'">'.number_format($zenValue).' Zen &mdash; '.$creditValue.' '.lang('buyzen_txt_6',true).'</option>';
							}
						echo '</select>';
					echo '</div>';

					echo '<div class="ucp-form-group ucp-buyzen-submit">';
						echo '<label>&nbsp;</label>';
						echo '<button name="submit" value="submit" class="btn btn-primary" style="width:100%;">'.lang('buyzen_txt_5',true).'</button>';
					echo '</div>';

				echo '</div>'; // ucp-buyzen-grid
			echo '</form>';
		echo '</div>';
	echo '</div>';

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}