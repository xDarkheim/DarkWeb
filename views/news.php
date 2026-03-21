<?php
/**
 * News view template.
 *
 * Variables injected by NewsController via ViewRenderer:
 *   @var bool                                        $showSingle
 *   @var int                                         $totalCount
 *   @var array<int, array<string, mixed>>            $viewItems
 *
 * Each $viewItems entry (single-article mode):
 *   item (NewsItem), newsTitle (string), newsUrl (string), content (string)
 *
 * Each $viewItems entry (list mode):
 *   item (NewsItem), newsTitle (string), newsUrl (string),
 *   content (string|null), postNum (string), isExpanded (bool)
 */
?>
<?php if ($showSingle): ?>

<div class="page-title"><span><?php echo lang('news_txt_4', true); ?></span></div>

<?php foreach ($viewItems as $entry): ?>
<?php $item = $entry['item']; ?>
<article class="news-single">
    <header class="news-single-header">
        <div class="news-single-badge"><?php echo lang('news_txt_6', true); ?></div>
        <h1 class="news-single-title"><?php echo htmlspecialchars($entry['newsTitle'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="news-single-meta">
            <span class="news-meta-item news-meta-date">
                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12">
                    <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <?php echo date("l, F jS Y", $item->date); ?>
            </span>
            <span class="news-meta-sep"></span>
            <span class="news-meta-item news-meta-author">
                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12">
                    <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <?php echo htmlspecialchars($item->author, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
    </header>
    <div class="news-single-body"><?php echo $entry['content']; ?></div>
    <footer class="news-single-footer">
        <div class="news-single-published">
            <?php echo langf('news_txt_1', [$item->author, date("l, F jS Y", $item->date)]); ?>
        </div>
        <a href="<?php echo __BASE_URL__; ?>news/" class="news-back-link">
            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="12" height="12">
                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <?php echo lang('news_txt_4', true); ?>
        </a>
    </footer>
</article>
<?php endforeach; ?>

<?php else: ?>

<div class="news-page-header">
    <div class="news-page-header-left">
        <div class="news-page-header-title"><?php echo lang('news_txt_4', true); ?></div>
        <div class="news-page-header-sub">Latest announcements &amp; server updates</div>
    </div>
    <div class="news-page-header-count"><span><?php echo $totalCount; ?></span>posts</div>
</div>

<div class="news-list">
<?php foreach ($viewItems as $entry): ?>
<?php $item = $entry['item']; ?>
<article class="news-card">
    <div class="news-card-inner">
        <div class="news-card-num"><?php echo $entry['postNum']; ?></div>
        <div class="news-card-content">
            <header class="news-card-header">
                <h2 class="news-card-title">
                    <a href="<?php echo $entry['newsUrl']; ?>"><?php echo htmlspecialchars($entry['newsTitle'], ENT_QUOTES, 'UTF-8'); ?></a>
                </h2>
                <div class="news-card-meta">
                    <span class="news-meta-item news-meta-date">
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="11" height="11">
                            <rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M1 6h14" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M5 1v2M11 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <?php echo date("F jS Y", $item->date); ?>
                    </span>
                    <span class="news-meta-sep"></span>
                    <span class="news-meta-item news-meta-author">
                        <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" width="11" height="11">
                            <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <?php echo htmlspecialchars($item->author, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </header>
            <?php if ($entry['isExpanded']): ?>
            <div class="news-card-body"><?php echo $entry['content']; ?></div>
            <footer class="news-card-footer">
                <a href="<?php echo $entry['newsUrl']; ?>" class="news-readmore">
                    <?php echo lang('news_txt_3'); ?> →
                </a>
            </footer>
            <?php else: ?>
            <a href="<?php echo $entry['newsUrl']; ?>" class="news-card-link-cover"
               aria-label="<?php echo htmlspecialchars($entry['newsTitle'], ENT_QUOTES, 'UTF-8'); ?>"></a>
            <?php endif; ?>
        </div>
    </div>
</article>
<?php endforeach; ?>
</div><!-- .news-list -->

<?php endif; ?>

