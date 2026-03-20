<?php
use Darkheim\Application\News\NewsService as News;
?>
<h1 class="page-header"><i class="bi bi-newspaper me-2"></i>Manage News</h1>
<?php
$News = new News();
if(!$News->isNewsDirWritable()) { message('error','The news cache folder is not writable.'); return; }
if(isset($_REQUEST['delete'])) {
	$News->removeNews($_REQUEST['delete']); $News->cacheNews(); $News->updateNewsCacheIndex();
	redirect(1, 'admincp/?module=managenews');
}
if(isset($_GET['deletetranslation'], $_GET['language'])) {
	try { $News->setId($_GET['deletetranslation']); $News->setLanguage($_GET['language']); $News->deleteNewsTranslation(); $News->updateNewsCacheIndex(); redirect(1, 'admincp/?module=managenews'); }
	catch(Exception $ex) { message('error', $ex->getMessage()); }
}
if(isset($_REQUEST['cache']) && $_REQUEST['cache'] == 1) {
	$News->cacheNews() ? message('success','News cached successfully') : message('error','No news to cache.');
	$News->updateNewsCacheIndex();
}
echo '<div class="mb-3 d-flex gap-2">';
echo '<a href="'.admincp_base('addnews').'" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Publish News</a>';
echo '<a href="'.admincp_base('managenews&cache=1').'" class="btn btn-default"><i class="bi bi-arrow-clockwise me-1"></i>Rebuild Cache</a>';
echo '</div>';
$news_list = $News->retrieveNews();
if(is_array($news_list)) {
	foreach($news_list as $row) {
		$News->setId($row['news_id']);
		$translations = $News->getNewsTranslationsDataList();
		echo '<div class="acp-card mb-3">';
		echo '<div class="acp-card-header d-flex justify-content-between align-items-center">';
		echo '<a href="'.__BASE_URL__.'news/'.$row['news_id'].'/" target="_blank" style="color:var(--accent);">'.$row['news_title'].'</a>';
		echo '<div class="d-flex gap-1">';
		echo '<a href="'.admincp_base("addnewstranslation&id=".$row['news_id']).'" class="btn btn-sm btn-default"><i class="bi bi-plus"></i> Translation</a>';
		echo '<a href="'.admincp_base("editnews&id=".$row['news_id']).'" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>';
		echo '<a href="'.admincp_base("managenews&delete=".$row['news_id']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this news?\')"><i class="bi bi-trash"></i> Delete</a>';
		echo '</div></div>';
		echo '<div class="p-3"><table class="dash-table"><tr><td>News ID</td><td>'.$row['news_id'].'</td></tr><tr><td>Author</td><td>'.$row['news_author'].'</td></tr><tr><td>Date</td><td>'.date("Y-m-d H:i",$row['news_date']).'</td></tr>';
		if(is_array($translations)) {
			$langs = implode(', ', array_column($translations, 'language'));
			echo '<tr><td>Translations</td><td>'.$langs.'</td></tr>';
		}
		echo '</table></div></div>';
	}
} else {
	inline_message('info', 'No news found.');
}

?>