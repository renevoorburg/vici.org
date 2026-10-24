<?php

namespace Vici\Geo;

class Shape
{
    /** @var ShapePoint[] */
    protected array $points = [];
    private bool $needsSort = false;

    public function addPoint(ShapePoint $point): self
    {
        $this->points[] = $point;
        $this->needsSort = true;
        return $this;
    }

    /**
     * @return ShapePoint[]
     */
    public function points(): array
    {
        if ($this->needsSort) {
            usort($this->points, function (ShapePoint $a, ShapePoint $b) {
                return $a->seq <=> $b->seq;
            });
            $this->needsSort = false;
        }
        return $this->points;
    }
}
