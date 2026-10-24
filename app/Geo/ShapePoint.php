<?php

namespace Vici\Geo;

class ShapePoint
{
    public float $lat;
    public float $lng;
    public int $seq;

    public function __construct(float $lat, float $lng, int $seq)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->seq = $seq;
    }
}
