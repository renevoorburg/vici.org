<?php

namespace Vici\Model\Site\Locale;

class Locale
{
    public string $language;
    public string $title;
    public string $summary;
    public string $description;
    public ?int $editorId = null;
    public ?string $editorName = null;
    public ?string $editDate = null;
    public ?string $translatedTitle = null;
    public ?string $translatedSummary = null;
}
