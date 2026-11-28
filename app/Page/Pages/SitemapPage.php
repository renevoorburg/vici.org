<?php

namespace Vici\Page\Pages;

use PDO;
use Vici\Session\Session;
use Vici\DB\DBConnector;

/**
 * Curated XML sitemap. Contains all content-rich items (at least one visible
 * image or a real description) plus a daily rotating slice of the bare
 * remainder (1/ROTATION_DAYS by id modulo), so the long tail is still
 * discovered over time. Regenerated at most once per CACHE_TTL seconds and
 * served from a file cache.
 */
class SitemapPage
{
    private const CACHE_TTL = 86400;
    private const ROTATION_DAYS = 30;
    private const MIN_TEXT_LENGTH = 200;

    private DBConnector $db;
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->db = $session->getDBConnector();
    }

    public function display(): void
    {
        $cacheFile = sys_get_temp_dir() . '/vici_sitemap.xml';

        if (!is_file($cacheFile) || filemtime($cacheFile) < time() - self::CACHE_TTL) {
            $tmp = $cacheFile . '.' . getmypid();
            file_put_contents($tmp, $this->generate());
            rename($tmp, $cacheFile);
        }

        header('Content-Type: application/xml; charset=UTF-8');
        header('Content-Length: ' . filesize($cacheFile));
        readfile($cacheFile);
    }

    private function generate(): string
    {
        $rotation = self::ROTATION_DAYS;
        $minLen = self::MIN_TEXT_LENGTH;

        $sql = "SELECT p.pnt_id,
                       DATE(GREATEST(COALESCE(pm.pmeta_edit_date, '1970-01-01'),
                                     COALESCE(tx.txt_edit, '1970-01-01'))) AS lastmod
                FROM points p
                LEFT JOIN pmetadata pm ON pm.pmeta_pnt_id = p.pnt_id
                LEFT JOIN (SELECT pil_pnt, COUNT(*) AS img_count
                           FROM pnt_img_lnk JOIN images ON img_id = pil_img AND img_hide = 0
                           GROUP BY pil_pnt) ic ON ic.pil_pnt = p.pnt_id
                LEFT JOIN (SELECT ptxt_pnt_id, MAX(ptxt_edit_date) AS txt_edit,
                                  MAX(CHAR_LENGTH(ptxt_full)) AS txt_maxlen
                           FROM ptexts GROUP BY ptxt_pnt_id) tx ON tx.ptxt_pnt_id = p.pnt_id
                WHERE p.pnt_hide = 0
                  AND (ic.img_count IS NOT NULL
                       OR tx.txt_maxlen > $minLen
                       OR MOD(p.pnt_id, $rotation) = MOD(DAYOFYEAR(CURDATE()), $rotation))
                ORDER BY p.pnt_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $base = $this->session->getViciBase();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= $this->hubEntries($base);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $xml .= '<url><loc>' . $base . '/vici/' . (int)$row['pnt_id'] . '</loc>';
            if ($row['lastmod'] !== '1970-01-01') {
                $xml .= '<lastmod>' . $row['lastmod'] . '</lastmod>';
            }
            $xml .= "</url>\n";
        }
        return $xml . '</urlset>' . "\n";
    }

    /**
     * The home page and the additions/changes hubs, with lastmod derived
     * from the actual newest create/edit stamps in the database.
     */
    private function hubEntries(string $base): string
    {
        $stmt = $this->db->query('SELECT DATE(MAX(pmeta_create_date)) AS added, DATE(MAX(pmeta_edit_date)) AS changed FROM pmetadata');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        $added = $row['added'] ?? null;
        $changed = $row['changed'] ?? null;
        $home = max((string)$added, (string)$changed);

        $xml = '';
        foreach (['/' => $home, '/additions' => $added, '/changes' => $changed] as $path => $lastmod) {
            $xml .= '<url><loc>' . $base . $path . '</loc>';
            if ($lastmod) {
                $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            }
            $xml .= "</url>\n";
        }
        return $xml;
    }
}
