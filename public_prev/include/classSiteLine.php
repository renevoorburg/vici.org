<?php
/**
 * SiteLine class provides a basic interface to all the line(parts) of one site.
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.0
 */

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/classLinePart.php';

class SiteLine
{
    private $siteId;
    private $note;
    private $owner;
    private $uploader;
    private $license;
    private $attribution;
    private $date;
    private $linePartArr = array();   // array of LinePart objects

    public function __construct($siteId = null)
    {
        $this->setId($siteId);
    }

    /* setters: */
    public function setId($siteId)
    {
        $this->siteId = $siteId;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function setAttribution($attribution)
    {
        $this->attribution = $attribution;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function addLinePart($lineId)
    {
        $this->linePartArr[] = new LinePart($lineId);
        return end($this->linePartArr);
    }

    /* getters: */
    public function getId()
    {
        return $this->siteId;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getLineParts($serialisation = 'wkt')
    {
        if (count($this->linePartArr) === 1) {
            $json = '{"type":"LineString","coordinates":' . $this->linePartArr[0]->getData(true) . '}';
        } elseif (count($this->linePartArr) >= 1) {
            $sep = '';
            $json = '{"type":"MultiLineString","coordinates":[';
            foreach ($this->linePartArr as $linePart) {
                $json .= $sep . $linePart->getData(true);
                $sep = ',';
            }
            $json .= "]}";
        }
        $geom = geoPHP::load($json,'json');
        return $geom->out($serialisation);
    }

    public function getOpengisLineKind()
    {
        if (count($this->linePartArr) === 1) {
            return 'LineString';
        }
        if (count($this->linePartArr) >= 1) {
           return 'MultiLineString';
        }
        return '';
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function getAuthor()
    {
        if ($this->owner) {
            return $this->owner;
        } else {
            return $this->uploader;
        }
    }

    public function getAttribution()
    {
        return $this->attribution;
    }

    public function isPublicDomain()
    {
        return (bool)strpos($this->license, 'publicdomain');
    }

}