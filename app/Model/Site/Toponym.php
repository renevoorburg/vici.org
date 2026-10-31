<?php

namespace Vici\Model\Site;

use Vici\Service\ReverseGeocoder;

/**
 * Toponym resolved once and stored on the point (pnt_country ISO code,
 * pnt_place place name). Only the localized country name is resolved at
 * render time, from the tiny countries table (per-request cached).
 */
class Toponym
{
    private string $countryCode;
    private string $place;
    private array $resolved = [];

    public function __construct(string $countryCode = '', string $place = '')
    {
        $this->countryCode = $countryCode;
        $this->place = $place;
    }

    public function __get($name)
    {
        if ($name === 'countryName' || $name === 'placeName') {
            return new ToponymFieldProxy($this, $name);
        }
        throw new \InvalidArgumentException("Unknown property: $name");
    }

    public function getCountryCode(): string {
        return $this->countryCode;
    }

    public function getPlace(): string {
        return $this->place;
    }

    public function resolve(string $lang): array
    {
        if (!isset($this->resolved[$lang])) {
            $this->resolved[$lang] = [
                'countryName' => ReverseGeocoder::countryNameFor($this->countryCode, $lang),
                'placeName' => $this->place,
            ];
        }
        return $this->resolved[$lang];
    }
}
