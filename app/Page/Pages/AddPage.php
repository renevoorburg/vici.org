<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class AddPage extends PageRenderer
{
    private Session $session;
    private string $template = 'add.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
    }
}
