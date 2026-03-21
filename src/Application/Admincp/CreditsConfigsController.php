<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Credits\CreditSystem;
use Darkheim\Infrastructure\View\ViewRenderer;

final class CreditsConfigsController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        $creditSystem = new CreditSystem();

        if (isset($_POST['new_submit'])) {
            try {
                $creditSystem->setConfigTitle($_POST['new_title']);
                $creditSystem->setConfigDatabase($_POST['new_database']);
                $creditSystem->setConfigTable($_POST['new_table']);
                $creditSystem->setConfigCreditsColumn($_POST['new_credits_column']);
                $creditSystem->setConfigUserColumn($_POST['new_user_column']);
                $creditSystem->setConfigUserColumnId($_POST['new_user_column_id']);
                $creditSystem->_configCheckOnline = $_POST['new_checkonline'];
                $creditSystem->_configDisplay     = $_POST['new_display'];
                $creditSystem->saveConfig();
                message('success', 'Configuration saved.');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        if (isset($_POST['edit_submit'])) {
            try {
                $creditSystem->setConfigId($_POST['edit_id']);
                $creditSystem->setConfigTitle($_POST['edit_title']);
                $creditSystem->setConfigDatabase($_POST['edit_database']);
                $creditSystem->setConfigTable($_POST['edit_table']);
                $creditSystem->setConfigCreditsColumn($_POST['edit_credits_column']);
                $creditSystem->setConfigUserColumn($_POST['edit_user_column']);
                $creditSystem->setConfigUserColumnId($_POST['edit_user_column_id']);
                $creditSystem->_configCheckOnline = $_POST['edit_checkonline'];
                $creditSystem->_configDisplay     = $_POST['edit_display'];
                $creditSystem->editConfig();
                message('success', 'Configuration updated.');
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        if (isset($_GET['delete'])) {
            try {
                $creditSystem->setConfigId($_GET['delete']);
                $creditSystem->deleteConfig();
            } catch (\Exception $ex) {
                message('error', $ex->getMessage());
            }
        }

        $isEditing  = isset($_GET['edit']);
        $editConfig = null;
        if ($isEditing) {
            $creditSystem->setConfigId($_GET['edit']);
            $editConfig = $creditSystem->showConfigs(true);
        }

        $configsList = $creditSystem->showConfigs();
        $configs     = [];
        if (is_array($configsList)) {
            foreach ($configsList as $data) {
                $configs[] = [
                    'id'           => (string) ($data['config_id'] ?? ''),
                    'title'        => (string) ($data['config_title'] ?? ''),
                    'dbDisplay'    => (string) config('SQL_DB_NAME', true),
                    'table'        => (string) ($data['config_table'] ?? ''),
                    'creditsCol'   => (string) ($data['config_credits_col'] ?? ''),
                    'userCol'      => (string) ($data['config_user_col'] ?? ''),
                    'userColId'    => (string) ($data['config_user_col_id'] ?? ''),
                    'checkOnline'  => (bool)   ($data['config_checkonline'] ?? false),
                    'display'      => (bool)   ($data['config_display'] ?? false),
                    'editUrl'      => admincp_base('creditsconfigs&edit=' . ($data['config_id'] ?? '')),
                    'deleteUrl'    => admincp_base('creditsconfigs&delete=' . ($data['config_id'] ?? '')),
                ];
            }
        }

        $this->view->render('admincp/creditsconfigs', [
            'isEditing'   => $isEditing,
            'editConfig'  => $editConfig,
            'dbName'      => (string) config('SQL_DB_NAME', true),
            'configs'     => $configs,
        ]);
    }
}

