<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Cache\CacheManager;
use Darkheim\Infrastructure\Helpers\FileHelper;
use Darkheim\Infrastructure\Runtime\NativeQueryStore;
use Darkheim\Infrastructure\Runtime\QueryStore;
use Darkheim\Infrastructure\View\ViewRenderer;

final class CacheManagerController
{
    private ViewRenderer $view;
    private QueryStore $query;

    public function __construct(?ViewRenderer $view = null, ?QueryStore $query = null)
    {
        $this->view = $view ?? new ViewRenderer();
        $this->query = $query ?? new NativeQueryStore();
    }

    public function render(): void
    {
        try {
            $cacheManager = new CacheManager();
            $admincpUrl = new AdmincpUrlGenerator();
            $this->handleAction($cacheManager, $admincpUrl);

            $cacheFileList = $cacheManager->getCacheFileListAndData();
            if (!is_array($cacheFileList)) {
                throw new \RuntimeException('No cache files found.');
            }

            $this->view->render('admincp/cachemanager', [
                'pageTitle' => 'Cache Manager',
                'cacheRows' => $this->cacheRows($cacheFileList, $admincpUrl),
                'profileCards' => $this->profileCards($cacheManager, $admincpUrl),
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function handleAction(CacheManager $cacheManager, AdmincpUrlGenerator $admincpUrl): void
    {
        $action = (string) $this->query->get('action', '');
        if ($action === '') {
            return;
        }

        try {
            switch ($action) {
                case 'clear':
                    $cacheManager->_file = (string) $this->query->get('file', '');
                    $cacheManager->clearCacheData();
                    break;
                case 'deleteguildcache':
                    $cacheManager->deleteGuildCache();
                    break;
                case 'deleteplayercache':
                    $cacheManager->deletePlayerCache();
                    break;
                default:
                    throw new \RuntimeException('Invalid action.');
            }

            \Darkheim\Infrastructure\Http\Redirector::go(3, $admincpUrl->base('cachemanager'));
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    /**
     * @param array<int,array<string,mixed>> $cacheFileList
     * @return array<int,array{file:string,size:string,lastModified:string,writableLabel:string,writableClass:string,clearUrl:string}>
     */
    private function cacheRows(array $cacheFileList, AdmincpUrlGenerator $admincpUrl): array
    {
        $rows = [];
        foreach ($cacheFileList as $row) {
            $file = (string) ($row['file'] ?? '');
            $isWritable = (int) ($row['write'] ?? 0) === 1;
            $rows[] = [
                'file' => $file,
                'size' => FileHelper::readableSize((int) ($row['size'] ?? 0)),
                'lastModified' => (string) ($row['edit'] ?? ''),
                'writableLabel' => $isWritable ? 'Yes' : 'Not Writable',
                'writableClass' => $isWritable ? 'badge-status on' : 'badge-status off',
                'clearUrl' => $admincpUrl->base('cachemanager&action=clear&file=' . urlencode($file)),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int,array{label:string,fileCount:string,totalSize:string,deleteUrl:string,showDelete:bool,deleteLabel:string}>
     */
    private function profileCards(CacheManager $cacheManager, AdmincpUrlGenerator $admincpUrl): array
    {
        $cards = [];
        foreach (['guild' => 'Guild Profiles', 'player' => 'Player Profiles'] as $type => $label) {
            $profileCache = $cacheManager->getCacheFileListAndData($type);
            $count = is_array($profileCache) ? count($profileCache) : 0;
            $size = 0;

            if (is_array($profileCache)) {
                foreach ($profileCache as $file) {
                    if (!is_array($file)) {
                        continue;
                    }
                    $size += (int) ($file['size'] ?? 0);
                }
            }

            $action = $type === 'guild' ? 'deleteguildcache' : 'deleteplayercache';
            $cards[] = [
                'label' => $label,
                'fileCount' => number_format($count),
                'totalSize' => FileHelper::readableSize($size),
                'deleteUrl' => $admincpUrl->base('cachemanager&action=' . $action),
                'showDelete' => $count > 0,
                'deleteLabel' => 'Delete ' . $label . ' Cache',
            ];
        }

        return $cards;
    }
}

