<?php

namespace Vici\Model\Site;

class SiteType
{
    public int $id;
    public string $title;
    public ?string $shortDescription;
    public ?string $longDescription;
    public ?int $parentId;
    public ?SiteType $parent = null;
    public int $sortOrder;
    public bool $isContemporary;
}
