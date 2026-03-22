<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\News\NewsService as News;
use Darkheim\Infrastructure\View\ViewRenderer;

final class EditNewsTranslationController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            $newsService = new News();
            \Darkheim\Infrastructure\Bootstrap\BootstrapContext::loadModuleConfig('news');

            if (!$newsService->isNewsDirWritable()) {
                throw new \RuntimeException('The news cache folder is not writable.');
            }

            if (isset($_POST['news_submit'])) {
                try {
                    $newsService->setId($_POST['news_id']);
                    $newsService->setLanguage($_POST['news_language']);
                    $newsService->setTitle($_POST['news_title']);
                    $newsService->setContent($_POST['news_content']);
                    $newsService->updateNewsTransation();
                    \Darkheim\Infrastructure\Http\Redirector::go(1, 'admincp/?module=managenews');
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $newsService->setId($_GET['id']);
            $newsService->setLanguage($_GET['language']);
            $newsData = $newsService->loadNewsTranslationData();
            if (!is_array($newsData)) {
                throw new \RuntimeException('Could not load news data.');
            }

            $this->view->render('admincp/editnewstranslation', [
                'newsId'      => (string) ($newsData['news_id'] ?? ''),
                'language'    => (string) ($newsData['news_language'] ?? ''),
                'formTitle'   => (string) ($_POST['news_title'] ?? base64_decode((string) ($newsData['news_title'] ?? ''))),
                'formContent' => (string) ($_POST['news_content'] ?? base64_decode((string) ($newsData['news_content'] ?? ''))),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }
}

