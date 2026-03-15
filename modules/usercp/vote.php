<?php

use Darkheim\Application\Vote\Vote;
use Darkheim\Application\Vote\VoteSiteRepository;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_7',true).'</span></div>';

try {

	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	$vote = new Vote();
	$voteSiteRepository = new VoteSiteRepository();

	if(isset($_POST['submit'])) {
		try {
			$vote->setUserid($_SESSION['userid']);
			$vote->setIp($_SERVER['REMOTE_ADDR']);
			$vote->setVotesiteId($_POST['voting_site_id']);
			$vote->vote();
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}

	echo '<div class="ucp-card">';
		echo '<div class="ucp-card-header"><i class="bi bi-star-fill"></i>'.lang('module_titles_txt_7',true).'</div>';
		echo '<div class="ucp-card-body" style="padding:0;">';
			echo '<table class="table general-table-ui" style="margin-bottom:0;">';
				echo '<thead><tr>';
					echo '<th>'.lang('vfc_txt_1',true).'</th>';
					echo '<th>'.lang('vfc_txt_2',true).'</th>';
					echo '<th></th>';
				echo '</tr></thead>';
				echo '<tbody>';
				$vote_sites = $voteSiteRepository->findAll();
				if(is_array($vote_sites)) {
					foreach($vote_sites as $thisVotesite) {
						echo '<form action="" method="post">';
							echo '<input type="hidden" name="voting_site_id" value="'.$thisVotesite['votesite_id'].'"/>';
							echo '<tr>';
								echo '<td>'.$thisVotesite['votesite_title'].'</td>';
								echo '<td><span class="ucp-character-level">'.$thisVotesite['votesite_reward'].'</span></td>';
								echo '<td><button name="submit" value="submit" class="btn btn-primary btn-sm">'.lang('vfc_txt_3',true).'</button></td>';
							echo '</tr>';
						echo '</form>';
					}
				}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	echo '</div>';

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}