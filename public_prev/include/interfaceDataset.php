<?php
/**
 * Dataset interface for walking specific data connected to a site.
 * Used to walk all attached data or just for one site.
 *
 * Can load all data or for one site only (set construct param).
 * Can walk all loaded data -next() - or just one site -next($siteId).
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.0 - 2015-09-02
 */

interface Dataset
{
    /**
     * Loads all data from db.
     * @param null $pntId supply if dataset is just for one site, not overall
     */
    public function __construct($pntId = null);

    /**
     * Sets a first current element (false if none) or walks to next.
     * Mind this behaviour is quite like "next()" but not identical.
     * @param null $siteId supply if to walk data for given site only
     * @return bool false if no next item available
     */
    public function walk($siteId = null);

    /**
     * @return mixed current item, as set using next()
     */
    public function current();

    /**
     * @param null $siteId supply if to count data for given site only
     * @return integer number of elements in set
     */
    public function count($siteId = null);

}