<?php
/**
 * ItemHTMLParts has helpers to generate all sorts of html for the item page.
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.1
 */

require_once __DIR__ . '/classDBConnector.php';
require_once __DIR__ . '/classViciCommon.php';
require_once __DIR__ . '/classImageData.php';
require_once __DIR__ . '/classSite.php';
require_once __DIR__ . '/classSiteKinds.php';

class ItemHTMLParts
{

    /**
     * @param DBConnector $db
     * @param Site $site
     * @param Lang $lngObj
     * @return string $htmlStr formatted html of nearby sites
     */
    public static function getMetadataHTML(DBConnector $db, Site $site, Lang $lngObj)
    {
        $lf = "\n";

        $geodb = new DBConnector('GEO');

        $reverse_geocoder = new GeoCoder\Reverse($geodb, $site->getLat(), $site->getLng() );
        $image = new ImageData($site->getId());
        $markerclass = 'icon' . $site->getKind() . ' marker';
        $siteKinds = new SiteKinds($lngObj);

        $alt = $lngObj->str($siteKinds->getName($site->getKind()));

        $image->walk();
        $siteKinds = new SiteKinds($lngObj);

        $htmlStr = '';

        $htmlStr .= '<div style="display:inline-block;vertical-align:top;margin-right:8px;margin-top:3px;margin-left:-2px;">';
        $htmlStr .= '<img id="myIcon" class="' . $markerclass . '" title="' . $alt . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="/>' . $lf;
        $htmlStr .= '</div>';



        $htmlStr .= '<div style="display:inline-block;vertical-align:top;margin-right:16px;">';
        $htmlStr .= '<h3>' . $lngObj->str('Location') . ':</h3>' . $lf;
        $htmlStr .= '<ul class="simpleList" style="padding-left:0;">' . $lf;

        $htmlStr .= '<li>' . $reverse_geocoder->getCountryName($lngObj->getLang()) . ", " . $reverse_geocoder->getNearbyPlace() . '</li>' . $lf;

        $htmlStr .= '<li>geo:'.$site->getLat().','.$site->getLng().'</li>' . $lf;
        $htmlStr .= '<li>' . $lngObj->str('accuracy_' . $site->getLocationAccuracy()) . '</li>';

        $htmlStr .= '</ul>';
        $htmlStr .= '</div>';


        if ( ($site->getKind() != 8) && ($site->getKind() != 19) ) {

            $periodStr = $site->getPeriodStr();
            if ($periodStr) {
                $htmlStr .= '<div style="display:inline-block;vertical-align:top;margin-right:16px;">';
                $htmlStr .= '<h3>' . $lngObj->str('Period or year') . ':</h3>' . $lf;
                $htmlStr .= '<ul class="simpleList" style="padding-left:0;">' . $lf;
                $htmlStr .= '<li>' . $periodStr . '</li>' . $lf;
                $htmlStr .= '</ul>';
                $htmlStr .= '</div>';

            }
        }




        $htmlStr .= '<div style="display:inline-block;vertical-align:top;margin-right:16px;">';
        $htmlStr .= '<h3>' . $lngObj->str('Class') . ':</h3>' . $lf;
        $htmlStr .= '<ul class="simpleList" style="padding-left:0;">' . $lf;
        $htmlStr .= '<li>' . $lngObj->str($siteKinds->getName($site->getKind())) . '</li>' . $lf;
        if ($site->getIsVisible()) {
            $htmlStr .= '<li>' . $lngObj->str('visible') . '</li>' . $lf;
        } else {
            $htmlStr .= '<li>' . $lngObj->str('invisible') . '</li>' . $lf;
        }

        $htmlStr .= '</ul>';
        $htmlStr .= '</div>';


        $htmlStr .= '<div style="display:inline-block;vertical-align:top;margin-right:16px;">';
        $htmlStr .= '<h3>' . $lngObj->str('Identifiers') . ':</h3>' . $lf;

        $htmlStr .= '<ul class="simpleList" style="padding-left:0;">' . $lf;
        $htmlStr .= '<li>vici:place=' . $site->getId() . '</li>' . $lf;

        foreach ($site->getExtIdsObj()->getIdentifierKeys() as $key => $idKey) {
            $idUrl = $site->getExtIdsObj()->getUrl($idKey);
            if(!empty($idUrl)) {
                $htmlStr .= '<li><a href="' . $idUrl . '">' . $site->getExtIdsObj()->getTag($idKey) . '</a></li>' . $lf;
            }
        }
        $htmlStr .= '</ul>' . $lf;
        $htmlStr .= '</div>';

        return $htmlStr;
    }

    /**
     * @param DBConnector $db
     * @param Site $site
     * @param Lang $lngObj
     * @return string $htmlStr formatted html of nearby sites
     */
    public static function getRelevantMuseumsHTML(DBConnector $db, Site $site, Lang $lngObj)
    {
        if ($site->getKind() === 8) { return ''; }

        $lang = $lngObj->getLang();

        $db->query("SET @radius = 10 ,
                               @lat = ". $site->getLat() ." , 
                               @lng = ". $site->getLng() ." , 
                               @lang = '$lang';");

        $sql = "SELECT DISTINCT pnt_id, pnt_name, psum_pnt_name, pnt_kind, pnt_dflt_short, psum_short FROM points
        JOIN pmetadata ON pnt_id = pmeta_pnt_id
        JOIN pnt_img_lnk ON pil_pnt=pnt_id	
        LEFT JOIN ( SELECT * FROM psummaries WHERE psum_lang=@lang ) AS x ON pnt_id = psum_pnt_id
        JOIN pkinds ON pkind_id=pnt_kind
        WHERE pnt_kind = 8 AND pil_img IN (
		    SELECT pil_img  FROM points
		    JOIN pmetadata ON pnt_id = pmeta_pnt_id
		    JOIN pnt_img_lnk ON pil_pnt=pnt_id	
		    WHERE pnt_hide=0 AND 
		          pnt_kind != 8 AND
		          6371*acos(cos(radians(@lat))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(@lng))+sin(radians(@lat))*sin(radians(pnt_lat))) < @radius
	    ) ORDER BY acos(cos(radians(@lat))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(@lng))+sin(radians(@lat))*sin(radians(pnt_lat))) ";

        $result = $db->query($sql);
        $siteKinds = new SiteKinds($lngObj);
        $lf = "\n";
        $htmlStr = '';

        while ($item = $result->fetch_object()) {

            $summary = viciCommon::htmlentitiesVici(isset($item->psum_short) ? $item->psum_short : $item->pnt_dflt_short);
            $title = viciCommon::htmlentitiesVici(isset($item->psum_pnt_name) ? $item->psum_pnt_name : $item->pnt_name);
            $markerclass = 'icon' . $item->pnt_kind . ' marker';
            $alt = $lngObj->str($siteKinds->getName($item->pnt_kind));

            $htmlStr .= '<div class="nearRow">' . $lf;
            $htmlStr .= '<div class="nearMarkerBox nMBItempage">' . $lf;
            $htmlStr .= '<a href="'.$lngObj->langURL($lang, '/vici/' . $item->pnt_id . '/').'"><img class="' . $markerclass . '" title="' . $alt . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="/></a>' . $lf;
            $htmlStr .= '</div>' . $lf;

            $htmlStr .= '<div class="nearTextBoxFull"><h3><a href="'.$lngObj->langURL($lang, '/vici/' . $item->pnt_id . '/').'">' . $title . '</a></h3><p>' . $summary . '</p></div>' . $lf;
            $htmlStr .= '</div>' . $lf;

        }

        if (! empty($htmlStr)) {
            $htmlStr = '<h2 style="margin-top:4px;margin-bottom:10px">' . $lngObj->str('Relevant museums') . '</h2>' . $lf . $htmlStr . '<br>';
        }

        return $htmlStr;
    }



    /**
     * @param DBConnector $db
     * @param Site $site
     * @param Lang $lngObj
     * @return string $htmlStr formatted html of nearby sites
     */
    public static function getNearbyHTML(DBConnector $db, Site $site, Lang $lngObj)
    {
        $lang = $lngObj->getLang();

        $sql = "SELECT
                  pnt_id,
                  pnt_name,
                  psum_pnt_name,
                  pnt_kind,
                  pnt_dflt_short,
                  psum_short,
                  6371*acos(cos(radians(" . $site->getLat() . "))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(" . $site->getLng() . "))+sin(radians(" . $site->getLat() . "))*sin(radians(pnt_lat)))  As D
            FROM
                points
            LEFT JOIN
                pmetadata ON pnt_id = pmeta_pnt_id
            LEFT JOIN
                ( SELECT * FROM psummaries WHERE psum_lang='" . $lang . "' ) AS x ON pnt_id = psum_pnt_id
            LEFT JOIN
                pkinds ON pkind_id=pnt_kind
            WHERE
                pnt_hide=0
                AND NOT pnt_id=" . $site->getId() . "
                AND 6371*acos(cos(radians(" . $site->getLat() . "))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(" . $site->getLng() . "))+sin(radians(" . $site->getLat() . "))*sin(radians(pnt_lat))) <25
            ORDER BY
                D
            LIMIT 3";

        $result = $db->query($sql);

        $siteKinds = new SiteKinds($lngObj);

        $lf = "\n";
        $htmlStr = '';

        while ($item = $result->fetch_object()) {

            $summary = viciCommon::htmlentitiesVici(isset($item->psum_short) ? $item->psum_short : $item->pnt_dflt_short);
            $title = viciCommon::htmlentitiesVici(isset($item->psum_pnt_name) ? $item->psum_pnt_name : $item->pnt_name);
            $markerclass = 'icon' . $item->pnt_kind . ' marker';
            $alt = $lngObj->str($siteKinds->getName($item->pnt_kind));

            $distance = intval($item->D) > 0 ? ' ('.intval($item->D).' km)' : '';

            $htmlStr .= '<div class="nearRow">' . $lf;
            $htmlStr .= '<div class="nearMarkerBox nMBItempage">' . $lf;
            $htmlStr .= '<a href="'.$lngObj->langURL($lang, '/vici/' . $item->pnt_id . '/').'"><img class="' . $markerclass . '" title="' . $alt . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAP//////zCH5BAEHAAAALAAAAAABAAEAAAICRAEAOw=="/></a>' . $lf;
            $htmlStr .= '</div>' . $lf;

            $htmlStr .= '<div class="nearTextBoxFull"><h3><a href="'.$lngObj->langURL($lang, '/vici/' . $item->pnt_id . '/').'">' . $title . '</a>' . $distance . '</h3><p>' . $summary . '</p></div>' . $lf;
            $htmlStr .= '</div>' . $lf;

        }

        if ($htmlStr == '') {
            $htmlStr = '<h2 style="margin-top:4px;margin-bottom:8px"">' . $lngObj->str('Nearby') . '</h2>' . $lf;
            $htmlStr .= '<div class="notice" style="margin-bottom:16px;line-height: 1.4em;">' . $lngObj->str('No known places nearby') . '</div>';

        } else {
            $htmlStr = '<h2 style="margin-top:4px;margin-bottom:10px">' . $lngObj->str('Nearby') . '</h2>' . $lf . $htmlStr;
        }

        return $htmlStr.'<br>';
    }


    public static function getNearbyImages(
        DBConnector $db,
        $lat,
        $lng,
        $imagePathPrepend = 'https://images.vici.org/crop/w175xh175',
        $radiusKM = 9,
        $maxAmount = 30,
        Lang $lngObj,
        $excludeSite = null )
    {
        $lang = $lngObj->getLang();

        $lf = "\n";
        $htmlStr = '';
        $jsonStr = '';
        $sep = '';
        $size_wh = array();

        $restrict = "";
        if (!is_null($excludeSite)) {
            $restrict = "AND img_id NOT IN (SELECT pil_img FROM pnt_img_lnk WHERE pil_pnt=".intval($excludeSite).")";
        }

        $sql =
            "SELECT
                pnt_id, pnt_name, img_id, img_path,imgd_title, imgd_width, imgd_height, 
                6371*acos(cos(radians(".$lat."))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(".$lng."))+sin(radians(".$lat."))*sin(radians(pnt_lat))) As D
            FROM points
            JOIN pnt_img_lnk ON pnt_id=pil_pnt
            LEFT JOIN images ON pil_img=img_id AND img_hide=0
            LEFT JOIN img_data ON imgd_imgid=img_id
            WHERE
                pnt_hide=0 AND
                6371*acos(cos(radians(".$lat."))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(".$lng."))+sin(radians(".$lat."))*sin(radians(pnt_lat))) < $radiusKM
                AND img_hide != 1
                AND pnt_kind != 8
                $restrict
            ORDER BY D
        LIMIT $maxAmount";

        //echo $sql;

        $result = $db->query($sql);
        while ($row = $result->fetch_object()) {
            $htmlStr .= '<a href="'.$lngObj->langURL($lang, '/vici/'.$row->pnt_id.'/').'" target="_top" title="'.$row->pnt_name.'"><img src="'.$imagePathPrepend .$row->img_path.'" alt="'.$row->imgd_title.'"></a>';

            $size_wh = self::normalizedSizeWH($row->imgd_width, $row->imgd_height);

            $jsonStr.= $sep;
            $jsonStr.= '{ src: "https://images.vici.org/size/w'.$size_wh[0].'xh'.$size_wh[1].$row->img_path.'", ' . $lf;
            $jsonStr.= "  title: \"" . $row->imgd_title;
            $jsonStr.= '<br>&copy; {creator} {license}';
            $jsonStr.=  " [&nbsp;<a href='" . $lngObj->langURL($lang, "/image.php?id=" .$row->img_id ). "'>". $lngObj->str("more information") . '</a>&nbsp;]",' . $lf;
            $jsonStr.= '  w: '. $size_wh[0] . ',' . $lf;
            $jsonStr.= '  h: '. $size_wh[1] . '}';
            $sep = ',' . $lf;

        }
        return $htmlStr;
    }


    private static function normalizedSizeWH ($width, $height) {
        $size_wh = array();

        $max_height = 1920;
        $max_width = 2560;

        if ( ($width > $max_width) || ($height > $max_height)) {
            $factor_height = $height / $max_height;
            $factor_width = $width / $max_width;

            $factor = $factor_height > $factor_width ? $factor_height : $factor_width;
            $size_wh[0] = (int) ($width / $factor);
            $size_wh[1] = (int) ($height / $factor);

        } else {
            $size_wh[0] = $width;
            $size_wh[1] = $height;
        }

        return $size_wh;
    }

    /**
     * @param Site $site
     * @param Lang $lngObj currently not used
     * @return string
     */
    public static function getItemImagesHTML(Site $site, Lang $lngObj)
    {
        $lang = $lngObj->getLang();
        $htmlStr = '';
        $jsonStr = '';
        $sep = '';
        $lf = "\n";
        $image = new ImageData($site->getId());
        $size_wh = array();

        while ($image->walk()) {
            $id = $image->current()->getId();
            $htmlStr .= '<figure class="item" >'
                . '<a href="//vici.org/image.php?id=' . $id . '">'
                . '<img class="itemImage" src="//images.vici.org/cover/w268xh268' . $image->current()->getPath()
                . '" alt title="' . $image->current()->getTitle()
                . ' ' . $image->current()->getDescription() . '">'
                . '</a>'
                . '</figure>' ;

            $size_wh = self::normalizedSizeWH($image->current()->getWidth(), $image->current()->getHeight());

            $jsonStr.= $sep;
            $jsonStr.= '{ src: "https://images.vici.org/size/w'.$size_wh[0].'xh'.$size_wh[1].$image->current()->getPath().'", ' . $lf;
            $jsonStr.= '  msrc:   "https://images.vici.org/cover/w268xh268'.$image->current()->getPath().'", ' . $lf;
            $jsonStr.= "  title: \"" . preg_replace('/"/', '\"', $image->current()->getTitle());
            $jsonStr.= '<br>&copy; ' . $image->current()->getCreatorName(). ' ; ' .$image->current()->getLicenseAbbr();
            $jsonStr.=  " [&nbsp;<a href='" . $lngObj->langURL($lang, "/image.php?id=" .$id ). "'>". $lngObj->str("more information") . '</a>&nbsp;]",' . $lf;
            $jsonStr.= '  w: '. $size_wh[0] . ',' . $lf;
            $jsonStr.= '  h: '. $size_wh[1] . '}';
            $sep = ',' . $lf;
        }

        if ($htmlStr == '') {
            $htmlStr = '<div class="itemGallery notice noimage">'.sprintf($lngObj->str('No images available yet. Please <a href="%s">add images related to this place</a>'), $lngObj->langURL($lang,'/upload/form.php?id='.$site->getId())).'</div>';
        } else {
            $htmlStr = '<div class="itemGallery" itemscope itemtype="http://schema.org/ImageGallery">' . $htmlStr . '</div>' . $lf;
            $htmlStr.= '<script>' . $lf . 'var pswpitemsObject = [ ' . $jsonStr . ' ];' . $lf . '</script>' . $lf;
        }

        return $htmlStr;
    }

    public static function accuracyOptionList($accuracy, Lang $lngObj)
    {
        $ret = '';
        for ($i = 0; $i <= 5; $i++) {
            $ret .= '<option value="' . $i . '" ';
            if ($i == $accuracy) {
                $ret .= 'selected="selected"';
            }
            $ret .= '>';
            $ret .= $lngObj->str("accuracy_" . $i);
            $ret .= '</option>';
        }
        return $ret;
    }

    public static function extIdInputHTML($idsArr)
    {
        $ret = '';
        for ($i = 0; $i <= 10; $i++) {
            if (isset($idsArr[$i])) {
                $value = $idsArr[$i];
            } else {
                $value = '';
            };

//            $value = is_null($idsArr[$i]) ? '' : $idsArr[$i];
            $ret .= '<input id="extid' . $i . '" name="extid' . $i . '" placeholder="external URI" type="text" value="' . $value . '" /><br>';
        }
        return $ret;
    }

    public static function extIdPostArr($post) : array
    {
        $ret = array();
        for ($i = 0; $i <= 10; $i++) {
            $value = trim($post['extid' . $i]);
            if(!empty($value)) {
                $ret[$i] = $value;
            }
        }
        return $ret;
    }


    /**
     * @param $mode
     * @param $id
     * @param Lang $lngObj
     * @return string
     */
    public static function editMenuHTML($mode, $id, Lang $lngObj)
    {
        $lang = $lngObj->getLang();
        $ret = '';

        // menu 'view':
        if ($mode == 'view') {
            $ret .= '<li class="selected">';
        } else {
            $ret .= '<li class="deselected">';
        }
        if ($mode == 'new') {
            $ret .= $lngObj->str('View');
        } else {
            $ret .= '<a href="'.$lngObj->langURL($lang,'/vici/' . $id . '/').'">' . $lngObj->str('View') . '</a>';
        }
        $ret .= '</li>';

        // menu 'edit':
        if ($mode == 'edit' || $mode == 'new') {
            $ret .= '<li class="selected">';
        } else {
            $ret .= '<li class="deselected">';
        }
        $ret .= '<a href="'.$lngObj->langURL($lang,'/edit.php?id=' . $id ).'">' . $lngObj->str('Edit') . '</a>';
        $ret .= '</li>';

        // menu  'addkml':
        if ($mode == 'addkml') {
            $ret .= '<li class="selected">';
        } else {
            $ret .= '<li class="deselected">';
        }
        if ($mode == 'new') {
            $ret .= $lngObj->str('Add lines');
        } else {
            $ret .= '<a href="'.$lngObj->langURL($lang, '/upload/form.php?id=' . $id . '&format=kml').'">' . $lngObj->str('Add lines') . '</a>';
        }
        $ret .= '</li>';

        // menu  'addimage':
        if ($mode == 'addimage') {
            $ret .= '<li class="selected">';
        } else {
            $ret .= '<li class="deselected">';
        }
        if ($mode == 'new') {
            $ret .= $lngObj->str('Add images');
        } else {
            $ret .= '<a href="'.$lngObj->langURL($lang, '/upload/form.php?id=' . $id) . '">' . $lngObj->str('Add images') . '</a>';
        }
        $ret .= '</li>';

        return $ret;
    }


}