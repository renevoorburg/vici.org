<?php

namespace Vici\Page;

use Smarty;

class Page extends Smarty
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplateDir(__DIR__ . '/../../templates');
        $this->setCompileDir(__DIR__ . '/../../var/templates_compile');
        $this->setCacheDir(__DIR__ . '/../../var/templates_cache');
        $this->setConfigDir(__DIR__ . '/../../config/smarty');

    }
}
