<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\News\NewsService as News;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AddNewsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $newsService = new News();
        loadModuleConfigs('news');

        if (!$newsService->isNewsDirWritable()) {
            message('error', 'The news cache folder is not writable.');
            return;
        }

        if (isset($_POST['news_submit'])) {
            $newsService->addNews($_POST['news_title'], $_POST['news_content'], $_POST['news_author'], 0);
            $newsService->cacheNews();
            $newsService->updateNewsCacheIndex();
            redirect(1, 'admincp/?module=managenews');
        }

        $this->view->render('admincp/addnews', []);
    }
}

