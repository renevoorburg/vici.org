<?php

namespace Vici\Model\Site;

class Toponym
{
    private float $latitude;
    private float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function __get($name)
    {
        if ($name === 'countryName') {
            return new ToponymFieldProxy($this, 'countryName');
        } elseif ($name === 'placeName') {
            return new ToponymFieldProxy($this, 'placeName');
        }
        throw new \InvalidArgumentException("Unknown property: $name");
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }
}