<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\News\NewsService as News;
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

        if (!$newsService->isNewsDirWritable()) {
            message('error', 'The news cache folder is not writable.');
            return;
        }

        if (isset($_REQUEST['delete'])) {
            $newsService->removeNews($_REQUEST['delete']);
            $newsService->cacheNews();
            $newsService->updateNewsCacheIndex();
            redirect(1, 'admincp/?module=managenews');
        }

        if (isset($_GET['deletetranslation'], $_GET['language'])) {
            try {
                $newsService->setId($_GET['deletetranslation']);
                $newsService->setLanguage($_GET['language']);
                $newsService->deleteNewsTranslation();
                $newsService->updateNewsCacheIndex();
                redirect(1, 'admincp/?module=managenews');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        if (isset($_REQUEST['cache']) && $_REQUEST['cache'] == 1) {
            $newsService->cacheNews()
                ? message('success', 'News cached successfully')
                : message('error', 'No news to cache.');
            $newsService->updateNewsCacheIndex();
        }

        $newsList = $newsService->retrieveNews();
        $items    = [];
        if (is_array($newsList)) {
            foreach ($newsList as $row) {
                $newsService->setId($row['news_id']);
                $translations    = $newsService->getNewsTranslationsDataList();
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
                    'addTransUrl'      => admincp_base('addnewstranslation&id=' . ($row['news_id'] ?? '')),
                    'editUrl'          => admincp_base('editnews&id=' . ($row['news_id'] ?? '')),
                    'deleteUrl'        => admincp_base('managenews&delete=' . ($row['news_id'] ?? '')),
                ];
            }
        }

        $this->view->render('admincp/managenews', [
            'items'       => $items,
            'addUrl'      => admincp_base('addnews'),
            'cacheUrl'    => admincp_base('managenews&cache=1'),
        ]);
    }
}

