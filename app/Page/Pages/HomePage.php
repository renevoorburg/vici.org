<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\I18n\Translator;

class HomePage extends PageRenderer
{

    private Translator $translator;
    private string $template = 'home.tpl';

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        parent::__construct($this->template, $translator);
        $this->assignTranslatedTemplateVars($this->template);
        
    }

}