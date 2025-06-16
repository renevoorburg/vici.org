<?php

namespace Vici\Page;

use Smarty;
use Vici\Session\Session;

class PageRenderer extends Smarty
{

    private Session $session;
    protected string $baseTemplate = 'base.tpl';
    
    public function __construct(string $template, Session $session)
    {
        parent::__construct();

        $this->setTemplateDir(__DIR__ . '/../../templates');
        $this->setCompileDir(__DIR__ . '/../../var/templates_compile');
        $this->setCacheDir(__DIR__ . '/../../var/templates_cache');
        $this->setConfigDir(__DIR__ . '/../../config/smarty');

        $this->session = $session;
        
        $this->setTemplate($template);
        $this->assignTranslatedTemplateVars($this->baseTemplate);
        $this->assign('availableLanguages', $this->session->getAvailableLanguages());
        if (isset($_ENV['VICIBASE'])) {
            $this->assign('vicibase', $_ENV['VICIBASE']);
        }
        if ($this->session->hasUser()) {
            $this->assign('username', $this->session->getUser()->getName());
        }
            
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

    public function setBaseTemplate(string $baseTemplate): self
    {
        $this->baseTemplate = $baseTemplate;
        return $this;
    }

    protected function assignTranslatedTemplateVars(string $template)
    {
        $baseVars = $this->getDefaultTemplateVars($template);
        
        foreach ($baseVars as $var => $defaultValue) {
            $this->assign($var, $this->session->translator->get($defaultValue));
        }
    }

    private function getDefaultTemplateVars(string $template): array 
    {
        $templateBaseName = pathinfo($template, PATHINFO_FILENAME);
        $cacheFile = $this->getCacheDir() . '/' . $templateBaseName . '_template_vars.php';

        $baseVars = [];
        $templateFile = $this->getTemplateDir()[0] . '/' . $template;
        if (!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($templateFile)) {
            $baseVars = $this->createDefaultTemplateVarsCache($templateFile, $cacheFile);
        } else {
            $baseVars = include($cacheFile);
        }
        return $baseVars;
    }
    
    private function createDefaultTemplateVarsCache(string $templateFile, string $cacheFile): array
    {
        $baseVars = [];
        $templateContent = file_get_contents($templateFile);
        
        // Zoek alle Smarty variabelen met standaardwaarden
        preg_match_all('/{\$([\w_]+)\|default:"([^"]+)"}/', $templateContent, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $baseVars[$match[1]] = $match[2];
        }
        file_put_contents($cacheFile, '<?php return ' . var_export($baseVars, true) . ';');
        return $baseVars;
    }
}
