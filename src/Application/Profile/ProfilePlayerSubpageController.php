<?php

declare(strict_types=1);

namespace Darkheim\Application\Profile;

use Darkheim\Application\Shared\Game\GameHelper;
use Darkheim\Application\Shared\Language\Translator;
use Darkheim\Application\Shared\UI\MessageRenderer;
use Darkheim\Domain\Validation\Validator;
use Darkheim\Infrastructure\Bootstrap\BootstrapContext;
use Darkheim\Infrastructure\Cache\CacheRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class ProfilePlayerSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (! BootstrapContext::moduleValue('active')) {
            MessageRenderer::inline('error', Translator::phrase('error_47'));
            return;
        }

        $request = (string) ($_GET['req'] ?? '');
        if ($request === '') {
            MessageRenderer::inline('error', Translator::phrase('error_25'));
            return;
        }

        try {
            $profiles = new ProfileRepository();
            $profiles->setType('player');
            $profiles->setRequest($request);
            $cData = $profiles->data();

            $playerName = (string) ($cData[1] ?? '');
            $classId    = (int) ($cData[2] ?? 0);
            $classMeta  = $GLOBALS['custom']['character_class'][$classId] ?? [];

            $onlineCharactersCache = new CacheRepository(__PATH_CACHE__)->load('online_characters.cache');
            $isOnline              = is_array($onlineCharactersCache) && in_array($playerName, $onlineCharactersCache, true);

            $className   = (string) ($classMeta[0] ?? '—');
            $classCss    = (string) ($classMeta[1] ?? '');
            $classAvatar = GameHelper::playerClassAvatar($classId, false);

            $hasCmd    = isset($classMeta['base_stats']['cmd']) && $classMeta['base_stats']['cmd'] > 0;
            $guildName = (string) ($cData[12] ?? '');
            $hasGuild  = Validator::hasValue($guildName);

            $level       = (float) ($cData[3] ?? 0);
            $masterLevel = (float) ($cData[14] ?? 0);
            $resets      = (float) ($cData[4] ?? 0);
            $grandResets = (float) ($cData[11] ?? 0);
            $pkKills     = (float) ($cData[10] ?? 0);

            $baseStats = [
                [
                    'label'    => 'Strength',
                    'icon'     => 'bi bi-lightning-charge-fill',
                    'barClass' => 'pf-stats-bar',
                    'percent'  => min(100, round(((float) ($cData[5] ?? 0)) / 32767 * 100)),
                    'value'    => number_format((float) ($cData[5] ?? 0)),
                ],
                [
                    'label'    => 'Agility',
                    'icon'     => 'bi bi-wind',
                    'barClass' => 'pf-stats-bar',
                    'percent'  => min(100, round(((float) ($cData[6] ?? 0)) / 32767 * 100)),
                    'value'    => number_format((float) ($cData[6] ?? 0)),
                ],
                [
                    'label'    => 'Vitality',
                    'icon'     => 'bi bi-heart-fill',
                    'barClass' => 'pf-stats-bar pf-bar-red',
                    'percent'  => min(100, round(((float) ($cData[7] ?? 0)) / 32767 * 100)),
                    'value'    => number_format((float) ($cData[7] ?? 0)),
                ],
                [
                    'label'    => 'Energy',
                    'icon'     => 'bi bi-magic',
                    'barClass' => 'pf-stats-bar pf-bar-blue',
                    'percent'  => min(100, round(((float) ($cData[8] ?? 0)) / 32767 * 100)),
                    'value'    => number_format((float) ($cData[8] ?? 0)),
                ],
            ];
            if ($hasCmd) {
                $baseStats[] = [
                    'label'    => 'Command',
                    'icon'     => 'bi bi-person-fill-up',
                    'barClass' => 'pf-stats-bar pf-bar-purple',
                    'percent'  => min(100, round(((float) ($cData[9] ?? 0)) / 32767 * 100)),
                    'value'    => number_format((float) ($cData[9] ?? 0)),
                ];
            }

            $this->view->render('subpages/profile/player', [
                'classCss'    => htmlspecialchars($classCss, ENT_QUOTES, 'UTF-8'),
                'classAvatar' => $classAvatar,
                'playerName'  => htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8'),
                'className'   => htmlspecialchars($className, ENT_QUOTES, 'UTF-8'),
                'onlineLabel' => $isOnline
                    ? '<span class="profile-badge online">' . Translator::phrase('profiles_txt_18') . '</span>'
                    : '<span class="profile-badge offline">' . Translator::phrase('profiles_txt_19') . '</span>',
                'level'          => number_format($level),
                'masterLevel'    => number_format($masterLevel),
                'hasResets'      => Validator::hasValue($resets),
                'resets'         => number_format($resets),
                'hasGrandResets' => Validator::hasValue($grandResets),
                'grandResets'    => number_format($grandResets),
                'pkKills'        => number_format($pkKills),
                'hasGuild'       => $hasGuild,
                'guildHtml'      => $hasGuild ? ProfileRenderer::guild($guildName) : '',
                'baseStats'      => $baseStats,
            ]);
        } catch (\Exception $e) {
            MessageRenderer::inline('error', $e->getMessage());
        }
    }
}
