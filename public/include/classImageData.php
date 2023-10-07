<?php
/**
 * ImageData class provides either all images or just images for one site.
 *
 * @implements interfaceDataset
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.2 - 2015-09-02
 */

require_once __DIR__ . '/interfaceDataset.php';
require_once __DIR__ . '/classDBConnector.php';
require_once __DIR__ . '/classImage.php';

class ImageData implements Dataset
{
    private $imgArr = array();  // a two dimensional array of Image objects; rows are sites
    private $numImages = 0;
    private $imgRow = null;     // array of Image, part of $imgArr used when walking full array
    private $currImg = null;    // Image used when walking full array; second level
    private $currSite = null;   // set when a specific site /pnt is walked for images


    public function __construct($pntId = null)
    {
        $db = new DBConnector();

        $restrictSql = is_null($pntId) ? '' : 'AND  pil_pnt=' . $pntId . ' ';
        $sql = "select pil_pnt, pil_img, pil_dflt, img_path, imgd_title, imgd_description, imgd_creator, license_url, "
            . "license_abbr, imgd_width, imgd_height, imgd_ownwork, acc_realname "
            . "from pnt_img_lnk "
            . "left join images on pil_img=img_id "
            . "left join img_data on pil_img=imgd_imgid "
            . "left join licenses on imgd_license=license_id "
            . "left join accounts on imgd_uploader=acc_id "
            . "where img_hide=0 " . $restrictSql
            . "order by pil_pnt, pil_dflt desc ";

        $result = $db->query($sql);
        $i = 0;
        while ($obj = $result->fetch_object()) {
            $this->imgArr[$obj->pil_pnt][$i] = new Image($obj->pil_img);
            $this->imgArr[$obj->pil_pnt][$i]->setPath($obj->img_path);
            $this->imgArr[$obj->pil_pnt][$i]->setTitle($obj->imgd_title);
            $this->imgArr[$obj->pil_pnt][$i]->setDescription($obj->imgd_description);
            if ($obj->imgd_ownwork) {
                $this->imgArr[$obj->pil_pnt][$i]->setCreatorName($obj->acc_realname);
            } else {
                $this->imgArr[$obj->pil_pnt][$i]->setCreatorName($obj->imgd_creator);
            }
            $this->imgArr[$obj->pil_pnt][$i]->setLicense($obj->license_url);
            $this->imgArr[$obj->pil_pnt][$i]->setWidth($obj->imgd_width);
            $this->imgArr[$obj->pil_pnt][$i]->setHeight($obj->imgd_height);
            $this->imgArr[$obj->pil_pnt][$i]->setLicenseAbbr($obj->license_abbr);
            $i++;
        }
        $this->numImages = $result->num_rows;
        $result->close();
    }

    /**
     * next image, for walking images in dataset,
     * or set first.
     * @param $siteId integer ; optional site to walk iso full dataset
     * @return bool
     */
    public function walk($siteId = null)
    {
        if (!is_null($siteId)) {
            // walk one siteId (row) only
            if ($siteId !== $this->currSite) {
                // initial call for a walkSiteOnly
                $this->currSite = $siteId;
                if (isset($this->imgArr[$siteId])) {
                    // select an imgRow and currImg
                    $this->imgRow = $this->imgArr[$siteId];
                    $this->currImg = current($this->imgRow);
                    return true;
                } else {
                    return false;
                }
            } else {
                // imgRow has been set before
                if (next($this->imgRow)) {
                    $this->currImg = current($this->imgRow);
                    return true;
                } else {
                    $this->currImg = null;
                    $this->currSite = null;
                    return false;
                }
            }
        }

        // we 're walking entire dataset
        if (!is_object($this->currImg)) {
            // initial call, set cursor
            if ($this->numImages > 0) {
                // 'next' is simply the first:
                $this->imgRow = current($this->imgArr);
                $this->currImg = current($this->imgRow);
                return true;
            } else {
                return false;
            }
        }
        if (next($this->imgRow)) {
            // next image in row:
            $this->currImg = current($this->imgRow);
            return true;
        } elseif (next($this->imgArr)) {
            // next marker (row) with images:
            $this->imgRow = current($this->imgArr);
            $this->currImg = current($this->imgRow);
            return true;
        }
        reset($this->imgArr);
        $this->currImg = null;
        return false;
    }

    /**
     * @return Object current Image
     */
    public function current()
    {
        return $this->currImg;
    }

    public function count($siteId = null)
    {
        if (is_null($siteId)) {
            return $this->numImages;
        }
        if (array_key_exists($siteId, $this->imgArr)) {
            return count($this->imgArr[$siteId]);
        }
        return 0;
    }

}