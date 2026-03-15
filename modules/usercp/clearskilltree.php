<?php

use Darkheim\Application\Character\Character;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_19',true).'</span></div>';

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
			$Character->CharacterClearSkillTree();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-lightning-fill"></i>'.lang('module_titles_txt_19',true).'</div>';
		echo '<div class="ucp-card-body" style="padding:0;">';
			echo '<table class="table general-table-ui" style="margin-bottom:0;">';
				echo '<thead><tr>';
					echo '<th></th>';
					echo '<th>'.lang('clearst_txt_1',true).'</th>';
					echo '<th>'.lang('clearst_txt_2',true).'</th>';
					echo '<th>'.lang('clearst_txt_5',true).'</th>';
					echo '<th>'.lang('clearst_txt_3',true).'</th>';
					echo '<th></th>';
				echo '</tr></thead>';
				echo '<tbody>';
				foreach($AccountCharacters as $thisCharacter) {
					$characterData    = $Character->CharacterData($thisCharacter);
					$characterIMG     = $Character->GenerateCharacterClassAvatar($characterData[_CLMN_CHR_CLASS_]);
					$characterMLVLData = $Character->getMasterLevelInfo($thisCharacter);
					echo '<form action="" method="post">';
						echo '<input type="hidden" name="character" value="'.$characterData[_CLMN_CHR_NAME_].'"/>';
						echo '<tr>';
							echo '<td>'.$characterIMG.'</td>';
							echo '<td>'.$characterData[_CLMN_CHR_NAME_].'</td>';
							echo '<td>'.number_format($characterMLVLData[_CLMN_ML_LVL_]).'</td>';
							echo '<td>'.number_format($characterMLVLData[_CLMN_ML_POINT_]).'</td>';
							echo '<td>'.number_format($characterData[_CLMN_CHR_ZEN_]).'</td>';
							echo '<td><button name="submit" value="submit" class="btn btn-primary btn-sm">'.lang('clearst_txt_4',true).'</button></td>';
						echo '</tr>';
					echo '</form>';
				}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	echo '</div>';

	$hasReqs = mconfig('required_level') > 0 || mconfig('required_master_level') > 0 || mconfig('zen_cost') > 0;
	if($hasReqs) {
		echo '<div class="module-requirements text-center">';
			if(mconfig('required_level') > 0)        echo '<p>'.langf('clearst_txt_8', array(number_format(mconfig('required_level')))).'</p>';
			if(mconfig('required_master_level') > 0) echo '<p>'.langf('clearst_txt_6', array(number_format(mconfig('required_master_level')))).'</p>';
			if(mconfig('zen_cost') > 0)              echo '<p>'.langf('clearst_txt_7', array(number_format(mconfig('zen_cost')))).'</p>';
		echo '</div>';
	}

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}