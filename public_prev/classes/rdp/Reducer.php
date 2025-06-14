<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 17-01-17
 * Time: 18:39
 */

namespace rdp;

use \rdp\shape\Line;
use \rdp\shape\Point;


class Reducer
{

    /**
     * Reduce the number of points in a shape using the Ramer-Douglas-Peucker algorithm
     *
     * @param   Line    $line       The shape to reduce
     * @param   float   $tolerance  The tolerance to decide whether or not
     *                              to keep a point, in geographic
     *                              coordinate system degrees
     * @param   bool   $nullify     Reduce to null when only 2 points remain,
     *                              closer than $tolerance.
     * @return  Line   The reduced line
     */
    public static function RDP($line, $tolerance, $nullify = false)
    {
        // if a shape has 2 or less points it cannot be reduced
        if ($tolerance <= 0 || count($line->getPoints()) < 2) {
            return $line;
        }

        if (count($line->getPoints()) == 2) {
            if ($nullify) {
                if (self::distance($line->getPoints()[0], $line->getPoints()[1]) < $tolerance) {
                    return null;
                }
            }
            return $line;
        }

        $points = $line->getPoints();
        $newLine = new Line(); // the new line to return

        // automatically add the first and last point to the returned shape
        $newLine->addPoint($points[0]);
        $newLine->addPoint($points[count($points) - 1]);

        // the first and last points in the original shape are
        // used as the entry point to the algorithm.
        self::reduce(
            $line,              // original line
            $newLine,           // reduced shape
            $tolerance,         // tolerance
            0,                  // index of first point
            count($points) - 1  // index of last point
        );

        if ($nullify && (count($newLine->getPoints()) == 2) &&  (self::distance($newLine->getPoints()[0], $newLine->getPoints()[1]) < $tolerance) ) {
            return null;
        }
        return $newLine;
    }

    /**
     * Reduce the points in $shape between the specified first and last
     * index. Add the shapes to keep to $newShape
     *
     * @param   Line    $line      The original line
     * @param   Line    $newLine   The reduced (output) line
     * @param   float   $tolerance  The tolerance to determine if a point is kept
     * @param   int     $firstIdx   The index in original lines's point of
     *                              the starting point for this line segment
     * @param   int     $lastIdx    The index in original line's point of
     *                              the ending point for this line segment
     */
    private function reduce(Line $line, Line $newLine, $tolerance, $firstIdx, $lastIdx)
    {
        if ($lastIdx <= $firstIdx + 1) {
            // overlapping indexes, just return
            return;
        }

        // retrieve all points for subsequent processing
        $points = $line->getPoints();

        // loop over the points between the first and last points
        // and find the point that is the furthest away

        $maxDistance = 0.0;
        $indexFarthest = 0;

        $firstPoint = $points[$firstIdx];
        $lastPoint = $points[$lastIdx];

        for ($idx = $firstIdx + 1; $idx < $lastIdx; $idx++) {
            $point = $points[$idx];

            $distance = self::orthogonalDistance($point, $firstPoint, $lastPoint);

            // only keep the point with the greatest distance
            if ($distance > $maxDistance) {
                $maxDistance = $distance;
                $indexFarthest = $idx;
            }
        }

        // if the point that is furthest away is within the tolerance,
        // it is simply discarded. Otherwise, it's added to the reduced
        // shape and the algorithm continues
        if ($maxDistance > $tolerance) {
            $newLine->addPoint($points[$indexFarthest]);

            // reduce the shape between the starting point to newly found point
            self::reduce($line, $newLine, $tolerance, $firstIdx, $indexFarthest);

            // reduce the shape between the newly found point and the finishing point
            self::reduce($line, $newLine, $tolerance, $indexFarthest, $lastIdx);
        }
    }

    /**
     * Calculate the distance between two points
     *
     * @param Point $lineStart
     * @param Point $lineEnd
     * @return float
     */
    private function distance(Point $lineStart, Point $lineEnd)
    {
        return sqrt(pow($lineEnd->getLat() - $lineStart->getLat(), 2) + pow($lineEnd->getLngAsX() - $lineStart->getLngAsX(), 2));
    }

    /**
     * Calculate the orthogonal distance from the line joining the
     * $lineStart and $lineEnd points from $point
     *
     * @param   Point   $point      The point the distance is being calculated for
     * @param   Point   $lineStart  The point that starts the line
     * @param   Point   $lineEnd    The point that ends the line
     * @return  float   The distance in geographic coordinate system degrees
     */
    private function orthogonalDistance(Point $point, Point $lineStart, Point $lineEnd)
    {

        // allow same end and start point:
        if (($lineStart->getLat() == $lineEnd->getLat()) && ($lineStart->getLng() == $lineEnd->getLng())) {

            return self::distance($point, $lineEnd);

        } else {

            $area = abs(
                (
                    $lineStart->getLat() * $lineEnd->getLngAsX()
                    + $lineEnd->getLat() * $point->getLngAsX()
                    + $point->getLat() * $lineStart->getLngAsX()
                    - $lineEnd->getLat() * $lineStart->getLngAsX()
                    - $point->getLat() * $lineEnd->getLngAsX()
                    - $lineStart->getLat() * $point->getLngAsX()
                ) / 2
            );

            $bottom = sqrt(pow($lineStart->getLat() - $lineEnd->getLat(), 2) + pow($lineStart->getLngAsX() - $lineEnd->getLngAsX(), 2));

            return $area / $bottom * 2.0;
        }
    }


}