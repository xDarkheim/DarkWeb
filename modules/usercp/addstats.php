<?php

use Darkheim\Application\Character\Character;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_25').'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));
	if(!is_array($custom['character_cmd'])) throw new Exception(lang('error_59',true));
	$maxStats = mconfig('addstats_max_stats');

	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));

	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->_character = $_POST['character'];
			if(isset($_POST['add_str']) && $_POST['add_str'] > 0) $Character->setStrength($_POST['add_str']);
			if(isset($_POST['add_agi']) && $_POST['add_agi'] > 0) $Character->setAgility($_POST['add_agi']);
			if(isset($_POST['add_vit']) && $_POST['add_vit'] > 0) $Character->setVitality($_POST['add_vit']);
			if(isset($_POST['add_ene']) && $_POST['add_ene'] > 0) $Character->setEnergy($_POST['add_ene']);
			if(isset($_POST['add_com']) && $_POST['add_com'] > 0) $Character->setCommand($_POST['add_com']);
			$Character->CharacterAddStats();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	foreach($AccountCharacters as $thisCharacter) {
		$characterData = $Character->CharacterData($thisCharacter);
		$characterIMG  = $Character->GenerateCharacterClassAvatar($characterData[_CLMN_CHR_CLASS_]);

		echo '<div class="ucp-card" style="margin-bottom:16px;">';
			echo '<div class="ucp-card-header">';
				echo '<i class="bi bi-bar-chart-fill"></i>';
				echo '<span style="color:var(--ucp-text);font-weight:700;margin-left:4px;">'.$characterData[_CLMN_CHR_NAME_].'</span>';
				echo '<span style="margin-left:auto;font-size:11px;color:var(--ucp-text-muted);">'.langf('addstats_txt_2', array(number_format($characterData[_CLMN_CHR_LVLUP_POINT_]))).'</span>';
			echo '</div>';
			echo '<div class="ucp-card-body">';

				echo '<div class="ucp-addstats-row">';
					// Avatar column
					echo '<div class="ucp-addstats-avatar">';
						echo $characterIMG;
					echo '</div>';

					// Form column
					echo '<div class="ucp-addstats-form">';
						echo '<form class="ucp-form ucp-stats-grid" action="" method="post">';
							echo '<input type="hidden" name="character" value="'.$characterData[_CLMN_CHR_NAME_].'"/>';

							// STR
							echo '<div class="ucp-form-group">';
								echo '<label>'.lang('addstats_txt_3',true).' <span class="ucp-stat-current">('.$characterData[_CLMN_CHR_STAT_STR_].')</span></label>';
								echo '<input type="number" class="form-control" min="1" step="1" max="'.$maxStats.'" name="add_str" placeholder="0">';
							echo '</div>';

							// AGI
							echo '<div class="ucp-form-group">';
								echo '<label>'.lang('addstats_txt_4',true).' <span class="ucp-stat-current">('.$characterData[_CLMN_CHR_STAT_AGI_].')</span></label>';
								echo '<input type="number" class="form-control" min="1" step="1" max="'.$maxStats.'" name="add_agi" placeholder="0">';
							echo '</div>';

							// VIT
							echo '<div class="ucp-form-group">';
								echo '<label>'.lang('addstats_txt_5',true).' <span class="ucp-stat-current">('.$characterData[_CLMN_CHR_STAT_VIT_].')</span></label>';
								echo '<input type="number" class="form-control" min="1" step="1" max="'.$maxStats.'" name="add_vit" placeholder="0">';
							echo '</div>';

							// ENE
							echo '<div class="ucp-form-group">';
								echo '<label>'.lang('addstats_txt_6',true).' <span class="ucp-stat-current">('.$characterData[_CLMN_CHR_STAT_ENE_].')</span></label>';
								echo '<input type="number" class="form-control" min="1" step="1" max="'.$maxStats.'" name="add_ene" placeholder="0">';
							echo '</div>';

							// CMD (only for applicable classes)
							if(in_array(
                                $characterData[_CLMN_CHR_CLASS_],
                                $custom['character_cmd'],
                                true
                            )
                            ) {
								echo '<div class="ucp-form-group">';
									echo '<label>'.lang('addstats_txt_7',true).' <span class="ucp-stat-current">('.$characterData[_CLMN_CHR_STAT_CMD_].')</span></label>';
									echo '<input type="number" class="form-control" min="1" step="1" max="'.$maxStats.'" name="add_com" placeholder="0">';
								echo '</div>';
							}

							echo '<div class="ucp-form-submit" style="grid-column:1/-1;">';
								echo '<button name="submit" value="submit" class="btn btn-primary">'.lang('addstats_txt_8',true).'</button>';
							echo '</div>';
						echo '</form>';
					echo '</div>';
				echo '</div>'; // ucp-addstats-row

			echo '</div>';
		echo '</div>';
	}

	// Requirements
	echo '<div class="module-requirements text-center">';
		if(mconfig('required_level') > 0)        echo '<p>'.langf('addstats_txt_11', array(number_format(mconfig('required_level')))).'</p>';
		if(mconfig('required_master_level') > 0) echo '<p>'.langf('addstats_txt_10', array(number_format(mconfig('required_master_level')))).'</p>';
		if(mconfig('zen_cost') > 0)              echo '<p>'.langf('addstats_txt_9',  array(number_format(mconfig('zen_cost')))).'</p>';
		echo '<p>'.langf('addstats_txt_12', array(number_format(mconfig('max_stats')))).'</p>';
		if(mconfig('minimum_limit') > 0)         echo '<p>'.langf('addstats_txt_13', array(number_format(mconfig('minimum_limit')))).'</p>';
	echo '</div>';

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}