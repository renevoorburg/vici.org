<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class RecentlyChangedPage extends PageRenderer
{

    private Session $session;
    private string $template = 'sitelist.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $db = $session->getDBConnector();
        $siteRepo = new \Vici\Model\Site\SiteRepository($db);
        $sites = $siteRepo->getRecentlyChanged();
        $this->assign('sites', $sites);
    }

}