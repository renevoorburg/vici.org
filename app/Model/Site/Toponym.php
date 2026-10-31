<?php

namespace Vici\Model\Site;

use Vici\Service\ReverseGeocoder;

class Toponym
{
    private float $latitude;
    private float $longitude;
    private ?ReverseGeocoder $geocoder = null;
    private array $resolved = [];

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function __get($name)
    {
        if ($name === 'countryName' || $name === 'placeName') {
            return new ToponymFieldProxy($this, $name);
        }
        throw new \InvalidArgumentException("Unknown property: $name");
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    /**
     * Resolve both toponym fields in one geocoder run and cache them per
     * language, so accessing countryName and placeName costs one
     * resolution instead of two.
     */
    public function resolve(string $lang): array
    {
        if (!isset($this->resolved[$lang])) {
            $this->geocoder ??= new ReverseGeocoder($this->latitude, $this->longitude);
            $data = $this->geocoder->resolveToponym($lang);
            $this->resolved[$lang] = [
                'countryName' => $data['countryName'][$lang] ?? '',
                'placeName' => $data['placeName'][$lang] ?? '',
            ];
        }
        return $this->resolved[$lang];
    }
}
