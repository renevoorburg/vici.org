<?php
/**
 * SiteLineData class provides either all lines or just lines for one site.
 *
 * @implements interfaceDataset
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.0 - 2015-09-02
 */

require_once __DIR__ . '/interfaceDataset.php';
require_once __DIR__ . '/classDBConnector.php';
require_once __DIR__ . '/classSiteLine.php';

class SiteLineData implements Dataset
{
    private $siteLineArr = array();     // an dimensional array of SiteLine objects; columns are lineparts
    private $count;
    private $isWalking = false;         // needed to ensure 'walk' points to first element when called first time
    private $currSiteLine = null;       // used when one specific SiteLine is requested through walk

    /**
     * Loads all data from db.
     * @param null $pntId supply if dataset is just for one site, not overall
     */
    public function __construct($pntId = null)
    {
        $db = new DBConnector();

        $restrictSql = is_null($pntId) ? '' : 'AND  line_pnt_id=' . $pntId . ' ';
        $sql = "select line_pnt_id, line_id, pldata_id, line_kind, line_sw_lat, line_sw_lng, line_ne_lat, line_ne_lng, line_note, line_uploader, line_owner, line_date, license_url, line_attribution, pldata_points "
            . "from plines "
            . "left join pline_data on line_id=pldata_pline_id "
            . "left join licenses on line_license=license_id "
            . "where pldata_tozoom=99 and line_hide=0 " . $restrictSql
            . "order by line_pnt_id, line_id";

        $result = $db->query($sql);
        $currPnt = null;
        $siteLine = null;
        while ($obj = $result->fetch_object()) {
            if ($obj->line_pnt_id != $currPnt) {
                $currPnt = $obj->line_pnt_id;
                $siteLine = new SiteLine($currPnt);
                $this->siteLineArr[$currPnt] = $siteLine;
                $siteLine->setNote($obj->line_note);
                $siteLine->setUploader($obj->line_uploader);
                $siteLine->setOwner($obj->line_owner);
                $siteLine->setLicense($obj->license_url);
                $siteLine->setAttribution($obj->line_attribution);
                $siteLine->setDate($obj->line_date);
            }
            $linePart = $siteLine->addLinePart($obj->line_id);
            $linePart->setData($obj->pldata_points);
            $linePart->setBox($obj->line_sw_lat, $obj->line_sw_lng, $obj->line_ne_lat, $obj->line_ne_lng);
        }
        $this->count = $result->num_rows;
        $result->close();
    }

    /**
     * @param null $siteId supply if to walk data for given site only
     * @return bool false if no next item available
     */
    public function walk($siteId = null)
    {
        if (!is_null($siteId)) {
            if ($siteId !== $this->currSiteLine) {
                if (isset($this->siteLineArr[$siteId])) {
                    $this->currSiteLine = $siteId;
                    return true;
                } else {
                    $this->currSiteLine = false;
                    return false;
                }
            } else {
                // second call for the $siteId should return false
                $this->currSiteLine = false;
                return false;
            }
        }

        // we 're walking entire dataset
        if ($this->isWalking) {
            $this->isWalking = next($this->siteLineArr);
            return $this->isWalking;
        }

        // first call, init
        $this->isWalking = $this->count > 0;
        if ($this->isWalking) {
            reset($this->siteLineArr);
        }
        return $this->isWalking;
    }

    /**
     * @return mixed current item, as set using next()
     */
    public function current()
    {
        if ($this->currSiteLine) {
            return $this->siteLineArr[$this->currSiteLine];
        }
        return current($this->siteLineArr);
    }

    /**
     * @param null $siteId supply if to count data for given site only
     * @return integer number of elements in set
     */
    public function count($siteId = null)
    {
        if (is_null($siteId)) {
            return $this->count;
        }
        if (isset($this->siteLineArr[$siteId])) {
            return count($this->siteLineArr[$siteId]);
        } else {
            return 0;
        }
    }
}