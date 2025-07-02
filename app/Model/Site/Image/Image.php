<?php

namespace Vici\Model\Site;

use Vici\Model\Users\User;
use Vici\Model\License\License;

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

}
