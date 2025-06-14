<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;

class HomePage extends PageRenderer
{
    public function __construct(string $language = null)
    {
        parent::__construct('home.tpl', $language);
        
    }
}