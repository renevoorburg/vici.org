<?php

namespace Vici\I18n;

class Translator {
    private array $strings = [];
    private string $language;
    private static string $basePath = __DIR__ . '/../../lang';

    public function __construct(string $lang) {
        $this->language = $lang;
        $file = self::$basePath . "/lang_$lang.php";
        if (file_exists($file)) {
            $this->strings = include $file;
        }
    }

    public function get(string $key): string {
        return $this->strings[$key] ?? $key;
    }
    
    /**
     * Get all translations or a subset as an array
     * 
     * @param array|null $keys Specific keys to include, or null for all
     * @param string|null $prefix Only include keys starting with this prefix
     * @return array
     */
    public function getTranslationsArray(?array $keys = null, ?string $prefix = null): array
    {
        $result = [];
        if ($keys !== null) {
            foreach ($keys as $key) {
                $result[$key] = $this->get($key);
            }
        }
        if ($prefix !== null) {
            foreach ($this->strings as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $result[$key] = $value;
                }
            }
        }
        if ($result != []) {
            return $result;
        }
        return $this->strings;
    }
    
    /**
     * Get translations as JSON for use in JavaScript
     * 
     * @param array|null $keys Specific keys to include, or null for all
     * @param string|null $prefix Only include keys starting with this prefix
     * @param bool $asScriptTag Whether to wrap the JSON in a script tag
     * @return string
     */
    public function getTranslationsJson(?array $keys = null, ?string $prefix = null, bool $asScriptTag = false): string
    {
        $translations = $this->getTranslationsArray($keys, $prefix);
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($asScriptTag) {
            return sprintf(
                '<script>window.viciTranslations = window.viciTranslations || {}; 
                window.viciTranslations["%s"] = %s;</script>', 
                $this->language, 
                $json
            );
        }
        
        return $json;
    }
    
    /**
     * Get all available languages based on language files in the lang directory
     * 
     * @return array List of available language codes
     */
    public static function getAvailableLanguages(): array
    {
        $languages = [];
        $files = glob(self::$basePath . '/lang_*.php');
        
        foreach ($files as $file) {
            // Extract language code from filename (lang_en.php -> en)
            if (preg_match('/lang_([a-z]{2})\.php$/', $file, $matches)) {
                $languages[] = $matches[1];
            }
        }
        
        return $languages;
    }
}