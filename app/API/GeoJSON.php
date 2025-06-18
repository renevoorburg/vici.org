<?php

namespace Vici\API;

use PDO;
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

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $linedata = array();
        $lines = array();   // TODO  =ugly but fast and simple??

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $requiredPointsArr[$row['line_pnt_id']] = true;

            if (! array_key_exists($row['line_pnt_id'], $lines)) {
                $lines[$row['line_pnt_id']] = new Line();
            }

            $lines[$row['line_pnt_id']]->points[] = $row['pldata_points'];
            $lines[$row['line_pnt_id']]->expire = $row['pldata_tozoom'];
            $lines[$row['line_pnt_id']]->kind = $row['line_kind'];

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

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        if ($stmt->errorCode() !== '00000') {
            // Handle query error
            echo "{\"type\":\"FeatureCollection\",\"features\":[],\"error\":\"Database query failed\"}";
            exit;
        }

        $sepx = "";
        echo "{\"type\":\"FeatureCollection\",\"features\":[";

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // prepare output:
            $name = (empty($row['psum_pnt_name']) ? $row['pnt_name'] : $row['psum_pnt_name']);
            $short = (empty($row['psum_short']) ? $row['pnt_dflt_short'] : $row['psum_short']);
            $kind = $row['pnt_kind'];

            $fullUrl = $perspective
                ? $normalizer->idToUrl($row['extid'])
                : $this->session->getViciBase() . '/vici/' . $row['pnt_id'] . '/' ;
            
            // write output:
            echo $sepx . "{\"type\":\"Feature\",";
            echo "\"id\":",$row['pnt_id'],",";
            echo "\"geometry\":{";

            $point = "\"type\":\"Point\",\"coordinates\":[" . $row['pnt_lng'] . "," . $row['pnt_lat'] . "]";

        if (array_key_exists($row['pnt_id'], $lines)) {
            echo "\"type\": \"GeometryCollection\",\"geometries\":[";
            echo "{" . $point . "},";
            if (count($lines[$row['pnt_id']]->points) > 1) {
                echo '{"type": "MultiLineString", "coordinates": ' . json_encode(Line::flipMultiLine($lines[$row['pnt_id']]->points)) . '}';
            } else {
                echo '{"type": "LineString", "coordinates": ' . json_encode(Line::flipLine(json_decode($lines[$row['pnt_id']]->points[0]))) . '}';
            }
            echo "]";
            } else {
                echo $point;
            }
            echo "},";


            echo "\"properties\":{";
        //    echo "\"id\":",$row['pnt_id'],",";
            echo "\"url\":\"",$fullUrl,"\",";
            echo "\"title\":",json_encode($name),",";
            echo "\"kind\":",$kind,",";
            if ($isFlat) {
                echo "\"zoomsmall\":1,";
            } else {
                echo "\"zoomsmall\":",$row['pkind_low'],",";
            }
            echo "\"zoomnormal\":",$row['pkind_high'],",";
            echo "\"zindex\":",$row['pkind_zindex'],",";
            echo "\"isvisible\":",$row['pnt_visible'],",";
            echo "\"identified\":",($row['pmeta_loc_accuracy']=='5'?"false":"true"),",";

            if (array_key_exists($row['pnt_id'], $lines)) {
                echo '"line": {';
                echo '"kind":' . Line::kindStr($lines[$row['pnt_id']]->kind) . ',';
                echo '"expire": ' . $lines[$row['pnt_id']]->expire;
                echo '},';
            }

            echo "\"summary\":",json_encode($short);
            if ($row['img_path']) {
                echo ",\"img\":", json_encode($row['img_path']);
            }
            echo "}}";
            $sepx=",";
        }
            
        echo "]}";
    }

}
