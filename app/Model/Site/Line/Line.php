<?php

namespace Vici\Model\Site\Line;

class Line
{
    public int $id;
    public ?string $kind = null;
    public ?string $owner = null;
    public ?string $attribution = null;
    public ?int $uploaderId = null;
    public ?int $licenseId = null;
    public array $coordinates = [];
}
