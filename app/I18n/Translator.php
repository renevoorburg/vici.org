<?php

namespace Vici\I18n;

class Translator {
    private array $strings = [];

    public function __construct(string $lang, string $basePath = __DIR__ . '/../../lang') {
        $file = $basePath . "/lang_$lang.php";
        if (file_exists($file)) {
            $this->strings = include $file;
        }
    }

    public function get(string $key): string {
        return $this->strings[$key] ?? $key;
    }
}