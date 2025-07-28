<?php

namespace Vici\Model\Site\Image;

use Vici\Model\User\User;
use Vici\Model\License\License;
use Vici\Model\Site\SiteCollection;

class Image
{
    public int $id;
    public string $title;
    public string $description;
    public string $language;
    public string $filepath;
    public bool $isPublished = true;
    public User $uploader;
    public bool $isOwnWork = false;
    public string $source;
    public string $creator;
    public License $license;
    public string $dateAdded;
    public int $width;
    public int $height;
    public string $md5sum;
    public SiteCollection $sites;

    public function getCreator(): string
    {
        return $this->isOwnWork ? $this->uploader->getRealName() : $this->creator;
    }

}
