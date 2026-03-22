<?php

declare(strict_types=1);

namespace Darkheim\Application\Admincp;

use Darkheim\Application\Auth\Common;
use Darkheim\Application\Character\Character;
use Darkheim\Domain\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Database\Connection;
use Darkheim\Infrastructure\View\ViewRenderer;

final class EditCharacterController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!isset($_GET['name'])) {
            \Darkheim\Application\View\MessageRenderer::toast('error', 'Please provide a valid character name.');
            return;
        }

        try {
            if (!Validator::AlphaNumeric($_GET['name'])) {
                throw new \RuntimeException('Invalid character name.');
            }
            $character = new Character();
            if (!$character->CharacterExists($_GET['name'])) {
                throw new \RuntimeException('Character does not exist.');
            }

            if (isset($_POST['characteredit_submit'])) {
                $this->handleSave($character);
            }

            $charData = $character->CharacterData($_GET['name']);
            if (!$charData) {
                throw new \RuntimeException('Could not retrieve character information (invalid character).');
            }

            $db     = Connection::Database('MuOnline');
            $admincpUrl = new AdmincpUrlGenerator();
            $common = new Common();

            $mLinfo = defined('_TBL_MASTERLVL_')
                ? $db->query_fetch_single('SELECT * FROM ' . _TBL_MASTERLVL_ . ' WHERE ' . _CLMN_ML_NAME_ . ' = ?', [$charData[_CLMN_CHR_NAME_]])
                : null;

            $custom       = BootstrapContext::runtimeState()?->customConfig() ?? [];
            $classOptions = [];
            if (isset($custom['character_class']) && is_array($custom['character_class'])) {
                foreach ($custom['character_class'] as $classId => $classInfo) {
                    $classOptions[] = [
                        'id'       => (string) $classId,
                        'label'    => $classInfo[0] . ' (' . $classInfo[1] . ')',
                        'selected' => (string) $classId === (string) $charData[_CLMN_CHR_CLASS_],
                    ];
                }
            }

            $this->view->render('admincp/editcharacter', [
                'charName'       => (string) ($charData[_CLMN_CHR_NAME_] ?? ''),
                'accountId'      => (string) ($charData[_CLMN_CHR_ACCID_] ?? ''),
                'accountInfoUrl' => $admincpUrl->base('accountinfo&id=' . $common->retrieveUserID($charData[_CLMN_CHR_ACCID_])),
                'classOptions'   => $classOptions,
                'level'          => (string) ($charData[_CLMN_CHR_LVL_] ?? ''),
                'resets'         => defined('_CLMN_CHR_RSTS_') ? (string) ($charData[_CLMN_CHR_RSTS_] ?? '') : null,
                'gresets'        => defined('_CLMN_CHR_GRSTS_') ? (string) ($charData[_CLMN_CHR_GRSTS_] ?? '') : null,
                'zen'            => (string) ($charData[_CLMN_CHR_ZEN_] ?? ''),
                'lvlPoints'      => (string) ($charData[_CLMN_CHR_LVLUP_POINT_] ?? ''),
                'pkLevel'        => (string) ($charData[_CLMN_CHR_PK_LEVEL_] ?? ''),
                'str'            => (string) ($charData[_CLMN_CHR_STAT_STR_] ?? ''),
                'agi'            => (string) ($charData[_CLMN_CHR_STAT_AGI_] ?? ''),
                'vit'            => (string) ($charData[_CLMN_CHR_STAT_VIT_] ?? ''),
                'ene'            => (string) ($charData[_CLMN_CHR_STAT_ENE_] ?? ''),
                'cmd'            => (string) ($charData[_CLMN_CHR_STAT_CMD_] ?? ''),
                'hasMasterLevel' => defined('_TBL_MASTERLVL_') && is_array($mLinfo),
                'mlLevel'        => is_array($mLinfo) ? (string) ($mLinfo[_CLMN_ML_LVL_] ?? '') : '',
                'mlExp'          => is_array($mLinfo) ? (string) ($mLinfo[_CLMN_ML_EXP_] ?? '') : '',
                'mlNextExp'      => is_array($mLinfo) && defined('_CLMN_ML_NEXP_') ? (string) ($mLinfo[constant('_CLMN_ML_NEXP_')] ?? '') : null,
                'mlPoints'       => is_array($mLinfo) ? (string) ($mLinfo[_CLMN_ML_POINT_] ?? '') : '',
            ]);
        } catch (\Exception $ex) {
            echo '<h1 class="page-header">Edit Character</h1>';
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }

    private function handleSave(Character $character): void
    {
        try {
            $db     = Connection::Database('MuOnline');
            $common = new Common();

            if ($_POST['characteredit_name'] !== $_GET['name']) {
                throw new \RuntimeException('Invalid character name.');
            }
            if (!isset($_POST['characteredit_account'])) {
                throw new \RuntimeException('Invalid account name.');
            }
            if ($common->accountOnline($_POST['characteredit_account'])) {
                throw new \RuntimeException('The account is currently online.');
            }

            $fields = ['class', 'level', 'zen', 'lvlpoints', 'pklevel', 'str', 'agi', 'vit', 'ene', 'cmd'];
            foreach ($fields as $f) {
                if (!Validator::UnsignedNumber($_POST['characteredit_' . $f])) {
                    throw new \RuntimeException('All the entered values must be numeric.');
                }
            }
            foreach (['resets', 'gresets'] as $opt) {
                $postKey = 'characteredit_' . $opt;
                if (isset($_POST[$postKey]) && !Validator::UnsignedNumber($_POST[$postKey])) {
                    throw new \RuntimeException('All the entered values must be numeric.');
                }
            }

            $updateData = [
                'name'      => $_POST['characteredit_name'],
                'class'     => $_POST['characteredit_class'],
                'level'     => $_POST['characteredit_level'],
                'zen'       => $_POST['characteredit_zen'],
                'lvlpoints' => $_POST['characteredit_lvlpoints'],
                'pklevel'   => $_POST['characteredit_pklevel'],
                'str'       => $_POST['characteredit_str'],
                'agi'       => $_POST['characteredit_agi'],
                'vit'       => $_POST['characteredit_vit'],
                'ene'       => $_POST['characteredit_ene'],
                'cmd'       => $_POST['characteredit_cmd'],
            ];

            $query  = 'UPDATE ' . _TBL_CHR_ . ' SET '
                . _CLMN_CHR_CLASS_ . ' = :class,'
                . _CLMN_CHR_LVL_ . ' = :level,'
                . (isset($_POST['characteredit_resets']) ? _CLMN_CHR_RSTS_ . ' = :resets,' : '')
                . (isset($_POST['characteredit_gresets']) ? _CLMN_CHR_GRSTS_ . ' = :gresets,' : '')
                . _CLMN_CHR_ZEN_ . ' = :zen,'
                . _CLMN_CHR_LVLUP_POINT_ . ' = :lvlpoints,'
                . _CLMN_CHR_PK_LEVEL_ . ' = :pklevel,'
                . _CLMN_CHR_STAT_STR_ . ' = :str,'
                . _CLMN_CHR_STAT_AGI_ . ' = :agi,'
                . _CLMN_CHR_STAT_VIT_ . ' = :vit,'
                . _CLMN_CHR_STAT_ENE_ . ' = :ene,'
                . _CLMN_CHR_STAT_CMD_ . ' = :cmd'
                . ' WHERE ' . _CLMN_CHR_NAME_ . ' = :name';

            if (isset($_POST['characteredit_resets'])) {
                $updateData['resets'] = $_POST['characteredit_resets'];
            }
            if (isset($_POST['characteredit_gresets'])) {
                $updateData['gresets'] = $_POST['characteredit_gresets'];
            }

            if (!$db->query($query, $updateData)) {
                throw new \RuntimeException('Could not update character data.');
            }

            if (defined('_TBL_MASTERLVL_')) {
                foreach (['mlevel', 'mlexp', 'mlpoint'] as $f) {
                    if (!Validator::UnsignedNumber($_POST['characteredit_' . $f])) {
                        throw new \RuntimeException('All the entered values must be numeric.');
                    }
                }
                $mlData  = [
                    'name'   => $_POST['characteredit_name'],
                    'level'  => $_POST['characteredit_mlevel'],
                    'exp'    => $_POST['characteredit_mlexp'],
                    'points' => $_POST['characteredit_mlpoint'],
                ];
                $mlQuery = 'UPDATE ' . _TBL_MASTERLVL_ . ' SET '
                    . _CLMN_ML_LVL_ . ' = :level,'
                    . _CLMN_ML_EXP_ . ' = :exp,'
                    . (defined('_CLMN_ML_NEXP_') && isset($_POST['characteredit_mlnextexp']) ? constant('_CLMN_ML_NEXP_') . ' = :nextexp,' : '')
                    . _CLMN_ML_POINT_ . ' = :points'
                    . ' WHERE ' . _CLMN_ML_NAME_ . ' = :name';
                if (defined('_CLMN_ML_NEXP_') && isset($_POST['characteredit_mlnextexp'])) {
                    $mlData['nextexp'] = $_POST['characteredit_mlnextexp'];
                }
                $db->query($mlQuery, $mlData);
            }
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
        }
    }
}

