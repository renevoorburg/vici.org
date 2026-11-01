<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserStatistics;

class StatisticsPage extends PageRenderer
{
    private string $template = 'statistics.tpl';

    public function __construct(Session $session)
    {
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $user = $session->getUser();
        $stats = new UserStatistics($session->getDBConnector());
        $t = fn($key) => $session->translator->get($key);

        $raw = $stats->forUser($user->getId());
        $rows = [
            ['label' => $t('Sites added'),        'note' => $t('points you created'),            'stats' => $raw['sites_added']],
            ['label' => $t('Sites edited'),       'note' => $t('points where you are the most recent editor'), 'stats' => $raw['sites_edited']],
            ['label' => $t('Annotations edited'), 'note' => $t('texts where you are the most recent editor'), 'stats' => $raw['texts']],
            ['label' => $t('Images added'),       'note' => $t('images you uploaded'),           'stats' => $raw['images']],
            ['label' => $t('Lines added'),        'note' => $t('lines you uploaded'),            'stats' => $raw['lines']],
        ];

        $this->assign('rows', $rows);
        $this->assign('stats_display_name', $user->getRealName());
    }
}
