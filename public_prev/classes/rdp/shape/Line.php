<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 17-01-17
 * Time: 18:39
 */

namespace rdp\shape;


use rdp\shape\write\LineWriterInterface;

class Line
{

    /**
     * @var Point[]    The list of points in the shape
     */
    protected $points = array();

    /**
     * @var bool    Whether or not the list of points needs sorting
     */
    protected $needsSort = false;

    /**
     * Add a point to the shape. Marks the list of points as out-of-order
     *
     * @param   Point $point The point to add to the shape
     * @param   Boolean $needsSort False if point given known index number
     */
    public function addPoint(Point $point, $needsSort = true)
    {
        $this->points[] = $point;
        $this->needsSort = $needsSort;
    }

    /**
     * Get the list of points. If the list is out of order
     * it is sorted by sequence value prior to returning
     *
     * @return  Point[]
     */
    public function getPoints()
    {
        if ($this->needsSort) {
            usort($this->points, array(__CLASS__, 'sort'));
            $this->needsSort = false;
        }

        return $this->points;
    }

    /**
     * @param LineWriterInterface $writer
     *
     * @return mixed
     */
    public function write(LineWriterInterface $writer)
    {
        return $writer->write($this);
    }

    /**
     * Sort callback to sort Point by sequence
     *
     * @param   Point $a
     * @param   Point $b
     * @return  int         -1, 0, or 1
     */
    private static function sort($a, $b)
    {
        if ($a->getSeq() < $b->getSeq()) {
            return -1;
        }
        if ($a->getSeq() < $b->getSeq()) {
            return 1;
        }
        return 0;
    }


}