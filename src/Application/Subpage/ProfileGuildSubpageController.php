<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage;

use Darkheim\Application\Profile\ProfileRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ProfileGuildSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!mconfig('active')) {
            inline_message('error', lang('error_47', true));
            return;
        }

        $request = (string) ($_GET['req'] ?? '');
        if ($request === '') {
            inline_message('error', lang('error_25', true));
            return;
        }

        try {
            $profiles = new ProfileRepository();
            $profiles->setType('guild');
            $profiles->setRequest($request);
            $guildData = $profiles->data();

            $guildName = (string) ($guildData[1] ?? '');
            $guildScore = (float) ($guildData[3] ?? 0);
            $guildMaster = trim((string) ($guildData[4] ?? ''));

            $allMembers = array_values(array_filter(array_map('trim', explode(',', (string) ($guildData[5] ?? '')))));
            $regularMembers = array_values(array_filter($allMembers, static fn (string $m): bool => trim($m) !== $guildMaster));

            $memberRows = [];
            foreach ($regularMembers as $index => $memberName) {
                $memberRows[] = [
                    'num'  => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'name' => playerProfile(trim($memberName)),
                    'role' => 'Member',
                ];
            }

            $this->view->render('subpages/profile/guild', [
                'guildLogoHtml'     => returnGuildLogo((string) ($guildData[2] ?? ''), 80),
                'guildName'         => htmlspecialchars($guildName, ENT_QUOTES, 'UTF-8'),
                'guildMasterHtml'   => playerProfile($guildMaster),
                'memberCount'       => count($allMembers),
                'guildScore'        => number_format($guildScore),
                'memberRows'        => $memberRows,
                'memberRowsCount'   => count($memberRows),
                'hasMembers'        => $memberRows !== [],
            ]);
        } catch (\Exception $e) {
            inline_message('error', $e->getMessage());
        }
    }
}

