<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Vote\Vote;
use Darkheim\Application\Vote\VoteSiteRepository;
use Darkheim\Infrastructure\View\ViewRenderer;

final class VoteSubpageController
{
    private ViewRenderer $view;

    public function __construct(?ViewRenderer $view = null)
    {
        $this->view = $view ?? new ViewRenderer();
    }

    public function render(): void
    {
        if (!isLoggedIn()) {
            redirect(1, 'login');
            return;
        }

        try {
            if (!mconfig('active')) {
                throw new \Exception(lang('error_47', true));
            }

            $vote = new Vote();
            $voteSiteRepository = new VoteSiteRepository();

            if (isset($_POST['submit'])) {
                try {
                    $vote->setUserid($_SESSION['userid']);
                    $vote->setIp((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
                    $vote->setVotesiteId((string) ($_POST['voting_site_id'] ?? ''));
                    $vote->vote();
                } catch (\Exception $ex) {
                    message('error', $ex->getMessage());
                }
            }

            $siteRows = [];
            $voteSites = $voteSiteRepository->findAll();
            if (is_array($voteSites)) {
                foreach ($voteSites as $site) {
                    if (!is_array($site)) {
                        continue;
                    }
                    $siteRows[] = [
                        'id'     => (string) ($site['votesite_id'] ?? ''),
                        'title'  => htmlspecialchars((string) ($site['votesite_title'] ?? ''), ENT_QUOTES, 'UTF-8'),
                        'reward' => htmlspecialchars((string) ($site['votesite_reward'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    ];
                }
            }

            $this->view->render('subpages/usercp/vote', [
                'pageTitle'   => lang('module_titles_txt_7', true),
                'cardTitle'   => lang('module_titles_txt_7', true),
                'headerTitle' => lang('vfc_txt_1', true),
                'headerReward'=> lang('vfc_txt_2', true),
                'buttonLabel' => lang('vfc_txt_3', true),
                'siteRows'    => $siteRows,
            ]);
        } catch (\Exception $ex) {
            inline_message('error', $ex->getMessage());
        }
    }
}

