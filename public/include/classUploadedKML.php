<?php

/**
Copyright 2014-2016, René Voorburg, rene@digitopia.nl

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

require_once(dirname(__FILE__).'/classDBConnector.php');
require_once(dirname(__FILE__).'/classReduceLines.php');

class UploadedKML 
{
    const KMLBASE = '/var/www/org.vici.static/public/',
          KMLDIR = 'kml',
          DFLTLICENSE = 2; // CC0;

    private $error = false,
            $errormsg = '',
            $db,
            $siteid,
            $path,
            $filename,
            $userid,
            $linekind,
            $license,
            $owner,
            $attribution;
            

    public function __construct($path, $filename, $userid, $siteid, $linekind)
    {               
        $this->path = trim($path);
        $this->filename =  $filename;
        $this->userid = $userid;
        $this->siteid = $siteid;
        $this->linekind = $linekind; 
        $this->license = self::DFLTLICENSE;
        $this->owner='';
        $this->attribution='';
    }
    
    public function setLicense($owner, $license, $attribution)
    {
        $this->owner = strip_tags(trim($owner));
        if ($this->owner != '') {
            $this->license = $license;
            $this->attribution = strip_tags(trim($attribution), '<a><b>');
        }
    }
    
    public function store()
    {   
        $linesObj = new ReduceLines($this->path, 'kml');
        $linesArr = $linesObj->getLines();
        if ($linesArr !== false) {
        
            // we have what we need, now store it in the db
            // connect and start commit sequence:
            $this->db = new DBConnector();
            $this->db->set_charset("utf8");        
            $result = $this->db->query('begin');
            $commitable = !$this->error;
            
            // lets set all existing lines for this point to 'hide';
            if ($commitable) {  
                $sql = "UPDATE plines SET line_hide=1 WHERE line_pnt_id=".$this->siteid;   
                $result = $this->db->query($sql);
                $commitable = ($this->db->errno == 0);
            }
            
            // now add each line, one by one:
            foreach ($linesArr as $linesetArr) {
                if ($commitable) {
                    $sql =  "INSERT INTO plines VALUES (".
                            "NULL, ".
                            $this->siteid.", ".
                            $this->linekind.", ".
                            "0, ".
                            "'".$linesetArr['sw_lat']."', ".
                            "'".$linesetArr['sw_lng']."', ".
                            "'".$linesetArr['ne_lat']."', ".
                            "'".$linesetArr['ne_lng']."', ".
                            "'', ".
                            $this->userid.", ".
                            "NULL, ".
                            $this->license.", ".
                            "'".$this->db->real_escape_string($this->owner)."', ".
                            "'".$this->db->real_escape_string($this->attribution)."'".
                            ")";
                    $result = $this->db->query($sql);
                    $lineId = $this->db->insert_id;
                    $commitable = ($this->db->errno == 0);
            
                    // add each version of the current line:
                    foreach ($linesetArr['linedata'] as $lineArr) {
                        if ($commitable) {
                            $sql = "INSERT INTO pline_data VALUES (".
                                    "NULL, ".
                                    $lineId.", ".
                                    $lineArr['fromZoom'].", ".
                                    $lineArr['toZoom'].", ".
                                    "'".$lineArr['data']."'".
                                ")";
                            $result = $this->db->query($sql);  
                            $commitable = ($this->db->errno == 0);
                        }    
                    }
                }
            }

            
            if ($commitable) {
                $this->db->query("commit");
                rename($this->path, self::KMLBASE.self::KMLDIR.'/'.$this->filename);
                chmod(self::KMLBASE.self::KMLDIR.'/'.$this->filename, 0644);
            } else {
                $this->db->query("rollback"); 
                $this->errormsg = 'Error: could not write lines to database.';   
                $this->error = true;
            }
            
            
        } else {
            // we didn't get a lineArr in return
            $this->errormsg = $linesObj->getLastMessage();
            $this->error = true;
        }
        return !$this->error; 
    }
    
    public function error()
    {
        return $this->error;
    }
    
    public function errorMsg()
    {
        return $this->errormsg;
    }
}