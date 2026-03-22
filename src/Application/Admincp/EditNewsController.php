<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\News\NewsService as News;
use Darkheim\Infrastructure\View\ViewRenderer;

final class EditNewsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $newsService = new News();
        \Darkheim\Infrastructure\Bootstrap\BootstrapContext::loadModuleConfig('news');

        if (!$newsService->isNewsDirWritable()) {
            \Darkheim\Application\View\MessageRenderer::toast('error', 'The news cache folder is not writable.');
            return;
        }

        if (isset($_POST['news_submit'])) {
            $newsService->editNews($_REQUEST['id'], $_POST['news_title'], $_POST['news_content'], $_POST['news_author'], 0, $_POST['news_date']);
            $newsService->cacheNews();
            $newsService->updateNewsCacheIndex();
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'admincp/?module=managenews');
        }

        $editNews = $newsService->loadNewsData($_REQUEST['id']);
        if (!$editNews) {
            \Darkheim\Application\View\MessageRenderer::toast('error', 'Could not load news data.');
            return;
        }

        $this->view->render('admincp/editnews', [
            'title'   => (string) ($editNews['news_title'] ?? ''),
            'content' => (string) ($editNews['news_content'] ?? ''),
            'author'  => (string) ($editNews['news_author'] ?? ''),
            'date'    => date('Y-m-d H:i', (int) ($editNews['news_date'] ?? 0)),
        ]);
    }
}

