<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class ItemPage extends PageRenderer
{

    private Session $session;
    private string $template = 'item.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('js_translations', $this->session->translator->getTranslationsJson(['more', 'show on map'], 'markerdef.'));

        $db = $session->getDBConnector();
        $siteRepo = new \Vici\Model\Site\SiteRepository($db);
        $site = $siteRepo->getById($session->getRequestedItem());

        $this->assign('id', $site->id);
        $this->assign('annotation', $site->locales[$session->getLanguage()]->description);
        $this->assign('period_start_qualifier', $site->period->startQualifier);
        $this->assign('period_end_qualifier', $site->period->endQualifier);
        $this->assign('lat', $site->representativeLocation->latitude);
        $this->assign('lng', $site->representativeLocation->longitude);
        $this->assign('country_name', $site->toponym->countryName);
        $this->assign('place_name', $site->toponym->placeName);
        $this->assign('q', $site->representativeLocation->qualifier);
        $this->assign('classification_description', $this->session->translator->get("markerdef." . $site->type->id));  
        $this->assign('classification_title', $this->session->translator->get($site->type->title));
        $this->assign('isVisible', $site->isVisible);
    }

}