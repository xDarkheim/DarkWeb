<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Infrastructure\View\ViewRenderer;

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
            if (!mconfig('active')) {
                inline_message('error', lang('error_47', true));
                return;
            }

            $clients = [];
            $patches = [];
            $tools   = [];

            $cache = loadCache('downloads.cache');
            if (is_array($cache)) {
                foreach ($cache as $d) {
                    if ($d['download_type'] == 1)     $clients[] = $d;
                    elseif ($d['download_type'] == 2) $patches[] = $d;
                    elseif ($d['download_type'] == 3) $tools[]   = $d;
                }
            }

            $this->view->render('downloads', [
                'showClients' => (bool) mconfig('show_client_downloads'),
                'showPatches' => (bool) mconfig('show_patch_downloads'),
                'showTools'   => (bool) mconfig('show_tool_downloads'),
                'clients'     => $clients,
                'patches'     => $patches,
                'tools'       => $tools,
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}
