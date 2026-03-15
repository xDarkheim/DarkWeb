<?php

use Darkheim\Application\News\NewsRepository;
use Darkheim\Infrastructure\Cache\CacheRepository;

try {
	if(!mconfig('active')) throw new Exception(lang('error_47',true));

	$newsRepo = new NewsRepository(
		new CacheRepository(__PATH_CACHE__),
		__PATH_NEWS_CACHE__
	);

	$allNews = $newsRepo->findAll();
	if(empty($allNews)) throw new Exception(lang('error_61'));

	$language = (config('language_switch_active',true) && isset($_SESSION['language_display']))
		? $_SESSION['language_display']
		: '';

	$requestedNewsId = isset($_GET['subpage']) ? (int)$_GET['subpage'] : 0;
	$showSingleNews  = false;
	$singleItem      = null;

	if($requestedNewsId > 0) {
		$singleItem = $newsRepo->findById($requestedNewsId);
		if($singleItem !== null) {
			$showSingleNews = true;
		}
	}

	$listLimit = (int)mconfig('news_list_limit');
	$i = 0;

	if($showSingleNews) {
		echo '<div class="page-title"><span>'.lang('news_txt_4',true).'</span></div>';
	} else {
		echo '<div class="news-page-header">';
			echo '<div class="news-page-header-left">';
				echo '<div class="news-page-header-title">'.lang('news_txt_4',true).'</div>';
				echo '<div class="news-page-header-sub">Latest announcements &amp; server updates</div>';
			echo '</div>';
			echo '<div class="news-page-header-count"><span>'.count($allNews).'</span>posts</div>';
		echo '</div>';
		echo '<div class="news-list">';
	}

	foreach($allNews as $item) {
		if($showSingleNews && $item->id !== $singleItem->id) continue;
		if(!$showSingleNews && $i > $listLimit) continue;

		$news_title = $item->titleForLanguage($language);
		$news_url   = $item->url(__BASE_URL__);

		if($showSingleNews) {
			$content = $newsRepo->loadContent($item->id, false, $language);
			echo '<article class="news-single">';
				echo '<header class="news-single-header">';
					echo '<div class="news-single-badge">'.lang('news_txt_6',true).'</div>';
					echo '<h1 class="news-single-title">'.$news_title.'</h1>';
					echo '<div class="news-single-meta">';
						echo '<span class="news-meta-item news-meta-date"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/><path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'.date("l, F jS Y", $item->date).'</span>';
						echo '<span class="news-meta-sep">·</span>';
						echo '<span class="news-meta-item news-meta-author"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'.$item->author.'</span>';
					echo '</div>';
				echo '</header>';
				echo '<div class="news-single-body">'.$content.'</div>';
				echo '<footer class="news-single-footer">';
					echo '<div class="news-single-published">'.langf('news_txt_1', [$item->author, date("l, F jS Y", $item->date)]).'</div>';
					echo '<a href="'.__BASE_URL__.'news/" class="news-back-link"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'.lang('news_txt_4',true).'</a>';
				echo '</footer>';
        } else {
			$short   = (bool)mconfig('news_short');
			$content = $newsRepo->loadContent($item->id, $short, $language);
			$postNum = str_pad($i + 1, 2, '0', STR_PAD_LEFT);

			echo '<article class="news-card">';
				echo '<div class="news-card-inner">';
					echo '<div class="news-card-num">'.$postNum.'</div>';
					echo '<div class="news-card-content">';
						echo '<header class="news-card-header">';
							echo '<h2 class="news-card-title"><a href="'.$news_url.'">'.$news_title.'</a></h2>';
							echo '<div class="news-card-meta">';
								echo '<span class="news-meta-item news-meta-date"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="11" height="11"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/><path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'.date("F jS Y", $item->date).'</span>';
								echo '<span class="news-meta-sep">·</span>';
								echo '<span class="news-meta-item news-meta-author"><svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="11" height="11"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'.$item->author.'</span>';
							echo '</div>';
						echo '</header>';
						if(mconfig('news_expanded') > $i) {
							echo '<div class="news-card-body">'.$content.'</div>';
							echo '<footer class="news-card-footer">';
								echo '<a href="'.$news_url.'" class="news-readmore">'.lang('news_txt_3').' →</a>';
							echo '</footer>';
						} else {
							echo '<a href="'.$news_url.'" class="news-card-link-cover" aria-label="'.$news_title.'"></a>';
						}
					echo '</div>';
				echo '</div>';
        }
        echo '</article>';

        $i++;
	}

	if(!$showSingleNews) echo '</div>'; // .news-list

} catch(Exception $ex) {
	inline_message('error', $ex->getMessage());
}