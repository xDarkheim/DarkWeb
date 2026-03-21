<?php

declare(strict_types=1);

use Darkheim\Application\Subpage\DonationPaypalSubpageController;
use Darkheim\Application\Subpage\LanguageSwitchSubpageController;
use Darkheim\Application\Subpage\ProfileGuildSubpageController;
use Darkheim\Application\Subpage\ProfilePlayerSubpageController;
use Darkheim\Application\Page\RankingsSectionController;
use Darkheim\Application\Subpage\Usercp\AddStatsSubpageController;
use Darkheim\Application\Subpage\Usercp\BuyZenSubpageController;
use Darkheim\Application\Subpage\Usercp\ClearPkSubpageController;
use Darkheim\Application\Subpage\Usercp\ClearSkillTreeSubpageController;
use Darkheim\Application\Subpage\Usercp\MyAccountSubpageController;
use Darkheim\Application\Subpage\Usercp\MyEmailSubpageController;
use Darkheim\Application\Subpage\Usercp\MyPasswordSubpageController;
use Darkheim\Application\Subpage\Usercp\ResetStatsSubpageController;
use Darkheim\Application\Subpage\Usercp\ResetSubpageController;
use Darkheim\Application\Subpage\Usercp\UnstickSubpageController;
use Darkheim\Application\Subpage\Usercp\VoteSubpageController;

/**
 * Subpage route registry.
 *
 * Key format: "{page}/{subpage}".
 */
return [
    'donation/paypal' => [
        'module_config' => 'donation-paypal',
        'controller' => DonationPaypalSubpageController::class,
    ],
    'language/switch' => [
        'module_config' => null,
        'controller' => LanguageSwitchSubpageController::class,
    ],
    'profile/guild' => [
        'module_config' => 'profiles',
        'controller' => ProfileGuildSubpageController::class,
    ],
    'profile/player' => [
        'module_config' => 'profiles',
        'controller' => ProfilePlayerSubpageController::class,
    ],
    'rankings/gens' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/grandresets' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/guilds' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/killers' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/level' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/master' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/online' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/resets' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'rankings/votes' => [
        'module_config' => 'rankings',
        'controller' => RankingsSectionController::class,
    ],
    'usercp/addstats' => [
        'module_config' => 'usercp.addstats',
        'controller' => AddStatsSubpageController::class,
    ],
    'usercp/buyzen' => [
        'module_config' => 'usercp.buyzen',
        'controller' => BuyZenSubpageController::class,
    ],
    'usercp/clearpk' => [
        'module_config' => 'usercp.clearpk',
        'controller' => ClearPkSubpageController::class,
    ],
    'usercp/clearskilltree' => [
        'module_config' => 'usercp.clearskilltree',
        'controller' => ClearSkillTreeSubpageController::class,
    ],
    'usercp/myaccount' => [
        'module_config' => 'usercp.myaccount',
        'controller' => MyAccountSubpageController::class,
    ],
    'usercp/myemail' => [
        'module_config' => 'usercp.myemail',
        'controller' => MyEmailSubpageController::class,
    ],
    'usercp/mypassword' => [
        'module_config' => 'usercp.mypassword',
        'controller' => MyPasswordSubpageController::class,
    ],
    'usercp/reset' => [
        'module_config' => 'usercp.reset',
        'controller' => ResetSubpageController::class,
    ],
    'usercp/resetstats' => [
        'module_config' => 'usercp.resetstats',
        'controller' => ResetStatsSubpageController::class,
    ],
    'usercp/unstick' => [
        'module_config' => 'usercp.unstick',
        'controller' => UnstickSubpageController::class,
    ],
    'usercp/vote' => [
        'module_config' => 'usercp.vote',
        'controller' => VoteSubpageController::class,
    ],
];

