<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;

class HomePage extends PageRenderer
{
    public function __construct(string $language = null)
    {
        // Roep de parent constructor aan met de specifieke template
        parent::__construct('base.tpl', $language);
        
    }
}