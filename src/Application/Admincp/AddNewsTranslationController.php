<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Language\LanguageRepository;
use Darkheim\Application\News\NewsService as News;
use Darkheim\Infrastructure\View\ViewRenderer;

final class AddNewsTranslationController
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

            if (! $newsService->isNewsDirWritable()) {
                throw new \RuntimeException('The news cache folder is not writable.');
            }

            if (isset($_POST['news_submit'])) {
                try {
                    $newsService->setId($_POST['news_id']);
                    $newsService->setLanguage($_POST['news_language']);
                    $newsService->setTitle($_POST['news_title']);
                    $newsService->setContent($_POST['news_content']);
                    $newsService->addNewsTransation();
                    $newsService->updateNewsCacheIndex();
                    \Darkheim\Infrastructure\Http\Redirector::go(1, 'admincp/?module=managenews');
                } catch (\Exception $ex) {
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
                }
            }

            $newsData = $newsService->loadNewsData($_GET['id']);
            if (! is_array($newsData)) {
                throw new \RuntimeException('Could not load news data.');
            }

            $languagesList = LanguageRepository::getInstalled();
            if (! is_array($languagesList)) {
                throw new \RuntimeException('There are no available languages.');
            }

            $defaultLang = (string) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::cmsValue('language_default', true);
            $languages   = array_filter($languagesList, static fn($l) => $l !== $defaultLang);

            $this->view->render('admincp/addnewstranslation', [
                'newsId'           => (string) ($newsData['news_id'] ?? ''),
                'defaultTitle'     => (string) ($newsData['news_title'] ?? ''),
                'defaultContent'   => (string) ($newsData['news_content'] ?? ''),
                'languages'        => array_values($languages),
                'selectedLanguage' => (string) ($_POST['news_language'] ?? ''),
                'formTitle'        => (string) ($_POST['news_title'] ?? ($newsData['news_title'] ?? '')),
                'formContent'      => (string) ($_POST['news_content'] ?? ($newsData['news_content'] ?? '')),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }
}
