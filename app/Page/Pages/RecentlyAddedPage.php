<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class RecentlyAddedPage extends PageRenderer
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

        $items = [];
        foreach ($siteRepo->findRecentlyAdded() as $site) {
            $items[] = ['type' => 'site', 'date' => $site->createDate, 'item' => $site];
        }
        foreach ($imageRepo->findRecentlyAdded(20) as $image) {
            $items[] = ['type' => 'image', 'date' => $image->dateAdded, 'item' => $image];
        }
        usort($items, fn($a, $b) => strcmp($b['date'], $a['date']));

        $this->assign('items', $items);
        $this->assign('search_results_label', $this->session->translator->get('Recently added'));
    }

}