<?php
    class ShapeReducer
    {
        /**
         * Reduce the number of points in a shape using the Douglas-Peucker algorithm
         *
         * @param   Shape   $shape      The shape to reduce
         * @param   float   $tolerance  The tolerance to decide whether or not
         *                              to keep a point, in geographic
         *                              coordinate system degrees
         * @return  Shape   The reduced shape
         */
        public function reduceWithTolerance($shape, $tolerance)
        {
            // if a shape has 2 or less points it cannot be reduced
            if ($tolerance <= 0 || count($shape->points()) < 3) {
                return $shape;
            }
 
            $points = $shape->points();
            $newShape = new Shape(); // the new shape to return
 
            // automatically add the first and last point to the returned shape
            $newShape->addPoint($points[0]);
            $newShape->addPoint($points[count($points) - 1]);
 
            // the first and last points in the original shape are
            // used as the entry point to the algorithm.
            $this->douglasPeuckerReduction(
                $shape,             // original shape
                $newShape,          // reduced shape
                $tolerance,         // tolerance
                0,                  // index of first point
                count($points) - 1  // index of last point
            );
 
            // all done, return the reduced shape
            return $newShape;
        }
 
        /**
         * Reduce the points in $shape between the specified first and last
         * index. Add the shapes to keep to $newShape
         *
         * @param   Shape   $shape      The original shape
         * @param   Shape   $newShape   The reduced (output) shape
         * @param   float   $tolerance  The tolerance to determine if a point is kept
         * @param   int     $firstIdx   The index in original shape's point of
         *                              the starting point for this line segment
         * @param   int     $lastIdx    The index in original shape's point of
         *                              the ending point for this line segment
         */
        public function douglasPeuckerReduction(Shape $shape, Shape $newShape, $tolerance, $firstIdx, $lastIdx)
        {
            if ($lastIdx <= $firstIdx + 1) {
                // overlapping indexes, just return
                return;
            }
 
            // retrieve all points for subsequent processing
            $points = $shape->points();
 
            // loop over the points between the first and last points
            // and find the point that is the furthest away
 
            $maxDistance = 0.0;
            $indexFarthest = 0;
 
            $firstPoint = $points[$firstIdx];
            $lastPoint = $points[$lastIdx];
 
            for ($idx = $firstIdx + 1; $idx < $lastIdx; $idx++) {
                $point = $points[$idx];
 
                $distance = $this->orthogonalDistance($point, $firstPoint, $lastPoint);
 
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
                $newShape->addPoint($points[$indexFarthest]);
 
                // reduce the shape between the starting point to newly found point
                $this->douglasPeuckerReduction($shape, $newShape, $tolerance, $firstIdx, $indexFarthest);
 
                // reduce the shape between the newly found point and the finishing point
                $this->douglasPeuckerReduction($shape, $newShape, $tolerance, $indexFarthest, $lastIdx);
            }
        }
 
        /**
         * Calculate the orthogonal distance from the line joining the
         * $lineStart and $lineEnd points from $point
         *
         * @param   ShapePoint  $point      The point the distance is being calculated for
         * @param   ShapePoint  $lineStart  The point that starts the line
         * @param   ShapePoint  $lineEnd    The point that ends the line
         * @return  float   The distance in geographic coordinate system degrees
         */
        public function orthogonalDistance($point, $lineStart, $lineEnd)
        {
            // allow same end and start point:  
            if (($lineStart->lat == $lineEnd->lat) && ($lineStart->lng == $lineEnd->lng)) {
                
                return sqrt(pow($lineEnd->lat - $point->lat, 2) + pow($lineEnd->lng - $point->lng, 2));
            
            } else {
        
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
    }