<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class HomePage extends PageRenderer
{

    private Session $session;
    private string $template = 'home.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('js_translations', $this->session->translator->getTranslationsJson(['more', 'show on map', 'Close', 'more info'], 'markerdef.'));
        $this->assign('title', $this->session->translator->get('archaeological atlas of antiquity'));
        $this->assign('meta_description', $this->session->translator->get('Interactive archaeological atlas of antiquity: explore hundreds of thousands of archaeological sites, monuments and finds on an interactive map.'));
    }

}