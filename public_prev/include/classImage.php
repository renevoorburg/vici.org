<?php
/**
 * Image class provides basic interface for one singular image element.
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.1 - 2015-09-15
 */

require_once __DIR__ . '/classDBConnector.php';

class Image
{
    private $restrictOwner = array ('/livius/i');
    private $id;
    private $path;
    private $title;
    private $description;
    private $creator;
    private $uploader;
    private $license;
    private $licenseAbbr;
    private $width;
    private $height;

    public function __construct($id = null, $loadFromDB = false)
    {
        if ($loadFromDB) {
            $db = new DBConnector();
            $sql = "select pil_pnt, pil_img, pil_dflt, img_path, imgd_title, imgd_description, imgd_creator, license_url, acc_realname, "
                . "license_abbr, imgd_width, imgd_height "
                . "from pnt_img_lnk "
                . "left join images on pil_img=img_id "
                . "left join img_data on pil_img=imgd_imgid "
                . "left join licenses on imgd_license=license_id "
                . "left join accounts on imgd_uploader=acc_id "
                . "where img_id=" . $id;
            $result = $db->query($sql);
            $obj = $result->fetch_object();

            $this->numImages = $result->num_rows;
            $this->setId($obj->pil_img);
            $this->setPath($obj->img_path);
            $this->setTitle($obj->imgd_title);
            $this->setDescription($obj->imgd_description);
            $this->setLicense($obj->license_url);
            $this->setCreatorName($obj->imgd_creator);
            $this->setUploaderName($obj->acc_realname);
            $this->setWidth($obj->imgd_width);
            $this->setHeight($obj->imgd_height);
            $result->close();
        } else {
            $this->setId($id);
        }
    }


    /* getters: */
    public function getId()
    {
        return $this->id;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function getCreatorName()
    {
        return $this->creator;
    }

    public function getUploaderName()
    {
        return $this->uploader;
    }

    function getOwnerName()
    {
        return $this->getCreatorName() ? $this->getCreatorName() : $this->getUploaderName();
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getLicenseAbbr()
    {
        return $this->licenseAbbr;
    }


    /* setters: */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function setCreatorName($creatorName)
    {
        $this->creator = $creatorName;
    }

    public function setUploaderName($uploaderName)
    {
        $this->uploader = $uploaderName;
    }

    public function setWidth($width)
    {
            $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function setLicenseAbbr($licenseAbbr)
    {
        $this->licenseAbbr = $licenseAbbr;
    }

    /* other: */
    public function isRestricted()
    {

        foreach ($this->restrictOwner as $pattern) {
            if (preg_match($pattern, $this->getOwnerName())) {
                return true;
            }
        }
        return false;

    }

}