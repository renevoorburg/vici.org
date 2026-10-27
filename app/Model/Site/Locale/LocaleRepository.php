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
        $stmt = $this->db->prepare("SELECT t.ptxt_lang AS language, s.psum_pnt_name AS title, s.psum_short AS summary, t.ptxt_full AS description,
            t.ptxt_editor AS editorId, e.acc_realname AS editorName, t.ptxt_edit_date AS editDate
        FROM points p
        LEFT JOIN ptexts t ON t.ptxt_pnt_id = p.pnt_id
        LEFT JOIN accounts e ON e.acc_id = t.ptxt_editor
        LEFT JOIN psummaries s ON s.psum_pnt_id = p.pnt_id AND t.ptxt_lang = s.psum_lang
        WHERE p.pnt_id = :id");
        $stmt->execute(['id' => $siteId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $locales = [];
        foreach ($rows as $row) {
            $lang = $row['language'] ?? 'en';
            $locale = new Locale();
            $locale->language = $lang;
            $locale->title = $row['title'] ?? '';
            $locale->summary = $row['summary'] ?? '';
            $locale->description = $row['description'] ?? '';
            $locale->editorId = isset($row['editorId']) ? (int)$row['editorId'] : null;
            $locale->editorName = $row['editorName'] ?? null;
            $locale->editDate = $row['editDate'] ?? null;
            $locale->translatedTitle = $row['title'] !== null ? $row['title'] : null;
            $locale->translatedSummary = $row['summary'] !== null ? $row['summary'] : null;
            $locales[$lang] = $locale;
        }

        // summaries may exist for languages that have no ptexts row; merge those in
        $stmt = $this->db->prepare("SELECT psum_lang AS language, psum_pnt_name AS title, psum_short AS summary
            FROM psummaries WHERE psum_pnt_id = :id");
        $stmt->execute(['id' => $siteId]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $lang = $row['language'];
            if (isset($locales[$lang])) {
                $locale = $locales[$lang];
            } else {
                $locale = new Locale();
                $locale->language = $lang;
                $locale->title = '';
                $locale->summary = '';
                $locale->description = '';
                $locales[$lang] = $locale;
            }
            $locale->translatedTitle = $row['title'] !== null ? $row['title'] : $locale->translatedTitle;
            $locale->translatedSummary = $row['summary'] !== null ? $row['summary'] : $locale->translatedSummary;
        }

        return $locales;
    }
}
