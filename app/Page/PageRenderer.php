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
    }

    /**
     * Set the template to be rendered
     * 
     * @param string $template Template name
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->assign('template', $template);
        return $this;
    }
    
    /**
     * Set the language for the page
     * 
     * @param string $language Language code
     * @return self
     */
    public function setLanguage(string $language): self
    {
        $this->assign('language', $language);
        return $this;
    }
    
    /**
     * Display the page using the template set with setTemplate()
     * 
     * @param string|null $template Optional template to override the one set with setTemplate()
     * @param string|null $cache_id Optional cache ID
     * @param string|null $compile_id Optional compile ID
     * @param Smarty_Internal_Template|null $parent Optional parent template
     * @return void
     */
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
}
