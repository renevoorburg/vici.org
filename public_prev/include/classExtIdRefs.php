<?php

/**
Copyright 2014-5, René Voorburg, rene@digitopia.nl

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


/**
 * WORST CODE EVER WARNING (SORRY!)
 *
 * Used to normalize external identifiers.
 *
 * Helps to extract indexed identifiers like pleiades from other ids.
 * Helps to compare to sets of external identifiers.
*/

class ExtIdRefs
{ 
    private 
        $pleiades,
        $livius,
        $romaq,
        $dare,
        $wikidata,
        $omnesviae,
        $normExtIdsStr,
        $normExtIdsArr,
        $rawExtIdStr;

    // CONST:
    private $normalizersArr = array ( 
        array ('^http:\/\/pleiades\.stoa\.org\/places\/([0-9]+).*$', 'pleiades:place=$1'),
        array ('^pleiades:places=([0-9]+).*$', 'pleiades:place=$1'),
        array ('^http:\/\/romaq\.org\/the-project\/aqueducts\/article\/([0-9]+).*$', 'romaq:aqid=$1'),
        array ('^romaq=([0-9]+).*$', 'romaq:aqid=$1'),
        array ('^wikidata=[Q|q]([0-9]+).*$', 'wikidata:entity=Q$1'),
        array ('^http:\/\/(?:www\.)?livius\.org\/museum\/([^\/]*)\/?$', 'livius:museum=$1'),
        array ('^http:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/?$', 'livius:$1=$2'),
        array ('^http:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/?$', 'livius:$1=$2/$3'),
        array ('^http:\/\/(?:www\.)?livius\.org\/[^\/]+\/([^\/]+)\/([^\/]*)\/([^\/]*)\/([^\/]*)\/?$', 'livius:$1=$2/$3/$4'),
        array ('^.*TPPlace[^0-9]?([0-9]+)[^0-9]*$', 'tp:place=$1'),
        array ('^http:\/\/francia\.ahlfeldt\.se\/page\/places\/([0-9]*)\/?$', 'dare:id=$1'),
        array ('^http:\/\/(?:www\.)?openstreetmap\.org\/browse\/node\/([0-9]*)\/?$', 'osm:node=$1'),
        array ('^http:\/\/(?:www\.)?openstreetmap\.org\/browse\/relation\/([0-9]*)\/?$', 'osm:relation=$1'),
        array ('^http:\/\/(?:www\.)?openstreetmap\.org\/browse\/way\/([0-9]*)\/?$', 'osm:way=$1'),
        array ('^http:\/\/(?:www\.)?perseus\.tufts\.edu\/hopper\/text\?doc=Perseus:text:1999\.04\.0006:entry=(.*)$', 'pecs=$1'),
        array ('^http:\/\/de\.structurae\.de\/structures\/data\/index\.cfm\?ID=(.*)$', 'structurae:id=$1'),
        array ('^wikipedia=(.*)$', 'wikipedia:en=$1'),
        array ('^http:\/\/([a-z]*)\.wikipedia\.org\/wiki\/(.*)$', 'wikipedia:$1=$2'),
        array ('^http:\/\/(?:www\.)?wikidata\.org\/wiki\/(.*)$', 'wikidata:entity=$1'),
        array ('^http:\/\/(?:www\.)?wikidata\.org\/entity\/(.*)$', 'wikidata:entity=$1')
    );
    
    // constructor sets the private vars by normalizing the raw input
    // ids (name=value) in $rawExtIdStr are expected inside <span></span> tags
    // optional indexed ids will override those in $rawExtIdsStr
    public function __construct($rawExtIdStr, $pleiades = null, $livius = null, $romaq = null, $dare = null) { 
        $this->rawExtIdStr = $rawExtIdStr;

        // clean and normalize identifiers from $extidsArr to $normExtIdsArr:
        $extidsArr = mb_split("<span>", $rawExtIdStr);
        foreach ($extidsArr as &$value) {
            // cleanup:
            $value = trim(mb_ereg_replace('<\/span>[\s]?$', '', $value));


            //echo $value;
            // standardize:
            foreach ($this->normalizersArr as &$rules) {
//                $value = mb_ereg_replace($rules[0], $rules[1], $value);
                $value = preg_replace('/'.$rules[0].'/', $rules[1], $value);

//                    echo $value ."\n";
            }    
        }
        unset($value);
        $normExtIdsArr = array_unique($extidsArr);
        sort($normExtIdsArr);
        $this->normExtIdsArr = $normExtIdsArr;
        
        // extracts the specials ids from $normExtIdsArr
        $this->pleiades = $pleiades;
        $this->livius = $livius;
        $this->romaq = $romaq;
        $this->dare = $dare;
        $this->NormExtIdsStr = '';
        foreach ($normExtIdsArr as $key => $value) {       
            $match = false;
    
            if (mb_ereg_match('^pleiades:place=([0-9]+)$', $value)) {
                $match = true;
                $this->pleiades = mb_ereg_replace('^pleiades:place=([0-9]+)$', '$1', $value);
                unset($normExtIdsArr[$key]);
            };
    
            if (!$match && mb_ereg_match('^livius:(museum|place|battle|people|religion|source-content|source-about)=(.*)$', $value)) {
                $match = true;
                $this->livius = mb_ereg_replace('^livius:(museum|place|battle|people|religion|source-content|source-about)=(.*)$', '$1=$2', $value);
                unset($normExtIdsArr[$key]);
            }
    
            if (!$match && mb_ereg_match('^romaq:aqid=([0-9]+)$', $value)) {
                $match = true;
                $this->pleiades = mb_ereg_replace('^romaq:aqid=([0-9]+)$', '$1', $value);
                unset($normExtIdsArr[$key]);
            }
    
            if (!$match && mb_ereg_match('^dare:id=([0-9]+)$', $value)) {
                $match = true;
                $this->pleiades = mb_ereg_replace('^dare:id=([0-9]+)$', '$1', $value);
                unset($normExtIdsArr[$key]);
            }
    
            if (!$match && mb_ereg_match('^wikidata:entity=([Q|q][0-9]+)$', $value)) {
                $match = true;
                $this->wikidata = preg_replace('/^wikidata:entity=([Q|q][0-9]+)$/', '$1', $value);
                unset($normExtIdsArr[$key]);
            }

            if (!$match && mb_ereg_match('^tp:place=([0-9]+)$', $value)) {
                $match = true;
                $this->omnesviae = preg_replace('/^tp:place=([0-9]+)$/', '$1', $value);
                unset($normExtIdsArr[$key]);
            }

            if(!$match) {
                // all the rest is combined
                $this->normExtIdsStr .= '<span>'.$value.'</span>';
            }
        }
        
    }
    
    // gets the string of all formatted id's that are not stored separately in the db 
    // providing the 'other' data for the db is the main use of this function
    public function getShortExtIdsStr() {
        $ret = $this->wikidata ? "<span>wikidata=".$this->wikidata."</span>" : "";
        $ret.= $this->omnesviae ? "<span>tp:place=".$this->omnesviae."</span>" : "";
        $ret.= $this->normExtIdsStr ;
        return $ret;
    }
    
    // gets the complete string of all formatted id's, includsing those that are stored separately in the db
    public function getFullExtIdsStr() {
        $ret = $this->getShortExtIdsStr();
        $ret = ($this->dare ? "<span>dare:id=".$this->dare."</span>".$ret :  $ret );
        $ret = ($this->romaq ? "<span>romaq:aqid=".$this->romaq."</span>".$ret :  $ret );
        $ret = ($this->livius ? "<span>livius:".$this->livius."</span>".$ret :  $ret );
        $ret = ($this->pleiades ? "<span>pleiades:place=".$this->pleiades."</span>".$ret :  $ret );
        return $ret;
    }

    public function getExtIdsArr() {
        return $this->normExtIdsArr;
    }

    public function getTag(string $pattern): string
    {
        preg_match($pattern, $this->rawExtIdStr, $matches);
        if (count($matches) > 0) {
            return $matches[0];
        } else {
            return '';
        }
    }

    public function getPleiades() {
        return $this->pleiades;
    }

    public function getPleiadesUrl() {
        return 'http://pleiades.stoa.org/places/'.$this->pleiades;
    }

    public function getPleiadesTag() {
        return 'pleiades:place='.$this->pleiades;
    }

    public function getLivius() {
        return $this->livius;
    }

    public function getLiviusUrl() {
        $parts = explode("=", $this->livius);
        switch ($parts[0]) {
            case "museum":
                return 'http://livius.org/'.$parts[0]."/".$parts[1];
                break;
            default:
                return 'http://livius.org/articles/'.$parts[0]."/".$parts[1];
        }

    }

    public function getLiviusTag() {
        return 'livius:'.$this->livius;
    }
    
    public function getRomaq() {
        return $this->romaq;
    }

    public function getRomaqUrl() {
        return 'http://romaq.org/the-project/aqueducts/article/'.$this->romaq;
    }

    public function getRomaqTag() {
        return 'romaq:aqid='.$this->romaq;
    }

    public function getDare() {
        return $this->dare;
    }
    public function getDareUrl() {
        return 'http://dare.ht.lu.se/places/'.$this->dare;
    }

    public function getDareTag() {
        return 'dare:id='.$this->dare;
    }
    
    public function getWikidata() {
        return $this->wikidata;
    }

    public function getWikidataUrl() {
        if ($this->wikidata) {
            return 'http://wikidata.org/entity/' . $this->wikidata;
        }
    }

    public function getWikidataTag() {
        if ($this->wikidata) {
            return 'wikidata:entity=' . $this->wikidata;
        }
    }
    
    public function setWikidata($wikidata) {
        $this->wikidata = $wikidata;
    }
    
}