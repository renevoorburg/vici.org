<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class RecentlyChangedPage extends PageRenderer
{

    private Session $session;
    private string $template = 'recently.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $db = $session->getDBConnector();
        $siteRepo = new \Vici\Model\Site\SiteRepository($db);
        $imageRepo = new \Vici\Model\Site\Image\ImageRepository($db);

        $sites = iterator_to_array($siteRepo->findRecentlyChanged());
        $thumbs = $imageRepo->findDefaultThumbs(array_map(fn($s) => $s->id, $sites));

        $items = [];
        foreach ($sites as $site) {
            $site->thumbPath = $thumbs[$site->id] ?? null;
            $items[] = ['type' => 'site', 'date' => $site->updateDate, 'item' => $site, 'metaMode' => 'changed'];
        }

        $this->assign('items', $items);
        $this->assign('search_results_label', $this->session->translator->get('Recently edited'));
    }

}