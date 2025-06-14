<?php
    require_once(dirname(__FILE__).'/classShapePoint.php');
 
    class Shape
    {
        /**
         * @var ShapePoint[]    The list of points in the shape
         */
        protected $_points = array();
 
        /**
         * @var bool    Whether or not the list of points needs sorting
         */
        protected $_needsSort = false;
 
        /**
         * Add a point to the shape. Marks the list of points as out-of-order
         *
         * @param   ShapePoint  $point  The point to add to the shape
         */
        public function addPoint(ShapePoint $point)
        {
            $this->_points[] = $point;
            $this->_needsSort = true;
            return $this;
        }
 
        /**
         * Get the list of points. If the list is out of order
         * it is sorted by sequence value prior to returning
         *
         * @return  ShapePoint[]
         */
        public function points()
        {
            if ($this->_needsSort) {
                usort($this->_points, array(__CLASS__, 'sort'));
                $this->_needsSort = false;
            }
 
            return $this->_points;
        }
 
        /**
         * Sort callback to sort ShapePoint by sequence
         *
         * @param   ShapePoint  $a
         * @param   ShapePoint  $b
         * @return  int         -1, 0, or 1
         */
        public static function sort($a, $b)
        {
            if ($a->seq < $b->seq) { return -1; }
            if ($a->seq > $b->seq) { return 1; }
            return 0;
        }
    }
?>