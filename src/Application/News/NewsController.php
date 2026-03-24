<?php

declare(strict_types=1);

namespace Darkheim\Application\News;

use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class NewsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            if (! BootstrapContext::moduleValue('active')) {
                MessageRenderer::inline('error', Translator::phrase('error_47', true));
                return;
            }

            $newsRepo = new NewsRepository(
                new CacheRepository(__PATH_CACHE__),
                __PATH_NEWS_CACHE__,
            );

            $allNews = $newsRepo->findAll();

            if (empty($allNews)) {
                MessageRenderer::inline('error', Translator::phrase('error_61'));
                return;
            }

            $language = (BootstrapContext::cmsValue('language_switch_active', true) && isset($_SESSION['language_display']))
                ? $_SESSION['language_display']
                : '';

            $requestedId = isset($_GET['subpage']) ? (int) $_GET['subpage'] : 0;
            $showSingle  = false;
            $singleItem  = null;

            if ($requestedId > 0) {
                $singleItem = $newsRepo->findById($requestedId);
                if ($singleItem !== null) {
                    $showSingle = true;
                }
            }

            $listLimit    = (int) BootstrapContext::moduleValue('news_list_limit');
            $short        = (bool) BootstrapContext::moduleValue('news_short');
            $newsExpanded = (int) BootstrapContext::moduleValue('news_expanded');

            // Build view items — all DB/cache I/O happens here, not in the template.
            $viewItems = [];

            if ($showSingle) {
                $content     = $newsRepo->loadContent($singleItem->id, false, $language);
                $dateLabel   = date('l, F jS Y', $singleItem->date);
                $viewItems[] = [
                    'newsTitle'      => $singleItem->titleForLanguage($language),
                    'newsUrl'        => $singleItem->url(__BASE_URL__),
                    'content'        => $content,
                    'author'         => $singleItem->author,
                    'dateLabel'      => $dateLabel,
                    'publishedLabel' => Translator::phraseFmt('news_txt_1', [$singleItem->author, $dateLabel]),
                ];
            } else {
                $cardIndex = 0;
                foreach ($allNews as $item) {
                    if ($cardIndex > $listLimit) {
                        break;
                    }
                    $isExpanded  = $newsExpanded > $cardIndex;
                    $content     = $isExpanded ? $newsRepo->loadContent($item->id, $short, $language) : null;
                    $viewItems[] = [
                        'newsTitle'  => $item->titleForLanguage($language),
                        'newsUrl'    => $item->url(__BASE_URL__),
                        'content'    => $content,
                        'postNum'    => str_pad((string) ($cardIndex + 1), 2, '0', STR_PAD_LEFT),
                        'isExpanded' => $isExpanded,
                        'author'     => $item->author,
                        'dateLabel'  => date('F jS Y', $item->date),
                    ];
                    $cardIndex++;
                }
            }

            $this->view->render('news', [
                'showSingle' => $showSingle,
                'totalCount' => count($allNews),
                'viewItems'  => $viewItems,
            ]);
        } catch (\Exception $ex) {
            MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
