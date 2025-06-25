<?php

namespace Vici\Model\Site;

class License
{
    public int $id;
    public string $title;      // Volledige naam/titel van de licentie
    public ?string $url;
    public ?bool $isUserSelectable;
    public ?string $abbreviation; // Korte weergave, bijvoorbeeld 'CC0 1.0'
}
