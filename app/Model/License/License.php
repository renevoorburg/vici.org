<?php

namespace Vici\Model\License;

class License
{
    public int $id;
    public string $name;
    public string $shortName;
    public ?string $uri;
    public bool $isUserSelectable;
}