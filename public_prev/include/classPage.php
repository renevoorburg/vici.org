<?php

require __DIR__ . '/../vendor/autoload.php';

class Page extends Smarty
{
    const DEBUG = false;

    public function __construct()
    {
        parent::__construct();
        $this->error_reporting = E_ALL & ~E_NOTICE;

        $this->setTemplateDir(__DIR__ . '/../smarty/templates');
        $this->setCompileDir(__DIR__ . '/../smarty/templates_c');
        $this->setCacheDir(__DIR__ . '/../smarty/cache');
        $this->setConfigDir(__DIR__ . '/../smarty/configs');

    }
}
