<?php
namespace Vici\Model\Site\Locale;

use Vici\DB\DBConnector;

class LocaleRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    /**
     * Haal alle SiteLocale-objecten die bij een site horen op
     */
    public function getBySiteId(int $siteId): array
    {
        $stmt = $this->db->prepare("SELECT t.ptxt_lang AS language, s.psum_pnt_name AS title, s.psum_short AS summary, t.ptxt_full AS description 
        FROM points p
        LEFT JOIN ptexts t ON t.ptxt_pnt_id = p.pnt_id 
        LEFT JOIN psummaries s ON s.psum_pnt_id = p.pnt_id AND t.ptxt_lang = s.psum_lang
        WHERE p.pnt_id = :id");
        $stmt->execute(['id' => $siteId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $locales = [];
        foreach ($rows as $row) {
            $locale = new Locale();
            $locale->language = $row['language'];
            $locale->title = $row['title'] ?? '';
            $locale->summary = $row['summary'] ?? '';
            $locale->description = $row['description'] ?? '';
            $locales[$row['language']] = $locale;
        }
        return $locales;
    }
}
