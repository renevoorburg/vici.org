<?php

namespace Vici\API;

use PDO;
use Vici\Session\Session;
use Vici\DB\DBConnector;

class Images extends APICall
{
    private const MAX_IMAGES = 300;

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
        if (!isset($_GET['zoom'], $_GET['bounds']) || !is_string($_GET['bounds']) || !is_numeric($_GET['zoom'])) {
            echo '{"images":[]}';
            return;
        }
        $zoom = (int)$_GET['zoom'];
        $bounds = explode(',', $_GET['bounds']);
        if (count($bounds) !== 4 || count(array_filter($bounds, 'is_numeric')) !== 4) {
            echo '{"images":[]}';
            return;
        }
        $minLat = (float)$bounds[0];
        $minLng = (float)$bounds[1];
        $maxLat = (float)$bounds[2];
        $maxLng = (float)$bounds[3];

        $reqLang = $_GET['lang'] ?? '';
        $lang = in_array($reqLang, ['de', 'en', 'fr', 'nl'], true)
            ? $reqLang
            : $this->session->getLanguage();

        // same era/visibility semantics as the map filter (see Highlights.php):
        $filterSQL = '';
        $era = $_GET['era'] ?? '';
        if (($era === 'onlyContemporary') || ($era === 'contemporaryEra')) {
            $filterSQL = ' AND (p2.pnt_kind=8 OR p2.pnt_kind=19)';
        } elseif (($era === 'onlyRoman') || ($era === 'historicalEra')) {
            $filterSQL = ' AND NOT (p2.pnt_kind=8 OR p2.pnt_kind=19)';
        }
        if (($_GET['visibility'] ?? '') === 'onlyVisible') {
            $filterSQL .= ' AND (p2.pnt_visible=1)';
        }

        // pnt_img_lnk can link one image to multiple points; the derived
        // table pins each image to the lowest point id that is actually
        // inside the requested view, so slides stay unique and in-scope.
        $sql = "SELECT p.pnt_id, pnt_name, pnt_kind, s.psum_pnt_name,
                i.img_id, i.img_path,
                d.imgd_title, d.imgd_width, d.imgd_height,
                d.imgd_creator, d.imgd_ownwork,
                lic.license_abbr, acc.acc_realname
            FROM (
                SELECT pil_img AS img_id, MIN(pil_pnt) AS pnt_id
                FROM pnt_img_lnk pil
                JOIN points p2 ON p2.pnt_id = pil.pil_pnt AND p2.pnt_hide = 0
                JOIN images i2 ON i2.img_id = pil.pil_img AND i2.img_hide = 0 AND i2.img_path != ''
                LEFT JOIN pkinds k2 ON k2.pkind_id = p2.pnt_kind
                WHERE p2.pnt_lat > :minLat AND p2.pnt_lat < :maxLat
                    AND p2.pnt_lng > :minLng AND p2.pnt_lng < :maxLng
                    AND k2.pkind_low <= :zoom" . $filterSQL . "
                GROUP BY pil_img) l
            JOIN points p ON p.pnt_id = l.pnt_id
            JOIN images i ON i.img_id = l.img_id AND i.img_hide = 0 AND i.img_path != ''
            LEFT JOIN img_data d ON d.imgd_imgid = i.img_id
            LEFT JOIN licenses lic ON lic.license_id = d.imgd_license
            LEFT JOIN accounts acc ON acc.acc_id = d.imgd_uploader
            LEFT JOIN psummaries s ON p.pnt_id = s.psum_pnt_id AND s.psum_lang = :lang
            ORDER BY RAND()
            LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('lang', $lang);
        $stmt->bindValue('minLat', $minLat);
        $stmt->bindValue('maxLat', $maxLat);
        $stmt->bindValue('minLng', $minLng);
        $stmt->bindValue('maxLng', $maxLng);
        $stmt->bindValue('zoom', $zoom, PDO::PARAM_INT);
        $stmt->bindValue('limit', self::MAX_IMAGES + 1, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $truncated = count($rows) > self::MAX_IMAGES;
        if ($truncated) {
            array_pop($rows);
        }

        $images = [];
        foreach ($rows as $row) {
            $images[] = [
                'id' => (int)$row['img_id'],
                'path' => $row['img_path'],
                'w' => (int)$row['imgd_width'],
                'h' => (int)$row['imgd_height'],
                'title' => $row['imgd_title'] ?? '',
                'creator' => $row['imgd_ownwork'] ? ($row['acc_realname'] ?? '') : ($row['imgd_creator'] ?? ''),
                'license' => $row['license_abbr'] ?? '',
                'siteId' => (int)$row['pnt_id'],
                'siteTitle' => $row['psum_pnt_name'] ?: $row['pnt_name'],
                'kind' => (int)$row['pnt_kind'],
            ];
        }

        echo json_encode(
            ['images' => $images, 'truncated' => $truncated],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }
}
