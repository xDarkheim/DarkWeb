<?php ?>
<div class="row">
	<div class="col-xs-12 col-sm-8 home-news-block">
		<?php
			$newsList = loadCache('news.cache');
			echo '<div class="home-news-header">';
				echo '<div class="home-news-header-left">';
					echo '<i class="bi bi-newspaper home-news-header-icon"></i>';
					echo '<span class="home-news-header-title">'.lang('news_txt_4').'</span>';
				echo '</div>';
				echo '<a href="'.__BASE_URL__.'news/" class="home-news-header-more">'.lang('news_txt_5').' <i class="bi bi-arrow-right"></i></a>';
			echo '</div>';

			if(is_array($newsList)) {
				echo '<div class="home-news-feed">';
				foreach($newsList as $key => $newsArticle) {
					if($key >= 7) break;
					// Cache stores plain-text titles (already decoded by updateNewsCacheIndex)
					$news_title = (string)($newsArticle['news_title'] ?? '');
					// Translations are still base64-encoded (raw from DB)
					if(isset($_SESSION['language_display'], $newsArticle['translations'])
							&& config('language_switch_active', true)
							&& is_array($newsArticle['translations'])
							&& array_key_exists($_SESSION['language_display'], $newsArticle['translations'])
					) {
						$decoded = base64_decode($newsArticle['translations'][$_SESSION['language_display']], true);
						if($decoded !== false && $decoded !== '') { $news_title = $decoded; }
					}
					if(!mb_check_encoding($news_title, 'UTF-8')) {
						$conv = @iconv('Windows-1252', 'UTF-8//IGNORE', $news_title);
						if($conv !== false && mb_check_encoding($conv, 'UTF-8')) { $news_title = $conv; }
					}
					$news_url = __BASE_URL__.'news/'.$newsArticle['news_id'].'/';
					echo '<a href="'.$news_url.'" class="home-news-item">';
						echo '<span class="home-news-item-badge">'.lang('news_txt_6').'</span>';
						echo '<span class="home-news-item-title">'.htmlspecialchars($news_title, ENT_QUOTES, 'UTF-8').'</span>';
						echo '<span class="home-news-item-date"><i class="bi bi-calendar3"></i>'.date("Y/m/d", $newsArticle['news_date']).'</span>';
					echo '</a>';
				}
				echo '</div>';
			} else {
				echo '<div class="home-news-empty"><i class="bi bi-inbox"></i> No news yet.</div>';
			}
		?>
	</div>
	<div class="col-xs-12 col-sm-4">
		<?php
		if(!isLoggedIn()) {
			echo '<div class="panel panel-sidebar">';
				echo '<div class="panel-heading">';
					echo '<h3 class="panel-title">'.lang('module_titles_txt_2').' <a href="'.__BASE_URL__.'forgotpassword" class="btn btn-primary btn-xs pull-right">'.lang('login_txt_4').'</a></h3>';
				echo '</div>';
				echo '<div class="panel-body">';
					echo '<form action="'.__BASE_URL__.'login" method="post">';
						echo '<div class="form-group">';
							echo '<input type="text" class="form-control" id="loginBox1" name="darkheimLogin_user" required>';
						echo '</div>';
						echo '<div class="form-group">';
							echo '<input type="password" class="form-control" id="loginBox2" name="darkheimLogin_pwd" required>';
						echo '</div>';
						echo '<button type="submit" name="darkheimLogin_submit" value="submit" class="btn btn-primary">'.lang('login_txt_3').'</button>';
					echo '</form>';
				echo '</div>';
			echo '</div>';
			echo '<div class="sidebar-banner"><a href="'.__BASE_URL__.'register"><img src="'.__PATH_TEMPLATE_IMG__.'sidebar_banner_join.jpg"/></a></div>';
		} else {
			echo '<div class="panel panel-sidebar panel-usercp">';
				echo '<div class="panel-heading">';
					echo '<h3 class="panel-title">'.lang('usercp_menu_title').' <a href="'.__BASE_URL__.'logout" class="btn btn-primary btn-xs pull-right">'.lang('login_txt_6').'</a></h3>';
				echo '</div>';
				echo '<div class="panel-body">';
						templateBuildUsercp();
				echo '</div>';
			echo '</div>';
		}
		?>
	</div>
</div>

<div class="row" style="margin-top: 20px;">
	<div class="col-xs-12 col-sm-4">
		<?php
		// Top Level
		$levelRankingData = LoadCacheData('rankings_level.cache');
		$topLevelLimit = 10;
		if(is_array($levelRankingData)) {
			$topLevel = array_slice($levelRankingData, 0, $topLevelLimit+1);
			echo '<div class="panel panel-sidebar">';
				echo '<div class="panel-heading">';
					echo '<h3 class="panel-title">'.lang('rankings_txt_1').'<a href="'.__BASE_URL__.'rankings/level" class="btn btn-primary btn-xs pull-right" style="text-align:center;width:22px;">+</a></h3>';
				echo '</div>';
				echo '<div class="panel-body" style="min-height:400px;">';
					echo '<table class="table table-condensed">';
						echo '<thead><tr>';
							echo '<th class="text-center">'.lang('rankings_txt_10').'</th>';
							echo '<th class="text-center">'.lang('rankings_txt_11').'</th>';
							echo '<th class="text-center">'.lang('rankings_txt_12').'</th>';
						echo '</tr></thead>';
						echo '<tbody>';
						foreach($topLevel as $key => $row) {
							if($key == 0) continue;
							echo '<tr>';
								echo '<td class="text-center">'.playerProfile($row[0]).'</td>';
								echo '<td class="text-center">'.getPlayerClass($row[1]).'</td>';
								echo '<td class="text-center">'.number_format($row[2]).'</td>';
							echo '</tr>';
						}
						echo '</tbody>';
					echo '</table>';
				echo '</div>';
			echo '</div>';
		}
		?>
	</div>
	<div class="col-xs-12 col-sm-4">
		<?php
		// Top Guilds
		$guildRankingData = LoadCacheData('rankings_guilds.cache');
		$topGuildLimit = 10;
		if(is_array($guildRankingData)) {
			$rankingsConfig = loadConfigurations('rankings');
			$topGuild = array_slice($guildRankingData, 0, $topGuildLimit+1);
			echo '<div class="panel panel-sidebar">';
				echo '<div class="panel-heading">';
					echo '<h3 class="panel-title">'.lang('rankings_txt_4').'<a href="'.__BASE_URL__.'rankings/guilds" class="btn btn-primary btn-xs pull-right" style="text-align:center;width:22px;">+</a></h3>';
				echo '</div>';
				echo '<div class="panel-body" style="min-height:400px;">';
					echo '<table class="table table-condensed">';
						echo '<thead><tr>';
							echo '<th class="text-center">'.lang('rankings_txt_17').'</th>';
							echo '<th class="text-center">'.lang('rankings_txt_28').'</th>';
							echo '<th class="text-center">'.lang('rankings_txt_19').'</th>';
						echo '</tr></thead>';
						echo '<tbody>';
						foreach($topGuild as $key => $row) {
							if($key == 0) continue;
							$multiplier = $rankingsConfig['guild_score_formula'] == 1 ? 1 : $rankingsConfig['guild_score_multiplier'];
							echo '<tr>';
								echo '<td class="text-center">'.guildProfile($row[0]).'</td>';
								echo '<td class="text-center">'.returnGuildLogo($row[3], 20).'</td>';
								echo '<td class="text-center">'.number_format(floor($row[2]*$multiplier)).'</td>';
							echo '</tr>';
						}
						echo '</tbody>';
					echo '</table>';
				echo '</div>';
			echo '</div>';
		}
		?>
	</div>
	<div class="col-xs-12 col-sm-4">
		<?php
		// Event Timers
		echo '<div class="panel panel-sidebar panel-sidebar-events">';
			echo '<div class="panel-heading">';
				echo '<h3 class="panel-title">'.lang('event_schedule').'</h3>';
			echo '</div>';
			echo '<div class="panel-body" style="min-height:400px;">';
				echo '<table class="table table-condensed">';
					echo '<tr><td><span id="bloodcastle_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="bloodcastle_next"></span><br /><span class="smalltext" id="bloodcastle"></span></td></tr>';
					echo '<tr><td><span id="devilsquare_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="devilsquare_next"></span><br /><span class="smalltext" id="devilsquare"></span></td></tr>';
					echo '<tr><td><span id="chaoscastle_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="chaoscastle_next"></span><br /><span class="smalltext" id="chaoscastle"></span></td></tr>';
					echo '<tr><td><span id="dragoninvasion_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="dragoninvasion_next"></span><br /><span class="smalltext" id="dragoninvasion"></span></td></tr>';
					echo '<tr><td><span id="goldeninvasion_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="goldeninvasion_next"></span><br /><span class="smalltext" id="goldeninvasion"></span></td></tr>';
					echo '<tr><td><span id="castlesiege_name"></span><br /><span class="smalltext">'.lang('event_schedule_start').'</span></td><td class="text-right"><span id="castlesiege_next"></span><br /><span class="smalltext" id="castlesiege"></span></td></tr>';
				echo '</table>';
			echo '</div>';
		echo '</div>';
		?>
	</div>
</div>