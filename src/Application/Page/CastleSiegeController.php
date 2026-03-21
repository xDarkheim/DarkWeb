<?php

declare(strict_types=1);

namespace Darkheim\Application\Page;

use Darkheim\Application\CastleSiege\CastleSiege;
use Darkheim\Infrastructure\View\ViewRenderer;

final class CastleSiegeController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        try {
            $castleSiege = new CastleSiege();
            $siegeData   = $castleSiege->siegeData();

            if (!is_array($siegeData)) {
                throw new \Exception(lang('error_103'));
            }
            if (!$castleSiege->moduleEnabled()) {
                throw new \Exception(lang('error_47'));
            }

            $castleData = is_array($siegeData['castle_data'] ?? null) ? $siegeData['castle_data'] : [];
            $ownerAlliance = is_array($siegeData['castle_owner_alliance'] ?? null) ? $siegeData['castle_owner_alliance'] : [];
            $registeredGuilds = is_array($siegeData['registered_guilds'] ?? null) ? $siegeData['registered_guilds'] : [];
            $schedule = is_array($siegeData['schedule'] ?? null) ? $siegeData['schedule'] : [];

            $ownerGuild = $ownerAlliance[0] ?? null;
            $showCastleOwner = $castleSiege->showCastleOwner()
                && (int)($castleData[_CLMN_MCD_OCCUPY_] ?? 0) === 1
                && is_array($ownerGuild);

            $owner = null;
            if ($showCastleOwner) {
                $owner = [
                    'logo'   => returnGuildLogo((string)($ownerGuild['G_Mark'] ?? ''), 80),
                    'name'   => guildProfile((string)($ownerGuild['G_Name'] ?? '')),
                    'master' => playerProfile((string)($ownerGuild['G_Master'] ?? '')),
                ];
            }

            $ownerAllianceRows = [];
            if ($showCastleOwner && $castleSiege->showCastleOwnerAlliance() && count($ownerAlliance) > 1) {
                foreach ($ownerAlliance as $idx => $guild) {
                    if ($idx === 0 || !is_array($guild)) {
                        continue;
                    }
                    $ownerAllianceRows[] = [
                        'logo'   => returnGuildLogo((string)($guild['G_Mark'] ?? ''), 22),
                        'name'   => guildProfile((string)($guild['G_Name'] ?? '')),
                        'master' => playerProfile((string)($guild['G_Master'] ?? '')),
                    ];
                }
            }

            $showBattleCountdown = $castleSiege->showBattleCountdown();
            $showCastleInformation = $castleSiege->showCastleInformation();
            $showCurrentStage = $showCastleInformation && $castleSiege->showCurrentStage() && is_array($siegeData['current_stage'] ?? null);
            $showNextStage = $showCastleInformation && $castleSiege->showNextStage() && is_array($siegeData['next_stage'] ?? null);
            $showBattleDuration = $showCastleInformation && $castleSiege->showBattleDuration();

            $currentStageTitle = $showCurrentStage ? (string)($siegeData['current_stage']['title'] ?? '') : '';
            $nextStageTitle = $showNextStage ? (string)($siegeData['next_stage']['title'] ?? '') : '';
            $nextStageCountdown = $showNextStage ? (string)($siegeData['next_stage_countdown'] ?? '') : '';
            $battleDuration = $showBattleDuration ? (string)($siegeData['warfare_duration'] ?? '') : '';

            $castleTaxRateStore = (int)($castleData[_CLMN_MCD_TRS_] ?? 0);
            $castleTaxRateChaos = (int)($castleData[_CLMN_MCD_TRC_] ?? 0);
            $castleTaxRateHunt = (int)($castleData[_CLMN_MCD_THZ_] ?? 0);
            $castleMoney = number_format((float)($castleData[_CLMN_MCD_MONEY_] ?? 0));

            $showRegisteredGuilds = $castleSiege->showRegisteredGuilds() && $registeredGuilds !== [];
            $registeredGuildRows = [];
            if ($showRegisteredGuilds) {
                foreach ($registeredGuilds as $idx => $guild) {
                    if (!is_array($guild)) {
                        continue;
                    }
                    $registeredGuildRows[] = [
                        'num'     => str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT),
                        'logo'    => returnGuildLogo((string)($guild['G_Mark'] ?? ''), 22),
                        'name'    => guildProfile((string)($guild['G_Name'] ?? '')),
                        'master'  => playerProfile((string)($guild['G_Master'] ?? '')),
                        'score'   => number_format((float)($guild['G_Score'] ?? 0)),
                        'members' => (int)($guild['member_count'] ?? 0),
                    ];
                }
            }

            $showSchedule = $castleSiege->showSchedule() && $schedule !== [];
            $currentStageTitleForCompare = (string)($siegeData['current_stage']['title'] ?? '');
            $scheduleRows = [];
            if ($showSchedule) {
                foreach ($schedule as $stage) {
                    if (!is_array($stage)) {
                        continue;
                    }
                    $stageTitle = (string)($stage['title'] ?? '');
                    $scheduleRows[] = [
                        'title'      => $stageTitle,
                        'start'      => $castleSiege->friendlyDateFormat((int)($stage['start_timestamp'] ?? 0)),
                        'end'        => $castleSiege->friendlyDateFormat((int)($stage['end_timestamp'] ?? 0)),
                        'isCurrent'  => $currentStageTitleForCompare !== '' && $currentStageTitleForCompare === $stageTitle,
                    ];
                }
            }

            $this->view->render('castlesiege', compact(
                'showCastleOwner',
                'owner',
                'ownerAllianceRows',
                'showBattleCountdown',
                'showCastleInformation',
                'showCurrentStage',
                'showNextStage',
                'showBattleDuration',
                'currentStageTitle',
                'nextStageTitle',
                'nextStageCountdown',
                'battleDuration',
                'castleTaxRateStore',
                'castleTaxRateChaos',
                'castleTaxRateHunt',
                'castleMoney',
                'showRegisteredGuilds',
                'registeredGuildRows',
                'showSchedule',
                'scheduleRows'
            ));
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}
