<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\Language\Translator;
use Darkheim\Infrastructure\View\ViewRenderer;
use Darkheim\Infrastructure\Cache\CacheRepository;

final class DownloadsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                \Darkheim\Application\View\MessageRenderer::inline('error', Translator::phrase('error_47'));
                return;
            }

            $clients = [];
            $patches = [];
            $tools   = [];

            $cache = (new CacheRepository(__PATH_CACHE__))->load('downloads.cache');
            if (is_array($cache)) {
                foreach ($cache as $d) {
                    if ($d['download_type'] == 1)     $clients[] = $d;
                    elseif ($d['download_type'] == 2) $patches[] = $d;
                    elseif ($d['download_type'] == 3) $tools[]   = $d;
                }
            }

            $this->view->render('downloads', [
                'showClients' => (bool) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('show_client_downloads'),
                'showPatches' => (bool) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('show_patch_downloads'),
                'showTools'   => (bool) \Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('show_tool_downloads'),
                'clients'     => $clients,
                'patches'     => $patches,
                'tools'       => $tools,
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}
