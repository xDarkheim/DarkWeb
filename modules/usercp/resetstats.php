<?php

use Darkheim\Application\Character\Character;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_18',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));

	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->_character = $_POST['character'];
			$Character->CharacterResetStats();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-bar-chart-fill"></i>'.lang('module_titles_txt_18',true).'</div>';
		echo '<div class="ucp-card-body" style="padding:0;">';
			echo '<table class="table general-table-ui" style="margin-bottom:0;">';
				echo '<thead><tr>';
					echo '<th></th>';
					echo '<th>'.lang('resetstats_txt_1',true).'</th>';
					echo '<th>'.lang('resetstats_txt_2',true).'</th>';
					echo '<th>'.lang('resetstats_txt_3',true).'</th>';
					echo '<th>'.lang('resetstats_txt_4',true).'</th>';
					echo '<th>'.lang('resetstats_txt_5',true).'</th>';
					echo '<th>'.lang('resetstats_txt_6',true).'</th>';
					echo '<th>'.lang('resetstats_txt_7',true).'</th>';
					echo '<th></th>';
				echo '</tr></thead>';
				echo '<tbody>';
				foreach($AccountCharacters as $thisCharacter) {
					$characterData = $Character->CharacterData($thisCharacter);
					$characterIMG  = $Character->GenerateCharacterClassAvatar($characterData[_CLMN_CHR_CLASS_]);
					echo '<form action="" method="post">';
						echo '<input type="hidden" name="character" value="'.$characterData[_CLMN_CHR_NAME_].'"/>';
						echo '<tr>';
							echo '<td>'.$characterIMG.'</td>';
							echo '<td>'.$characterData[_CLMN_CHR_NAME_].'</td>';
							echo '<td>'.$characterData[_CLMN_CHR_LVL_].'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_STAT_STR_]).'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_STAT_AGI_]).'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_STAT_VIT_]).'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_STAT_ENE_]).'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_STAT_CMD_]).'</td>';
							echo '<td><button name="submit" value="submit" class="btn btn-primary btn-sm">'.lang('resetstats_txt_8',true).'</button></td>';
						echo '</tr>';
					echo '</form>';
				}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	echo '</div>';

	if(mconfig('zen_cost') > 0) {
		echo '<div class="module-requirements text-center">';
			echo '<p>'.langf('resetstats_txt_9', array(number_format(mconfig('zen_cost')))).'</p>';
		echo '</div>';
	}

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}