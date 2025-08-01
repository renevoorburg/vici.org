<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class SearchPage extends PageRenderer
{

    private Session $session;
    private string $template = 'search.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $db = $session->getDBConnector();
        $siteRepo = new \Vici\Model\Site\SiteRepository($db);
        if (mb_strlen($q) < 2) {
            $sites = [];
        } else {
            $sites = $siteRepo->search($q);
        }
        $this->assign('sites', $sites);
        $this->assign('query', $q);

    }

}