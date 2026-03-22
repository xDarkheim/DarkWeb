<?php

declare(strict_types=1);

namespace Darkheim\Application\Subpage\Usercp;

use Darkheim\Application\Language\Translator;
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
        if (!\Darkheim\Application\Auth\SessionManager::websiteAuthenticated()) {
            \Darkheim\Infrastructure\Http\Redirector::go(1, 'login');
            return;
        }

        try {
            if (!\Darkheim\Infrastructure\Bootstrap\BootstrapContext::moduleValue('active')) {
                throw new \Exception(Translator::phrase('error_47'));
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
                    \Darkheim\Application\View\MessageRenderer::toast('error', $ex->getMessage());
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
                'pageTitle'   => Translator::phrase('module_titles_txt_7'),
                'cardTitle'   => Translator::phrase('module_titles_txt_7'),
                'headerTitle' => Translator::phrase('vfc_txt_1'),
                'headerReward'=> Translator::phrase('vfc_txt_2'),
                'buttonLabel' => Translator::phrase('vfc_txt_3'),
                'siteRows'    => $siteRows,
            ]);
        } catch (\Exception $ex) {
            \Darkheim\Application\View\MessageRenderer::inline('error', $ex->getMessage());
        }
    }
}

