<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp\Controller\News;

use Darkheim\Application\Admincp\Layout\AdmincpUrlGenerator;
use Darkheim\Application\News\NewsService as News;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Infrastructure\Http\Redirector;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ManageNewsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $newsService = new News();
        $admincpUrl  = new AdmincpUrlGenerator();

        if (! $newsService->isNewsDirWritable()) {
            MessageRenderer::toast('error', 'The news cache folder is not writable.');
            return;
        }

        if (isset($_REQUEST['delete'])) {
            $newsService->removeNews($_REQUEST['delete']);
            $newsService->cacheNews();
            $newsService->updateNewsCacheIndex();
            Redirector::go(1, 'admincp/?module=managenews');
        }

        if (isset($_GET['deletetranslation'], $_GET['language'])) {
            try {
                $newsService->setId($_GET['deletetranslation']);
                $newsService->setLanguage($_GET['language']);
                $newsService->deleteNewsTranslation();
                $newsService->updateNewsCacheIndex();
                Redirector::go(1, 'admincp/?module=managenews');
            } catch (\Exception $ex) {
                MessageRenderer::toast('error', $ex->getMessage());
            }
        }

        if (isset($_REQUEST['cache']) && $_REQUEST['cache'] == 1) {
            $newsService->cacheNews()
                ? MessageRenderer::toast('success', 'News cached successfully')
                : MessageRenderer::toast('error', 'No news to cache.');
            $newsService->updateNewsCacheIndex();
        }

        $newsList = $newsService->retrieveNews();
        $items    = [];
        if (is_array($newsList)) {
            foreach ($newsList as $row) {
                $newsService->setId($row['news_id']);
                $translations     = $newsService->getNewsTranslationsDataList();
                $translationLangs = [];
                if (is_array($translations)) {
                    $translationLangs = array_column($translations, 'language');
                }
                $items[] = [
                    'id'               => (string) ($row['news_id'] ?? ''),
                    'title'            => (string) ($row['news_title'] ?? ''),
                    'author'           => (string) ($row['news_author'] ?? ''),
                    'date'             => date('Y-m-d H:i', (int) ($row['news_date'] ?? 0)),
                    'publicUrl'        => __BASE_URL__ . 'news/' . ($row['news_id'] ?? '') . '/',
                    'translationLangs' => implode(', ', $translationLangs),
                    'addTransUrl'      => $admincpUrl->base('addnewstranslation&id=' . ($row['news_id'] ?? '')),
                    'editUrl'          => $admincpUrl->base('editnews&id=' . ($row['news_id'] ?? '')),
                    'deleteUrl'        => $admincpUrl->base('managenews&delete=' . ($row['news_id'] ?? '')),
                ];
            }
        }

        $this->view->render('admincp/managenews', [
            'items'    => $items,
            'addUrl'   => $admincpUrl->base('addnews'),
            'cacheUrl' => $admincpUrl->base('managenews&cache=1'),
        ]);
    }
}
