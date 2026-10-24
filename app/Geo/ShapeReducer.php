<?php

namespace Vici\Geo;

class ShapeReducer
{
    /**
     * Reduce the number of points in a shape using the Douglas-Peucker algorithm.
     */
    public function reduceWithTolerance(Shape $shape, float $tolerance): Shape
    {
        if ($tolerance <= 0 || count($shape->points()) < 3) {
            return $shape;
        }

        $points = $shape->points();
        $newShape = new Shape();

        $newShape->addPoint($points[0]);
        $newShape->addPoint($points[count($points) - 1]);

        $this->douglasPeuckerReduction($shape, $newShape, $tolerance, 0, count($points) - 1);

        return $newShape;
    }

    private function douglasPeuckerReduction(Shape $shape, Shape $newShape, float $tolerance, int $firstIdx, int $lastIdx): void
    {
        if ($lastIdx <= $firstIdx + 1) {
            return;
        }

        $points = $shape->points();

        $maxDistance = 0.0;
        $indexFarthest = 0;

        $firstPoint = $points[$firstIdx];
        $lastPoint = $points[$lastIdx];

        for ($idx = $firstIdx + 1; $idx < $lastIdx; $idx++) {
            $point = $points[$idx];
            $distance = $this->orthogonalDistance($point, $firstPoint, $lastPoint);
            if ($distance > $maxDistance) {
                $maxDistance = $distance;
                $indexFarthest = $idx;
            }
        }

        if ($maxDistance > $tolerance) {
            $newShape->addPoint($points[$indexFarthest]);
            $this->douglasPeuckerReduction($shape, $newShape, $tolerance, $firstIdx, $indexFarthest);
            $this->douglasPeuckerReduction($shape, $newShape, $tolerance, $indexFarthest, $lastIdx);
        }
    }

    private function orthogonalDistance(ShapePoint $point, ShapePoint $lineStart, ShapePoint $lineEnd): float
    {
        if (($lineStart->lat == $lineEnd->lat) && ($lineStart->lng == $lineEnd->lng)) {
            return sqrt(pow($lineEnd->lat - $point->lat, 2) + pow($lineEnd->lng - $point->lng, 2));
        }

        $area = abs(
            (
                $lineStart->lat * $lineEnd->lng
                + $lineEnd->lat * $point->lng
                + $point->lat * $lineStart->lng
                - $lineEnd->lat * $lineStart->lng
                - $point->lat * $lineEnd->lng
                - $lineStart->lat * $point->lng
            ) / 2
        );

        $bottom = sqrt(pow($lineStart->lat - $lineEnd->lat, 2) + pow($lineStart->lng - $lineEnd->lng, 2));

        return $area / $bottom * 2.0;
    }
}
