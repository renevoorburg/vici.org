<?php

namespace Vici\Model\Site;

use Vici\Model\Site\SiteType;
use Vici\Model\Site\Locale\LocaleCollection;
use Vici\Model\Site\Point;
use Vici\Model\Site\Toponym;
use Vici\Model\Site\Period;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\User\User;
use Vici\Model\Site\Identifier\Identifier;
use Vici\Model\Site\Identifier\IdentifierCollection;
use Vici\Model\Site\Line\LineCollection;

class Site
{
    public int $id;
    public string $defaultTitle;
    public string $defaultSummary;
    public SiteType $type;
    public Point $representativeLocation;
    public LineCollection $lines;
    public LocaleCollection $locales;
    public ImageCollection $images;
    public Toponym $toponym;
    public Period $period;
    public User $creator;
    public User $updater;
    public string $createDate;
    public string $updateDate;
    public IdentifierCollection $identifiers;
    public bool $isVisible = true;
    public bool $isPublished = true;




}
