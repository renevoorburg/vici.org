<?php

namespace Vici\Model\Site;

class Site
{
    public int $id;
    public SiteType $type;
    /** @var SiteLocale[] */
    public array $locales = [];
    /** @var Image[] */
    public array $images = [];
    // ... overige metadata indien nodig
}
