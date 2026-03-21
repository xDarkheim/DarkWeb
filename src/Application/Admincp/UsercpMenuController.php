<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Infrastructure\View\ViewRenderer;

final class UsercpMenuController
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

            if (isset($_POST['usercp_submit'])) {
                $this->upsertEntry(true);
            }

            if (isset($_POST['new_submit'])) {
                $this->upsertEntry(false);
            }

            $cfg = loadConfig('usercp');
            if (!is_array($cfg)) {
                throw new \RuntimeException('Usercp configs empty.');
            }

            $rows = [];
            foreach ($cfg as $id => $element) {
                if (!is_array($element)) {
                    continue;
                }
                $rows[] = [
                    'id' => (string) $id,
                    'order' => (string) ($element['order'] ?? ''),
                    'active' => (bool) ($element['active'] ?? false),
                    'type' => (string) ($element['type'] ?? 'internal'),
                    'link' => (string) ($element['link'] ?? ''),
                    'phrase' => (string) ($element['phrase'] ?? ''),
                    'icon' => (string) ($element['icon'] ?? 'usercp_default.png'),
                    'visibility' => (string) ($element['visibility'] ?? 'user'),
                    'newtab' => (bool) ($element['newtab'] ?? false),
                    'deleteUrl' => '?module=usercp&delete=' . urlencode((string) $id),
                ];
            }

            $this->view->render('admincp/usercpmenu', [
                'rows' => $rows,
            ]);
        } catch (\Exception $ex) {
            message('error', $ex->getMessage());
        }
    }

    private function deleteEntry(string $id): void
    {
        try {
            $cfg = loadConfig('usercp');
            if (!is_array($cfg)) {
                throw new \RuntimeException('Usercp configs empty.');
            }
            if (!array_key_exists($id, $cfg)) {
                throw new \RuntimeException('Invalid id.');
            }
            unset($cfg[$id]);
            $this->saveConfig($cfg);
            message('success', 'Changes successfully saved!');
        } catch (\Exception $ex) {
            message('error', $ex->getMessage());
        }
    }

    private function upsertEntry(bool $isEdit): void
    {
        try {
            $cfg = loadConfig('usercp');
            if (!is_array($cfg)) {
                throw new \RuntimeException('Usercp configs empty.');
            }

            if ($isEdit && !isset($_POST['usercp_id'])) {
                throw new \RuntimeException('Please fill all the form fields.');
            }
            if (!isset($_POST['usercp_type'], $_POST['usercp_phrase'], $_POST['usercp_link'])) {
                throw new \RuntimeException('Please fill all the form fields.');
            }
            if (!in_array($_POST['usercp_type'], ['internal', 'external'], true)) {
                throw new \RuntimeException('Link type is not valid.');
            }
            if (!in_array($_POST['usercp_visibility'], ['user', 'guest', 'always'], true)) {
                throw new \RuntimeException('Link visibility is not a valid option.');
            }

            $newElementData = [
                'active' => ($_POST['usercp_status'] ?? '0') == 1,
                'type' => (string) $_POST['usercp_type'],
                'phrase' => (string) $_POST['usercp_phrase'],
                'link' => (string) $_POST['usercp_link'],
                'icon' => (string) ($_POST['usercp_icon'] ?? 'usercp_default.png'),
                'visibility' => (string) $_POST['usercp_visibility'],
                'newtab' => ($_POST['usercp_newtab'] ?? '0') == 1,
                'order' => (int) ($_POST['usercp_order'] ?? 0),
            ];

            if ($isEdit) {
                $cfg[(string) $_POST['usercp_id']] = $newElementData;
            } else {
                $cfg[] = $newElementData;
            }

            usort($cfg, static fn(array $a, array $b): int => ((int) ($a['order'] ?? 0)) - ((int) ($b['order'] ?? 0)));
            $this->saveConfig($cfg);
            message('success', $isEdit ? 'Changes successfully saved!' : 'Usercp successfully updated!');
        } catch (\Exception $ex) {
            message('error', $ex->getMessage());
        }
    }

    /** @param array<int|string,mixed> $cfg */
    private function saveConfig(array $cfg): void
    {
        $json = json_encode($cfg, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $cfgFile = fopen(__PATH_CONFIGS__ . 'usercp-menu.json', 'wb');
        if (!$cfgFile) {
            throw new \RuntimeException('There was a problem opening the usercp file.');
        }
        fwrite($cfgFile, $json);
        fclose($cfgFile);
    }
}

