<?php

namespace Vici\API;

use Vici\Session\Session;
use Vici\DB\DBConnector;
use Vici\Geometries\Line;
use Vici\Identifiers\NormalizersIndex;

class GeoJSON extends APICall
{
    public Session $session;
    public DBConnector $db;
    
    public function __construct(Session $session)
    {
        parent::__construct($session);
        $this->session = $session;
        $this->db = $session->getDBConnector();
    }
    
    public function payload()
    {
        $lng = $this->session->getLanguage();

        //  A 'perspective' shows only markers that have a specified external identifier.
        //  Links to the related external site are added to the output. 
        //  Perspectives are only applied when a boundary / zoom based subset is requested.
        $perspective = (isset($_GET['perspective']) ? $_GET['perspective'] : '');

        if ($perspective) {
            $normalizer = NormalizersIndex::getIndexedNormalizer($perspective);
        }

        // Set general SQL modifiers for when we are using a perspective on the data:
        $perspectiveLineJoinSQL = ($perspective ? " LEFT JOIN pmetadata ON line_pnt_id=pmeta_pnt_id " : "");
        $perspectivePointJoinSQL = " LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id ";

        // set specific (SQL) modifiers for when using a perspective: 
        $perspectiveArr = array ( 
            ''         => array ( "",                         ""),
            'pleiades' => array ( " AND pmeta_pleiades > 0 ",   ", pmeta_pleiades AS extid "),
            'livius'   => array ( " AND pmeta_livius > '' ",    ", pmeta_livius AS extid "),
            'mithraeum'=> array ( " AND pmeta_mithraeum > '' ", ", pmeta_mithraeum AS extid "),
            'romaq'    => array ( " AND pmeta_romaq > 0 ",      ", pmeta_romaq AS extid ")
        );
        $perspectiveRestrictSQL = $perspectiveArr[$perspective][0];
        $perspectiveSelectSQL   = $perspectiveArr[$perspective][1];

        // 'include' points are added non-the-less
        $requiredPointsArr = array();
        if (isset($_GET['require'])) {
            foreach (explode(",", $_GET['require']) as $value) {
                $requiredPointsArr[intval($value)] = true;
            }
        }

        // when called with focus param only restrict query to show just one point & its lines:
        $focusLineRestrictSQL = '';
        $focusPointRestrictSQL = '';
        if (isset($_GET['focus'])) {
            $focusPointRestrictSQL = 'AND pnt_id='.(int)$_GET['focus'];
            $focusLineRestrictSQL =  'AND line_pnt_id='.(int)$_GET['focus'];
        }

        $showZoomedArea = !isset($_GET['focus']) && isset($_GET['zoom']) && isset($_GET['bounds']);

        // First we deal with the lines:
        if ($showZoomedArea) {
            $zoom = (int)$_GET['zoom'];
            $bounds = explode(",", $_GET['bounds']);
            $minLat = (float)$bounds[0];
            $minLng = (float)$bounds[1];
            $maxLat = (float)$bounds[2];
            $maxLng = (float)$bounds[3];  
            
            //  line query for the given view:       
            $sql  = "SELECT line_id, line_pnt_id, line_kind, line_note, pldata_tozoom, pldata_points "
                    . "FROM points "
                    . "LEFT JOIN plines ON line_pnt_id=pnt_id "
                    . "LEFT JOIN pline_data ON line_id=pldata_pline_id "
                    . $perspectiveLineJoinSQL
                    . "WHERE pnt_hide=0 AND line_hide=0 AND $zoom >= pldata_fromzoom AND $zoom < pldata_tozoom "
                    . $perspectiveRestrictSQL
                    . "AND (($minLat < line_ne_lat) AND ($maxLat > line_sw_lat) AND ($minLng < line_ne_lng) AND ($maxLng > line_sw_lng)) ";            
        } else {
            // line query everything:
            $sql  = "SELECT line_id, line_pnt_id, line_kind, line_note, pldata_tozoom, pldata_points "
                    . "FROM points "
                    . "LEFT JOIN plines ON line_pnt_id=pnt_id "
                    . "LEFT JOIN pline_data ON line_id=pldata_pline_id "
                    . "WHERE pnt_hide=0 AND line_hide=0 AND pldata_tozoom=99 "
                    . $focusLineRestrictSQL;
        }

        $result = $this->db->query($sql); 

        $linedata = array();
        $lines = array();   // TODO  =ugly but fast and simple??

        while (list($line_id, $line_pnt_id, $line_kind, $line_note, $pldata_tozoom, $pldata_points) = $result->fetch_row()) {
            $requiredPointsArr[$line_pnt_id] = true;

            if (! array_key_exists($line_pnt_id, $lines)) {
                $lines[$line_pnt_id] = new Line();
            }

            $lines[$line_pnt_id]->points[] = $pldata_points;
            $lines[$line_pnt_id]->expire = $pldata_tozoom;
            $lines[$line_pnt_id]->kind = $line_kind;

        }

        // points:

        $isFlat = isset($_GET['flat']);
        $zoomRestrictSQL = $isFlat ? "" : "AND pkind_low <= $zoom ";

        if ($showZoomedArea) {
            // create SQL to assure all points related to selected lines (code above) are included:
            $additionalPointsSQL = "";
        //    foreach($requiredPointsArr as $line_pnt_id => $ignored) {
        //        $additionalPointsSQL .= " OR pnt_id=$line_pnt_id";
        //    }

            if (!empty($requiredPointsArr)) {
                $additionalPointsSQL = " OR pnt_id IN (". implode(",", array_keys($requiredPointsArr)) . ")";
            }

            $sql  = "SELECT pnt_id, pnt_name, pnt_kind, pnt_lat, pnt_lng, pnt_visible, pnt_dflt_short, pnt_promote, pkind_low, pkind_high, pkind_zindex, psum_pnt_name, psum_short, img_path, pmeta_loc_accuracy "
                    .$perspectiveSelectSQL
                    . "FROM points " 
                    . "LEFT JOIN pkinds ON pkind_id=pnt_kind "
                    . "LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1 "
                    . "LEFT JOIN images ON pil_img=img_id  AND img_hide=0 "
                    //. "LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id "
                    . "LEFT JOIN psummaries ON pnt_id=psum_pnt_id AND psum_lang='".$lng."' "
                    .$perspectivePointJoinSQL
                    . "WHERE pnt_hide=0 "
                    . "AND pnt_lat > $minLat AND pnt_lat < $maxLat AND pnt_lng > $minLng AND pnt_lng < $maxLng "
                    .$zoomRestrictSQL
                    .$perspectiveRestrictSQL
                    .$additionalPointsSQL ;
        } else {
            $sql  = "SELECT pnt_id, pnt_name, pnt_kind, pnt_lat, pnt_lng, pnt_visible, pnt_dflt_short, pnt_promote, pkind_low, pkind_high, pkind_zindex, psum_pnt_name, psum_short, img_path, pmeta_loc_accuracy "
                    . "FROM points " 
                    . "LEFT JOIN pkinds ON pkind_id=pnt_kind "
                    . "LEFT JOIN pnt_img_lnk ON pnt_id=pil_pnt AND pil_dflt=1 "
                    . "LEFT JOIN images ON pil_img=img_id AND img_hide=0 "
                    . "LEFT JOIN pmetadata ON pnt_id=pmeta_pnt_id "
                    . "LEFT JOIN psummaries ON pnt_id=psum_pnt_id AND psum_lang='".$lng."' "
                    . "WHERE pnt_hide=0 "
                    . $focusPointRestrictSQL;
        }

        $result = $this->db->query($sql); // no error handling

        if (!$result) {
            // Handle query error
            echo "{\"type\":\"FeatureCollection\",\"features\":[],\"error\":\"Database query failed\"}";
            exit;
        }

        $sepx = "";
        echo "{\"type\":\"FeatureCollection\",\"features\":[";

        while($obj = $result->fetch_object()) {

            // prepare output:
            $name = (empty($obj->psum_pnt_name) ? $obj->pnt_name : $obj->psum_pnt_name);
            $short = (empty($obj->psum_short) ? $obj->pnt_dflt_short : $obj->psum_short);
            $kind = $obj->pnt_kind;

            $fullUrl = $perspective
                ? $normalizer->idToUrl($obj->extid)
                : $this->session->getViciBase() . '/vici/' . $obj->pnt_id . '/' ;
            
            // write output:
            echo $sepx . "{\"type\":\"Feature\",";
            echo "\"id\":",$obj->pnt_id,",";
            echo "\"geometry\":{";

            $point = "\"type\":\"Point\",\"coordinates\":[" . $obj->pnt_lng . "," . $obj->pnt_lat . "]";

        if (array_key_exists($obj->pnt_id, $lines)) {
            echo "\"type\": \"GeometryCollection\",\"geometries\":[";
            echo "{" . $point . "},";
            if (count($lines[$obj->pnt_id]->points) > 1) {
                echo '{"type": "MultiLineString", "coordinates": ' . json_encode(Line::flipMultiLine($lines[$obj->pnt_id]->points)) . '}';
            } else {
                echo '{"type": "LineString", "coordinates": ' . json_encode(Line::flipLine(json_decode($lines[$obj->pnt_id]->points[0]))) . '}';
            }
            echo "]";
            } else {
                echo $point;
            }
            echo "},";


            echo "\"properties\":{";
        //    echo "\"id\":",$obj->pnt_id,",";
            echo "\"url\":\"",$fullUrl,"\",";
            echo "\"title\":",json_encode($name),",";
            echo "\"kind\":",$kind,",";
            if ($isFlat) {
                echo "\"zoomsmall\":1,";
            } else {
                echo "\"zoomsmall\":",$obj->pkind_low,",";
            }
            echo "\"zoomnormal\":",$obj->pkind_high,",";
            echo "\"zindex\":",$obj->pkind_zindex,",";
            echo "\"isvisible\":",$obj->pnt_visible,",";
            echo "\"identified\":",($obj->pmeta_loc_accuracy=='5'?"false":"true"),",";

            if (array_key_exists($obj->pnt_id, $lines)) {
                echo '"line": {';
                echo '"kind":' . Line::kindStr($lines[$obj->pnt_id]->kind) . ',';
                echo '"expire": ' . $lines[$obj->pnt_id]->expire;
                echo '},';
            }

            echo "\"summary\":",json_encode($short);
            if ($obj->img_path) {
                echo ",\"img\":", json_encode($obj->img_path);
            }
            echo "}}";
            $sepx=",";
        }
            
        echo "]}";
    }

}
