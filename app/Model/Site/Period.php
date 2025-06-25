<?php

namespace Vici\Model\Site;

class Period
{
    public int $startYear;
    public ?int $endYear = null;
    public string $startQualifier;
    public ?string $endQualifier = null;
}
