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
        $stmt = $this->db->prepare(
            'SELECT line_id, line_kind, line_owner, line_attribution, line_uploader, line_license FROM plines WHERE line_hide=0 AND line_pnt_id=:site_id'
        );
        $stmt->execute(['site_id' => $siteId]);
        $collection = new LineCollection();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $line = new Line();
            $line->id = (int)$row['line_id'];
            $line->kind = $row['line_kind'] ?? null;
            $line->owner = $row['line_owner'] ?? null;
            $line->attribution = $row['line_attribution'] ?? null;
            $line->uploaderId = $row['line_uploader'] ?? null;
            $line->licenseId = $row['line_license'] ?? null;
            $collection[] = $line;
        }
        return $collection;
    }
}
