<?php

namespace Vici\Model\Site;

use Vici\Model\Site\Point;
use Vici\Model\Site\Toponym;
use Vici\Model\Site\Period;

class Site
{
    public int $id;
    public SiteType $type;
    public Point $representativeLocation;
    public Toponym $toponym;
    public string $visibility;
    public Period $period;
    public bool $isPublished = true;

    /** @var SiteLocale[] */
    public array $locales = [];

    /** @var Image[] */
    public array $images = [];

}
