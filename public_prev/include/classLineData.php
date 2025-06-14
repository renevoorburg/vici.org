<?php
/**
Copyright 2014, René Voorburg, rene@digitopia.nl

This file is part of the Vici.org source.

Vici.org source is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Vici.org  source is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
*/
 
require_once (dirname(__FILE__).'/classDBConnector.php');

class LineData 
{
    private $kmlArr = array();
    private $freeLicense;
    private $license;
    private $url;
    private $owner;
    private $uploader;
    private $attribution;
    private $numLines = 0;

    public function __construct($id)
    {
        $db = new DBConnector();
        $db->set_charset("utf8");
        
        $sql = "SELECT line_id, line_kind, line_owner, line_attribution, acc_realname, license_short, license_url, license_uploadable "
              ."FROM plines "
              ."LEFT JOIN accounts ON line_uploader=acc_id "
              ."LEFT JOIN licenses ON line_license=license_id "
              ."WHERE line_hide=0 AND line_pnt_id=$id";
        $result = $db->query($sql);

        $this->numLines = $result->num_rows;
        if ($this->numLines > 0 ) {

            $lineIds = array();
            while ($line = $result->fetch_object()) {
                $lineIds[] = $line->line_id;

                if (!isset($this->license)) {
                    $this->license = $line->license_short;
                    $this->url = $line->license_url;
                    $this->freeLicense = $line->license_uploadable;
                    $this->owner = $line->line_owner;
                    $this->uploader = $line->acc_realname;
                    $this->attribution = $line->line_attribution;
                }
            }
            $lineIdsStr = implode(', ', $lineIds);

            $sql = "SELECT pldata_points "
                 . "FROM pline_data "
                 . "WHERE pldata_pline_id IN ($lineIdsStr) AND pldata_tozoom = 99";

            if ($result = $db->query($sql)) {
                while (list($linedata) = $result->fetch_row()) {
                    $linedataArr = json_decode($linedata);
                    $kmlStr = '';
                    foreach ($linedataArr as $point) {
                        $kmlStr .= $point[1].",".$point[0].",0 ";
                    }
                    $this->kmlArr[] = $kmlStr;
                }
            }

        }
    }
    
    public function getKML() 
    {
        return $this->kmlArr;
    } 
    
    public function getLicense()
    {
        //$licenseText = $this->license;
    
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
    
    public function isFree()
    {
        return $this->freeLicense;
    }

    public function isPublicDomain() {
        return (bool)strpos($this->url, 'publicdomain');
    }
    
    public function getNumLines()
    {
        return $this->numLines;
    }
    
    
}