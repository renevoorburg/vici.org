<?php

namespace Vici\Model\Site;

use Vici\Model\Site\SiteType;
use Vici\Model\Site\Locale\LocaleCollection;
use Vici\Model\Site\Point;
use Vici\Model\Site\Toponym;
use Vici\Model\Site\Period;

class Site
{
    public int $id;
    public string $defaultTitle;
    public string $defaultSummary;
    public SiteType $type;
    public Point $representativeLocation;
    public LocaleCollection $locales;
    public Toponym $toponym;
    public Period $period;
    public bool $isVisible = true;
    public bool $isPublished = true;


    /** @var Image[] */
    public array $images = [];


}
