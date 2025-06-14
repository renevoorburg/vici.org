<?php

namespace Vici\Page;

use Smarty;

class PageRenderer extends Smarty
{
    public function __construct(string $template = null, string $language = null)
    {
        parent::__construct();

        $this->setTemplateDir(__DIR__ . '/../../templates');
        $this->setCompileDir(__DIR__ . '/../../var/templates_compile');
        $this->setCacheDir(__DIR__ . '/../../var/templates_cache');
        $this->setConfigDir(__DIR__ . '/../../config/smarty');

        if ($template !== null) {
            $this->setTemplate($template);
        }
        
        if ($language !== null) {
            $this->setLanguage($language);
        }

        $this->setBaseTemplateVars();

    }



    public function setTemplate(string $template): self
    {
        $this->assign('template', $template);
        return $this;
    }
    
    public function setLanguage(string $language): self
    {
        $this->assign('language', $language);
        return $this;
    }
    
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if ($template === null) {
            $template = $this->getTemplateVars('template');
            if ($template === null) {
                throw new \Exception('No template specified. Use setTemplate() or provide a template parameter.');
            }
        }
        
        parent::display($template, $cache_id, $compile_id, $parent);
    }

    private function setBaseTemplateVars()
    {
        $this->assign('sitesubtitle', "archeologische atlas");
    }
}
