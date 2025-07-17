<?php

namespace Vici\Model\Site\Line;

use Vici\DB\DBConnector;

class LineRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function getLinesForSite(int $siteId): LineCollection
    {
        $stmt = $this->db->prepare("
            SELECT line_id, line_kind, line_owner, line_attribution, line_uploader, line_license, pldata_points 
            FROM plines 
            LEFT JOIN pline_data ON pline_data.pldata_pline_id = plines.line_id AND pline_data.pldata_tozoom = 99
            WHERE plines.line_hide=0 AND plines.line_pnt_id=:site_id"
        );

        $stmt->execute(['site_id' => $siteId]);
        $collection = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $collection[] = $this->mapRowToLine($row);
        }
        return new LineCollection($collection);
    }

    private function mapRowToLine(array $row): Line
    {
        $line = new Line();
        $line->id = (int)$row['line_id'];
        $line->kind = $row['line_kind'] ?? null;
        $line->owner = $row['line_owner'] ?? null;
        $line->attribution = $row['line_attribution'] ?? null;
        $line->uploaderId = $row['line_uploader'] ?? null;
        $line->licenseId = $row['line_license'] ?? null;
        $line->coordinates = json_decode($row['pldata_points']);
        return $line;
    }
}
    