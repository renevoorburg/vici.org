<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;
use Vici\Model\User\UserBioRepository;
use Vici\Model\User\UserStatistics;
use Vici\Model\Site\SiteRepository;
use Vici\Model\Site\Image\ImageRepository;

class UserPage extends PageRenderer
{
    private string $template = 'user.tpl';

    public function __construct(Session $session)
    {
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $db = $session->getDBConnector();
        $userRepo = new UserRepository($db);
        $bioRepo = new UserBioRepository($db);

        $profileUser = $userRepo->findById($session->getRequestedItem());
        if ($profileUser === null || $profileUser->isDeleted()) {
            http_response_code(404);
            $this->assign('profile_found', false);
            $this->assign('title', $session->translator->get('User not found.'));
            return;
        }

        $bio = $bioRepo->get($profileUser->getId());
        $loggedIn = $session->hasUser();
        $isSelf = $loggedIn && $session->getUser()->getId() === $profileUser->getId();
        $isAdmin = $loggedIn && $session->getUser()->isAdmin();

        // anonymous visitors see the name, plus bio and links when 'public' is on
        $showFull = $loggedIn;
        $showPublic = $loggedIn || $bio->public;

        $this->assign('profile_found', true);
        $this->assign('profile_id', $profileUser->getId());
        $this->assign('profile_name', $profileUser->getRealName());
        $this->assign('profile_bio', $showPublic ? $bio->bio : '');
        $this->assign('profile_links', $showPublic ? $bio->getSafeLinks() : []);
        $this->assign('logged_in', $loggedIn);
        $this->assign('viewer_is_self', $isSelf);
        $this->assign('is_admin', $isAdmin);
        $levelLabels = [0 => 'Deleted', 1 => 'Locked', 2 => 'Unconfirmed', 3 => 'Confirmed', 4 => 'Trusted', 5 => 'Administrator'];
        $level = $profileUser->getLevel();
        $label = $session->translator->get($levelLabels[$level] ?? ('Level ' . $level));
        $this->assign('profile_level_label', "{$label} ({$level})");
        $this->assign('title', $profileUser->getRealName());

        if (!$showFull) {
            return;
        }
        $this->assign('can_contact', $bio->contact && !$isSelf);

        // GitHub-style heatmap: 53 columns of 7 days, ending today
        $stats = new UserStatistics($db);
        $daily = $stats->dailyContributions($profileUser->getId());
        $this->assign('heatmap_total', array_sum($daily));

        $today = new \DateTime('today');
        $day = (clone $today)->modify('-364 days')->modify('last sunday');
        $weeks = [];
        $week = [];
        $weekLabel = '';
        while ($day <= $today) {
            $key = $day->format('Y-m-d');
            $count = $daily[$key] ?? 0;
            $level = $count === 0 ? 0 : ($count < 5 ? 1 : ($count < 10 ? 2 : ($count < 20 ? 3 : 4)));
            $week[] = ['date' => $key, 'count' => $count, 'level' => $level];
            // GitHub-style: month label sits above the column containing the 1st
            if ((int)$day->format('j') === 1) {
                $weekLabel = $session->translator->get($day->format('M'));
            }
            $day->modify('+1 day');
            if ($day->format('N') === '7') {
                $weeks[] = ['label' => $weekLabel, 'days' => $week];
                $week = [];
                $weekLabel = '';
            }
        }
        if ($week) {
            $weeks[] = ['label' => $weekLabel, 'days' => $week];
        }
        $this->assign('heatmap_weeks', $weeks);

        // weekday labels for rows Mon/Wed/Fri (row 0 = Sunday, like GitHub)
        $dayLabels = array_fill(0, 7, '');
        foreach ([1 => 'Mon', 3 => 'Wed', 5 => 'Fri'] as $i => $name) {
            $dayLabels[$i] = $session->translator->get($name);
        }
        $this->assign('hm_day_labels', $dayLabels);

        // recent contributions, thumbnails eager-loaded to avoid N+1
        $siteRepo = new SiteRepository($db);
        $imageRepo = new ImageRepository($db);

        $sites = iterator_to_array($siteRepo->findByCreator($profileUser->getId(), 8));
        $thumbs = $imageRepo->findDefaultThumbs(array_map(fn($s) => $s->id, $sites));
        foreach ($sites as $site) {
            $site->thumbPath = $thumbs[$site->id] ?? null;
        }
        $this->assign('contrib_sites', $sites);
        $this->assign('contrib_images', iterator_to_array($imageRepo->findByUploader($profileUser->getId(), 8)));
    }
}
