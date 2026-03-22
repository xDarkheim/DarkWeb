<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\View\ViewRenderer;

final class NavbarController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            if (isset($_GET['delete'])) {
                $this->deleteEntry((string) $_GET['delete']);
            }

            if (isset($_POST['navbar_submit'])) {
                $this->upsertEntry(true);
            }

            if (isset($_POST['new_submit'])) {
                $this->upsertEntry(false);
            }

            $cfg = BootstrapContext::configProvider()?->config('navbar');
            if (! is_array($cfg)) {
                throw new \RuntimeException('Navbar configs empty.');
            }

            $rows = [];
            foreach ($cfg as $id => $element) {
                if (! is_array($element)) {
                    continue;
                }
                $rows[] = [
                    'id'         => (string) $id,
                    'order'      => (string) ($element['order'] ?? ''),
                    'active'     => (bool) ($element['active'] ?? false),
                    'type'       => (string) ($element['type'] ?? 'internal'),
                    'link'       => (string) ($element['link'] ?? ''),
                    'phrase'     => (string) ($element['phrase'] ?? ''),
                    'visibility' => (string) ($element['visibility'] ?? 'user'),
                    'newtab'     => (bool) ($element['newtab'] ?? false),
                    'deleteUrl'  => '?module=navbar&delete=' . urlencode((string) $id),
                ];
            }

            $this->view->render('admincp/navbar', [
                'rows' => $rows,
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function deleteEntry(string $id): void
    {
        try {
            $cfg = BootstrapContext::configProvider()?->config('navbar');
            if (! is_array($cfg)) {
                throw new \RuntimeException('Navbar configs empty.');
            }
            if (! array_key_exists($id, $cfg)) {
                throw new \RuntimeException('Invalid id.');
            }
            unset($cfg[$id]);
            $this->saveConfig($cfg);
            \Darkheim\Application\View\MessageRenderer::toast('success', 'Changes successfully saved!');
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function upsertEntry(bool $isEdit): void
    {
        try {
            $cfg = BootstrapContext::configProvider()?->config('navbar');
            if (! is_array($cfg)) {
                throw new \RuntimeException('Navbar configs empty.');
            }

            if ($isEdit && ! isset($_POST['navbar_id'])) {
                throw new \RuntimeException('Please fill all the form fields.');
            }
            if (! isset($_POST['navbar_type'], $_POST['navbar_phrase'])) {
                throw new \RuntimeException('Please fill all the form fields.');
            }
            if (! in_array($_POST['navbar_type'], ['internal', 'external'], true)) {
                throw new \RuntimeException('Link type is not valid.');
            }
            if (! in_array($_POST['navbar_visibility'], ['user', 'guest', 'always'], true)) {
                throw new \RuntimeException('Link visibility is not a valid option.');
            }

            $newElementData = [
                'active'     => ($_POST['navbar_status'] ?? '0') == 1,
                'type'       => (string) $_POST['navbar_type'],
                'phrase'     => (string) $_POST['navbar_phrase'],
                'link'       => (string) ($_POST['navbar_link'] ?? ''),
                'visibility' => (string) $_POST['navbar_visibility'],
                'newtab'     => ($_POST['navbar_newtab'] ?? '0') == 1,
                'order'      => (int) ($_POST['navbar_order'] ?? 0),
            ];

            if ($isEdit) {
                $cfg[(string) $_POST['navbar_id']] = $newElementData;
            } else {
                $cfg[] = $newElementData;
            }

            usort($cfg, static fn(array $a, array $b): int => ((int) ($a['order'] ?? 0)) - ((int) ($b['order'] ?? 0)));
            $this->saveConfig($cfg);
            \Darkheim\Application\View\MessageRenderer::toast('success', $isEdit ? 'Changes successfully saved!' : 'Navbar successfully updated!');
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    /** @param array<int|string,mixed> $cfg */
    private function saveConfig(array $cfg): void
    {
        $json    = json_encode($cfg, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $cfgFile = fopen(__PATH_CONFIGS__ . 'navigation.json', 'wb');
        if (! $cfgFile) {
            throw new \RuntimeException('There was a problem opening the navbar file.');
        }
        fwrite($cfgFile, $json);
        fclose($cfgFile);
    }
}
